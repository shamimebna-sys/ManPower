import { readFileSync } from 'node:fs';
import { describe, expect, it } from 'vitest';

const FINANCE_MARKERS = [
  'payment_requests',
  'PaymentRequest',
  'createInvoice',
  'wallet',
  'ledger',
  'manpower fee',
  'admissionPaymentId',
  'finalGroupPaymentId',
  'medicalFeePaymentId',
];

function source(path: string): string {
  return readFileSync(new URL(path, import.meta.url), 'utf8');
}

describe('M6 finance and M7 exclusion', () => {
  it('overseas handlers do not write finance fields or invoke M7', () => {
    const files = [
      '../src/routes/overseas-documents.ts',
      '../src/routes/licenses.ts',
      '../src/routes/live-status.ts',
      '../src/overseas/live-status.ts',
      '../src/overseas/write-data.ts',
    ];
    for (const file of files) {
      const text = source(file);
      for (const marker of FINANCE_MARKERS) {
        expect(text, `${file} must not contain ${marker}`).not.toContain(marker);
      }
      expect(text).not.toContain('manpower-trainings');
      expect(text).not.toContain('selected_candidates');
    }
  });
});
