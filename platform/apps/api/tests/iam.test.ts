import { beforeEach, describe, expect, it, vi } from 'vitest';
import request from 'supertest';

const mocks = vi.hoisted(() => {
  const tx = {
    userRole: { upsert: vi.fn() },
    rolePermission: { upsert: vi.fn() },
    user: { findUnique: vi.fn(), update: vi.fn() },
    session: { updateMany: vi.fn() },
    auditEvent: { create: vi.fn() },
  };
  return {
    tx,
    prisma: {
      session: { findUnique: vi.fn() },
      role: { findUnique: vi.fn() },
      permission: { findUnique: vi.fn() },
      auditEvent: { create: vi.fn() },
      $queryRaw: vi.fn(),
      $transaction: vi.fn(async (callback: (client: typeof tx) => unknown) => callback(tx)),
    },
  };
});

vi.mock('../src/config/env', () => ({
  getEnv: () => ({
    NODE_ENV: 'test',
    CORS_ORIGINS: ['http://localhost:3000'],
    LOG_LEVEL: 'silent',
    SESSION_TTL_HOURS: 24,
    BCRYPT_ROUNDS: 12,
  }),
}));
vi.mock('../src/lib/prisma', () => ({ prisma: mocks.prisma }));
vi.mock('../src/lib/logger', () => ({
  logger: {
    info: vi.fn(), warn: vi.fn(), error: vi.fn(), fatal: vi.fn(), debug: vi.fn(), trace: vi.fn(),
  },
}));

import { createApp } from '../src/app';

const actor = {
  id: '11111111-1111-4111-8111-111111111111',
  email: 'admin@example.com',
  username: 'admin',
  displayName: 'Admin',
  passwordHash: '$hash',
  status: 'ACTIVE' as const,
  roles: [{
    role: {
      key: 'iam_admin',
      permissions: [
        { permission: { key: 'iam.user_role.manage' } },
        { permission: { key: 'iam.role_permission.manage' } },
        { permission: { key: 'iam.user.manage' } },
      ],
    },
  }],
};

const session = {
  id: '22222222-2222-4222-8222-222222222222',
  csrfHash: 'a58806c745411a50a426fc29be96491986d7620cdbc6e0084c75f853bb7944a1',
  expiresAt: new Date(Date.now() + 60_000),
  revokedAt: null,
  user: actor,
};

describe('IAM assignment endpoints', () => {
  const app = createApp();
  const authHeaders = (call: request.Test) =>
    call
      .set('Cookie', ['manpower_session=session-token', 'manpower_csrf=csrf-token'])
      .set('X-CSRF-Token', 'csrf-token');

  beforeEach(() => {
    vi.clearAllMocks();
    mocks.prisma.session.findUnique.mockResolvedValue(session);
    mocks.tx.userRole.upsert.mockResolvedValue({});
    mocks.tx.rolePermission.upsert.mockResolvedValue({});
    mocks.tx.auditEvent.create.mockResolvedValue({});
    mocks.tx.user.update.mockResolvedValue({});
    mocks.tx.session.updateMany.mockResolvedValue({ count: 1 });
  });

  it('assigns a stable role key and audits the change', async () => {
    mocks.prisma.role.findUnique.mockResolvedValue({
      id: '33333333-3333-4333-8333-333333333333',
      key: 'administrator',
    });
    const response = await authHeaders(
      request(app)
        .post('/api/v1/iam/users/44444444-4444-4444-8444-444444444444/roles')
        .send({ roleKey: 'administrator' })
    );
    expect(response.status).toBe(200);
    expect(mocks.tx.userRole.upsert).toHaveBeenCalled();
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'iam.role.assigned' }),
      })
    );
  });

  it('grants a stable permission key and audits the change', async () => {
    mocks.prisma.permission.findUnique.mockResolvedValue({
      id: '55555555-5555-4555-8555-555555555555',
      key: 'iam.user.read',
    });
    const response = await authHeaders(
      request(app)
        .post('/api/v1/iam/roles/33333333-3333-4333-8333-333333333333/permissions')
        .send({ permissionKey: 'iam.user.read' })
    );
    expect(response.status).toBe(200);
    expect(mocks.tx.rolePermission.upsert).toHaveBeenCalled();
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'iam.permission.granted' }),
      })
    );
  });

  it('deactivates an account, revokes sessions, and audits the change', async () => {
    mocks.tx.user.findUnique.mockResolvedValue({ status: 'ACTIVE' });
    const response = await authHeaders(
      request(app)
        .patch('/api/v1/iam/users/44444444-4444-4444-8444-444444444444/status')
        .send({ status: 'INACTIVE' })
    );
    expect(response.status).toBe(200);
    expect(mocks.tx.session.updateMany).toHaveBeenCalled();
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'iam.account.status_changed' }),
      })
    );
  });
});
