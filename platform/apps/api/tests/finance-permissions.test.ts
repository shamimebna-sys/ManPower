import { describe, expect, it } from 'vitest';
import {
  M4_ROLE_GRANTS,
  M7_PERMISSION_KEYS,
  M7_ROLE_GRANTS,
} from '../src/iam/permission-catalogue';
import { AUDIT_EVENTS } from '../src/audit/audit';
import { QUARANTINE_REASONS } from '../src/finance/quarantine';
import { MANPOWER_TITLE } from '../src/finance/money';

describe('M7 permission and audit matrix', () => {
  it('defines nine finance keys and no delete keys', () => {
    expect(M7_PERMISSION_KEYS).toHaveLength(9);
    expect(M7_PERMISSION_KEYS.every((key) => key.startsWith('finance.'))).toBe(true);
    expect(M7_PERMISSION_KEYS.some((key) => key.includes('delete'))).toBe(false);
  });

  it('grants staff keys and scopes agent/sub_agent without admin or fx manage', () => {
    expect(M7_ROLE_GRANTS.owner).toHaveLength(9);
    expect(M7_ROLE_GRANTS.administrator).toEqual(expect.arrayContaining([...M7_PERMISSION_KEYS]));
    expect(M7_ROLE_GRANTS.agent).toEqual([
      'finance.read',
      'finance.wallet.read',
      'finance.payment_request.create',
    ]);
    expect(M7_ROLE_GRANTS.sub_agent).toEqual(M7_ROLE_GRANTS.agent);
    expect(M7_ROLE_GRANTS.agent).not.toContain('finance.payment_request.approve');
    expect(M7_ROLE_GRANTS.agent).not.toContain('finance.fx_rate.manage');
    expect(M7_ROLE_GRANTS.teacher).toBeUndefined();
    expect(M7_ROLE_GRANTS.agency).toBeUndefined();
    expect(M4_ROLE_GRANTS.teacher).toEqual([]);
  });

  it('uses approved finance audit names and keeps manpower title exact', () => {
    expect(AUDIT_EVENTS.PAYMENT_REQUEST_APPROVED).toBe('payment_request.approved');
    expect(AUDIT_EVENTS.FX_RATE_ENTERED).toBe('fx_rate.entered');
    expect(AUDIT_EVENTS.HISTORICAL_JOURNAL_IMPORTED).toBe('historical_journal.imported');
    expect(Object.values(AUDIT_EVENTS).some((value) => value.endsWith('.deleted'))).toBe(false);
    expect(MANPOWER_TITLE).toBe('Manpower Fee');
    expect(QUARANTINE_REASONS).toContain('UNSUPPORTED_TEACHER_RELATIONSHIP');
    expect(QUARANTINE_REASONS).toContain('CACHE_ONLY_BALANCE');
  });
});
