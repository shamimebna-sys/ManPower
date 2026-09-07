import { describe, expect, it } from 'vitest';
import type { AuthenticatedUser } from '@manpower/shared';
import { resolveCandidateAccess } from '../src/auth/candidate-access';
import {
  assertScheduleReadAccess,
  assertTeacherReadAccess,
  isTeacherSelfScoped,
  resolveScheduleReadScope,
  resolveTeacherReadScope,
  teacherActorId,
} from '../src/training/access';

function user(partial: Partial<AuthenticatedUser> & Pick<AuthenticatedUser, 'roles' | 'permissions'>): AuthenticatedUser {
  return {
    id: '11111111-1111-4111-8111-111111111111',
    email: 'user@example.com',
    username: 'user',
    displayName: 'User',
    status: 'ACTIVE',
    bindings: {
      agentId: null,
      subAgentId: null,
      agencierId: null,
      companierId: null,
      candidateId: null,
      employerId: null,
      teacherId: null,
    },
    ...partial,
  };
}

describe('M5 teacher and schedule scope helpers', () => {
  it('self-scopes a bound teacher and hides unbound teachers', () => {
    const bound = user({
      roles: ['teacher'],
      permissions: ['training.teacher.read'],
      bindings: {
        agentId: null,
        subAgentId: null,
        agencierId: null,
        companierId: null,
        candidateId: null,
        employerId: null,
        teacherId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
      },
    });
    const unbound = user({ roles: ['teacher'], permissions: ['training.teacher.read'] });
    expect(isTeacherSelfScoped(bound)).toBe(true);
    expect(teacherActorId(bound)).toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
    expect(resolveTeacherReadScope(bound)).toEqual({
      kind: 'self',
      teacherId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    });
    expect(resolveTeacherReadScope(unbound)).toEqual({ kind: 'none' });
    expect(() =>
      assertTeacherReadAccess(bound, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')
    ).toThrow('Teacher not found');
    expect(() =>
      assertTeacherReadAccess(bound, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
    ).not.toThrow();
  });

  it('does not self-scope administrator or owner teacher reads', () => {
    const admin = user({
      roles: ['administrator'],
      permissions: ['training.teacher.read'],
    });
    expect(isTeacherSelfScoped(admin)).toBe(false);
    expect(resolveTeacherReadScope(admin)).toEqual({ kind: 'all' });
  });

  it('blocks teacher candidate browsing', () => {
    const teacher = user({ roles: ['teacher'], permissions: ['training.teacher.read'] });
    expect(resolveCandidateAccess({ roles: teacher.roles })).toEqual({ kind: 'none' });
  });

  it('blocks cross-teacher schedule access when teacher-scoped', () => {
    const teacher = user({
      roles: ['teacher'],
      permissions: ['training.schedule.read'],
      bindings: {
        agentId: null,
        subAgentId: null,
        agencierId: null,
        companierId: null,
        candidateId: null,
        employerId: null,
        teacherId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
      },
    });
    expect(resolveScheduleReadScope(teacher)).toEqual({
      kind: 'teacher',
      teacherId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    });
    expect(() =>
      assertScheduleReadAccess(teacher, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')
    ).toThrow('Class schedule not found');
    expect(() =>
      assertScheduleReadAccess(teacher, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
    ).not.toThrow();
  });
});
