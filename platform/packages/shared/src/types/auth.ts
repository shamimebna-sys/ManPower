export type AccountStatus = 'ACTIVE' | 'INACTIVE' | 'SUSPENDED';

/** Optional partner/self IDs used by A07 candidate row-scope. */
export interface CandidateScopeIds {
  agentId?: string | null;
  subAgentId?: string | null;
  agencierId?: string | null;
  companierId?: string | null;
  candidateId?: string | null;
  employerId?: string | null;
}

export interface UserBindings {
  agentId: string | null;
  subAgentId: string | null;
  agencierId: string | null;
  companierId: string | null;
  candidateId: string | null;
  employerId: string | null;
}

export interface AuthenticatedUser {
  id: string;
  email: string;
  username: string | null;
  displayName: string;
  status: AccountStatus;
  roles: string[];
  permissions: string[];
  candidateScope?: CandidateScopeIds;
  bindings?: UserBindings;
}

export interface LoginResult {
  user: AuthenticatedUser;
  csrfToken: string;
}

export interface MessageResult {
  message: string;
}
