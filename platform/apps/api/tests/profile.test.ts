import { beforeEach, describe, expect, it, vi } from 'vitest';
import request from 'supertest';
import { Prisma } from '@prisma/client';

const mocks = vi.hoisted(() => {
  const tx = {
    candidateEducation: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    candidateExperience: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    candidateSkill: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    candidateSkillTag: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn(), findFirst: vi.fn() },
    candidateLanguage: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    candidateTraining: { create: vi.fn(), update: vi.fn(), findUnique: vi.fn() },
    auditEvent: { create: vi.fn() },
  };
  return {
    tx,
    prisma: {
      session: { findUnique: vi.fn() },
      candidate: { findUnique: vi.fn() },
      candidateEducation: { findMany: vi.fn(), count: vi.fn() },
      candidateExperience: { findMany: vi.fn(), count: vi.fn() },
      candidateSkill: { findMany: vi.fn(), count: vi.fn() },
      candidateSkillTag: { findMany: vi.fn(), count: vi.fn() },
      candidateLanguage: { findMany: vi.fn(), count: vi.fn() },
      candidateTraining: { findMany: vi.fn(), count: vi.fn() },
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

const candidateId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
const otherCandidateId = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
const recordId = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';

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
        { permission: { key: 'candidate.education.read' } },
        { permission: { key: 'candidate.education.manage' } },
        { permission: { key: 'candidate.experience.read' } },
        { permission: { key: 'candidate.experience.manage' } },
        { permission: { key: 'candidate.skills.read' } },
        { permission: { key: 'candidate.skills.manage' } },
        { permission: { key: 'candidate.languages.read' } },
        { permission: { key: 'candidate.languages.manage' } },
        { permission: { key: 'candidate.training.read' } },
        { permission: { key: 'candidate.training.manage' } },
      ],
    },
  }],
};

const reader = {
  ...actor,
  roles: [{ role: { key: 'reader', permissions: [{ permission: { key: 'candidate.education.read' } }] } }],
};

const session = {
  id: '22222222-2222-4222-8222-222222222222',
  csrfHash: 'a58806c745411a50a426fc29be96491986d7620cdbc6e0084c75f853bb7944a1',
  expiresAt: new Date(Date.now() + 60_000),
  revokedAt: null,
  user: actor,
};

const stamps = {
  createdAt: new Date('2026-01-01T00:00:00.000Z'),
  updatedAt: new Date('2026-01-01T00:00:00.000Z'),
};

describe('candidate supporting domains', () => {
  const app = createApp();
  const auth = (call: request.Test, user = actor) => {
    mocks.prisma.session.findUnique.mockResolvedValue({ ...session, user });
    return call
      .set('Cookie', ['manpower_session=session-token', 'manpower_csrf=csrf-token'])
      .set('X-CSRF-Token', 'csrf-token');
  };

  beforeEach(() => {
    vi.clearAllMocks();
    mocks.prisma.candidate.findUnique.mockResolvedValue({ id: candidateId });
    mocks.tx.auditEvent.create.mockResolvedValue({});
  });

  it('creates, reads, and updates education with audit', async () => {
    const education = {
      id: recordId,
      candidateId,
      examName: 'SSC',
      instituteName: 'Test School',
      subjectGroupMajor: 'Science',
      educationLabel: 'SSC',
      startDate: null,
      endDate: null,
      durationYear: 5,
      resultType: null,
      result: '4.75',
      achievements: null,
      certificateFileRef: null,
      boardName: 'Jessore',
      scale: '5.00',
      passingYear: '2015',
      status: 'A',
      sourceUserId: null,
      createdByLegacyId: null,
      updatedByLegacyId: null,
      ...stamps,
    };
    mocks.tx.candidateEducation.create.mockResolvedValue(education);
    const created = await auth(request(app).post(`/api/v1/candidates/${candidateId}/educations`)).send({
      examName: 'SSC',
      instituteName: 'Test School',
      result: '4.75',
    });
    expect(created.status).toBe(201);
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'candidate.education.created' }) })
    );

    mocks.prisma.candidateEducation.findMany.mockResolvedValue([education]);
    mocks.prisma.candidateEducation.count.mockResolvedValue(1);
    const listed = await auth(request(app).get(`/api/v1/candidates/${candidateId}/educations`));
    expect(listed.status).toBe(200);
    expect(listed.body.data.items).toHaveLength(1);

    mocks.tx.candidateEducation.findUnique.mockResolvedValue(education);
    mocks.tx.candidateEducation.update.mockResolvedValue({ ...education, result: '5.00' });
    const updated = await auth(request(app).patch(`/api/v1/candidates/${candidateId}/educations/${recordId}`)).send({
      result: '5.00',
    });
    expect(updated.status).toBe(200);
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'candidate.education.updated' }) })
    );
  });

  it('rejects invalid education payloads', async () => {
    const response = await auth(request(app).post(`/api/v1/candidates/${candidateId}/educations`)).send({
      examName: '',
    });
    expect(response.status).toBe(422);
  });

  it('creates experience, validates dates, and audits', async () => {
    const invalid = await auth(request(app).post(`/api/v1/candidates/${candidateId}/experiences`)).send({
      companyName: 'Hotel',
      startDate: '2024-12-01',
      endDate: '2023-01-01',
    });
    expect(invalid.status).toBe(422);

    const experience = {
      id: recordId,
      candidateId,
      companyName: 'Hotel',
      companyAddress: 'Dhaka',
      countryRef: 'Bangladesh',
      designation: 'Cleaner',
      department: null,
      startDate: new Date('2020-01-01T00:00:00.000Z'),
      endDate: null,
      responsibilities: 'Clean rooms',
      expertise: null,
      descriptions: null,
      achievements: null,
      status: 'A',
      sourceUserId: null,
      createdByLegacyId: null,
      updatedByLegacyId: null,
      ...stamps,
    };
    mocks.tx.candidateExperience.create.mockResolvedValue(experience);
    const created = await auth(request(app).post(`/api/v1/candidates/${candidateId}/experiences`)).send({
      companyName: 'Hotel',
      startDate: '2020-01-01',
    });
    expect(created.status).toBe(201);
    expect(created.body.data.endDate).toBeNull();
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'candidate.experience.created' }) })
    );

    mocks.prisma.candidateExperience.findMany.mockResolvedValue([experience]);
    mocks.prisma.candidateExperience.count.mockResolvedValue(1);
    const listed = await auth(request(app).get(`/api/v1/candidates/${candidateId}/experiences`));
    expect(listed.status).toBe(200);

    mocks.tx.candidateExperience.findUnique.mockResolvedValue(experience);
    mocks.tx.candidateExperience.update.mockResolvedValue({ ...experience, designation: 'Supervisor' });
    const updated = await auth(request(app).patch(`/api/v1/candidates/${candidateId}/experiences/${recordId}`)).send({
      designation: 'Supervisor',
    });
    expect(updated.status).toBe(200);
  });

  it('assigns skills, reads them, and rejects duplicate active skill tags', async () => {
    const skill = {
      id: recordId,
      candidateId,
      title: 'IELTS',
      instituteName: 'British Council',
      details: null,
      resultScore: new Prisma.Decimal('6.5'),
      examScore: new Prisma.Decimal('9'),
      certificateFileRef: null,
      status: 'A',
      sourceUserId: null,
      createdByLegacyId: null,
      updatedByLegacyId: null,
      ...stamps,
    };
    mocks.tx.candidateSkill.create.mockResolvedValue(skill);
    const created = await auth(request(app).post(`/api/v1/candidates/${candidateId}/skills`)).send({
      title: 'IELTS',
      resultScore: 6.5,
      examScore: 9,
    });
    expect(created.status).toBe(201);
    expect(created.body.data.resultScore).toBe('6.5');
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'candidate.skill.added' }) })
    );

    mocks.prisma.candidateSkill.findMany.mockResolvedValue([skill]);
    mocks.prisma.candidateSkill.count.mockResolvedValue(1);
    const listed = await auth(request(app).get(`/api/v1/candidates/${candidateId}/skills`));
    expect(listed.status).toBe(200);

    mocks.tx.candidateSkillTag.findFirst.mockResolvedValue({ id: recordId });
    const duplicate = await auth(request(app).post(`/api/v1/candidates/${candidateId}/skill-list`)).send({
      skillName: 'Cooking',
    });
    expect(duplicate.status).toBe(409);
  });

  it('adds and reads languages without inventing proficiency enums', async () => {
    const language = {
      id: recordId,
      candidateId,
      languageName: 'English',
      languageStatus: 'Very Good',
      status: 'A',
      sourceUserId: null,
      ...stamps,
    };
    mocks.tx.candidateLanguage.create.mockResolvedValue(language);
    const created = await auth(request(app).post(`/api/v1/candidates/${candidateId}/languages`)).send({
      languageName: 'English',
      languageStatus: 'Very Good',
    });
    expect(created.status).toBe(201);
    expect(created.body.data.languageStatus).toBe('Very Good');
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'candidate.language.added' }) })
    );

    mocks.prisma.candidateLanguage.findMany.mockResolvedValue([language]);
    mocks.prisma.candidateLanguage.count.mockResolvedValue(1);
    const listed = await auth(request(app).get(`/api/v1/candidates/${candidateId}/languages`));
    expect(listed.status).toBe(200);
  });

  it('creates, reads, and updates profile training (not manpower training)', async () => {
    const training = {
      id: recordId,
      candidateId,
      title: 'Kitchen Cleaner',
      instituteName: 'Bhulta High School',
      topics: 'Hygiene',
      startDate: new Date('2025-08-01T00:00:00.000Z'),
      endDate: new Date('2025-08-31T00:00:00.000Z'),
      duration: 0,
      durationType: null,
      countryRef: 'Bangladesh',
      descriptions: null,
      achievements: null,
      certificateFileRef: null,
      address: 'Dhaka',
      status: 'A',
      sourceUserId: null,
      createdByLegacyId: null,
      updatedByLegacyId: null,
      ...stamps,
    };
    mocks.tx.candidateTraining.create.mockResolvedValue(training);
    const created = await auth(request(app).post(`/api/v1/candidates/${candidateId}/trainings`)).send({
      title: 'Kitchen Cleaner',
      startDate: '2025-08-01',
      endDate: '2025-08-31',
    });
    expect(created.status).toBe(201);
    expect(mocks.tx.auditEvent.create).toHaveBeenCalledWith(
      expect.objectContaining({ data: expect.objectContaining({ eventType: 'candidate.training.created' }) })
    );

    mocks.prisma.candidateTraining.findMany.mockResolvedValue([training]);
    mocks.prisma.candidateTraining.count.mockResolvedValue(1);
    const listed = await auth(request(app).get(`/api/v1/candidates/${candidateId}/trainings`));
    expect(listed.status).toBe(200);

    mocks.tx.candidateTraining.findUnique.mockResolvedValue(training);
    mocks.tx.candidateTraining.update.mockResolvedValue({ ...training, topics: 'Safety' });
    const updated = await auth(request(app).patch(`/api/v1/candidates/${candidateId}/trainings/${recordId}`)).send({
      topics: 'Safety',
    });
    expect(updated.status).toBe(200);
  });

  it('returns 401 without authentication', async () => {
    const response = await request(app).get(`/api/v1/candidates/${candidateId}/educations`);
    expect(response.status).toBe(401);
  });

  it('returns 403 without manage permission and without CSRF', async () => {
    const forbidden = await auth(
      request(app).post(`/api/v1/candidates/${candidateId}/educations`),
      reader
    ).send({ examName: 'SSC' });
    expect(forbidden.status).toBe(403);

    mocks.prisma.session.findUnique.mockResolvedValue({ ...session, user: actor });
    const csrf = await request(app)
      .post(`/api/v1/candidates/${candidateId}/educations`)
      .set('Cookie', ['manpower_session=session-token'])
      .send({ examName: 'SSC' });
    expect(csrf.status).toBe(403);
  });

  it('returns 404 when the candidate does not exist', async () => {
    mocks.prisma.candidate.findUnique.mockResolvedValue(null);
    const response = await auth(request(app).get(`/api/v1/candidates/${candidateId}/educations`));
    expect(response.status).toBe(404);
    expect(response.body.error.message).toBe('Candidate not found');
  });

  it('returns 404 when a child record belongs to another candidate', async () => {
    mocks.tx.candidateEducation.findUnique.mockResolvedValue({
      id: recordId,
      candidateId: otherCandidateId,
    });
    const response = await auth(
      request(app).patch(`/api/v1/candidates/${candidateId}/educations/${recordId}`)
    ).send({ result: '5.00' });
    expect(response.status).toBe(404);
    expect(mocks.tx.candidateEducation.update).not.toHaveBeenCalled();
  });

  it('returns 422 for malformed identifiers', async () => {
    const response = await auth(request(app).get('/api/v1/candidates/not-a-uuid/educations'));
    expect(response.status).toBe(422);
  });

  it('rolls back when audit write fails inside the transaction', async () => {
    mocks.tx.candidateEducation.create.mockResolvedValue({
      id: recordId,
      candidateId,
      examName: 'SSC',
      instituteName: null,
      subjectGroupMajor: null,
      educationLabel: null,
      startDate: null,
      endDate: null,
      durationYear: null,
      resultType: null,
      result: null,
      achievements: null,
      certificateFileRef: null,
      boardName: null,
      scale: null,
      passingYear: null,
      status: 'A',
      sourceUserId: null,
      createdByLegacyId: null,
      updatedByLegacyId: null,
      ...stamps,
    });
    mocks.tx.auditEvent.create.mockRejectedValue(new Error('audit failed'));
    const response = await auth(request(app).post(`/api/v1/candidates/${candidateId}/educations`)).send({
      examName: 'SSC',
    });
    expect(response.status).toBe(500);
    expect(mocks.prisma.$transaction).toHaveBeenCalled();
    expect(mocks.tx.candidateEducation.create).toHaveBeenCalled();
    expect(mocks.tx.auditEvent.create).toHaveBeenCalled();
  });
});
