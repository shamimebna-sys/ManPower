import type { Prisma, PrismaClient } from '@prisma/client';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { asMoney, asRate, billTypeCodeForTitle, mapLegacyCurrencyId, w1BaseAmount, type IsoCurrency } from './money.js';
import { type QuarantineReason } from './quarantine.js';
import { POSTING_ISOLATION, counterpartLine, postJournal, walletLiabilityLine } from './posting.js';
import { ensureWalletForAgent, ensureWalletForSubAgent, lockWalletAccounts } from './wallets.js';
import { isUniqueConflict } from '../training/util.js';

export type LegacyPaymentInput = {
  id: string;
  status: string;
  type: string;
  amount: string;
  currencyId: number;
  exchangeRate: string;
  agentId: number | null;
  subAgentId: number | null;
  teacherId: number | null;
  sourceTable?: string | undefined;
  sourceHash: string;
  isBackup?: boolean | undefined;
};

export type ReconstructionResult = {
  reconstructed: number;
  quarantined: number;
  journals: string[];
  requestsImported: number;
};

export type LegacyPaymentRequestInput = {
  id: string;
  status: string;
  amount: string;
  billTitle: string;
  legacyBillCode?: number | undefined;
  candidateId?: string | undefined;
  agentId?: number | null | undefined;
  subAgentId?: number | null | undefined;
  paymentId?: string | undefined;
  sourceTable?: string | undefined;
  sourceHash: string;
  isBackup?: boolean | undefined;
};

function parseAmount(raw: string): Prisma.Decimal | null {
  try {
    const value = asMoney(raw);
    if (!value.isFinite()) return null;
    return value;
  } catch {
    return null;
  }
}

export async function reconstructQualifyingPayments(
  db: PrismaClient,
  rows: LegacyPaymentInput[],
  migrationRunId: string,
  actorUserId?: string
): Promise<ReconstructionResult> {
  const journals: string[] = [];
  let reconstructed = 0;
  let quarantined = 0;

  for (const row of rows) {
    const outcome = await db.$transaction(
      async (tx) => {
        const quarantinedRow = async (reason: QuarantineReason, impact: string) => {
          await tx.financeQuarantine.create({
            data: {
              sourceTable: row.sourceTable ?? 'payments',
              sourcePk: row.id,
              sourceHash: row.sourceHash,
              migrationRunId,
              reason,
              payload: row as unknown as Prisma.InputJsonValue,
              reconciliationImpact: impact,
            },
          });
          await writeAuditEvent(
            {
              eventType: AUDIT_EVENTS.QUARANTINE_CREATED,
              actorUserId,
              targetType: 'finance_quarantine',
              targetId: row.id,
              metadata: { reason, migrationRunId },
            },
            tx
          );
          return 'quarantine' as const;
        };

        if (row.isBackup || (row.sourceTable && row.sourceTable !== 'payments')) {
          return quarantinedRow('BACKUP_ROW', 'excluded from reconstruction');
        }
        if (row.teacherId && row.teacherId !== 0) {
          return quarantinedRow('UNSUPPORTED_TEACHER_RELATIONSHIP', 'no teacher wallet invented');
        }
        if (row.status !== 'A') {
          return quarantinedRow('INCONSISTENT_STATUS', 'only status A is live money');
        }
        const currency = mapLegacyCurrencyId(row.currencyId);
        if (!currency) return quarantinedRow('INVALID_CURRENCY', 'currency not 1 or 2');
        const amount = parseAmount(row.amount);
        if (!amount) return quarantinedRow('INVALID_AMOUNT', 'unparseable amount');
        let rate: Prisma.Decimal;
        try {
          rate = asRate(row.exchangeRate);
        } catch {
          return quarantinedRow('UNPARSEABLE_RATE', 'unparseable exchange_rate');
        }

        const existingMap = await tx.legacyKeyMap.findUnique({
          where: {
            sourceSystem_sourceTable_sourceId: {
              sourceSystem: 'manpower_mysql',
              sourceTable: 'payments',
              sourceId: row.id,
            },
          },
        });
        if (existingMap) {
          journals.push(existingMap.targetId);
          return 'duplicate' as const;
        }

        let wallet;
        if (row.subAgentId && row.subAgentId !== 0) {
          const sub = await tx.subAgent.findUnique({ where: { sourceLegacyId: BigInt(row.subAgentId) } });
          if (!sub) return quarantinedRow('MISSING_WALLET_OWNER', 'sub-agent not found');
          wallet = await ensureWalletForSubAgent(tx, sub.id);
        } else if (row.agentId && row.agentId !== 0) {
          const agent = await tx.agent.findUnique({ where: { sourceLegacyId: BigInt(row.agentId) } });
          if (!agent) return quarantinedRow('MISSING_WALLET_OWNER', 'agent not found');
          wallet = await ensureWalletForAgent(tx, agent.id);
        } else {
          return quarantinedRow('MISSING_WALLET_OWNER', 'no agent or sub-agent');
        }

        try {
          await lockWalletAccounts(tx, wallet.id);
          const converted = w1BaseAmount(amount, currency, rate);
          const isDeposit = row.type.toUpperCase() === 'CR';
          const entryType = isDeposit ? 'WALLET_DEPOSIT' : 'FEE_APPROVAL';
          const walletSide = isDeposit ? 'CREDIT' : 'DEBIT';
          const counterpartType = isDeposit ? 'DEPOSIT_CLEARING' : 'FEE_INCOME';
          const counterpartSide = isDeposit ? 'DEBIT' : 'CREDIT';
          const walletLine = await walletLiabilityLine(tx, wallet.id, currency, walletSide, amount, converted);
          const other = counterpartLine(counterpartType, counterpartSide, currency, amount, converted);
          const header = await postJournal(tx, {
            entryType,
            sourceType: 'payments',
            sourceId: row.id,
            idempotencyKey: `hist-payment:${row.id}`,
            actorUserId: actorUserId ?? null,
            walletId: wallet.id,
            lines: [walletLine, other],
            auditEvent: AUDIT_EVENTS.HISTORICAL_JOURNAL_IMPORTED,
          });

          await tx.legacyKeyMap.create({
            data: {
              sourceSystem: 'manpower_mysql',
              sourceTable: 'payments',
              sourceId: row.id,
              targetType: 'journal_entry',
              targetId: header.id,
              migrationRunId,
              sourceRowHash: row.sourceHash,
            },
          });
          await tx.reconstructionManifest.create({
            data: {
              sourcePaymentId: row.id,
              sourceTable: 'payments',
              sourceHash: row.sourceHash,
              migrationRunId,
              walletId: wallet.id,
              currencyCode: currency,
              sourceAmount: amount,
              sourceExchangeRate: rate,
              journalId: header.id,
            },
          });
          journals.push(header.id);
          return 'reconstructed' as const;
        } catch (error) {
          if (isUniqueConflict(error)) {
            const map = await tx.legacyKeyMap.findUnique({
              where: {
                sourceSystem_sourceTable_sourceId: {
                  sourceSystem: 'manpower_mysql',
                  sourceTable: 'payments',
                  sourceId: row.id,
                },
              },
            });
            if (map) {
              journals.push(map.targetId);
              return 'duplicate' as const;
            }
          }
          throw error;
        }
      },
      { isolationLevel: POSTING_ISOLATION }
    );

    if (outcome === 'reconstructed') reconstructed += 1;
    if (outcome === 'quarantine') quarantined += 1;
  }

  return { reconstructed, quarantined, journals, requestsImported: 0 };
}

export async function importHistoricalPaymentRequests(
  db: PrismaClient,
  rows: LegacyPaymentRequestInput[],
  migrationRunId: string,
  actorUserId?: string
): Promise<{ imported: number; quarantined: number }> {
  let imported = 0;
  let quarantined = 0;
  for (const row of rows) {
    const outcome = await db.$transaction(async (tx) => {
      const quarantinedRow = async (reason: QuarantineReason, impact: string) => {
        await tx.financeQuarantine.create({
          data: {
            sourceTable: row.sourceTable ?? 'payment_requests',
            sourcePk: row.id,
            sourceHash: row.sourceHash,
            migrationRunId,
            reason,
            payload: row as unknown as Prisma.InputJsonValue,
            reconciliationImpact: impact,
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.QUARANTINE_CREATED,
            actorUserId,
            targetType: 'finance_quarantine',
            targetId: row.id,
            metadata: { reason, migrationRunId },
          },
          tx
        );
        return 'quarantine' as const;
      };

      const sourceTable = row.sourceTable ?? 'payment_requests';
      if (row.isBackup || (sourceTable !== 'payment_requests' && sourceTable !== 'sub_agent_payment_requests')) {
        return quarantinedRow('BACKUP_ROW', 'historical request not live');
      }
      if (row.status !== 'P' && row.status !== 'A' && row.status !== 'R') {
        return quarantinedRow('INCONSISTENT_STATUS', 'request status not P/A/R');
      }
      const amount = parseAmount(row.amount);
      if (!amount) return quarantinedRow('INVALID_AMOUNT', 'unparseable amount');
      if (!row.candidateId) return quarantinedRow('MISSING_SOURCE_REFERENCES', 'candidate not mapped');
      const candidate = await tx.candidate.findUnique({ where: { id: row.candidateId } });
      if (!candidate) return quarantinedRow('MISSING_SOURCE_REFERENCES', 'candidate not found');

      let sourceLegacyId: bigint;
      try {
        sourceLegacyId = BigInt(row.id);
      } catch {
        return quarantinedRow('MISSING_SOURCE_REFERENCES', 'non-numeric source pk');
      }

      const existing = await tx.paymentRequest.findFirst({
        where: { sourceLegacyId, sourceTable },
      });
      if (existing) return 'duplicate' as const;

      let wallet;
      if (row.subAgentId && row.subAgentId !== 0) {
        const sub = await tx.subAgent.findUnique({ where: { sourceLegacyId: BigInt(row.subAgentId) } });
        if (!sub) return quarantinedRow('MISSING_WALLET_OWNER', 'sub-agent not found');
        wallet = await ensureWalletForSubAgent(tx, sub.id);
      } else if (row.agentId && row.agentId !== 0) {
        const agent = await tx.agent.findUnique({ where: { sourceLegacyId: BigInt(row.agentId) } });
        if (!agent) return quarantinedRow('MISSING_WALLET_OWNER', 'agent not found');
        wallet = await ensureWalletForAgent(tx, agent.id);
      } else if (candidate.agentId) {
        const agent = await tx.agent.findUnique({ where: { sourceLegacyId: candidate.agentId } });
        if (!agent) return quarantinedRow('MISSING_WALLET_OWNER', 'candidate agent not found');
        wallet = await ensureWalletForAgent(tx, agent.id);
      } else {
        return quarantinedRow('MISSING_WALLET_OWNER', 'no agent or sub-agent');
      }

      let journalId: string | null = null;
      if (row.paymentId) {
        const map = await tx.legacyKeyMap.findUnique({
          where: {
            sourceSystem_sourceTable_sourceId: {
              sourceSystem: 'manpower_mysql',
              sourceTable: 'payments',
              sourceId: row.paymentId,
            },
          },
        });
        journalId = map?.targetId ?? null;
      }

      await tx.paymentRequest.create({
        data: {
          status: row.status,
          candidateId: candidate.id,
          walletId: wallet.id,
          amount,
          billTitle: row.billTitle,
          billTypeCode: billTypeCodeForTitle(row.billTitle),
          legacyBillCode: row.legacyBillCode ?? null,
          sourceLegacyId,
          sourceTable,
          journalId,
        },
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.PAYMENT_REQUEST_CREATED,
          actorUserId,
          targetType: 'payment_request',
          targetId: row.id,
          metadata: { status: row.status, historical: true, migrationRunId },
        },
        tx
      );
      return 'imported' as const;
    });
    if (outcome === 'imported') imported += 1;
    if (outcome === 'quarantine') quarantined += 1;
  }
  return { imported, quarantined };
}

export async function addResidualOpeningRow(
  db: PrismaClient,
  input: {
    batchId: string;
    sourcePaymentId?: string | null;
    sourceTable?: string | null;
    sourceHash?: string | null;
    migrationRunId: string;
    walletId: string;
    currency: IsoCurrency;
    amount: string;
    exchangeRate: string;
  }
) {
  if (input.sourcePaymentId) {
    const reconstructed = await db.reconstructionManifest.findUnique({
      where: { sourcePaymentId: input.sourcePaymentId },
    });
    if (reconstructed) {
      throw AppError.conflict('DR-H1 forbids opening posting of a reconstructed payment');
    }
  }
  return db.openingManifest.create({
    data: {
      batchId: input.batchId,
      sourcePaymentId: input.sourcePaymentId ?? null,
      sourceTable: input.sourceTable ?? null,
      sourceHash: input.sourceHash ?? null,
      migrationRunId: input.migrationRunId,
      walletId: input.walletId,
      currencyCode: input.currency,
      sourceAmount: asMoney(input.amount),
      sourceExchangeRate: asRate(input.exchangeRate),
    },
  });
}
