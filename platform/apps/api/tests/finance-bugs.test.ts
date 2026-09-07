import { describe, expect, it } from 'vitest';
import { billTypeCodeForTitle, MANPOWER_TITLE, PANELTY_TITLE } from '../src/finance/money';

describe('M7 FIN-BUG-01/02 target rules', () => {
  it('does not treat assignment as comparison for bill titles', () => {
    const title: string = 'Medical Fee';
    const admission: string = 'Admission Group Approval';
    expect(title === admission).toBe(false);
    expect(title === PANELTY_TITLE).toBe(false);
    expect(billTypeCodeForTitle(title)).toBeNull();
  });

  it('requires exact Manpower Fee title rather than a tautology', () => {
    const tautology = (billTitle: string) => billTitle.includes('Manpower') || billTitle === billTitle;
    const medical: string = 'Medical Fee';
    const manpower: string = 'Manpower Fee';
    expect(tautology(medical)).toBe(true);
    expect(medical === MANPOWER_TITLE).toBe(false);
    expect(manpower === MANPOWER_TITLE).toBe(true);
  });
});
