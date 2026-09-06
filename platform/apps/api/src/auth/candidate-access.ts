import type { AuthenticatedUser } from '@manpower/shared';
import type { Prisma } from '@prisma/client';
import { AppError } from '../middleware/errorHandler.js';

/** Approved target role keys. Do not invent additional roles. */
export const APPROVED_ROLE_KEYS = [
  'super_admin',
  'administrator',
  'owner',
  'agent',
  'sub_agent',
  'candidate',
  'employer',
  'company',
  'agency',
  'employee',
  'teacher',
] as const;

export type ApprovedRoleKey = (typeof APPROVED_ROLE_KEYS)[number];

export type CandidateScopeActor = {
  roles: readonly string[];
  agentId?: string | bigint | null | undefined;
  subAgentId?: string | bigint | null | undefined;
  agencierId?: string | bigint | null | undefined;
  companierId?: string | bigint | null | undefined;
  candidateId?: string | null | undefined;
  employerId?: string | null | undefined;
};

export type CandidateAccess =
  | { kind: 'all' }
  | { kind: 'none' }
  | { kind: 'filter'; where: Prisma.CandidateWhereInput };

export type CandidateScopeRow = {
  id: string;
  agentId?: bigint | null;
  subAgentId?: bigint | null;
  agencierId?: bigint | null;
  companierId?: bigint | null;
};

function asBigInt(value: string | bigint | null | undefined): bigint | undefined {
  if (value === null || value === undefined || value === '') {
    return undefined;
  }
  return typeof value === 'bigint' ? value : BigInt(value);
}

export function actorFromAuth(user: AuthenticatedUser): CandidateScopeActor {
  const scope = user.candidateScope;
  return {
    roles: user.roles,
    agentId: scope?.agentId,
    subAgentId: scope?.subAgentId,
    agencierId: scope?.agencierId,
    companierId: scope?.companierId,
    candidateId: scope?.candidateId,
    employerId: scope?.employerId,
  };
}

/**
 * Locked A07 candidate row-scope. Permission checks stay in requirePermission.
 * Partner profile IDs are optional until Recruitment attaches them.
 */
export function resolveCandidateAccess(actor: CandidateScopeActor): CandidateAccess {
  const roles = new Set(actor.roles);

  if (
    roles.has('super_admin') ||
    roles.has('administrator') ||
    roles.has('owner') ||
    roles.has('employee')
  ) {
    return { kind: 'all' };
  }

  const clauses: Prisma.CandidateWhereInput[] = [];

  if (roles.has('agent')) {
    const agentId = asBigInt(actor.agentId);
    if (agentId !== undefined) {
      clauses.push({ agentId });
    }
  }
  if (roles.has('sub_agent')) {
    const subAgentId = asBigInt(actor.subAgentId);
    if (subAgentId !== undefined) {
      clauses.push({ subAgentId });
    }
  }
  if (roles.has('agency')) {
    const agencierId = asBigInt(actor.agencierId);
    if (agencierId !== undefined) {
      clauses.push({ agencierId });
    }
  }
  if (roles.has('company')) {
    const companierId = asBigInt(actor.companierId);
    if (companierId !== undefined) {
      clauses.push({ companierId });
    }
  }
  if (roles.has('candidate') && actor.candidateId) {
    clauses.push({ id: actor.candidateId });
  }

  if (clauses.length === 0) {
    return { kind: 'none' };
  }
  const first = clauses[0];
  if (first === undefined) {
    return { kind: 'none' };
  }
  return { kind: 'filter', where: clauses.length === 1 ? first : { OR: clauses } };
}

export function canAccessCandidateRow(
  actor: CandidateScopeActor,
  row: CandidateScopeRow
): boolean {
  const roles = new Set(actor.roles);
  if (
    roles.has('super_admin') ||
    roles.has('administrator') ||
    roles.has('owner') ||
    roles.has('employee')
  ) {
    return true;
  }
  if (roles.has('agent')) {
    const agentId = asBigInt(actor.agentId);
    if (agentId !== undefined && row.agentId === agentId) {
      return true;
    }
  }
  if (roles.has('sub_agent')) {
    const subAgentId = asBigInt(actor.subAgentId);
    if (subAgentId !== undefined && row.subAgentId === subAgentId) {
      return true;
    }
  }
  if (roles.has('agency')) {
    const agencierId = asBigInt(actor.agencierId);
    if (agencierId !== undefined && row.agencierId === agencierId) {
      return true;
    }
  }
  if (roles.has('company')) {
    const companierId = asBigInt(actor.companierId);
    if (companierId !== undefined && row.companierId === companierId) {
      return true;
    }
  }
  if (roles.has('candidate') && actor.candidateId && row.id === actor.candidateId) {
    return true;
  }
  return false;
}

export function applyCandidateAccess(
  filters: Prisma.CandidateWhereInput,
  access: CandidateAccess
): Prisma.CandidateWhereInput | null {
  if (access.kind === 'none') {
    return null;
  }
  if (access.kind === 'all') {
    return filters;
  }
  return { AND: [filters, access.where] };
}

export function assertCandidateAccess(actor: CandidateScopeActor, row: CandidateScopeRow): void {
  if (!canAccessCandidateRow(actor, row)) {
    throw AppError.notFound('Candidate');
  }
}

export function assertCanCreateCandidate(actor: CandidateScopeActor): void {
  if (resolveCandidateAccess(actor).kind === 'none') {
    throw AppError.forbidden('Candidate create is not in scope for this role');
  }
}
