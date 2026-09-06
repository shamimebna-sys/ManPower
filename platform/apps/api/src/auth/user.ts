import type { AuthenticatedUser, CandidateScopeIds, UserBindings } from '@manpower/shared';

interface PartnerLegacyRef {
  sourceLegacyId: bigint | null;
}

interface UserWithRoles {
  id: string;
  email: string;
  username: string | null;
  displayName: string;
  status: 'ACTIVE' | 'INACTIVE' | 'SUSPENDED';
  agentId?: string | null;
  subAgentId?: string | null;
  agencierId?: string | null;
  companierId?: string | null;
  candidateId?: string | null;
  employerId?: string | null;
  agent?: PartnerLegacyRef | null;
  subAgent?: PartnerLegacyRef | null;
  agencier?: PartnerLegacyRef | null;
  companier?: PartnerLegacyRef | null;
  roles: Array<{
    role: {
      key: string;
      permissions: Array<{ permission: { key: string } }>;
    };
  }>;
}

function legacyId(value: bigint | null | undefined): string | null {
  return value === null || value === undefined ? null : value.toString();
}

export function toAuthenticatedUser(user: UserWithRoles): AuthenticatedUser {
  const roles = user.roles.map(({ role }) => role.key);
  const permissions = new Set(
    user.roles.flatMap(({ role }) => role.permissions.map(({ permission }) => permission.key))
  );

  const bindings: UserBindings = {
    agentId: user.agentId ?? null,
    subAgentId: user.subAgentId ?? null,
    agencierId: user.agencierId ?? null,
    companierId: user.companierId ?? null,
    candidateId: user.candidateId ?? null,
    employerId: user.employerId ?? null,
  };

  const candidateScope: CandidateScopeIds = {
    agentId: legacyId(user.agent?.sourceLegacyId),
    subAgentId: legacyId(user.subAgent?.sourceLegacyId),
    agencierId: legacyId(user.agencier?.sourceLegacyId),
    companierId: legacyId(user.companier?.sourceLegacyId),
    candidateId: user.candidateId ?? null,
    employerId: user.employerId ?? null,
  };

  return {
    id: user.id,
    email: user.email,
    username: user.username,
    displayName: user.displayName,
    status: user.status,
    roles,
    permissions: [...permissions].sort(),
    bindings,
    candidateScope,
  };
}

const partnerLegacySelect = { select: { sourceLegacyId: true } } as const;

export const authUserInclude = {
  roles: {
    include: {
      role: {
        include: {
          permissions: {
            include: { permission: true },
          },
        },
      },
    },
  },
  agent: partnerLegacySelect,
  subAgent: partnerLegacySelect,
  agencier: partnerLegacySelect,
  companier: partnerLegacySelect,
} as const;
