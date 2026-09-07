import { beforeEach, describe, expect, it, vi } from 'vitest';
import request from 'supertest';

const mocks = vi.hoisted(() => {
  const tx = {
    teacher: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    classGroup: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn(), findMany: vi.fn() },
    classSchedule: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    exam: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    examClassGroup: { deleteMany: vi.fn(), upsert: vi.fn() },
    examResult: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn(), findMany: vi.fn(), count: vi.fn() },
    candidate: { findUnique: vi.fn(), findMany: vi.fn(), update: vi.fn() },
    manpowerTrainingEvent: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    user: { findUnique: vi.fn(), update: vi.fn() },
    auditEvent: { create: vi.fn() },
  };
  return {
    tx,
    prisma: {
      session: { findUnique: vi.fn() },
      teacher: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      classGroup: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      classSchedule: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      exam: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      examResult: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
      candidate: { findUnique: vi.fn(), findMany: vi.fn() },
      manpowerTrainingEvent: { findMany: vi.fn(), findUnique: vi.fn(), count: vi.fn() },
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

const teacherRow = {
  id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  sourceLegacyId: 1n,
  code: 'T1',
  name: 'Teacher One',
  email: 't1@example.com',
  secondaryEmail: null,
  mobile: '01700000001',
  secondaryMobile: null,
  emergencyMobile: null,
  nid: null,
  passportNo: null,
  dob: null,
  gender: null,
  nationality: null,
  fatherName: null,
  motherName: null,
  bloodGroup: null,
  status: 'A',
  createdAt: new Date('2026-01-01T00:00:00.000Z'),
  updatedAt: new Date('2026-01-01T00:00:00.000Z'),
};

const otherTeacher = { ...teacherRow, id: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', name: 'Teacher Two' };

const group = {
  id: 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
  sourceLegacyId: 42n,
  name: 'Rapid Group',
  description: null,
  code: 'G42',
  status: 'A',
  feeAmount: { toString: () => '0' },
  createdAt: new Date('2026-01-01T00:00:00.000Z'),
  updatedAt: new Date('2026-01-01T00:00:00.000Z'),
};

const failGroup = { ...group, id: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', sourceLegacyId: 1n, name: 'Admission For Interview' };

const owner = {
  id: '11111111-1111-4111-8111-111111111111',
  email: 'owner@example.com',
  username: 'owner',
  displayName: 'Owner',
  passwordHash: '$hash',
  status: 'ACTIVE' as const,
  teacherId: null as string | null,
  roles: [{
    role: {
      key: 'owner',
      permissions: [
        { permission: { key: 'training.teacher.read' } },
        { permission: { key: 'training.teacher.manage' } },
        { permission: { key: 'training.class_group.read' } },
        { permission: { key: 'training.class_group.manage' } },
        { permission: { key: 'training.schedule.read' } },
        { permission: { key: 'training.schedule.manage' } },
        { permission: { key: 'training.exam.read' } },
        { permission: { key: 'training.exam.manage' } },
        { permission: { key: 'training.exam_result.read' } },
        { permission: { key: 'training.exam_result.manage' } },
        { permission: { key: 'training.manpower.read' } },
        { permission: { key: 'training.manpower.manage' } },
      ],
    },
  }],
};

const administrator = {
  ...owner,
  roles: [{
    role: {
      key: 'administrator',
      permissions: [
        { permission: { key: 'training.teacher.read' } },
        { permission: { key: 'training.class_group.read' } },
        { permission: { key: 'training.schedule.read' } },
        { permission: { key: 'training.manpower.read' } },
      ],
    },
  }],
};

const teacherUser = {
  ...owner,
  teacherId: teacherRow.id,
  roles: [{
    role: {
      key: 'teacher',
      permissions: [{ permission: { key: 'training.teacher.read' } }],
    },
  }],
};

const unboundTeacher = {
  ...teacherUser,
  teacherId: null,
};

const session = {
  id: '22222222-2222-4222-8222-222222222222',
  csrfHash: 'a58806c745411a50a426fc29be96491986d7620cdbc6e0084c75f853bb7944a1',
  expiresAt: new Date(Date.now() + 60_000),
  revokedAt: null,
  user: owner,
};

describe('M5 training API', () => {
  const app = createApp();
  const auth = (call: request.Test, user: typeof owner = owner) => {
    mocks.prisma.session.findUnique.mockResolvedValue({ ...session, user });
    return call
      .set('Cookie', ['manpower_session=session-token', 'manpower_csrf=csrf-token'])
      .set('X-CSRF-Token', 'csrf-token');
  };

  beforeEach(() => {
    vi.clearAllMocks();
    mocks.tx.auditEvent.create.mockResolvedValue({});
  });

  it('creates a teacher and audits the write', async () => {
    mocks.tx.teacher.create.mockResolvedValue(teacherRow);
    const response = await auth(request(app).post('/api/v1/teachers')).send({ name: 'Teacher One' });
    expect(response.status).toBe(201);
    expect(response.body.data.name).toBe('Teacher One');
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'training.teacher.created' }),
      })
    );
  });

  it('lets a bound teacher read only self and 404s another teacher', async () => {
    mocks.prisma.teacher.findUnique.mockResolvedValue(otherTeacher);
    const denied = await auth(request(app).get(`/api/v1/teachers/${otherTeacher.id}`), teacherUser);
    expect(denied.status).toBe(404);

    mocks.prisma.teacher.findUnique.mockResolvedValue(teacherRow);
    const allowed = await auth(request(app).get(`/api/v1/teachers/${teacherRow.id}`), teacherUser);
    expect(allowed.status).toBe(200);
    expect(allowed.body.data.id).toBe(teacherRow.id);
  });

  it('returns an empty teacher list for an unbound teacher', async () => {
    const response = await auth(request(app).get('/api/v1/teachers'), unboundTeacher);
    expect(response.status).toBe(200);
    expect(response.body.data.items).toEqual([]);
    expect(mocks.prisma.teacher.findMany).not.toHaveBeenCalled();
  });

  it('blocks teacher candidate browsing and exam access', async () => {
    const candidates = await auth(request(app).get('/api/v1/candidates'), teacherUser);
    expect(candidates.status).toBe(403);
    const exams = await auth(request(app).get('/api/v1/exams'), teacherUser);
    expect(exams.status).toBe(403);
    const results = await auth(request(app).get('/api/v1/exam-results'), teacherUser);
    expect(results.status).toBe(403);
  });

  it('blocks administrator exam and exam-result access', async () => {
    expect((await auth(request(app).get('/api/v1/exams'), administrator)).status).toBe(403);
    expect((await auth(request(app).get('/api/v1/exam-results'), administrator)).status).toBe(403);
  });

  it('returns 404 for class-group and schedule ID substitution when missing', async () => {
    mocks.prisma.classGroup.findUnique.mockResolvedValue(null);
    expect((await auth(request(app).get('/api/v1/class-groups/eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee'))).status).toBe(404);
    mocks.prisma.classSchedule.findUnique.mockResolvedValue(null);
    expect((await auth(request(app).get('/api/v1/class-schedules/eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee'))).status).toBe(404);
  });

  it('publishes missing exam results transactionally and is idempotent', async () => {
    const examId = 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee';
    const candidateId = 'ffffffff-ffff-4fff-8fff-ffffffffffff';
    mocks.tx.exam.findUnique.mockResolvedValue({
      id: examId,
      status: 'A',
      classGroups: [{ classGroupId: group.id }],
    });
    mocks.tx.classGroup.findMany.mockResolvedValue([group]);
    mocks.tx.candidate.findMany.mockResolvedValue([
      { id: candidateId, classGroupRefId: group.id, classGroupId: 42n },
    ]);
    mocks.tx.examResult.findMany.mockResolvedValue([]);
    mocks.tx.examResult.create.mockResolvedValue({ id: '99999999-9999-4999-8999-999999999999' });
    mocks.tx.examResult.count.mockResolvedValue(1);
    const first = await auth(request(app).post(`/api/v1/exams/${examId}/publish`)).send({});
    expect(first.status).toBe(200);
    expect(first.body.data.created).toBe(1);
    expect(mocks.tx.examResult.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({
          examId,
          candidateId,
          result: null,
          abroadEx: null,
        }),
      })
    );
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ eventType: 'training.exam.published' }),
      })
    );

    mocks.tx.examResult.findMany.mockResolvedValue([{ candidateId }]);
    mocks.tx.examResult.count.mockResolvedValue(1);
    const second = await auth(request(app).post(`/api/v1/exams/${examId}/publish`)).send({});
    expect(second.status).toBe(200);
    expect(second.body.data.created).toBe(0);
  });

  it('PASS updates candidate class group and leaves status unchanged; FAIL does not move the group', async () => {
    const resultId = '99999999-9999-4999-8999-999999999999';
    const candidateId = 'ffffffff-ffff-4fff-8fff-ffffffffffff';
    const existing = {
      id: resultId,
      examId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
      candidateId,
      classGroupId: group.id,
      abroadEx: null,
      localEx: null,
      bl: null,
      skill: null,
      english: null,
      result: null,
      remarks: null,
      status: 'A',
      sourceLegacyId: null,
      createdAt: new Date('2026-01-01T00:00:00.000Z'),
      updatedAt: new Date('2026-01-01T00:00:00.000Z'),
      candidate: { id: candidateId, status: 'A', classGroupId: 42n, classGroupRefId: group.id },
    };
    mocks.tx.examResult.findUnique.mockResolvedValue(existing);
    mocks.tx.classGroup.findUnique.mockResolvedValue({ id: failGroup.id, sourceLegacyId: 99n });
    mocks.tx.examResult.update.mockResolvedValue({
      ...existing,
      result: 'PASS',
      abroadEx: 0,
      classGroupId: failGroup.id,
    });
    const pass = await auth(request(app).patch(`/api/v1/exam-results/${resultId}`)).send({
      result: 'PASS',
      abroadEx: 0,
      classGroupId: failGroup.id,
    });
    expect(pass.status).toBe(200);
    expect(pass.body.data.result).toBe('PASS');
    expect(pass.body.data.abroadEx).toBe(0);
    expect(mocks.tx.candidate.update).toHaveBeenCalledWith({
      where: { id: candidateId },
      data: { classGroupRefId: failGroup.id, classGroupId: 99n },
    });

    mocks.tx.candidate.update.mockClear();
    mocks.tx.examResult.findUnique.mockResolvedValue({
      ...existing,
      result: 'PASS',
      candidate: { id: candidateId, status: 'A', classGroupId: 99n, classGroupRefId: failGroup.id },
    });
    mocks.tx.examResult.update.mockResolvedValue({ ...existing, result: 'FAIL', classGroupId: group.id });
    const fail = await auth(request(app).patch(`/api/v1/exam-results/${resultId}`)).send({
      result: 'FAIL',
      classGroupId: failGroup.id,
    });
    expect(fail.status).toBe(200);
    expect(fail.body.data.result).toBe('FAIL');
    expect(mocks.tx.candidate.update).not.toHaveBeenCalled();
    expect(mocks.tx.examResult.update).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({ classGroupId: group.id }),
      })
    );
  });

  it('blocks unauthorized manpower IDOR and exam ID substitution', async () => {
    const agentUser = {
      ...owner,
      roles: [{
        role: {
          key: 'agent',
          permissions: [
            { permission: { key: 'training.manpower.read' } },
            { permission: { key: 'training.manpower.manage' } },
          ],
        },
      }],
    };
    mocks.prisma.manpowerTrainingEvent.findUnique.mockResolvedValue({
      id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
      candidateId: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
      sourceLegacyId: null,
      certificateIssueDate: null,
      certificateExpireDate: null,
      certificateFileRef: null,
      manpowerFileRef: null,
      fingerPrintFileRef: null,
      trainingStartDate: null,
      trainingEndDate: null,
      status: 'A',
      createdAt: new Date('2026-01-01T00:00:00.000Z'),
      updatedAt: new Date('2026-01-01T00:00:00.000Z'),
      candidate: {
        id: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        agentId: 99n,
        subAgentId: null,
        agencierId: null,
        companierId: null,
      },
    });
    const manpower = await auth(
      request(app).get('/api/v1/manpower-trainings/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
      agentUser
    );
    expect(manpower.status).toBe(404);

    mocks.prisma.exam.findUnique.mockResolvedValue(null);
    const exam = await auth(request(app).get('/api/v1/exams/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    expect(exam.status).toBe(404);
  });
});
