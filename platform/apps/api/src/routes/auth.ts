import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import rateLimit from 'express-rate-limit';
import { ChangePasswordSchema, LoginSchema } from '@manpower/shared';
import type { ApiResponse, LoginResult, MessageResult } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { getEnv } from '../config/env.js';
import { verifyPassword, hashPassword } from '../auth/password.js';
import { authUserInclude, toAuthenticatedUser } from '../auth/user.js';
import { createSecureToken, CSRF_COOKIE, hashToken, SESSION_COOKIE } from '../auth/tokens.js';
import { requireAuth, requireCsrf } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';

export const authRouter: ExpressRouter = Router();

const loginLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  limit: 10,
  standardHeaders: true,
  legacyHeaders: false,
  message: {
    success: false,
    error: { code: 'RATE_LIMITED', message: 'Too many login attempts. Try again later.' },
  },
});

function cookieOptions(expiresAt: Date) {
  const secure = getEnv().NODE_ENV === 'production';
  return {
    httpOnly: true,
    secure,
    sameSite: 'strict' as const,
    path: '/',
    expires: expiresAt,
  };
}

authRouter.post('/login', loginLimiter, async (req: Request, res: Response) => {
  const input = LoginSchema.parse(req.body);
  const identifier = input.identifier.toLowerCase();
  const user = await prisma.user.findFirst({
    where: { OR: [{ email: identifier }, { username: identifier }] },
    include: authUserInclude,
  });

  const validPassword = await verifyPassword(input.password, user?.passwordHash);
  if (!user || !validPassword || user.status !== 'ACTIVE') {
    await writeAuditEvent({
      eventType: AUDIT_EVENTS.LOGIN_FAILURE,
      targetType: 'user',
      targetId: user?.id,
      metadata: { reason: user?.status !== 'ACTIVE' ? 'account_unavailable' : 'invalid_credentials' },
      request: req,
    });
    throw AppError.unauthorized('Invalid credentials');
  }

  const sessionToken = createSecureToken();
  const csrfToken = createSecureToken();
  const expiresAt = new Date(Date.now() + getEnv().SESSION_TTL_HOURS * 60 * 60 * 1000);

  await prisma.$transaction(async (tx) => {
    await tx.session.create({
      data: {
        userId: user.id,
        tokenHash: hashToken(sessionToken),
        csrfHash: hashToken(csrfToken),
        expiresAt,
        ipAddress: req.ip ?? null,
        userAgent: req.headers['user-agent']?.slice(0, 500) ?? null,
      },
    });
    await writeAuditEvent(
      {
        eventType: AUDIT_EVENTS.LOGIN_SUCCESS,
        actorUserId: user.id,
        targetType: 'user',
        targetId: user.id,
        request: req,
      },
      tx
    );
  });

  res.cookie(SESSION_COOKIE, sessionToken, cookieOptions(expiresAt));
  res.cookie(CSRF_COOKIE, csrfToken, {
    ...cookieOptions(expiresAt),
    httpOnly: false,
  });

  const body: ApiResponse<LoginResult> = {
    success: true,
    data: { user: toAuthenticatedUser(user), csrfToken },
  };
  res.status(200).json(body);
});

authRouter.get('/me', requireAuth, (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const body: ApiResponse<LoginResult['user']> = {
    success: true,
    data: req.auth.user,
  };
  res.status(200).json(body);
});

authRouter.post('/logout', requireAuth, requireCsrf, async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const auth = req.auth;
  await prisma.$transaction(async (tx) => {
    await tx.session.update({
      where: { id: auth.sessionId },
      data: { revokedAt: new Date() },
    });
    await writeAuditEvent(
      {
        eventType: AUDIT_EVENTS.LOGOUT,
        actorUserId: auth.user.id,
        targetType: 'session',
        targetId: auth.sessionId,
        request: req,
      },
      tx
    );
  });

  res.clearCookie(SESSION_COOKIE, { path: '/' });
  res.clearCookie(CSRF_COOKIE, { path: '/' });
  const body: ApiResponse<MessageResult> = {
    success: true,
    data: { message: 'Logged out' },
  };
  res.status(200).json(body);
});

authRouter.post(
  '/change-password',
  requireAuth,
  requireCsrf,
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const auth = req.auth;
    const input = ChangePasswordSchema.parse(req.body);
    const user = await prisma.user.findUnique({
      where: { id: auth.user.id },
      select: { id: true, passwordHash: true },
    });
    if (!user || !(await verifyPassword(input.currentPassword, user.passwordHash))) {
      throw AppError.badRequest('Current password is incorrect');
    }

    const passwordHash = await hashPassword(input.newPassword);
    await prisma.$transaction(async (tx) => {
      await tx.user.update({
        where: { id: user.id },
        data: { passwordHash, passwordChangedAt: new Date() },
      });
      await tx.session.updateMany({
        where: { userId: user.id, revokedAt: null },
        data: { revokedAt: new Date() },
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.PASSWORD_CHANGED,
          actorUserId: user.id,
          targetType: 'user',
          targetId: user.id,
          request: req,
        },
        tx
      );
    });

    res.clearCookie(SESSION_COOKIE, { path: '/' });
    res.clearCookie(CSRF_COOKIE, { path: '/' });
    const body: ApiResponse<MessageResult> = {
      success: true,
      data: { message: 'Password changed. Please sign in again.' },
    };
    res.status(200).json(body);
  }
);
