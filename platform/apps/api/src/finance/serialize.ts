import type {
  FinanceQuarantine,
  FxRateEntry,
  JournalEntry,
  JournalLine,
  PaymentRequest,
  ReconstructionManifest,
  Wallet,
  WalletDeposit,
} from '@prisma/client';
import { availableEur } from './wallets.js';
import type { PrismaClient } from '@prisma/client';

export function moneyString(value: { toFixed: (digits?: number) => string } | null | undefined, digits = 6): string | null {
  return value ? value.toFixed(digits) : null;
}

export function toWalletRecord(
  wallet: Wallet,
  available: string,
  projection: string | null
) {
  return {
    id: wallet.id,
    ownerType: wallet.ownerType,
    agentId: wallet.agentId,
    subAgentId: wallet.subAgentId,
    teacherId: wallet.teacherId,
    availableEur: available,
    projectedAvailableEur: projection,
    ledgerAuthoritative: true as const,
  };
}

export function toPaymentRequestRecord(row: PaymentRequest) {
  return {
    id: row.id,
    status: row.status,
    candidateId: row.candidateId,
    walletId: row.walletId,
    amount: moneyString(row.amount) ?? '0.000000',
    billTitle: row.billTitle,
    billTypeCode: row.billTypeCode,
    legacyBillCode: row.legacyBillCode,
    journalId: row.journalId,
    sourceLegacyId: row.sourceLegacyId?.toString() ?? null,
    createdAt: row.createdAt.toISOString(),
  };
}

export function toDepositRecord(row: WalletDeposit) {
  return {
    id: row.id,
    status: row.status,
    walletId: row.walletId,
    amount: moneyString(row.amount) ?? '0.000000',
    currencyCode: row.currencyCode,
    fxRate: moneyString(row.fxRate, 8),
    journalId: row.journalId,
    createdAt: row.createdAt.toISOString(),
  };
}

export function toJournalRecord(row: JournalEntry & { lines: JournalLine[] }) {
  return {
    id: row.id,
    status: row.status,
    entryType: row.entryType,
    sourceType: row.sourceType,
    sourceId: row.sourceId,
    idempotencyKey: row.idempotencyKey,
    postedAt: row.postedAt.toISOString(),
    lines: row.lines.map((line) => ({
      lineNo: line.lineNo,
      accountType: line.accountType,
      side: line.side,
      currency: line.currencyCode,
      amount: moneyString(line.amount) ?? '0.000000',
      fxRate: moneyString(line.fxRate, 8) ?? '0.00000000',
      baseAmount: moneyString(line.baseAmount) ?? '0.000000',
      fxDirection: line.fxDirection,
      billTitle: line.billTitle,
      billTypeCode: line.billTypeCode,
    })),
  };
}

export function toFxRecord(row: FxRateEntry) {
  return {
    id: row.id,
    fromCurrency: row.fromCurrency,
    toCurrency: row.toCurrency,
    rate: moneyString(row.rate, 8) ?? '0.00000000',
    effectiveAt: row.effectiveAt.toISOString(),
    enteredByUserId: row.enteredByUserId,
  };
}

export function toQuarantineRecord(row: FinanceQuarantine) {
  return {
    id: row.id,
    sourceTable: row.sourceTable,
    sourcePk: row.sourcePk,
    sourceHash: row.sourceHash,
    migrationRunId: row.migrationRunId,
    reason: row.reason,
    payload: row.payload,
    reconciliationImpact: row.reconciliationImpact,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
  };
}

export function toManifestRecord(row: ReconstructionManifest) {
  return {
    sourcePaymentId: row.sourcePaymentId,
    sourceTable: row.sourceTable,
    sourceHash: row.sourceHash,
    migrationRunId: row.migrationRunId,
    walletId: row.walletId,
    currency: row.currencyCode,
    sourceAmount: moneyString(row.sourceAmount) ?? '0.000000',
    sourceExchangeRate: moneyString(row.sourceExchangeRate, 8) ?? '0.00000000',
    journalId: row.journalId,
  };
}

export async function walletWithBalance(db: PrismaClient, wallet: Wallet) {
  const available = await availableEur(db, wallet.id);
  return toWalletRecord(wallet, moneyString(available) ?? '0.000000', moneyString(wallet.projectedAvailableEur));
}
