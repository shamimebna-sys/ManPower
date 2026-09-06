import type { AuthenticatedUser, EmployerCandidatePurpose } from '@manpower/shared';
import type { Prisma, PrismaClient } from '@prisma/client';
import { AppError } from '../middleware/errorHandler.js';

type AssignmentClient = Pick<PrismaClient, 'employerCandidate' | 'candidate' | 'employer'>;

export function employerActorId(user: AuthenticatedUser): string | null {
  return user.bindings?.employerId ?? user.candidateScope?.employerId ?? null;
}

export function candidateActorId(user: AuthenticatedUser): string | null {
  return user.bindings?.candidateId ?? user.candidateScope?.candidateId ?? null;
}

export function isEmployerOnly(user: AuthenticatedUser): boolean {
  const roles = new Set(user.roles);
  return (
    roles.has('employer') &&
    !roles.has('super_admin') &&
    !roles.has('administrator') &&
    !roles.has('owner') &&
    !roles.has('employee')
  );
}

export function isCandidateOnly(user: AuthenticatedUser): boolean {
  const roles = new Set(user.roles);
  return (
    roles.has('candidate') &&
    !roles.has('super_admin') &&
    !roles.has('administrator') &&
    !roles.has('owner') &&
    !roles.has('employee') &&
    !roles.has('employer')
  );
}

export function resolveAssignmentEmployerId(
  user: AuthenticatedUser,
  requestedEmployerId?: string
): string {
  if (isEmployerOnly(user)) {
    const bound = employerActorId(user);
    if (!bound) {
      throw AppError.forbidden('Employer identity is not bound');
    }
    if (requestedEmployerId && requestedEmployerId !== bound) {
      throw AppError.notFound('Employer candidate');
    }
    return bound;
  }
  if (!requestedEmployerId) {
    throw AppError.badRequest('employerId is required');
  }
  return requestedEmployerId;
}

export function assignmentListScope(
  user: AuthenticatedUser,
  filters: Prisma.EmployerCandidateWhereInput
): Prisma.EmployerCandidateWhereInput | null {
  if (isEmployerOnly(user)) {
    const bound = employerActorId(user);
    if (!bound) return null;
    return { AND: [filters, { employerId: bound }] };
  }
  if (isCandidateOnly(user)) {
    const bound = candidateActorId(user);
    if (!bound) return null;
    return { AND: [filters, { candidateId: bound }] };
  }
  return filters;
}

export async function assertActiveMembershipAvailable(
  client: AssignmentClient,
  input: {
    employerId: string;
    candidateId: string;
    purpose: EmployerCandidatePurpose;
    excludeId?: string;
  }
): Promise<void> {
  const existing = await client.employerCandidate.findFirst({
    where: {
      employerId: input.employerId,
      candidateId: input.candidateId,
      purpose: input.purpose,
      status: 'A',
      ...(input.excludeId ? { id: { not: input.excludeId } } : {}),
    },
    select: { id: true },
  });
  if (existing) {
    throw AppError.conflict('An active membership already exists for this employer, candidate, and purpose');
  }
}

export async function assertCandidateAndEmployerExist(
  client: AssignmentClient,
  candidateId: string,
  employerId: string
): Promise<void> {
  const [candidate, employer] = await Promise.all([
    client.candidate.findUnique({ where: { id: candidateId }, select: { id: true } }),
    client.employer.findUnique({ where: { id: employerId }, select: { id: true } }),
  ]);
  if (!candidate) throw AppError.notFound('Candidate');
  if (!employer) throw AppError.notFound('Employer');
}

export function assertCanViewAssignment(
  user: AuthenticatedUser,
  row: { employerId: string; candidateId: string }
): void {
  if (isEmployerOnly(user)) {
    if (employerActorId(user) !== row.employerId) {
      throw AppError.notFound('Employer candidate');
    }
    return;
  }
  if (isCandidateOnly(user)) {
    if (candidateActorId(user) !== row.candidateId) {
      throw AppError.notFound('Employer candidate');
    }
    return;
  }
}

export function assertCanManageAssignment(
  user: AuthenticatedUser,
  row: { employerId: string }
): void {
  if (isCandidateOnly(user)) {
    throw AppError.forbidden('Candidates cannot manage employer assignments');
  }
  if (isEmployerOnly(user) && employerActorId(user) !== row.employerId) {
    throw AppError.notFound('Employer candidate');
  }
}
