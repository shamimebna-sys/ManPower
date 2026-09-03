import { beforeEach, describe, expect, it, vi } from 'vitest';
import request from 'supertest';
import { Prisma } from '@prisma/client';

const mocks = vi.hoisted(() => {
  const tx = {
    candidate: {
      create: vi.fn(),
      update: vi.fn(),
      findUnique: vi.fn(),
      count: vi.fn(),
    },
    auditEvent: { create: vi.fn() },
    $queryRaw: vi.fn(),
  };
  return {
    tx,
    prisma: {
      session: { findUnique: vi.fn() },
      candidate: {
        findMany: vi.fn(),
        findUnique: vi.fn(),
        count: vi.fn(),
      },
      auditEvent: { create: vi.fn() },
      $queryRaw: vi.fn(),
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
    LOG_LEVEL: 'info',
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
      key: 'employee',
      permissions: [
        { permission: { key: 'candidate.read' } },
        { permission: { key: 'candidate.create' } },
        { permission: { key: 'candidate.update' } },
        { permission: { key: 'candidate.status.manage' } },
      ],
    },
  }],
};

const reader = {
  ...actor,
  roles: [{ role: { key: 'reader', permissions: [{ permission: { key: 'candidate.read' } }] } }],
};

const session = {
  id: '22222222-2222-4222-8222-222222222222',
  csrfHash: 'a58806c745411a50a426fc29be96491986d7620cdbc6e0084c75f853bb7944a1',
  expiresAt: new Date(Date.now() + 60_000),
  revokedAt: null,
  user: actor,
};

const candidate = {
  id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  code: '9000001',
  name: 'Test Candidate',
  email: 'candidate@example.com',
  secondaryEmail: null,
  mobile: '01700000000',
  secondaryMobile: null,
  emergencyMobile: null,
  bid: null,
  bidFileRef: null,
  nid: '1990123456789',
  nidFileRef: null,
  passportNo: 'A1234567',
  passportIssueDate: null,
  passportExpireDate: null,
  passportFileRef: null,
  dob: null,
  fullPhotoFileRef: null,
  halfPhotoFileRef: null,
  fatherName: null,
  motherName: null,
  nationality: 'Bangladeshi',
  gender: 'Male',
  bloodGroup: null,
  presentAddressHouse: null,
  presentAddressRoad: null,
  presentAddressVillage: null,
  presentAddressPost: null,
  presentAddressThanaId: null,
  presentAddressDistrictId: null,
  presentAddressDivisionId: null,
  permanentAddressHouse: null,
  permanentAddressRoad: null,
  permanentAddressVillage: null,
  permanentAddressPost: null,
  permanentAddressThanaId: null,
  permanentAddressDistrictId: null,
  permanentAddressDivisionId: null,
  basicInfoCareer: null,
  basicInfoSpecial: null,
  otherSkills: null,
  balance: new Prisma.Decimal('0'),
  cvFileRef: null,
  status: 'A',
  createdAt: new Date('2026-01-01T00:00:00.000Z'),
  updatedAt: new Date('2026-01-01T00:00:00.000Z'),
  agentId: 10n,
  classGroupId: 3n,
  remarks: null,
  replacementRemarks: null,
  admissionPaymentId: null,
  finalGroupPaymentId: null,
  medicalFeePaymentId: null,
  abroadEx: 0,
  localEx: 0,
  driveLink: null,
  facebookLink: null,
  youtubeLink: null,
  linkedinLink: null,
  twitterLink: null,
  instagramLink: null,
  position: null,
  skillCertificateFileRef: null,
  height: null,
  weight: null,
  maritalStatus: null,
  expertise: null,
  skills: null,
  extraCurricular: null,
  interest: null,
  attribute: null,
  companierId: null,
  agencierId: null,
  stampFileRef: null,
  positionId: null,
  companyStatus: 'ACTIVE',
  subAgentId: null,
  countryId: null,
  replacedByLegacyId: null,
};

describe('candidate API', () => {
  const app = createApp();
  const auth = (call: request.Test, user = actor) => {
    mocks.prisma.session.findUnique.mockResolvedValue({ ...session, user });
    return call
      .set('Cookie', ['manpower_session=session-token', 'manpower_csrf=csrf-token'])
      .set('X-CSRF-Token', 'csrf-token');
  };

  beforeEach(() => {
    vi.clearAllMocks();
    mocks.tx.candidate.count.mockResolvedValue(0);
    mocks.tx.$queryRaw.mockResolvedValue([{ next: 1n }]);
    mocks.tx.auditEvent.create.mockResolvedValue({});
  });

  it('creates a candidate and writes an audit event', async () => {
    mocks.tx.candidate.create.mockResolvedValue(candidate);
    const response = await auth(request(app).post('/api/v1/candidates')).send({
      name: 'Test Candidate',
      email: 'candidate@example.com',
      mobile: '01700000000',
      passportNo: 'A1234567',
      agentId: 10,
      classGroupId: 3,
    });
    expect(response.status).toBe(201);
    expect(response.body.data.email).toBe('candidate@example.com');
    expect(mocks.tx.$queryRaw).toHaveBeenCalled();
    expect(mocks.tx.candidate.count).not.toHaveBeenCalled();
    expect(response.body.data.passwordHash).toBeUndefined();
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'candidate.created' }) })
    );
    const auditPayload = JSON.stringify(mocks.tx.auditEvent.create.mock.calls);
    expect(auditPayload).not.toContain('A1234567');
    expect(auditPayload).not.toContain('1990123456789');
  });

  it('retrieves a candidate', async () => {
    mocks.prisma.candidate.findUnique.mockResolvedValue(candidate);
    const response = await auth(request(app).get(`/api/v1/candidates/${candidate.id}`));
    expect(response.status).toBe(200);
    expect(response.body.data.code).toBe('9000001');
    expect(response.body.data.agentId).toBe('10');
  });

  it('rejects status changes on the general update endpoint', async () => {
    const response = await auth(request(app).patch(`/api/v1/candidates/${candidate.id}`)).send({
      status: 'I',
    });
    expect(response.status).toBe(400);
    expect(mocks.tx.candidate.update).not.toHaveBeenCalled();
  });

  it('updates a candidate without changing status', async () => {
    mocks.tx.candidate.findUnique.mockResolvedValue({ id: candidate.id });
    mocks.tx.candidate.update.mockResolvedValue({ ...candidate, name: 'Updated' });
    const response = await auth(request(app).patch(`/api/v1/candidates/${candidate.id}`)).send({
      name: 'Updated',
    });
    expect(response.status).toBe(200);
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'candidate.updated' }) })
    );
  });

  it('rejects invalid create payloads', async () => {
    const response = await auth(request(app).post('/api/v1/candidates')).send({
      name: '',
      email: 'not-an-email',
    });
    expect(response.status).toBe(422);
  });

  it('paginates candidate lists', async () => {
    mocks.prisma.candidate.findMany.mockResolvedValue([candidate, { ...candidate, id: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' }]);
    mocks.prisma.candidate.count.mockResolvedValue(2);
    const response = await auth(request(app).get('/api/v1/candidates?limit=1'));
    expect(response.status).toBe(200);
    expect(response.body.data.items).toHaveLength(1);
    expect(response.body.meta.pagination.nextCursor).toBe(candidate.id);
    expect(response.body.meta.pagination.total).toBe(2);
  });

  it('filters candidates by status and search fields', async () => {
    mocks.prisma.candidate.findMany.mockResolvedValue([candidate]);
    mocks.prisma.candidate.count.mockResolvedValue(1);
    const response = await auth(
      request(app).get('/api/v1/candidates?status=A&passportNo=A123&q=Test')
    );
    expect(response.status).toBe(200);
    expect(mocks.prisma.candidate.findMany).toHaveBeenCalledWith(
      expect.objectContaining({
        where: expect.objectContaining({
          status: 'A',
          passportNo: expect.objectContaining({ contains: 'A123' }),
        }),
      })
    );
  });

  it('returns 401 without authentication', async () => {
    const response = await request(app).get('/api/v1/candidates');
    expect(response.status).toBe(401);
  });

  it('returns 403 without candidate.read', async () => {
    const response = await auth(request(app).get('/api/v1/candidates'), {
      ...actor,
      roles: [{ role: { key: 'viewer', permissions: [] } }],
    });
    expect(response.status).toBe(403);
  });

  it('returns 403 when a reader tries to create', async () => {
    const response = await auth(request(app).post('/api/v1/candidates'), reader).send({
      name: 'Test Candidate',
      email: 'candidate@example.com',
      mobile: '01700000000',
      passportNo: 'A1234567',
      agentId: 10,
      classGroupId: 3,
    });
    expect(response.status).toBe(403);
  });

  it('audits status changes and does not hard-delete', async () => {
    mocks.tx.candidate.findUnique.mockResolvedValue({ id: candidate.id, status: 'A' });
    mocks.tx.candidate.update.mockResolvedValue({ ...candidate, status: 'I' });
    const response = await auth(
      request(app).patch(`/api/v1/candidates/${candidate.id}/status`)
    ).send({ status: 'I' });
    expect(response.status).toBe(200);
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'candidate.status.changed' }),
      })
    );
    expect(mocks.tx.candidate.update).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ status: 'I' }) })
    );
  });

  it('maps unique conflicts to 409', async () => {
    mocks.tx.candidate.create.mockRejectedValue({ code: 'P2002' });
    const response = await auth(request(app).post('/api/v1/candidates')).send({
      name: 'Test Candidate',
      email: 'candidate@example.com',
      mobile: '01700000000',
      passportNo: 'A1234567',
      agentId: 10,
      classGroupId: 3,
    });
    expect(response.status).toBe(409);
  });

  it('creates and audits inside one transaction', async () => {
    mocks.tx.candidate.create.mockResolvedValue(candidate);
    await auth(request(app).post('/api/v1/candidates')).send({
      name: 'Test Candidate',
      email: 'candidate@example.com',
      mobile: '01700000000',
      passportNo: 'A1234567',
      agentId: 10,
      classGroupId: 3,
    });
    expect(mocks.prisma.$transaction).toHaveBeenCalled();
    expect(mocks.tx.candidate.create).toHaveBeenCalled();
    expect(mocks.tx.auditEvent.create).toHaveBeenCalled();
  });
});
