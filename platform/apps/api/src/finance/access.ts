import type { AuthenticatedUser } from '@manpower/shared';
import type { Prisma } from '@prisma/client';
import { AppError } from '../middleware/errorHandler.js';
import { actorFromAuth, assertCandidateAccess } from '../auth/candidate-access.js';

export function isGlobalFinance(user: AuthenticatedUser): boolean {
  return user.roles.includes('super_admin') || user.roles.includes('administrator') || user.roles.includes('owner');
}

export function isStaffFinance(user: AuthenticatedUser): boolean {
  return isGlobalFinance(user) || user.roles.includes('employee');
}

export function walletOwnerWhere(user: AuthenticatedUser): Prisma.WalletWhereInput {
  if (user.roles.includes('sub_agent') && user.bindings?.subAgentId) {
    return { ownerType: 'SUB_AGENT', subAgentId: user.bindings.subAgentId };
  }
  if (user.roles.includes('agent') && user.bindings?.agentId) {
    return { ownerType: 'AGENT', agentId: user.bindings.agentId };
  }
  return { id: '00000000-0000-0000-0000-000000000000' };
}

export function assertWalletScope(
  user: AuthenticatedUser,
  wallet: { ownerType: string; agentId: string | null; subAgentId: string | null }
): void {
  if (isStaffFinance(user)) return;
  if (user.roles.includes('agent') && wallet.ownerType === 'AGENT' && wallet.agentId && wallet.agentId === user.bindings?.agentId) {
    return;
  }
  if (
    user.roles.includes('sub_agent') &&
    wallet.ownerType === 'SUB_AGENT' &&
    wallet.subAgentId &&
    wallet.subAgentId === user.bindings?.subAgentId
  ) {
    return;
  }
  throw AppError.notFound('Wallet');
}

export function assertCandidateFinanceScope(
  user: AuthenticatedUser,
  candidate: {
    id: string;
    agentId: bigint | null;
    subAgentId: bigint | null;
    agencierId: bigint | null;
    companierId: bigint | null;
  }
): void {
  assertCandidateAccess(actorFromAuth(user), candidate);
}