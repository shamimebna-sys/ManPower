import type { AuthenticatedUser } from '@manpower/shared';

declare global {
  namespace Express {
    interface Request {
      auth?: {
        user: AuthenticatedUser;
        sessionId: string;
        csrfHash: string;
        rawSessionToken: string;
      };
    }
  }
}

export {};
