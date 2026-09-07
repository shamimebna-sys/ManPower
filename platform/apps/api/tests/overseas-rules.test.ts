import { describe, expect, it } from 'vitest';
import { MedicalWriteSchema, PrivateFileIdSchema } from '@manpower/shared';
import { LATEST_ORDER, LATEST_RULES } from '../src/overseas/latest';
import { LIVE_STATUS_LABELS } from '../src/overseas/live-status';
import { AUDIT_EVENTS } from '../src/audit/audit';

describe('M6 latest() and live-status rules', () => {
  it('locks deterministic latest() order without an implicit status filter', () => {
    expect(LATEST_ORDER).toEqual([
      { createdAt: 'desc' },
      { sourceLegacyId: { sort: 'desc', nulls: 'last' } },
    ]);
    expect(LATEST_RULES.implicitStatusFilter).toBe(false);
    expect(LATEST_RULES.duplicatesAllowed).toBe(true);
    expect(LATEST_RULES.hardDelete).toBe(false);
  });

  it('preserves exact dump labels and skips step 5 in the lookup table', () => {
    expect(LIVE_STATUS_LABELS[5]).toBe('Selection');
    expect(LIVE_STATUS_LABELS[6]).toBe('Labour Contact');
    expect(LIVE_STATUS_LABELS[7]).toBe('Police Clearance & Medical');
    expect(LIVE_STATUS_LABELS[8]).toBe('VISA/Work Permite');
    expect(Object.keys(LIVE_STATUS_LABELS)).toHaveLength(10);
  });

  it('uses the approved overseas audit names and no VOIDED or delete events', () => {
    expect(AUDIT_EVENTS.OVERSEAS_MEDICAL_CREATED).toBe('overseas.medical.created');
    expect(AUDIT_EVENTS.OVERSEAS_LICENSE_UPDATED).toBe('overseas.license.updated');
    expect(AUDIT_EVENTS.OVERSEAS_STATUS_CHANGED).toBe('overseas.status.changed');
    const values = Object.values(AUDIT_EVENTS);
    expect(values.some((value) => value.includes('VOIDED'))).toBe(false);
    expect(values.some((value) => value.endsWith('.deleted'))).toBe(false);
  });

  it('rejects public file URLs and accepts omitted or UUID private refs', () => {
    expect(PrivateFileIdSchema.safeParse('https://example.com/file.pdf').success).toBe(false);
    expect(PrivateFileIdSchema.safeParse('//cdn.example/file.pdf').success).toBe(false);
    expect(PrivateFileIdSchema.safeParse('').success).toBe(true);
    expect(PrivateFileIdSchema.safeParse(undefined).success).toBe(true);
    expect(PrivateFileIdSchema.safeParse('11111111-1111-4111-8111-111111111111').success).toBe(true);
    expect(MedicalWriteSchema.safeParse({ candidateId: '11111111-1111-4111-8111-111111111111' }).success).toBe(
      true
    );
  });
});
