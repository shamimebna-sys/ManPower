import { Prisma } from '@prisma/client';

export const ROUND_HALF_UP = Prisma.Decimal.ROUND_HALF_UP;
export const PANELTY_TITLE = 'Panelty Fee';
export const PANELTY_CODE = '100';
export const MANPOWER_TITLE = 'Manpower Fee';
export const LEGACY_BILL_CODE_101 = 101;

export type IsoCurrency = 'BDT' | 'EUR';

export function asMoney(value: string | number | Prisma.Decimal): Prisma.Decimal {
  return new Prisma.Decimal(value).toDecimalPlaces(6, ROUND_HALF_UP);
}

export function asRate(value: string | number | Prisma.Decimal): Prisma.Decimal {
  return new Prisma.Decimal(value).toDecimalPlaces(8, ROUND_HALF_UP);
}

export function mapLegacyCurrencyId(currencyId: number): IsoCurrency | null {
  if (currencyId === 1) return 'BDT';
  if (currencyId === 2) return 'EUR';
  return null;
}

export function billTypeCodeForTitle(title: string): string | null {
  return title === PANELTY_TITLE ? PANELTY_CODE : null;
}

export type W1Converted = {
  baseAmount: Prisma.Decimal;
  fxDirection: string;
  fxRate: Prisma.Decimal;
};

/** W1: EUR = BDT / R ; EUR identity EUR = A */
export function w1BaseAmount(
  amount: Prisma.Decimal,
  currency: IsoCurrency,
  r: Prisma.Decimal
): W1Converted {
  const money = asMoney(amount);
  if (currency === 'EUR') {
    return { baseAmount: money, fxDirection: 'EUR = A', fxRate: asRate(1) };
  }
  const rate = asRate(r);
  if (rate.lte(0)) {
    throw new Error('FX rate R must be greater than zero');
  }
  return {
    baseAmount: money.div(rate).toDecimalPlaces(6, ROUND_HALF_UP),
    fxDirection: 'EUR = BDT / R',
    fxRate: rate,
  };
}

export function linesBalance(lines: Array<{ side: 'DEBIT' | 'CREDIT'; baseAmount: Prisma.Decimal }>): boolean {
  const debit = lines
    .filter((line) => line.side === 'DEBIT')
    .reduce((sum, line) => sum.plus(line.baseAmount), new Prisma.Decimal(0));
  const credit = lines
    .filter((line) => line.side === 'CREDIT')
    .reduce((sum, line) => sum.plus(line.baseAmount), new Prisma.Decimal(0));
  return debit.eq(credit) && lines.length >= 2;
}

export function liabilityBalance(credits: Prisma.Decimal, debits: Prisma.Decimal): Prisma.Decimal {
  return credits.minus(debits).toDecimalPlaces(6, ROUND_HALF_UP);
}
