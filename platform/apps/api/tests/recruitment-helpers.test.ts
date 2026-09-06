import { describe, expect, it } from 'vitest';
import { PartnerWriteSchema, UserBindingSchema, EmployerCandidateWriteSchema } from '@manpower/shared';
import { M4_ROLE_GRANTS } from '../src/iam/permission-catalogue';
import { assertExclusiveBinding, emptyBindings } from '../src/recruitment/bindings';
import { AppError } from '../src/middleware/errorHandler';
import {
  assignmentListScope,
  isCandidateOnly,
  resolveAssignmentEmployerId,
} from '../src/recruitment/employer-candidates';
import type { AuthenticatedUser } from '@manpower/shared';

function user(partial: Partial<AuthenticatedUser>): AuthenticatedUser {
  return {
    id: '11111111-1111-4111-8111-111111111111',
    email: 'user@example.com',
    username: 'user',
    displayName: 'User',
    status: 'ACTIVE',
    roles: [],
    permissions: [],
    ...partial,
  };
}

describe('M4 recruitment helpers', () => {
  it('validates partner, binding, and assignment payloads', () => {
    expect(PartnerWriteSchema.parse({ name: 'Agent A', status: 'A' }).name).toBe('Agent A');
    expect(UserBindingSchema.parse({ domain: 'agent', targetId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' }).domain).toBe(
      'agent'
    );
    expect(EmployerCandidateWriteSchema.parse({
      candidateId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
      purpose: 'FAVORITE',
    }).purpose).toBe('FAVORITE');
    expect(() => EmployerCandidateWriteSchema.parse({ candidateId: 'x', purpose: 'FAVOURITE' })).toThrow();
  });

  it('prevents a second cross-domain binding', () => {
    const current = { ...emptyBindings(), agentId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' };
    expect(() =>
      assertExclusiveBinding(current, 'companier', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')
    ).toThrow(AppError);
  });

  it('allows replacing the same-domain binding', () => {
    const current = { ...emptyBindings(), agentId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' };
    expect(() =>
      assertExclusiveBinding(current, 'agent', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')
    ).not.toThrow();
  });

  it('forces employer users onto their own employer identity', () => {
    const actor = user({
      roles: ['employer'],
      bindings: { ...emptyBindings(), employerId: 'cccccccc-cccc-4ccc-8ccc-cccccccccccc' },
    });
    expect(resolveAssignmentEmployerId(actor)).toBe('cccccccc-cccc-4ccc-8ccc-cccccccccccc');
    expect(() =>
      resolveAssignmentEmployerId(actor, 'dddddddd-dddd-4ddd-8ddd-dddddddddddd')
    ).toThrow(AppError);
  });

  it('scopes employer and candidate assignment lists', () => {
    const employer = user({
      roles: ['employer'],
      bindings: { ...emptyBindings(), employerId: 'cccccccc-cccc-4ccc-8ccc-cccccccccccc' },
    });
    const candidate = user({
      roles: ['candidate'],
      bindings: { ...emptyBindings(), candidateId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' },
    });
    expect(assignmentListScope(employer, { status: 'A' })).toEqual({
      AND: [{ status: 'A' }, { employerId: 'cccccccc-cccc-4ccc-8ccc-cccccccccccc' }],
    });
    expect(assignmentListScope(candidate, {})).toEqual({
      AND: [{}, { candidateId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' }],
    });
    expect(isCandidateOnly(candidate)).toBe(true);
  });

  it('does not grant teacher or IAM keys through M4 role grants', () => {
    expect(M4_ROLE_GRANTS.teacher).toEqual([]);
    expect(M4_ROLE_GRANTS.employer).toEqual([
      'employer_candidate.read',
      'employer_candidate.manage',
    ]);
    expect(M4_ROLE_GRANTS.company).toEqual(['candidate.read']);
    expect(M4_ROLE_GRANTS.agent).not.toContain('iam.user.manage');
    expect(M4_ROLE_GRANTS.administrator).not.toContain('iam.user_role.manage');
  });
});
