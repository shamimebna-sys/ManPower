import type { Prisma, PrismaClient } from '@prisma/client';
import { AppError } from '../middleware/errorHandler.js';
import { asRate, type IsoCurrency } from './money.js';

export type Db = PrismaClient | Prisma.TransactionClient;

export async function latestAdministrativeRate(
  db: Db,
  from: IsoCurrency,
  to: IsoCurrency,
  at: Date
) {
  return db.fxRateEntry.findFirst({
    where: { fromCurrency: from, toCurrency: to, effectiveAt: { lte: at } },
    orderBy: { effectiveAt: 'desc' },
  });
}

export async function requireAdministrativeRate(
  db: Db,
  from: IsoCurrency,
  to: IsoCurrency,
  at: Date,
  explicitId?: string | null
) {
  if (from === to) {
    return { id: null as string | null, rate: asRate(1) };
  }
  if (explicitId) {
    const row = await db.fxRateEntry.findUnique({ where: { id: explicitId } });
    if (!row) throw AppError.notFound('FX rate');
    if (row.fromCurrency !== from || row.toCurrency !== to) {
      throw AppError.badRequest('FX rate pair does not match the posting currency');
    }
    return { id: row.id, rate: asRate(row.rate) };
  }
  const latest = await latestAdministrativeRate(db, from, to, at);
  if (!latest) throw AppError.badRequest('No administrative FX rate is effective for this posting');
  return { id: latest.id, rate: asRate(latest.rate) };
}
