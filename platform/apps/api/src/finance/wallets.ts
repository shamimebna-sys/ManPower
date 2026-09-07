import type { AccountType, Prisma, PrismaClient } from '@prisma/client';
import { AppError } from '../middleware/errorHandler.js';
import { asMoney, liabilityBalance, type IsoCurrency } from './money.js';

export type Db = PrismaClient | Prisma.TransactionClient;

export async function ensureCurrencies(db: Db): Promise<void> {
  await db.currency.upsert({ where: { code: 'BDT' }, create: { code: 'BDT', name: 'Bangladeshi Taka' }, update: {} });
  await db.currency.upsert({ where: { code: 'EUR' }, create: { code: 'EUR', name: 'Euro' }, update: {} });
}

export async function chartAccountId(db: Db, accountType: AccountType) {
  const row = await db.financeAccount.findUnique({ where: { accountType } });
  if (!row) throw AppError.internal(`Finance account ${String(accountType)} is not seeded`);
  return row.id;
}

export async function ensureWalletForAgent(db: Db, agentId: string) {
  await ensureCurrencies(db);
  const existing = await db.wallet.findFirst({ where: { ownerType: 'AGENT', agentId } });
  if (existing) return existing;
  return db.wallet.create({
    data: {
      ownerType: 'AGENT',
      agentId,
      accounts: { create: [{ currencyCode: 'BDT' }, { currencyCode: 'EUR' }] },
    },
  });
}

export async function ensureWalletForSubAgent(db: Db, subAgentId: string) {
  await ensureCurrencies(db);
  const existing = await db.wallet.findFirst({ where: { ownerType: 'SUB_AGENT', subAgentId } });
  if (existing) return existing;
  return db.wallet.create({
    data: {
      ownerType: 'SUB_AGENT',
      subAgentId,
      accounts: { create: [{ currencyCode: 'BDT' }, { currencyCode: 'EUR' }] },
    },
  });
}

export async function lockWalletAccounts(db: Db, walletId: string): Promise<void> {
  await db.$queryRaw`SELECT id FROM finance.wallet_accounts WHERE wallet_id = ${walletId}::uuid FOR UPDATE`;
}

export async function walletAccount(db: Db, walletId: string, currency: IsoCurrency) {
  const account = await db.walletAccount.findUnique({
    where: { walletId_currencyCode: { walletId, currencyCode: currency } },
  });
  if (!account) throw AppError.notFound('Wallet account');
  return account;
}

export async function availableEur(db: Db, walletId: string): Promise<Prisma.Decimal> {
  const accounts = await db.walletAccount.findMany({ where: { walletId }, select: { id: true } });
  const ids = accounts.map((account) => account.id);
  if (ids.length === 0) return asMoney(0);
  const lines = await db.journalLine.findMany({
    where: {
      walletAccountId: { in: ids },
      journal: { status: 'POSTED' },
    },
    select: { side: true, baseAmount: true },
  });
  const credits = lines
    .filter((line) => line.side === 'CREDIT')
    .reduce((sum, line) => sum.plus(line.baseAmount), asMoney(0));
  const debits = lines
    .filter((line) => line.side === 'DEBIT')
    .reduce((sum, line) => sum.plus(line.baseAmount), asMoney(0));
  return liabilityBalance(credits, debits);
}

export async function refreshProjection(db: Db, walletId: string): Promise<Prisma.Decimal> {
  const available = await availableEur(db, walletId);
  await db.wallet.update({ where: { id: walletId }, data: { projectedAvailableEur: available } });
  return available;
}
