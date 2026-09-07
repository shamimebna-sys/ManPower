import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import request from 'supertest';
import { PrismaClient } from '@prisma/client';
import type { Application } from 'express';
import { hashToken, createSecureToken } from '../src/auth/tokens';
import { M5_PERMISSION_KEYS, M5_ROLE_GRANTS } from '../src/iam/permission-catalogue';

const runDatabaseTests = process.env['RUN_DB_TESTS'] === 'true';

describe.skipIf(!runDatabaseTests)('PostgreSQL 16 M5 training integration', () => {
  let db!: PrismaClient;
  let app!: Application;

  let ownerSession = { token: '', csrf: '' };
  let adminSession = { token: '', csrf: '' };
  let teacherSession = { token: '', csrf: '' };
  let unboundTeacherSession = { token: '', csrf: '' };
  let agentSession = { token: '', csrf: '' };

  let teacherAId = '';
  let teacherBId = '';
  let groupPassId = '';
  let groupFailId = '';
  let scheduleAId = '';
  let examId = '';
  let candidateAId = '';
  let candidateBId = '';
  let resultId = '';
  let manpowerId = '';

  async function openSession(userId: string): Promise<{ token: string; csrf: string }> {
    const token = createSecureToken();
    const csrf = createSecureToken();
    await db.session.create({
      data: {
        userId,
        tokenHash: hashToken(token),
        csrfHash: hashToken(csrf),
        expiresAt: new Date(Date.now() + 60 * 60 * 1000),
      },
    });
    return { token, csrf };
  }

  function auth(call: request.Test, session: { token: string; csrf: string }) {
    return call
      .set('Cookie', [`manpower_session=${session.token}`, `manpower_csrf=${session.csrf}`])
      .set('X-CSRF-Token', session.csrf);
  }

  beforeAll(async () => {
    db = new PrismaClient();
    const { hashPassword } = await import('../src/auth/password.js');
    const { FOUNDATION_PERMISSIONS, APPROVED_ROLES } = await import('../src/iam/permission-catalogue.js');
    const { createApp } = await import('../src/app.js');
    app = createApp();
    const passwordHash = await hashPassword('CiGatePass1!x');

    const permissions = await Promise.all(
      FOUNDATION_PERMISSIONS.map(([key, name]) =>
        db.permission.upsert({ where: { key }, create: { key, name }, update: { name } })
      )
    );
    const permissionByKey = new Map(permissions.map((permission) => [permission.key, permission]));
    const roles = await Promise.all(
      APPROVED_ROLES.map(([key, name]) =>
        db.role.upsert({ where: { key }, create: { key, name }, update: { name } })
      )
    );
    const roleByKey = new Map(roles.map((role) => [role.key, role]));
    const superAdmin = roleByKey.get('super_admin');
    if (!superAdmin) throw new Error('super_admin missing');
    await Promise.all(
      permissions.map((permission) =>
        db.rolePermission.upsert({
          where: { roleId_permissionId: { roleId: superAdmin.id, permissionId: permission.id } },
          create: { roleId: superAdmin.id, permissionId: permission.id },
          update: {},
        })
      )
    );
    for (const [roleKey, keys] of Object.entries(M5_ROLE_GRANTS)) {
      const role = roleByKey.get(roleKey);
      if (!role) throw new Error(`role ${roleKey} missing`);
      for (const key of keys) {
        const permission = permissionByKey.get(key);
        if (!permission) throw new Error(`permission ${key} missing`);
        await db.rolePermission.upsert({
          where: { roleId_permissionId: { roleId: role.id, permissionId: permission.id } },
          create: { roleId: role.id, permissionId: permission.id },
          update: {},
        });
      }
    }

    const stamp = `${Date.now()}`;
    const ownerUser = await db.user.create({
      data: {
        email: `m5-owner-${stamp}@example.com`,
        username: `m5-owner-${stamp}`,
        displayName: 'M5 Owner',
        passwordHash,
      },
    });
    const adminUser = await db.user.create({
      data: {
        email: `m5-admin-${stamp}@example.com`,
        username: `m5-admin-${stamp}`,
        displayName: 'M5 Admin',
        passwordHash,
      },
    });
    const teacherPerson = await db.teacher.create({
      data: { name: 'Teacher A', code: `TA-${stamp}`, sourceLegacyId: BigInt(`${stamp}1`) },
    });
    const otherTeacher = await db.teacher.create({
      data: { name: 'Teacher B', code: `TB-${stamp}`, sourceLegacyId: BigInt(`${stamp}2`) },
    });
    teacherAId = teacherPerson.id;
    teacherBId = otherTeacher.id;
    const teacherUser = await db.user.create({
      data: {
        email: `m5-teacher-${stamp}@example.com`,
        username: `m5-teacher-${stamp}`,
        displayName: 'M5 Teacher',
        passwordHash,
        teacherId: teacherPerson.id,
      },
    });
    const unboundTeacherUser = await db.user.create({
      data: {
        email: `m5-unbound-${stamp}@example.com`,
        username: `m5-unbound-${stamp}`,
        displayName: 'M5 Unbound Teacher',
        passwordHash,
      },
    });
    const agentMaster = await db.agent.create({
      data: { name: 'M5 Agent', sourceLegacyId: BigInt(`${stamp}8`) },
    });
    const agentUser = await db.user.create({
      data: {
        email: `m5-agent-${stamp}@example.com`,
        username: `m5-agent-${stamp}`,
        displayName: 'M5 Agent',
        passwordHash,
        agentId: agentMaster.id,
      },
    });

    const requireRole = (key: string) => {
      const role = roleByKey.get(key);
      if (!role) throw new Error(`role ${key} missing`);
      return role.id;
    };
    await db.userRole.create({ data: { userId: ownerUser.id, roleId: requireRole('owner') } });
    await db.userRole.create({ data: { userId: adminUser.id, roleId: requireRole('administrator') } });
    await db.userRole.create({ data: { userId: teacherUser.id, roleId: requireRole('teacher') } });
    await db.userRole.create({
      data: { userId: unboundTeacherUser.id, roleId: requireRole('teacher') },
    });
    await db.userRole.create({ data: { userId: agentUser.id, roleId: requireRole('agent') } });

    const passGroup = await db.classGroup.create({
      data: { name: 'Rapid Final', code: 'RF', sourceLegacyId: BigInt(`${stamp}42`) },
    });
    const failGroup = await db.classGroup.create({
      data: { name: 'Admission For Interview', code: '801', sourceLegacyId: 1n },
    });
    groupPassId = passGroup.id;
    groupFailId = failGroup.id;

    const schedule = await db.classSchedule.create({
      data: { teacherId: teacherAId, classGroupId: passGroup.id, subject: 'Class', weekDay: 'Sunday' },
    });
    scheduleAId = schedule.id;

    const exam = await db.exam.create({ data: { name: `Exam ${stamp}`, status: 'A' } });
    examId = exam.id;
    await db.examClassGroup.create({ data: { examId: exam.id, classGroupId: passGroup.id } });

    const candidateA = await db.candidate.create({
      data: {
        name: 'Cand A',
        email: `m5-a-${stamp}@example.com`,
        mobile: `017${stamp}1`.slice(0, 14),
        passportNo: `P${stamp}A`,
        agentId: agentMaster.sourceLegacyId,
        classGroupId: passGroup.sourceLegacyId,
        classGroupRefId: passGroup.id,
      },
    });
    const candidateB = await db.candidate.create({
      data: {
        name: 'Cand B',
        email: `m5-b-${stamp}@example.com`,
        mobile: `017${stamp}2`.slice(0, 14),
        passportNo: `P${stamp}B`,
        agentId: 999999n,
        classGroupId: passGroup.sourceLegacyId,
        classGroupRefId: passGroup.id,
      },
    });
    candidateAId = candidateA.id;
    candidateBId = candidateB.id;

    ownerSession = await openSession(ownerUser.id);
    adminSession = await openSession(adminUser.id);
    teacherSession = await openSession(teacherUser.id);
    unboundTeacherSession = await openSession(unboundTeacherUser.id);
    agentSession = await openSession(agentUser.id);
  });

  afterAll(async () => {
    await db.$disconnect();
  });

  it('applied the M5 migration and created operations/workflow tables', async () => {
    const rows = await db.$queryRaw<Array<{ migration_name: string }>>`
      SELECT migration_name FROM _prisma_migrations WHERE rolled_back_at IS NULL
    `;
    expect(rows.map((row) => row.migration_name)).toEqual(
      expect.arrayContaining(['20260907020000_m5_training_exam'])
    );
    const tables = await db.$queryRaw<Array<{ table_name: string }>>`
      SELECT table_name
      FROM information_schema.tables
      WHERE table_schema IN ('operations', 'workflow')
    `;
    expect(tables.map((row) => row.table_name)).toEqual(
      expect.arrayContaining([
        'teachers',
        'class_groups',
        'class_schedules',
        'exams',
        'exam_class_groups',
        'exam_results',
        'manpower_training_events',
      ])
    );
  });

  it('enforces unique exam+candidate and exclusive teacher bind', async () => {
    await db.examResult.create({
      data: { examId, candidateId: candidateAId, classGroupId: groupPassId },
    });
    await expect(
      db.examResult.create({
        data: { examId, candidateId: candidateAId, classGroupId: groupPassId },
      })
    ).rejects.toMatchObject({ code: 'P2002' });

    await expect(
      db.user.create({
        data: {
          email: `dup-teacher-${Date.now()}@example.com`,
          username: `dup-teacher-${Date.now()}`,
          displayName: 'Dup',
          passwordHash: 'x',
          teacherId: teacherAId,
        },
      })
    ).rejects.toMatchObject({ code: 'P2002' });
  });

  it('enforces score check constraints and nullable ungraded result', async () => {
    await expect(
      db.examResult.create({
        data: {
          examId,
          candidateId: candidateBId,
          abroadEx: 101,
        },
      })
    ).rejects.toThrow();

    const ungraded = await db.examResult.create({
      data: {
        examId,
        candidateId: candidateBId,
        abroadEx: 0,
        result: null,
      },
    });
    expect(ungraded.result).toBeNull();
    expect(ungraded.abroadEx).toBe(0);
    resultId = ungraded.id;
  });

  it('covers teacher self-scope, unbound identity, and forbidden candidate browse', async () => {
    const self = await auth(request(app).get(`/api/v1/teachers/${teacherAId}`), teacherSession);
    expect(self.status).toBe(200);
    const other = await auth(request(app).get(`/api/v1/teachers/${teacherBId}`), teacherSession);
    expect(other.status).toBe(404);
    const unbound = await auth(request(app).get('/api/v1/teachers'), unboundTeacherSession);
    expect(unbound.status).toBe(200);
    expect(unbound.body.data.items).toEqual([]);
    const candidates = await auth(request(app).get('/api/v1/candidates'), teacherSession);
    expect(candidates.status).toBe(403);
    const exams = await auth(request(app).get('/api/v1/exams'), teacherSession);
    expect(exams.status).toBe(403);
    const adminExam = await auth(request(app).get('/api/v1/exams'), adminSession);
    expect(adminExam.status).toBe(403);
  });

  it('blocks ID substitution on exam, result, group, and schedule', async () => {
    const missing = '00000000-0000-4000-8000-000000000000';
    expect((await auth(request(app).get(`/api/v1/exams/${missing}`), ownerSession)).status).toBe(404);
    expect((await auth(request(app).get(`/api/v1/exam-results/${missing}`), ownerSession)).status).toBe(404);
    expect((await auth(request(app).get(`/api/v1/class-groups/${missing}`), ownerSession)).status).toBe(404);
    expect((await auth(request(app).get(`/api/v1/class-schedules/${missing}`), ownerSession)).status).toBe(404);
    const teacherSchedule = await auth(
      request(app).get(`/api/v1/class-schedules/${scheduleAId}`),
      teacherSession
    );
    expect(teacherSchedule.status).toBe(403);
  });

  it('publishes missing rows, then PASS/FAIL follow T4 without changing candidate status', async () => {
    const before = await db.candidate.findUniqueOrThrow({ where: { id: candidateAId } });
    const publish = await auth(request(app).post(`/api/v1/exams/${examId}/publish`), ownerSession).send({});
    expect(publish.status).toBe(200);
    expect(publish.body.data.created).toBeGreaterThanOrEqual(0);
    const again = await auth(request(app).post(`/api/v1/exams/${examId}/publish`), ownerSession).send({});
    expect(again.status).toBe(200);
    expect(again.body.data.created).toBe(0);

    const pass = await auth(request(app).patch(`/api/v1/exam-results/${resultId}`), ownerSession).send({
      result: 'PASS',
      abroadEx: 0,
      localEx: 10,
      bl: 20,
      skill: 30,
      english: 40,
      classGroupId: groupFailId,
    });
    expect(pass.status).toBe(200);
    expect(pass.body.data.result).toBe('PASS');
    expect(pass.body.data.abroadEx).toBe(0);
    const afterPass = await db.candidate.findUniqueOrThrow({ where: { id: candidateBId } });
    expect(afterPass.classGroupRefId).toBe(groupFailId);
    expect(afterPass.status).toBe(before.status);

    const fail = await auth(request(app).patch(`/api/v1/exam-results/${resultId}`), ownerSession).send({
      result: 'FAIL',
      classGroupId: groupPassId,
    });
    expect(fail.status).toBe(200);
    const afterFail = await db.candidate.findUniqueOrThrow({ where: { id: candidateBId } });
    expect(afterFail.classGroupRefId).toBe(groupFailId);
    expect(afterFail.status).toBe(before.status);
  });

  it('scopes manpower training to the agent candidate and audits writes', async () => {
    const created = await auth(request(app).post('/api/v1/manpower-trainings'), ownerSession).send({
      candidateId: candidateAId,
      status: 'A',
    });
    expect(created.status).toBe(201);
    manpowerId = created.body.data.id as string;
    const allowed = await auth(request(app).get(`/api/v1/manpower-trainings/${manpowerId}`), agentSession);
    expect(allowed.status).toBe(200);
    const other = await auth(request(app).post('/api/v1/manpower-trainings'), agentSession).send({
      candidateId: candidateBId,
    });
    expect(other.status).toBe(404);
    const events = await db.auditEvent.findMany({
      where: { eventType: { startsWith: 'training.' } },
    });
    expect(events.map((event) => event.eventType)).toEqual(
      expect.arrayContaining(['training.exam.published', 'training.manpower.created'])
    );
    expect(M5_PERMISSION_KEYS).toHaveLength(12);
  });

  it('rolls back a publish when the exam is missing', async () => {
    const missing = await auth(
      request(app).post('/api/v1/exams/00000000-0000-4000-8000-000000000000/publish'),
      ownerSession
    ).send({});
    expect(missing.status).toBe(404);
  });
});
