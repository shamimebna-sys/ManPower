import type { AccountType, JournalEntryType, JournalSide, Prisma, PrismaClient } from '@prisma/client';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import type { Request } from 'express';
import { asMoney, linesBalance, type IsoCurrency, type W1Converted } from './money.js';
import { lockWalletAccounts, refreshProjection, walletAccount } from './wallets.js';
import { isUniqueConflict } from '../training/util.js';

export const POSTING_ISOLATION = 'Serializable' as const;

export type Db = PrismaClient | Prisma.TransactionClient;

export type LineDraft = {
  accountType: AccountType;
  side: JournalSide;
  currency: IsoCurrency;
  amount: Prisma.Decimal;
  fxRate: Prisma.Decimal;
  baseAmount: Prisma.Decimal;
  fxDirection: string;
  walletAccountId?: string | null | undefined;
  billTypeCode?: string | null | undefined;
  billTitle?: string | null | undefined;
  sourceType?: string | null | undefined;
  sourceId?: string | null | undefined;
};

export type PostJournalInput = {
  entryType: JournalEntryType;
  sourceType: string;
  sourceId: string;
  idempotencyKey: string;
  reference?: string | null | undefined;
  actorUserId?: string | null | undefined;
  approvedByUserId?: string | null | undefined;
  paymentRequestId?: string | null | undefined;
  walletDepositId?: string | null | undefined;
  fxRateEntryId?: string | null | undefined;
  reversesJournalId?: string | null | undefined;
  correlationId?: string | null | undefined;
  walletId?: string | null | undefined;
  lines: LineDraft[];
  request?: Request | undefined;
  auditEvent: string;
};

export async function postJournal(db: Db, input: PostJournalInput) {
  if (!linesBalance(input.lines)) {
    throw AppError.badRequest('Journal lines must balance in EUR base_amount and include at least two lines');
  }
  const existing = await db.journalEntry.findUnique({ where: { idempotencyKey: input.idempotencyKey } });
  if (existing) return existing;

  try {
    const accounts = await db.financeAccount.findMany();
    const accountByType = new Map(accounts.map((account) => [account.accountType, account.id]));

    const header = await db.journalEntry.create({
      data: {
        status: 'POSTED',
        entryType: input.entryType,
        sourceType: input.sourceType,
        sourceId: input.sourceId,
        reference: input.reference ?? null,
        createdByUserId: input.actorUserId ?? null,
        approvedByUserId: input.approvedByUserId ?? null,
        idempotencyKey: input.idempotencyKey,
        paymentRequestId: input.paymentRequestId ?? null,
        walletDepositId: input.walletDepositId ?? null,
        fxRateEntryId: input.fxRateEntryId ?? null,
        reversesJournalId: input.reversesJournalId ?? null,
        correlationId: input.correlationId ?? null,
        lines: {
          create: input.lines.map((line, index) => ({
            lineNo: index + 1,
            accountType: line.accountType,
            accountId: accountByType.get(line.accountType) ?? null,
            walletAccountId: line.walletAccountId ?? null,
            side: line.side,
            currencyCode: line.currency,
            amount: line.amount,
            fxRate: line.fxRate,
            baseAmount: line.baseAmount,
            fxDirection: line.fxDirection,
            billTypeCode: line.billTypeCode ?? null,
            billTitle: line.billTitle ?? null,
            sourceType: line.sourceType ?? null,
            sourceId: line.sourceId ?? null,
          })),
        },
      },
    });

    if (input.walletId) {
      await refreshProjection(db, input.walletId);
    }

    await writeAuditEvent(
      {
        eventType: input.auditEvent,
        actorUserId: input.actorUserId ?? undefined,
        targetType: 'journal',
        targetId: header.id,
        metadata: { entryType: input.entryType, sourceType: input.sourceType, sourceId: input.sourceId },
        request: input.request,
      },
      db
    );
    return header;
  } catch (error) {
    if (isUniqueConflict(error)) {
      const raced = await db.journalEntry.findUnique({ where: { idempotencyKey: input.idempotencyKey } });
      if (raced) return raced;
      if (input.paymentRequestId) {
        const byRequest = await db.journalEntry.findFirst({
          where: { paymentRequestId: input.paymentRequestId, entryType: input.entryType },
        });
        if (byRequest) return byRequest;
      }
    }
    throw error;
  }
}

export async function reverseJournal(
  db: Db,
  journalId: string,
  actorUserId: string,
  reason: string,
  request?: Request
) {
  const original = await db.journalEntry.findUnique({
    where: { id: journalId },
    include: { lines: { orderBy: { lineNo: 'asc' } } },
  });
  if (!original) throw AppError.notFound('Journal');
  if (original.status === 'REVERSED' && original.reversedByJournalId) {
    const existing = await db.journalEntry.findUnique({ where: { id: original.reversedByJournalId } });
    if (existing) return existing;
  }
  const reversal = await postJournal(db, {
    entryType: 'REVERSAL',
    sourceType: 'reversal',
    sourceId: original.id,
    idempotencyKey: `reversal:${original.id}`,
    actorUserId,
    approvedByUserId: actorUserId,
    reversesJournalId: original.id,
    paymentRequestId: original.paymentRequestId,
    walletDepositId: original.walletDepositId,
    lines: original.lines.map((line) => ({
      accountType: line.accountType,
      side: line.side === 'DEBIT' ? 'CREDIT' : 'DEBIT',
      currency: line.currencyCode as IsoCurrency,
      amount: asMoney(line.amount),
      fxRate: line.fxRate,
      baseAmount: asMoney(line.baseAmount),
      fxDirection: line.fxDirection,
      walletAccountId: line.walletAccountId,
      billTypeCode: line.billTypeCode,
      billTitle: line.billTitle,
      sourceType: line.sourceType,
      sourceId: line.sourceId,
    })),
    request,
    auditEvent: AUDIT_EVENTS.JOURNAL_REVERSED,
    correlationId: reason,
  });
  await db.journalEntry.update({
    where: { id: original.id },
    data: { status: 'REVERSED', reversedByJournalId: reversal.id },
  });
  return reversal;
}

export function counterpartLine(
  accountType: AccountType,
  side: JournalSide,
  currency: IsoCurrency,
  amount: Prisma.Decimal,
  converted: W1Converted
): LineDraft {
  return {
    accountType,
    side,
    currency,
    amount,
    fxRate: converted.fxRate,
    baseAmount: converted.baseAmount,
    fxDirection: converted.fxDirection,
  };
}

export async function walletLiabilityLine(
  db: Db,
  walletId: string,
  currency: IsoCurrency,
  side: JournalSide,
  amount: Prisma.Decimal,
  converted: W1Converted
): Promise<LineDraft> {
  const account = await walletAccount(db, walletId, currency);
  return {
    accountType: 'WALLET_LIABILITY',
    side,
    currency,
    amount,
    fxRate: converted.fxRate,
    baseAmount: converted.baseAmount,
    fxDirection: converted.fxDirection,
    walletAccountId: account.id,
  };
}

export { lockWalletAccounts };
