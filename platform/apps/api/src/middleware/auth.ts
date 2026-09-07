import type { Request, Response, NextFunction, RequestHandler } from 'express';
import type { ApiErrorResponse } from '@manpower/shared';
import { ERROR_CODES, HTTP_STATUS } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { authUserInclude, toAuthenticatedUser } from '../auth/user.js';
import { CSRF_COOKIE, SESSION_COOKIE, hashToken, safeTokenMatches } from '../auth/tokens.js';

function unauthorized(res: Response): void {
  const body: ApiErrorResponse = {
    success: false,
    error: { code: ERROR_CODES.UNAUTHORIZED, message: 'Authentication required' },
  };
  res.status(HTTP_STATUS.UNAUTHORIZED).json(body);
}

export async function requireAuth(req: Request, res: Response, next: NextFunction): Promise<void> {
  const token = req.cookies?.[SESSION_COOKIE] as string | undefined;
  if (!token) {
    unauthorized(res);
    return;
  }

  const session = await prisma.session.findUnique({
    where: { tokenHash: hashToken(token) },
    include: { user: { include: authUserInclude } },
  });

  if (
    !session ||
    session.revokedAt !== null ||
    session.expiresAt <= new Date() ||
    session.user.status !== 'ACTIVE'
  ) {
    unauthorized(res);
    return;
  }

  req.auth = {
    user: toAuthenticatedUser(session.user),
    sessionId: session.id,
    csrfHash: session.csrfHash,
    rawSessionToken: token,
  };
  next();
}

export function requireCsrf(req: Request, res: Response, next: NextFunction): void {
  const header = req.headers['x-csrf-token'];
  const cookie = req.cookies?.[CSRF_COOKIE] as string | undefined;
  const csrf = typeof header === 'string' ? header : undefined;

  if (!req.auth || !csrf || !cookie || csrf !== cookie || !safeTokenMatches(csrf, req.auth.csrfHash)) {
    const body: ApiErrorResponse = {
      success: false,
      error: { code: ERROR_CODES.FORBIDDEN, message: 'Invalid CSRF token' },
    };
    res.status(HTTP_STATUS.FORBIDDEN).json(body);
    return;
  }
  next();
}

export function requirePermission(permissionKey: string): RequestHandler {
  return requireAnyPermission(permissionKey);
}

export function requireAnyPermission(...permissionKeys: string[]): RequestHandler {
  return (req, res, next): void => {
    if (!req.auth) {
      unauthorized(res);
      return;
    }

    const allowed =
      req.auth.user.roles.includes('super_admin') ||
      permissionKeys.some((key) => req.auth?.user.permissions.includes(key));

    if (!allowed) {
      const body: ApiErrorResponse = {
        success: false,
        error: { code: ERROR_CODES.FORBIDDEN, message: 'Insufficient permission' },
      };
      res.status(HTTP_STATUS.FORBIDDEN).json(body);
      return;
    }
    next();
  };
}
