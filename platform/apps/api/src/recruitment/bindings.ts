import type { BindingDomain, UserBindings } from '@manpower/shared';
import type { Prisma, PrismaClient } from '@prisma/client';
import { AppError } from '../middleware/errorHandler.js';

const BINDING_FIELDS = [
  'agentId',
  'subAgentId',
  'agencierId',
  'companierId',
  'candidateId',
  'employerId',
] as const;

const DOMAIN_FIELD: Record<BindingDomain, (typeof BINDING_FIELDS)[number]> = {
  agent: 'agentId',
  sub_agent: 'subAgentId',
  agencier: 'agencierId',
  companier: 'companierId',
  candidate: 'candidateId',
  employer: 'employerId',
};

type BindingClient = Pick<
  PrismaClient,
  'agent' | 'subAgent' | 'agencier' | 'companier' | 'candidate' | 'employer' | 'user'
>;

export function emptyBindings(): UserBindings {
  return {
    agentId: null,
    subAgentId: null,
    agencierId: null,
    companierId: null,
    candidateId: null,
    employerId: null,
  };
}

export function toUserBindings(row: {
  agentId: string | null;
  subAgentId: string | null;
  agencierId: string | null;
  companierId: string | null;
  candidateId: string | null;
  employerId: string | null;
}): UserBindings {
  return {
    agentId: row.agentId,
    subAgentId: row.subAgentId,
    agencierId: row.agencierId,
    companierId: row.companierId,
    candidateId: row.candidateId,
    employerId: row.employerId,
  };
}

export async function assertBindingTargetExists(
  client: BindingClient,
  domain: BindingDomain,
  targetId: string
): Promise<void> {
  const found =
    domain === 'agent'
      ? await client.agent.findUnique({ where: { id: targetId }, select: { id: true } })
      : domain === 'sub_agent'
        ? await client.subAgent.findUnique({ where: { id: targetId }, select: { id: true } })
        : domain === 'agencier'
          ? await client.agencier.findUnique({ where: { id: targetId }, select: { id: true } })
          : domain === 'companier'
            ? await client.companier.findUnique({ where: { id: targetId }, select: { id: true } })
            : domain === 'candidate'
              ? await client.candidate.findUnique({ where: { id: targetId }, select: { id: true } })
              : await client.employer.findUnique({ where: { id: targetId }, select: { id: true } });
  if (!found) {
    const labels: Record<BindingDomain, string> = {
      agent: 'Agent',
      sub_agent: 'SubAgent',
      agencier: 'Agencier',
      companier: 'Companier',
      candidate: 'Candidate',
      employer: 'Employer',
    };
    throw AppError.notFound(labels[domain]);
  }
}

export function assertExclusiveBinding(
  current: UserBindings,
  domain: BindingDomain,
  targetId: string
): void {
  const nextField = DOMAIN_FIELD[domain];
  const occupied = BINDING_FIELDS.filter((field) => current[field] && field !== nextField);
  if (occupied.length > 0) {
    throw AppError.conflict('A user may bind to only one recruitment identity at a time');
  }
  void targetId;
}

export function bindingUpdate(
  domain: BindingDomain,
  targetId: string | null
): Prisma.UserUncheckedUpdateInput {
  return { [DOMAIN_FIELD[domain]]: targetId };
}

export function bindingField(domain: BindingDomain): (typeof BINDING_FIELDS)[number] {
  return DOMAIN_FIELD[domain];
}
