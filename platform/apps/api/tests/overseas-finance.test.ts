import { readFileSync } from 'node:fs';
import { describe, expect, it } from 'vitest';

const WRITE_FINANCE_MARKERS = [
  'createInvoice',
  'admissionPaymentId',
  'finalGroupPaymentId',
  'medicalFeePaymentId',
];

function source(path: string): string {
  return readFileSync(new URL(path, import.meta.url), 'utf8');
}

describe('M6 finance write exclusion', () => {
  it('overseas write handlers do not mutate finance FKs or invoices', () => {
    const files = [
      '../src/routes/overseas-documents.ts',
      '../src/routes/licenses.ts',
      '../src/overseas/write-data.ts',
    ];
    for (const file of files) {
      const text = source(file);
      for (const marker of WRITE_FINANCE_MARKERS) {
        expect(text, `${file} must not contain ${marker}`).not.toContain(marker);
      }
      expect(text).not.toContain('selected_candidates');
    }
  });

  it('live-status reads Manpower Fee requests and does not write candidate payment FKs', () => {
    const text = source('../src/overseas/live-status.ts');
    expect(text).toContain('paymentRequest');
    expect(text).toContain('MANPOWER_TITLE');
    expect(text).not.toContain('admissionPaymentId');
    expect(text).not.toContain('createInvoice');
  });
});
