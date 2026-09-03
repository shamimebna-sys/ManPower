import { beforeEach, describe, expect, it, vi } from 'vitest';
import request from 'supertest';

const mocks = vi.hoisted(() => {
  const tx = {
    session: {
      create: vi.fn(),
      update: vi.fn(),
      updateMany: vi.fn(),
    },
    user: { update: vi.fn(), findUnique: vi.fn() },
    auditEvent: { create: vi.fn() },
  };
  return {
    tx,
    prisma: {
      user: { findFirst: vi.fn(), findUnique: vi.fn() },
      session: { findUnique: vi.fn() },
      role: { findUnique: vi.fn() },
      permission: { findUnique: vi.fn() },
      auditEvent: { create: vi.fn() },
      $queryRaw: vi.fn(),
      $transaction: vi.fn(async (callback: (client: typeof tx) => unknown) => callback(tx)),
    },
    verifyPassword: vi.fn(),
    hashPassword: vi.fn(),
  };
});

vi.mock('../src/config/env', () => ({
  getEnv: () => ({
    NODE_ENV: 'test',
    PORT: 4001,
    DATABASE_URL: 'postgresql://test:test@localhost:5432/test',
    API_VERSION: 'v1',
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
vi.mock('../src/auth/password', () => ({
  verifyPassword: mocks.verifyPassword,
  hashPassword: mocks.hashPassword,
}));
vi.mock('../src/auth/tokens', async () => {
  const actual = (await vi.importActual('../src/auth/tokens')) as object;
  let call = 0;
  return {
    ...actual,
    createSecureToken: vi.fn(() => (++call % 2 === 1 ? 'session-token' : 'csrf-token')),
  };
});

import { createApp } from '../src/app';

const user = {
  id: '11111111-1111-4111-8111-111111111111',
  email: 'admin@example.com',
  username: 'admin',
  displayName: 'Administrator',
  passwordHash: '$hash',
  status: 'ACTIVE' as const,
  roles: [
    {
      role: {
        key: 'administrator',
        permissions: [{ permission: { key: 'iam.user.read' } }],
      },
    },
  ],
};

const session = {
  id: '22222222-2222-4222-8222-222222222222',
  tokenHash: 'unused',
  csrfHash: 'a58806c745411a50a426fc29be96491986d7620cdbc6e0084c75f853bb7944a1',
  expiresAt: new Date(Date.now() + 60_000),
  revokedAt: null,
  user,
};

describe('authentication API', () => {
  const app = createApp();

  beforeEach(() => {
    vi.clearAllMocks();
    mocks.tx.session.create.mockResolvedValue({});
    mocks.tx.session.update.mockResolvedValue({});
    mocks.tx.session.updateMany.mockResolvedValue({ count: 1 });
    mocks.tx.auditEvent.create.mockResolvedValue({});
    mocks.tx.user.update.mockResolvedValue({});
    mocks.hashPassword.mockResolvedValue('$new-hash');
  });

  it('logs in with valid credentials and never returns passwordHash', async () => {
    mocks.prisma.user.findFirst.mockResolvedValue(user);
    mocks.verifyPassword.mockResolvedValue(true);

    const response = await request(app)
      .post('/api/v1/auth/login')
      .send({ identifier: 'admin', password: 'ValidPassword!42' });

    expect(response.status).toBe(200);
    expect(response.body.data.user.email).toBe(user.email);
    expect(JSON.stringify(response.body)).not.toContain('passwordHash');
    expect(response.headers['set-cookie']).toEqual(
      expect.arrayContaining([expect.stringContaining('manpower_session=')])
    );
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'auth.login.success' }) })
    );
  });

  it('uses the same generic response for invalid password and unknown user', async () => {
    mocks.prisma.user.findFirst.mockResolvedValueOnce(user).mockResolvedValueOnce(null);
    mocks.verifyPassword.mockResolvedValue(false);
    const invalid = await request(app)
      .post('/api/v1/auth/login')
      .send({ identifier: 'admin', password: 'wrong' });
    const unknown = await request(app)
      .post('/api/v1/auth/login')
      .send({ identifier: 'nobody', password: 'wrong' });

    expect(invalid.status).toBe(401);
    expect(unknown.status).toBe(401);
    expect(invalid.body.error.message).toBe('Invalid credentials');
    expect(unknown.body.error.message).toBe('Invalid credentials');
    expect(mocks.prisma.auditEvent.create).toHaveBeenCalledTimes(2);
  });

  it('returns the current user for a valid session', async () => {
    mocks.prisma.session.findUnique.mockResolvedValue(session);
    const response = await request(app)
      .get('/api/v1/auth/me')
      .set('Cookie', 'manpower_session=session-token');
    expect(response.status).toBe(200);
    expect(response.body.data.permissions).toContain('iam.user.read');
    expect(response.body.data.passwordHash).toBeUndefined();
  });

  it('returns 401 for /me without a session', async () => {
    const response = await request(app).get('/api/v1/auth/me');
    expect(response.status).toBe(401);
  });

  it('logs out, revokes the session, and creates an audit event', async () => {
    mocks.prisma.session.findUnique.mockResolvedValue(session);
    const response = await request(app)
      .post('/api/v1/auth/logout')
      .set('Cookie', [
        'manpower_session=session-token',
        'manpower_csrf=csrf-token',
      ])
      .set('X-CSRF-Token', 'csrf-token');
    expect(response.status).toBe(200);
    expect(mocks.tx.session.update).toHaveBeenCalled();
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'auth.logout' }) })
    );
  });

  it('changes the password, revokes all sessions, and audits the event', async () => {
    mocks.prisma.session.findUnique.mockResolvedValue(session);
    mocks.prisma.user.findUnique.mockResolvedValue({ id: user.id, passwordHash: '$hash' });
    mocks.verifyPassword.mockResolvedValue(true);
    const response = await request(app)
      .post('/api/v1/auth/change-password')
      .set('Cookie', [
        'manpower_session=session-token',
        'manpower_csrf=csrf-token',
      ])
      .set('X-CSRF-Token', 'csrf-token')
      .send({ currentPassword: 'OldPassword!42', newPassword: 'NewPassword!84' });
    expect(response.status).toBe(200);
    expect(mocks.tx.user.update).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ passwordHash: '$new-hash' }) })
    );
    expect(mocks.tx.session.updateMany).toHaveBeenCalled();
    expect(JSON.stringify(mocks.tx.auditEvent.create.mock.calls)).not.toContain('NewPassword!84');
  });
});
