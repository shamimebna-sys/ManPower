import { describe, expect, it } from 'vitest';
import { Prisma } from '@prisma/client';
import {
  billTypeCodeForTitle,
  linesBalance,
  MANPOWER_TITLE,
  mapLegacyCurrencyId,
  PANELTY_CODE,
  PANELTY_TITLE,
  w1BaseAmount,
} from '../src/finance/money';

describe('M7 money and W1', () => {
  it('maps legacy currency ids and Panelty code 100 only', () => {
    expect(mapLegacyCurrencyId(1)).toBe('BDT');
    expect(mapLegacyCurrencyId(2)).toBe('EUR');
    expect(mapLegacyCurrencyId(3)).toBeNull();
    expect(billTypeCodeForTitle(PANELTY_TITLE)).toBe(PANELTY_CODE);
    expect(billTypeCodeForTitle(MANPOWER_TITLE)).toBeNull();
  });

  it('applies W1 EUR = BDT / R and EUR = A', () => {
    const bdt = w1BaseAmount(new Prisma.Decimal('700000'), 'BDT', new Prisma.Decimal('140'));
    expect(bdt.baseAmount.toFixed(6)).toBe('5000.000000');
    expect(bdt.fxDirection).toBe('EUR = BDT / R');
    const eur = w1BaseAmount(new Prisma.Decimal('3000'), 'EUR', new Prisma.Decimal('1'));
    expect(eur.baseAmount.toFixed(6)).toBe('3000.000000');
    expect(eur.fxDirection).toBe('EUR = A');
  });

  it('rejects non-positive rates and requires balanced journals', () => {
    expect(() => w1BaseAmount(new Prisma.Decimal('10'), 'BDT', new Prisma.Decimal(0))).toThrow(/greater than zero/);
    expect(
      linesBalance([
        { side: 'DEBIT', baseAmount: new Prisma.Decimal('10') },
        { side: 'CREDIT', baseAmount: new Prisma.Decimal('10') },
      ])
    ).toBe(true);
    expect(linesBalance([{ side: 'DEBIT', baseAmount: new Prisma.Decimal('10') }])).toBe(false);
  });
});
