export type AccountStatus = 'ACTIVE' | 'INACTIVE' | 'SUSPENDED';

/** Optional partner/self IDs used by A07 candidate row-scope. Absent until Recruitment attaches them. */
export interface CandidateScopeIds {
  agentId?: string | null;
  subAgentId?: string | null;
  agencierId?: string | null;
  candidateId?: string | null;
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
}

export interface LoginResult {
  user: AuthenticatedUser;
  csrfToken: string;
}

export interface MessageResult {
  message: string;
}
