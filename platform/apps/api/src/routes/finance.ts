import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  FinanceListQuerySchema,
  FxRateWriteSchema,
  JournalIdParams,
  PaymentRequestIdParams,
  PaymentRequestWriteSchema,
  ReconciliationWriteSchema,
  ReconstructionWriteSchema,
  ReverseJournalSchema,
  WalletDepositWriteSchema,
  WalletIdParams,
} from '@manpower/shared';
import type {
  ApiResponse,
  FxRateRecord,
  JournalRecord,
  PaymentRequestListResult,
  PaymentRequestRecord,
  QuarantineRecord,
  ReconciliationResult,
  ReconstructionResult,
  WalletDepositRecord,
  WalletListResult,
  WalletRecord,
} from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { actorFromAuth, assertCandidateAccess } from '../auth/candidate-access.js';
import { asMoney, asRate, billTypeCodeForTitle, LEGACY_BILL_CODE_101, PANELTY_CODE, PANELTY_TITLE, w1BaseAmount } from '../finance/money.js';
import { assertWalletScope, isStaffFinance, walletOwnerWhere } from '../finance/access.js';
import { requireAdministrativeRate } from '../finance/fx.js';
import {
  counterpartLine,
  lockWalletAccounts,
  POSTING_ISOLATION,
  postJournal,
  reverseJournal,
  walletLiabilityLine,
} from '../finance/posting.js';
import { importHistoricalPaymentRequests, reconstructQualifyingPayments } from '../finance/reconstruction.js';
import { runReconciliation } from '../finance/reconciliation.js';
import {
  toDepositRecord,
  toFxRecord,
  toJournalRecord,
  toPaymentRequestRecord,
  toQuarantineRecord,
  walletWithBalance,
} from '../finance/serialize.js';
import {
  availableEur,
  ensureWalletForAgent,
  ensureWalletForSubAgent,
} from '../finance/wallets.js';
import { isUniqueConflict } from '../training/util.js';

export const financeRouter: ExpressRouter = Router();
financeRouter.use(requireAuth);

financeRouter.get('/wallets', requirePermission('finance.wallet.read'), async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const actor = req.auth.user;
  const wallets = await prisma.wallet.findMany({ orderBy: { createdAt: 'desc' }, take: 100 });
  const scoped = wallets.filter((wallet) => {
    try {
      assertWalletScope(actor, wallet);
      return true;
    } catch {
      return false;
    }
  });
  const items = await Promise.all(scoped.map((wallet) => walletWithBalance(prisma, wallet)));
  const body: ApiResponse<WalletListResult> = { success: true, data: { items } };
  res.status(200).json(body);
});

financeRouter.post(
  '/wallets/ensure',
  requireCsrf,
  requirePermission('finance.wallet.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const user = req.auth.user;
    let wallet;
    if (user.bindings?.subAgentId && user.roles.includes('sub_agent')) {
      wallet = await ensureWalletForSubAgent(prisma, user.bindings.subAgentId);
    } else if (user.bindings?.agentId && user.roles.includes('agent')) {
      wallet = await ensureWalletForAgent(prisma, user.bindings.agentId);
    } else if (typeof req.body?.agentId === 'string' && (user.roles.includes('owner') || user.roles.includes('administrator') || user.roles.includes('employee') || user.roles.includes('super_admin'))) {
      wallet = await ensureWalletForAgent(prisma, req.body.agentId as string);
    } else if (typeof req.body?.subAgentId === 'string' && (user.roles.includes('owner') || user.roles.includes('administrator') || user.roles.includes('employee') || user.roles.includes('super_admin'))) {
      wallet = await ensureWalletForSubAgent(prisma, req.body.subAgentId as string);
    } else {
      throw AppError.badRequest('No in-scope agent or sub-agent wallet to ensure');
    }
    const record = await walletWithBalance(prisma, wallet);
    const body: ApiResponse<WalletRecord> = { success: true, data: record };
    res.status(201).json(body);
  }
);

financeRouter.get('/wallets/:id', requirePermission('finance.wallet.read'), async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const { id } = WalletIdParams.parse(req.params);
  const wallet = await prisma.wallet.findUnique({ where: { id } });
  if (!wallet) throw AppError.notFound('Wallet');
  assertWalletScope(req.auth.user, wallet);
  const record = await walletWithBalance(prisma, wallet);
  const journals = await prisma.journalEntry.findMany({
    where: { lines: { some: { walletAccount: { walletId: id } } } },
    include: { lines: { orderBy: { lineNo: 'asc' } } },
    orderBy: { postedAt: 'desc' },
    take: 50,
  });
  const body: ApiResponse<{ wallet: WalletRecord; journals: JournalRecord[] }> = {
    success: true,
    data: { wallet: record, journals: journals.map(toJournalRecord) },
  };
  res.status(200).json(body);
});

financeRouter.get(
  '/payment-requests',
  requirePermission('finance.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const query = FinanceListQuerySchema.parse(req.query);
    const where: Prisma.PaymentRequestWhereInput = {
      ...(query.status ? { status: query.status } : {}),
    };
    if (query.walletId) {
      const wallet = await prisma.wallet.findUnique({ where: { id: query.walletId } });
      if (!wallet) throw AppError.notFound('Wallet');
      assertWalletScope(req.auth.user, wallet);
      where.walletId = query.walletId;
    } else if (!isStaffFinance(req.auth.user)) {
      const scoped = await prisma.wallet.findMany({
        where: walletOwnerWhere(req.auth.user),
        select: { id: true },
      });
      where.walletId = { in: scoped.map((wallet) => wallet.id) };
    }
    const items = await prisma.paymentRequest.findMany({
      where,
      orderBy: { createdAt: 'desc' },
      take: query.limit,
    });
    const body: ApiResponse<PaymentRequestListResult> = {
      success: true,
      data: { items: items.map(toPaymentRequestRecord) },
    };
    res.status(200).json(body);
  }
);

financeRouter.post(
  '/payment-requests',
  requireCsrf,
  requirePermission('finance.payment_request.create'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const input = PaymentRequestWriteSchema.parse(req.body);
    const candidate = await prisma.candidate.findUnique({
      where: { id: input.candidateId },
      select: { id: true, agentId: true, subAgentId: true, agencierId: true, companierId: true },
    });
    if (!candidate) throw AppError.notFound('Candidate');
    assertCandidateAccess(actorFromAuth(req.auth.user), candidate);

    let walletId = input.walletId;
    if (!walletId) {
      if (req.auth.user.bindings?.subAgentId && req.auth.user.roles.includes('sub_agent')) {
        walletId = (await ensureWalletForSubAgent(prisma, req.auth.user.bindings.subAgentId)).id;
      } else if (req.auth.user.bindings?.agentId) {
        walletId = (await ensureWalletForAgent(prisma, req.auth.user.bindings.agentId)).id;
      } else if (candidate.agentId) {
        const agent = await prisma.agent.findUnique({ where: { sourceLegacyId: candidate.agentId } });
        if (!agent) throw AppError.badRequest('Candidate agent has no target wallet owner');
        walletId = (await ensureWalletForAgent(prisma, agent.id)).id;
      } else {
        throw AppError.badRequest('Wallet is required');
      }
    }
    const wallet = await prisma.wallet.findUnique({ where: { id: walletId } });
    if (!wallet) throw AppError.notFound('Wallet');
    assertWalletScope(req.auth.user, wallet);

    const billTypeCode = billTypeCodeForTitle(input.billTitle);
    const legacyBillCode =
      input.legacyBillCode === LEGACY_BILL_CODE_101 ? LEGACY_BILL_CODE_101 : input.legacyBillCode ?? null;
    if (legacyBillCode === LEGACY_BILL_CODE_101 && billTypeCode === PANELTY_CODE) {
      throw AppError.badRequest('bill_code 101 cannot map to Panelty Fee / 100');
    }

    try {
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.paymentRequest.create({
          data: {
            candidateId: candidate.id,
            walletId: wallet.id,
            amount: asMoney(input.amount),
            billTitle: input.billTitle,
            billTypeCode,
            legacyBillCode,
            createdByUserId: actorUserId,
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.PAYMENT_REQUEST_CREATED,
            actorUserId,
            targetType: 'payment_request',
            targetId: row.id,
            metadata: { billTitle: row.billTitle, status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<PaymentRequestRecord> = { success: true, data: toPaymentRequestRecord(created) };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A pending request already exists for this candidate and bill title');
      }
      throw error;
    }
  }
);

financeRouter.post(
  '/payment-requests/:id/approve',
  requireCsrf,
  requirePermission('finance.payment_request.approve'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const { id } = PaymentRequestIdParams.parse(req.params);
    const result = await prisma.$transaction(
      async (tx) => {
        await tx.$queryRaw`SELECT id FROM finance.payment_requests WHERE id = ${id}::uuid FOR UPDATE`;
        const requestRow = await tx.paymentRequest.findUnique({ where: { id } });
        if (!requestRow) throw AppError.notFound('Payment request');
        if (requestRow.status === 'A' && requestRow.journalId) {
          const existing = await tx.journalEntry.findUnique({
            where: { id: requestRow.journalId },
            include: { lines: { orderBy: { lineNo: 'asc' } } },
          });
          if (existing) return { request: requestRow, journal: existing };
        }
        if (requestRow.status !== 'P') throw AppError.conflict('Payment request is not pending');
        await lockWalletAccounts(tx, requestRow.walletId);
        const fee = asMoney(requestRow.amount);
        const converted = w1BaseAmount(fee, 'EUR', asMoney(1));
        const available = await availableEur(tx, requestRow.walletId);
        if (available.lt(converted.baseAmount)) {
          throw AppError.conflict('Insufficient available EUR balance');
        }
        const walletLine = await walletLiabilityLine(tx, requestRow.walletId, 'EUR', 'DEBIT', fee, converted);
        const income = counterpartLine('FEE_INCOME', 'CREDIT', 'EUR', fee, converted);
        income.billTitle = requestRow.billTitle;
        income.billTypeCode = requestRow.billTitle === PANELTY_TITLE ? PANELTY_CODE : requestRow.billTypeCode;
        const journal = await postJournal(tx, {
          entryType: 'FEE_APPROVAL',
          sourceType: 'payment_request',
          sourceId: requestRow.id,
          idempotencyKey: `fee-approve:${requestRow.id}`,
          actorUserId,
          approvedByUserId: actorUserId,
          paymentRequestId: requestRow.id,
          walletId: requestRow.walletId,
          lines: [walletLine, income],
          request: req,
          auditEvent: AUDIT_EVENTS.JOURNAL_POSTED,
        });
        const updated = await tx.paymentRequest.update({
          where: { id: requestRow.id },
          data: { status: 'A', journalId: journal.id },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.PAYMENT_REQUEST_APPROVED,
            actorUserId,
            targetType: 'payment_request',
            targetId: updated.id,
            metadata: { journalId: journal.id, billTitle: updated.billTitle },
            request: req,
          },
          tx
        );
        const loaded = await tx.journalEntry.findUniqueOrThrow({
          where: { id: journal.id },
          include: { lines: { orderBy: { lineNo: 'asc' } } },
        });
        return { request: updated, journal: loaded };
      },
      { isolationLevel: POSTING_ISOLATION }
    );
    const body: ApiResponse<{ request: PaymentRequestRecord; journal: JournalRecord }> = {
      success: true,
      data: { request: toPaymentRequestRecord(result.request), journal: toJournalRecord(result.journal) },
    };
    res.status(200).json(body);
  }
);

financeRouter.post(
  '/payment-requests/:id/reject',
  requireCsrf,
  requirePermission('finance.payment_request.reject'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const { id } = PaymentRequestIdParams.parse(req.params);
    const result = await prisma.$transaction(
      async (tx) => {
        await tx.$queryRaw`SELECT id FROM finance.payment_requests WHERE id = ${id}::uuid FOR UPDATE`;
        const requestRow = await tx.paymentRequest.findUnique({ where: { id } });
        if (!requestRow) throw AppError.notFound('Payment request');
        if (requestRow.status !== 'P') throw AppError.conflict('Payment request is not pending');
        const zero = asMoney(0);
        const converted = w1BaseAmount(zero, 'EUR', asMoney(1));
        const walletLine = await walletLiabilityLine(tx, requestRow.walletId, 'EUR', 'DEBIT', zero, converted);
        const income = counterpartLine('FEE_INCOME', 'CREDIT', 'EUR', zero, converted);
        const journal = await postJournal(tx, {
          entryType: 'FEE_REJECTION_ZERO',
          sourceType: 'payment_request',
          sourceId: requestRow.id,
          idempotencyKey: `fee-reject:${requestRow.id}`,
          actorUserId,
          paymentRequestId: requestRow.id,
          walletId: requestRow.walletId,
          lines: [walletLine, income],
          request: req,
          auditEvent: AUDIT_EVENTS.JOURNAL_POSTED,
        });
        const updated = await tx.paymentRequest.update({
          where: { id: requestRow.id },
          data: { status: 'R', journalId: journal.id },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.PAYMENT_REQUEST_REJECTED,
            actorUserId,
            targetType: 'payment_request',
            targetId: updated.id,
            metadata: { journalId: journal.id },
            request: req,
          },
          tx
        );
        const loaded = await tx.journalEntry.findUniqueOrThrow({
          where: { id: journal.id },
          include: { lines: { orderBy: { lineNo: 'asc' } } },
        });
        return { request: updated, journal: loaded };
      },
      { isolationLevel: POSTING_ISOLATION }
    );
    const body: ApiResponse<{ request: PaymentRequestRecord; journal: JournalRecord }> = {
      success: true,
      data: { request: toPaymentRequestRecord(result.request), journal: toJournalRecord(result.journal) },
    };
    res.status(200).json(body);
  }
);

financeRouter.post(
  '/deposits',
  requireCsrf,
  requirePermission('finance.payment_request.create'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const input = WalletDepositWriteSchema.parse(req.body);
    const wallet = await prisma.wallet.findUnique({ where: { id: input.walletId } });
    if (!wallet) throw AppError.notFound('Wallet');
    assertWalletScope(req.auth.user, wallet);
    const created = await prisma.$transaction(async (tx) => {
      const row = await tx.walletDeposit.create({
        data: {
          walletId: wallet.id,
          amount: asMoney(input.amount),
          currencyCode: input.currencyCode,
          fxRateEntryId: input.fxRateEntryId ?? null,
          createdByUserId: actorUserId,
        },
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.WALLET_DEPOSIT_CREATED,
          actorUserId,
          targetType: 'wallet_deposit',
          targetId: row.id,
          request: req,
        },
        tx
      );
      return row;
    });
    const body: ApiResponse<WalletDepositRecord> = { success: true, data: toDepositRecord(created) };
    res.status(201).json(body);
  }
);

financeRouter.post(
  '/deposits/:id/approve',
  requireCsrf,
  requirePermission('finance.payment_request.approve'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const { id } = PaymentRequestIdParams.parse(req.params);
    const result = await prisma.$transaction(
      async (tx) => {
        const deposit = await tx.walletDeposit.findUnique({ where: { id } });
        if (!deposit) throw AppError.notFound('Wallet deposit');
        if (deposit.status === 'A' && deposit.journalId) {
          const existing = await tx.journalEntry.findUnique({
            where: { id: deposit.journalId },
            include: { lines: { orderBy: { lineNo: 'asc' } } },
          });
          if (existing) return { deposit, journal: existing };
        }
        if (deposit.status !== 'P') throw AppError.conflict('Deposit is not pending');
        await lockWalletAccounts(tx, deposit.walletId);
        const amount = asMoney(deposit.amount);
        const currency = deposit.currencyCode === 'EUR' ? 'EUR' : 'BDT';
        const rate = await requireAdministrativeRate(
          tx,
          currency,
          'EUR',
          new Date(),
          deposit.fxRateEntryId
        );
        const converted = w1BaseAmount(amount, currency, rate.rate);
        const walletLine = await walletLiabilityLine(tx, deposit.walletId, currency, 'CREDIT', amount, converted);
        const clearing = counterpartLine('DEPOSIT_CLEARING', 'DEBIT', currency, amount, converted);
        const journal = await postJournal(tx, {
          entryType: 'WALLET_DEPOSIT',
          sourceType: 'wallet_deposit',
          sourceId: deposit.id,
          idempotencyKey: `deposit-approve:${deposit.id}`,
          actorUserId,
          approvedByUserId: actorUserId,
          walletDepositId: deposit.id,
          fxRateEntryId: rate.id,
          walletId: deposit.walletId,
          lines: [clearing, walletLine],
          request: req,
          auditEvent: AUDIT_EVENTS.JOURNAL_POSTED,
        });
        const updated = await tx.walletDeposit.update({
          where: { id: deposit.id },
          data: { status: 'A', journalId: journal.id, fxRate: converted.fxRate, fxRateEntryId: rate.id },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.WALLET_DEPOSIT_APPROVED,
            actorUserId,
            targetType: 'wallet_deposit',
            targetId: updated.id,
            metadata: { journalId: journal.id },
            request: req,
          },
          tx
        );
        const loaded = await tx.journalEntry.findUniqueOrThrow({
          where: { id: journal.id },
          include: { lines: { orderBy: { lineNo: 'asc' } } },
        });
        return { deposit: updated, journal: loaded };
      },
      { isolationLevel: POSTING_ISOLATION }
    );
    const body: ApiResponse<{ deposit: WalletDepositRecord; journal: JournalRecord }> = {
      success: true,
      data: { deposit: toDepositRecord(result.deposit), journal: toJournalRecord(result.journal) },
    };
    res.status(200).json(body);
  }
);

financeRouter.get('/journals/:id', requirePermission('finance.journal.read'), async (req: Request, res: Response) => {
  const { id } = JournalIdParams.parse(req.params);
  const journal = await prisma.journalEntry.findUnique({
    where: { id },
    include: { lines: { orderBy: { lineNo: 'asc' } } },
  });
  if (!journal) throw AppError.notFound('Journal');
  const body: ApiResponse<JournalRecord> = { success: true, data: toJournalRecord(journal) };
  res.status(200).json(body);
});

financeRouter.post(
  '/journals/:id/reverse',
  requireCsrf,
  requirePermission('finance.administration'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const { id } = JournalIdParams.parse(req.params);
    const { reason } = ReverseJournalSchema.parse(req.body);
    const reversal = await prisma.$transaction(
      async (tx) => reverseJournal(tx, id, actorUserId, reason, req),
      { isolationLevel: POSTING_ISOLATION }
    );
    const loaded = await prisma.journalEntry.findUniqueOrThrow({
      where: { id: reversal.id },
      include: { lines: { orderBy: { lineNo: 'asc' } } },
    });
    const body: ApiResponse<JournalRecord> = { success: true, data: toJournalRecord(loaded) };
    res.status(201).json(body);
  }
);

financeRouter.get('/fx-rates', requirePermission('finance.read'), async (_req: Request, res: Response) => {
  const items = await prisma.fxRateEntry.findMany({ orderBy: { effectiveAt: 'desc' }, take: 50 });
  const body: ApiResponse<{ items: FxRateRecord[] }> = { success: true, data: { items: items.map(toFxRecord) } };
  res.status(200).json(body);
});

financeRouter.post(
  '/fx-rates',
  requireCsrf,
  requirePermission('finance.fx_rate.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const input = FxRateWriteSchema.parse(req.body);
    if (input.fromCurrency === input.toCurrency && input.rate !== '1') {
      throw AppError.badRequest('Identity FX pair must use rate 1');
    }
    const created = await prisma.$transaction(async (tx) => {
      const row = await tx.fxRateEntry.create({
        data: {
          fromCurrency: input.fromCurrency,
          toCurrency: input.toCurrency,
          rate: asRate(input.rate),
          effectiveAt: input.effectiveAt ? new Date(input.effectiveAt) : new Date(),
          enteredByUserId: actorUserId,
        },
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.FX_RATE_ENTERED,
          actorUserId,
          targetType: 'fx_rate_entry',
          targetId: row.id,
          metadata: { rate: row.rate.toFixed(), from: row.fromCurrency, to: row.toCurrency },
          request: req,
        },
        tx
      );
      return row;
    });
    const body: ApiResponse<FxRateRecord> = { success: true, data: toFxRecord(created) };
    res.status(201).json(body);
  }
);

financeRouter.post(
  '/reconstruction',
  requireCsrf,
  requirePermission('finance.administration'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = ReconstructionWriteSchema.parse(req.body);
    if (input.payments.length === 0 && input.requests.length === 0) {
      throw AppError.badRequest('Reconstruction requires staged payments and/or payment requests');
    }
    const result = await reconstructQualifyingPayments(
      prisma,
      input.payments,
      input.migrationRunId,
      req.auth.user.id
    );
    const imported = await importHistoricalPaymentRequests(
      prisma,
      input.requests,
      input.migrationRunId,
      req.auth.user.id
    );
    result.quarantined += imported.quarantined;
    result.requestsImported = imported.imported;
    const body: ApiResponse<ReconstructionResult> = { success: true, data: result };
    res.status(200).json(body);
  }
);

financeRouter.get('/quarantine', requirePermission('finance.reconciliation.read'), async (req: Request, res: Response) => {
  const query = FinanceListQuerySchema.parse(req.query);
  const items = await prisma.financeQuarantine.findMany({
    orderBy: { createdAt: 'desc' },
    take: query.limit,
  });
  const body: ApiResponse<{ items: QuarantineRecord[] }> = {
    success: true,
    data: { items: items.map(toQuarantineRecord) },
  };
  res.status(200).json(body);
});

financeRouter.post(
  '/reconciliation',
  requireCsrf,
  requirePermission('finance.reconciliation.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = ReconciliationWriteSchema.parse(req.body);
    const { run, gates, passed } = await runReconciliation(prisma, input.migrationRunId);
    await writeAuditEvent({
      eventType: AUDIT_EVENTS.RECONCILIATION_COMPLETED,
      actorUserId: req.auth.user.id,
      targetType: 'reconciliation_run',
      targetId: run.id,
      metadata: { passed: passed ? 'true' : 'false', migrationRunId: input.migrationRunId },
      request: req,
    });
    const body: ApiResponse<ReconciliationResult> = {
      success: true,
      data: { id: run.id, migrationRunId: run.migrationRunId, status: run.status, gates, passed },
    };
    res.status(200).json(body);
  }
);
