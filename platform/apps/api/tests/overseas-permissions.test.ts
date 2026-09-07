import { describe, expect, it } from 'vitest';
import {
  M4_ROLE_GRANTS,
  M6_OVERSEAS_READ_KEYS,
  M6_PERMISSION_KEYS,
  M6_ROLE_GRANTS,
} from '../src/iam/permission-catalogue';

describe('M6 permission matrix', () => {
  it('defines exactly fourteen M6 keys and no delete keys', () => {
    expect(M6_PERMISSION_KEYS).toHaveLength(14);
    expect(M6_PERMISSION_KEYS.some((key) => key.includes('delete'))).toBe(false);
    expect(M6_PERMISSION_KEYS).toContain('overseas.labour_contract.manage');
    expect(M6_PERMISSION_KEYS).toContain('operations.license.read');
  });

  it('grants staff all operational M6 keys including license', () => {
    expect(M6_ROLE_GRANTS.owner).toEqual(expect.arrayContaining([...M6_PERMISSION_KEYS]));
    expect(M6_ROLE_GRANTS.administrator).toEqual(expect.arrayContaining([...M6_PERMISSION_KEYS]));
    expect(M6_ROLE_GRANTS.employee).toEqual(expect.arrayContaining([...M6_PERMISSION_KEYS]));
  });

  it('grants agent/sub_agent/agency candidate-scoped overseas including labour, without license', () => {
    for (const role of ['agent', 'sub_agent', 'agency'] as const) {
      const grants = M6_ROLE_GRANTS[role] ?? [];
      expect(grants).toEqual(expect.arrayContaining(['overseas.labour_contract.read', 'overseas.labour_contract.manage']));
      expect(grants).not.toContain('operations.license.read');
      expect(grants).not.toContain('operations.license.manage');
      expect(grants).toHaveLength(12);
    }
  });

  it('grants company, candidate, employer, and teacher no M6 keys', () => {
    expect(M6_ROLE_GRANTS.company).toBeUndefined();
    expect(M6_ROLE_GRANTS.candidate).toBeUndefined();
    expect(M6_ROLE_GRANTS.employer).toBeUndefined();
    expect(M6_ROLE_GRANTS.teacher).toBeUndefined();
    expect(M4_ROLE_GRANTS.teacher).toEqual([]);
    expect(M4_ROLE_GRANTS.company).toEqual(['candidate.read']);
  });

  it('does not invent a live-status catalogue key', () => {
    expect(M6_PERMISSION_KEYS.some((key) => key.includes('live_status') || key.includes('live-status'))).toBe(
      false
    );
    expect(M6_OVERSEAS_READ_KEYS).toHaveLength(6);
  });
});
