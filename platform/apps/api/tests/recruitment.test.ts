import { beforeEach, describe, expect, it, vi } from 'vitest';
import request from 'supertest';

const mocks = vi.hoisted(() => {
  const tx = {
    agent: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    subAgent: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    agencier: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    companier: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    employer: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    employerCandidate: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn(), findFirst: vi.fn() },
    candidate: { findUnique: vi.fn() },
    user: { findUnique: vi.fn(), update: vi.fn() },
    auditEvent: { create: vi.fn() },
  };
  return {
    tx,
    prisma: {
      session: { findUnique: vi.fn() },
      agent: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      subAgent: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      agencier: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      companier: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      employer: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      employerCandidate: {
        findMany: vi.fn(),
        findUnique: vi.fn(),
        findFirst: vi.fn(),
        count: vi.fn(),
      },
      candidate: { findUnique: vi.fn() },
      user: { findUnique: vi.fn() },
      auditEvent: { create: vi.fn() },
      $transaction: vi.fn(async (arg: unknown) => {
        if (Array.isArray(arg)) return Promise.all(arg);
        return (arg as (client: typeof tx) => unknown)(tx);
      }),
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

const admin = {
  id: '11111111-1111-4111-8111-111111111111',
  email: 'admin@example.com',
  username: 'admin',
  displayName: 'Admin',
  passwordHash: '$hash',
  status: 'ACTIVE' as const,
  roles: [{
    role: {
      key: 'administrator',
      permissions: [
        { permission: { key: 'partners.read' } },
        { permission: { key: 'partners.manage' } },
        { permission: { key: 'partners.user_binding.manage' } },
        { permission: { key: 'employer_candidate.read' } },
        { permission: { key: 'employer_candidate.manage' } },
      ],
    },
  }],
};

const teacher = {
  ...admin,
  roles: [{ role: { key: 'teacher', permissions: [] } }],
};

const employerUser = {
  ...admin,
  employerId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
  roles: [{
    role: {
      key: 'employer',
      permissions: [
        { permission: { key: 'employer_candidate.read' } },
        { permission: { key: 'employer_candidate.manage' } },
      ],
    },
  }],
};

const candidateUser = {
  ...admin,
  candidateId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  roles: [{
    role: {
      key: 'candidate',
      permissions: [{ permission: { key: 'employer_candidate.read' } }],
    },
  }],
};

const session = {
  id: '22222222-2222-4222-8222-222222222222',
  csrfHash: 'a58806c745411a50a426fc29be96491986d7620cdbc6e0084c75f853bb7944a1',
  expiresAt: new Date(Date.now() + 60_000),
  revokedAt: null,
  user: admin,
};

const agent = {
  id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  sourceLegacyId: 10n,
  code: 'A10',
  name: 'Agent Ten',
  email: 'agent@example.com',
  mobile: '01700000000',
  address: null,
  status: 'A',
  countryId: null,
  balance: { toString: () => '0' },
  logoFileRef: null,
  createdAt: new Date('2026-01-01T00:00:00.000Z'),
  updatedAt: new Date('2026-01-01T00:00:00.000Z'),
};

const assignment = {
  id: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
  employerId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
  candidateId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  purpose: 'FAVORITE' as const,
  status: 'A',
  createdAt: new Date('2026-01-01T00:00:00.000Z'),
  updatedAt: new Date('2026-01-01T00:00:00.000Z'),
};

describe('M4 recruitment API', () => {
  const app = createApp();
  const auth = (call: request.Test, user = admin) => {
    mocks.prisma.session.findUnique.mockResolvedValue({ ...session, user });
    return call
      .set('Cookie', ['manpower_session=session-token', 'manpower_csrf=csrf-token'])
      .set('X-CSRF-Token', 'csrf-token');
  };

  beforeEach(() => {
    vi.clearAllMocks();
    mocks.tx.auditEvent.create.mockResolvedValue({});
  });

  it('creates a partner and audits the write', async () => {
    mocks.tx.agent.create.mockResolvedValue(agent);
    const response = await auth(request(app).post('/api/v1/partners/agent')).send({
      name: 'Agent Ten',
      code: 'A10',
      sourceLegacyId: '10',
    });
    expect(response.status).toBe(201);
    expect(response.body.data.type).toBe('agent');
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'recruitment.partner.created' }),
      })
    );
  });

  it('lists partners for an authorized role', async () => {
    mocks.prisma.agent.findMany.mockResolvedValue([agent]);
    mocks.prisma.agent.count.mockResolvedValue(1);
    const response = await auth(request(app).get('/api/v1/partners/agent'));
    expect(response.status).toBe(200);
    expect(response.body.data.items).toHaveLength(1);
  });

  it('rejects teacher access to partner management', async () => {
    const response = await auth(request(app).get('/api/v1/partners/agent'), teacher);
    expect(response.status).toBe(403);
  });

  it('binds a user to an agent and rejects a second domain', async () => {
    mocks.tx.user.findUnique.mockResolvedValue({
      id: admin.id,
      agentId: null,
      subAgentId: null,
      agencierId: null,
      companierId: null,
      candidateId: null,
      employerId: null,
    });
    mocks.tx.agent.findUnique.mockResolvedValue({ id: agent.id });
    mocks.tx.user.update.mockResolvedValue({
      agentId: agent.id,
      subAgentId: null,
      agencierId: null,
      companierId: null,
      candidateId: null,
      employerId: null,
    });
    const response = await auth(request(app).put(`/api/v1/iam/users/${admin.id}/bindings`)).send({
      domain: 'agent',
      targetId: agent.id,
    });
    expect(response.status).toBe(200);
    expect(response.body.data.agentId).toBe(agent.id);
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'user.partner.bound' }),
      })
    );

    mocks.tx.user.findUnique.mockResolvedValue({
      id: admin.id,
      agentId: agent.id,
      subAgentId: null,
      agencierId: null,
      companierId: null,
      candidateId: null,
      employerId: null,
    });
    const conflict = await auth(request(app).put(`/api/v1/iam/users/${admin.id}/bindings`)).send({
      domain: 'companier',
      targetId: 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    });
    expect(conflict.status).toBe(409);
  });

  it('creates an employer-candidate assignment and audits it', async () => {
    mocks.tx.candidate.findUnique.mockResolvedValue({ id: assignment.candidateId });
    mocks.tx.employer.findUnique.mockResolvedValue({ id: assignment.employerId });
    mocks.tx.employerCandidate.findFirst.mockResolvedValue(null);
    mocks.tx.employerCandidate.create.mockResolvedValue(assignment);
    const response = await auth(request(app).post('/api/v1/employer-candidates')).send({
      employerId: assignment.employerId,
      candidateId: assignment.candidateId,
      purpose: 'FAVORITE',
    });
    expect(response.status).toBe(201);
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'employer_candidate.created' }),
      })
    );
  });

  it('rejects a second active membership for the same triple', async () => {
    mocks.tx.candidate.findUnique.mockResolvedValue({ id: assignment.candidateId });
    mocks.tx.employer.findUnique.mockResolvedValue({ id: assignment.employerId });
    mocks.tx.employerCandidate.findFirst.mockResolvedValue({ id: assignment.id });
    const response = await auth(request(app).post('/api/v1/employer-candidates')).send({
      employerId: assignment.employerId,
      candidateId: assignment.candidateId,
      purpose: 'FAVORITE',
    });
    expect(response.status).toBe(409);
  });

  it('inactivates an assignment instead of deleting it', async () => {
    mocks.tx.employerCandidate.findUnique.mockResolvedValue(assignment);
    mocks.tx.employerCandidate.update.mockResolvedValue({ ...assignment, status: 'I' });
    const response = await auth(
      request(app).patch(`/api/v1/employer-candidates/${assignment.id}/status`)
    ).send({ status: 'I' });
    expect(response.status).toBe(200);
    expect(response.body.data.status).toBe('I');
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'employer_candidate.status.changed' }),
      })
    );
  });

  it('does not expose a DELETE employer-candidate route', async () => {
    const response = await auth(request(app).delete(`/api/v1/employer-candidates/${assignment.id}`));
    expect(response.status).toBe(404);
  });

  it('prevents an employer from assigning for another employer', async () => {
    const response = await auth(request(app).post('/api/v1/employer-candidates'), employerUser).send({
      employerId: 'ffffffff-ffff-4fff-8fff-ffffffffffff',
      candidateId: assignment.candidateId,
      purpose: 'RESERVE',
    });
    expect(response.status).toBe(404);
  });

  it('prevents a candidate from managing assignments', async () => {
    const response = await auth(request(app).post('/api/v1/employer-candidates'), candidateUser).send({
      employerId: assignment.employerId,
      candidateId: assignment.candidateId,
      purpose: 'SELECTED',
    });
    expect(response.status).toBe(403);
  });
});
