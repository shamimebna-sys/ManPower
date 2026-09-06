import { describe, expect, it } from 'vitest';
import { AppError } from '../src/middleware/errorHandler';
import {
  applyCandidateAccess,
  assertCanCreateCandidate,
  assertCandidateAccess,
  canAccessCandidateRow,
  resolveCandidateAccess,
} from '../src/auth/candidate-access';

const row = {
  id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  agentId: BigInt(10),
  subAgentId: BigInt(4),
  agencierId: BigInt(2),
  companierId: BigInt(7),
};

describe('A07 candidate row-scope helper', () => {
  it('gives global access to super_admin, administrator, owner, and employee', () => {
    for (const key of ['super_admin', 'administrator', 'owner', 'employee']) {
      expect(resolveCandidateAccess({ roles: [key] }).kind).toBe('all');
      expect(canAccessCandidateRow({ roles: [key] }, row)).toBe(true);
    }
  });

  it('scopes agent, sub_agent, agency, and candidate when IDs are present', () => {
    expect(resolveCandidateAccess({ roles: ['agent'], agentId: '10' })).toEqual({
      kind: 'filter',
      where: { agentId: BigInt(10) },
    });
    expect(canAccessCandidateRow({ roles: ['agent'], agentId: '10' }, row)).toBe(true);
    expect(canAccessCandidateRow({ roles: ['agent'], agentId: '99' }, row)).toBe(false);

    expect(canAccessCandidateRow({ roles: ['sub_agent'], subAgentId: '4' }, row)).toBe(true);
    expect(canAccessCandidateRow({ roles: ['agency'], agencierId: '2' }, row)).toBe(true);
    expect(canAccessCandidateRow({ roles: ['company'], companierId: '7' }, row)).toBe(true);
    expect(canAccessCandidateRow({ roles: ['company'], companierId: '8' }, row)).toBe(false);
    expect(canAccessCandidateRow({ roles: ['candidate'], candidateId: row.id }, row)).toBe(true);
    expect(
      canAccessCandidateRow({ roles: ['candidate'], candidateId: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' }, row)
    ).toBe(false);
  });

  it('denies employer, teacher, and unresolved scoped roles without IDs', () => {
    for (const key of ['employer', 'teacher', 'agent', 'sub_agent', 'agency', 'company', 'candidate']) {
      expect(resolveCandidateAccess({ roles: [key] }).kind).toBe('none');
      expect(canAccessCandidateRow({ roles: [key] }, row)).toBe(false);
    }
  });

  it('scopes company to candidates.companier_id and keeps teacher blocked', () => {
    expect(resolveCandidateAccess({ roles: ['company'], companierId: '7' })).toEqual({
      kind: 'filter',
      where: { companierId: BigInt(7) },
    });
    expect(resolveCandidateAccess({ roles: ['teacher'], candidateId: row.id }).kind).toBe('none');
    expect(canAccessCandidateRow({ roles: ['teacher'] }, row)).toBe(false);
  });

  it('does not treat unknown roles as global', () => {
    expect(resolveCandidateAccess({ roles: ['reader'] }).kind).toBe('none');
    expect(applyCandidateAccess({ status: 'A' }, { kind: 'none' })).toBeNull();
  });

  it('keeps employee list filters unchanged when access is all', () => {
    expect(applyCandidateAccess({ status: 'A' }, { kind: 'all' })).toEqual({ status: 'A' });
  });

  it('throws not-found on out-of-scope rows and forbids create when scope is none', () => {
    expect(() => assertCandidateAccess({ roles: ['employer'] }, row)).toThrow(AppError);
    expect(() => assertCanCreateCandidate({ roles: ['company'] })).toThrow(AppError);
    expect(() => assertCanCreateCandidate({ roles: ['employee'] })).not.toThrow();
  });
});
