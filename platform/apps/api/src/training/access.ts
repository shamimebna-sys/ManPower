import type { AuthenticatedUser } from '@manpower/shared';
import { AppError } from '../middleware/errorHandler.js';

const GLOBAL_TEACHER_READ_ROLES = new Set(['super_admin', 'administrator', 'owner', 'employee']);

export function teacherActorId(user: AuthenticatedUser): string | null {
  return user.bindings?.teacherId ?? null;
}

export function isTeacherSelfScoped(user: AuthenticatedUser): boolean {
  const roles = new Set(user.roles);
  if (roles.has('super_admin')) {
    return false;
  }
  return roles.has('teacher') && ![...GLOBAL_TEACHER_READ_ROLES].some((role) => roles.has(role));
}

export type TeacherReadScope =
  | { kind: 'all' }
  | { kind: 'self'; teacherId: string }
  | { kind: 'none' };

export function resolveTeacherReadScope(user: AuthenticatedUser): TeacherReadScope {
  if (!isTeacherSelfScoped(user)) {
    return { kind: 'all' };
  }
  const teacherId = teacherActorId(user);
  if (!teacherId) {
    return { kind: 'none' };
  }
  return { kind: 'self', teacherId };
}

export function assertTeacherReadAccess(user: AuthenticatedUser, teacherId: string): void {
  const scope = resolveTeacherReadScope(user);
  if (scope.kind === 'all') {
    return;
  }
  if (scope.kind === 'self' && scope.teacherId === teacherId) {
    return;
  }
  throw AppError.notFound('Teacher');
}

export type ScheduleReadScope =
  | { kind: 'all' }
  | { kind: 'teacher'; teacherId: string }
  | { kind: 'none' };

export function resolveScheduleReadScope(user: AuthenticatedUser): ScheduleReadScope {
  if (!isTeacherSelfScoped(user)) {
    return { kind: 'all' };
  }
  const teacherId = teacherActorId(user);
  if (!teacherId) {
    return { kind: 'none' };
  }
  return { kind: 'teacher', teacherId };
}

export function assertScheduleReadAccess(user: AuthenticatedUser, teacherId: string | null): void {
  const scope = resolveScheduleReadScope(user);
  if (scope.kind === 'all') {
    return;
  }
  if (scope.kind === 'teacher' && teacherId === scope.teacherId) {
    return;
  }
  throw AppError.notFound('Class schedule');
}
