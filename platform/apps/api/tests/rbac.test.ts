import { describe, expect, it, vi } from 'vitest';
import type { NextFunction, Request, Response } from 'express';

vi.mock('../src/config/env', () => ({
  getEnv: () => ({
    NODE_ENV: 'test',
    DATABASE_URL: 'postgresql://test:test@localhost:5432/test',
    LOG_LEVEL: 'info',
  }),
}));
vi.mock('../src/lib/prisma', () => ({ prisma: {} }));

import { toAuthenticatedUser } from '../src/auth/user';
import { requirePermission } from '../src/middleware/auth';

const baseUser = {
  id: '11111111-1111-4111-8111-111111111111',
  email: 'user@example.com',
  username: 'user',
  displayName: 'Test User',
  status: 'ACTIVE' as const,
};

describe('RBAC permission evaluation', () => {
  it('combines and de-duplicates permissions across multiple roles', () => {
    const user = toAuthenticatedUser({
      ...baseUser,
      roles: [
        {
          role: {
            key: 'reader',
            permissions: [{ permission: { key: 'iam.user.read' } }],
          },
        },
        {
          role: {
            key: 'manager',
            permissions: [
              { permission: { key: 'iam.user.read' } },
              { permission: { key: 'iam.user.manage' } },
            ],
          },
        },
      ],
    });
    expect(user.roles).toEqual(['reader', 'manager']);
    expect(user.permissions).toEqual(['iam.user.manage', 'iam.user.read']);
  });

  function responseMock() {
    const res = {
      status: vi.fn(),
      json: vi.fn(),
    };
    res.status.mockReturnValue(res);
    return res as unknown as Response;
  }

  it('returns 401 when no authenticated principal exists', () => {
    const req = {} as Request;
    const res = responseMock();
    const next = vi.fn() as NextFunction;
    requirePermission('iam.user.read')(req, res, next);
    expect(res.status).toHaveBeenCalledWith(401);
    expect(next).not.toHaveBeenCalled();
  });

  it('returns 403 when the permission is absent', () => {
    const req = {
      auth: { user: { ...baseUser, roles: ['reader'], permissions: [] } },
    } as unknown as Request;
    const res = responseMock();
    const next = vi.fn() as NextFunction;
    requirePermission('iam.user.manage')(req, res, next);
    expect(res.status).toHaveBeenCalledWith(403);
    expect(next).not.toHaveBeenCalled();
  });

  it('allows a user with the required permission', () => {
    const req = {
      auth: {
        user: { ...baseUser, roles: ['manager'], permissions: ['iam.user.manage'] },
      },
    } as unknown as Request;
    const res = responseMock();
    const next = vi.fn() as NextFunction;
    requirePermission('iam.user.manage')(req, res, next);
    expect(next).toHaveBeenCalledOnce();
  });

  it('allows super_admin without numeric legacy role IDs', () => {
    const req = {
      auth: { user: { ...baseUser, roles: ['super_admin'], permissions: [] } },
    } as unknown as Request;
    const res = responseMock();
    const next = vi.fn() as NextFunction;
    requirePermission('iam.role_permission.manage')(req, res, next);
    expect(next).toHaveBeenCalledOnce();
  });
});
