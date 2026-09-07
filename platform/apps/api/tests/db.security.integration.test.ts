import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import request from 'supertest';
import { PrismaClient } from '@prisma/client';
import type { Application } from 'express';
import { hashToken, createSecureToken } from '../src/auth/tokens';

const runDatabaseTests = process.env['RUN_DB_TESTS'] === 'true';

describe.skipIf(!runDatabaseTests)('PostgreSQL 16 security integration', () => {
  let db!: PrismaClient;
  let app!: Application;

  const fixtures = {
    admin: {
      email: 'ci-admin@example.com',
      password: 'CiGatePass1!x',
    },
    viewer: {
      email: 'ci-viewer@example.com',
      password: 'CiGatePass1!x',
    },
    agent: {
      email: 'ci-agent@example.com',
      password: 'CiGatePass1!x',
    },
  };

  let adminSession = { token: '', csrf: '' };
  let viewerSession = { token: '', csrf: '' };
  let agentSession = { token: '', csrf: '' };
  let candidateAId = '';
  let candidateBId = '';
  let educationId = '';

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

  function auth(call: request.Test, session: { token: string; csrf: string }, csrfHeader = true) {
    const req = call.set('Cookie', [`manpower_session=${session.token}`, `manpower_csrf=${session.csrf}`]);
    return csrfHeader ? req.set('X-CSRF-Token', session.csrf) : req;
  }

  beforeAll(async () => {
    db = new PrismaClient();
    const { hashPassword } = await import('../src/auth/password.js');
    const { FOUNDATION_PERMISSIONS } = await import('../src/iam/permission-catalogue.js');
    const { createApp } = await import('../src/app.js');
    app = createApp();

    const passwordHash = await hashPassword(fixtures.admin.password);
    const permissions = await Promise.all(
      FOUNDATION_PERMISSIONS.map(([key, name]) =>
        db.permission.upsert({
          where: { key },
          create: { key, name },
          update: { name },
        })
      )
    );
    const superAdmin = await db.role.upsert({
      where: { key: 'super_admin' },
      create: { key: 'super_admin', name: 'Super Admin', isSystem: true },
      update: {},
    });
    const employee = await db.role.upsert({
      where: { key: 'employee' },
      create: { key: 'employee', name: 'Employee' },
      update: {},
    });
    const agent = await db.role.upsert({
      where: { key: 'agent' },
      create: { key: 'agent', name: 'Agent' },
      update: {},
    });
    await Promise.all(
      permissions.map((permission) =>
        db.rolePermission.upsert({
          where: {
            roleId_permissionId: { roleId: superAdmin.id, permissionId: permission.id },
          },
          create: { roleId: superAdmin.id, permissionId: permission.id },
          update: {},
        })
      )
    );
    // Isolate this suite from M4/M5/M6 grant seeds on the shared CI database.
    // The viewer fixture is an employee with no candidate.read. Do not rely on
    // leftover runtime grants from other integration files.
    await db.rolePermission.deleteMany({ where: { roleId: employee.id } });
    const read = permissions.find((permission) => permission.key === 'candidate.read');
    if (!read) {
      throw new Error('Required candidate permissions are missing');
    }
    await db.rolePermission.upsert({
      where: { roleId_permissionId: { roleId: agent.id, permissionId: read.id } },
      create: { roleId: agent.id, permissionId: read.id },
      update: {},
    });

    const adminUser = await db.user.upsert({
      where: { email: fixtures.admin.email },
      create: {
        email: fixtures.admin.email,
        username: 'ci-admin',
        displayName: 'CI Admin',
        passwordHash,
      },
      update: { passwordHash },
    });
    const viewerUser = await db.user.upsert({
      where: { email: fixtures.viewer.email },
      create: {
        email: fixtures.viewer.email,
        username: 'ci-viewer',
        displayName: 'CI Viewer',
        passwordHash,
      },
      update: { passwordHash },
    });
    const agentUser = await db.user.upsert({
      where: { email: fixtures.agent.email },
      create: {
        email: fixtures.agent.email,
        username: 'ci-agent',
        displayName: 'CI Agent',
        passwordHash,
      },
      update: { passwordHash },
    });
    await db.userRole.upsert({
      where: { userId_roleId: { userId: adminUser.id, roleId: superAdmin.id } },
      create: { userId: adminUser.id, roleId: superAdmin.id },
      update: {},
    });
    await db.userRole.upsert({
      where: { userId_roleId: { userId: viewerUser.id, roleId: employee.id } },
      create: { userId: viewerUser.id, roleId: employee.id },
      update: {},
    });
    await db.userRole.upsert({
      where: { userId_roleId: { userId: agentUser.id, roleId: agent.id } },
      create: { userId: agentUser.id, roleId: agent.id },
      update: {},
    });

    adminSession = await openSession(adminUser.id);
    viewerSession = await openSession(viewerUser.id);
    agentSession = await openSession(agentUser.id);

    const stamp = `${Date.now()}`;
    const candidateA = await db.candidate.create({
      data: {
        name: 'Security A',
        email: `sec-a-${stamp}@example.com`,
        mobile: `030${stamp.slice(-8)}`,
        passportNo: `SECA${stamp}`,
        agentId: 10,
        classGroupId: 1,
        code: `9SA${stamp}`.slice(0, 10),
      },
    });
    const candidateB = await db.candidate.create({
      data: {
        name: 'Security B',
        email: `sec-b-${stamp}@example.com`,
        mobile: `031${stamp.slice(-8)}`,
        passportNo: `SECB${stamp}`,
        agentId: 20,
        classGroupId: 1,
        code: `9SB${stamp}`.slice(0, 10),
      },
    });
    const education = await db.candidateEducation.create({
      data: { candidateId: candidateA.id, examName: 'HSC' },
    });
    candidateAId = candidateA.id;
    candidateBId = candidateB.id;
    educationId = education.id;
  }, 60_000);

  afterAll(async () => {
    await db.$disconnect();
  });

  it('returns 401 without authentication', async () => {
    const response = await request(app).get('/api/v1/candidates');
    expect(response.status).toBe(401);
  });

  it('returns 403 when the role has no candidate.read grant', async () => {
    const response = await auth(request(app).get('/api/v1/candidates'), viewerSession);
    expect(response.status).toBe(403);
  });

  it('returns 403 when CSRF is missing on a mutation', async () => {
    const response = await auth(
      request(app).post('/api/v1/candidates').send({
        name: 'CSRF Blocked',
        email: 'csrf-blocked@example.com',
        mobile: '01711111111',
        passportNo: 'CSRF1',
        agentId: 1,
        classGroupId: 1,
      }),
      adminSession,
      false
    );
    expect(response.status).toBe(403);
  });

  it('returns 404 for child IDOR across candidate parents', async () => {
    const response = await auth(
      request(app).patch(`/api/v1/candidates/${candidateBId}/educations/${educationId}`).send({
        examName: 'Stolen',
      }),
      adminSession
    );
    expect(response.status).toBe(404);
    const untouched = await db.candidateEducation.findUnique({ where: { id: educationId } });
    expect(untouched?.examName).toBe('HSC');
  });

  it('returns an empty list for an agent without a bound agentId', async () => {
    const response = await auth(request(app).get('/api/v1/candidates'), agentSession);
    expect(response.status).toBe(200);
    expect(response.body.data.items).toEqual([]);
  });

  it('returns 422 for invalid create payloads against the live API', async () => {
    const response = await auth(request(app).post('/api/v1/candidates'), adminSession).send({
      name: '',
      email: 'not-an-email',
    });
    expect(response.status).toBe(422);
  });

  it('returns 409 for a unique email conflict on the live database', async () => {
    const existing = await db.candidate.findUnique({ where: { id: candidateAId } });
    const response = await auth(request(app).post('/api/v1/candidates'), adminSession).send({
      name: 'Dup Email',
      email: existing?.email,
      mobile: '01722222222',
      passportNo: 'DUPMAIL1',
      agentId: 1,
      classGroupId: 1,
    });
    expect(response.status).toBe(409);
  });

  it('denies teacher and unauthorized roles from M4 management endpoints', async () => {
    const teacherRole = await db.role.upsert({
      where: { key: 'teacher' },
      create: { key: 'teacher', name: 'Teacher' },
      update: {},
    });
    const { hashPassword } = await import('../src/auth/password.js');
    const teacherUser = await db.user.create({
      data: {
        email: `ci-teacher-${Date.now()}@example.com`,
        username: `ci-teacher-${Date.now()}`,
        displayName: 'CI Teacher',
        passwordHash: await hashPassword(fixtures.admin.password),
      },
    });
    await db.userRole.create({ data: { userId: teacherUser.id, roleId: teacherRole.id } });
    const teacherSession = await openSession(teacherUser.id);
    const candidates = await auth(request(app).get('/api/v1/candidates'), teacherSession);
    const partners = await auth(request(app).get('/api/v1/partners/agent'), teacherSession);
    const manage = await auth(request(app).post('/api/v1/partners/agent'), teacherSession).send({
      name: 'Blocked',
    });
    expect(candidates.status).toBe(403);
    expect(partners.status).toBe(403);
    expect(manage.status).toBe(403);
  });

  it('prevents agent A from reading agent B candidates after UUID binding', async () => {
    const mark = `${Date.now()}`;
    const agentA = await db.agent.create({
      data: { name: 'Agent A', sourceLegacyId: BigInt(`8${mark.slice(-7)}`) },
    });
    const agentB = await db.agent.create({
      data: { name: 'Agent B', sourceLegacyId: BigInt(`9${mark.slice(-7)}`) },
    });
    const visible = await db.candidate.create({
      data: {
        name: 'Bound A',
        email: `bound-a-${mark}@example.com`,
        mobile: `050${mark.slice(-8)}`,
        passportNo: `BDA${mark}`,
        agentId: agentA.sourceLegacyId,
        classGroupId: 1,
        code: `9BA${mark}`.slice(0, 10),
      },
    });
    const hidden = await db.candidate.create({
      data: {
        name: 'Bound B',
        email: `bound-b-${mark}@example.com`,
        mobile: `051${mark.slice(-8)}`,
        passportNo: `BDB${mark}`,
        agentId: agentB.sourceLegacyId,
        classGroupId: 1,
        code: `9BB${mark}`.slice(0, 10),
      },
    });
    const agentRole = await db.role.findUnique({ where: { key: 'agent' } });
    if (!agentRole) throw new Error('agent role missing');
    const { hashPassword } = await import('../src/auth/password.js');
    const userA = await db.user.create({
      data: {
        email: `bound-agent-${mark}@example.com`,
        username: `bound-agent-${mark}`,
        displayName: 'Bound Agent',
        passwordHash: await hashPassword(fixtures.admin.password),
        agentId: agentA.id,
      },
    });
    await db.userRole.create({ data: { userId: userA.id, roleId: agentRole.id } });
    const sessionA = await openSession(userA.id);
    const list = await auth(request(app).get('/api/v1/candidates'), sessionA);
    expect(list.status).toBe(200);
    const ids = (list.body.data.items as Array<{ id: string }>).map((item) => item.id);
    expect(ids).toContain(visible.id);
    expect(ids).not.toContain(hidden.id);
    const denied = await auth(request(app).get(`/api/v1/candidates/${hidden.id}`), sessionA);
    expect(denied.status).toBe(404);
  });

  it('prevents an employer from assigning outside its bound identity', async () => {
    const mark = `${Date.now()}`;
    const employerA = await db.employer.create({ data: { name: `Emp A ${mark}` } });
    const employerB = await db.employer.create({ data: { name: `Emp B ${mark}` } });
    const employerRole = await db.role.upsert({
      where: { key: 'employer' },
      create: { key: 'employer', name: 'Employer' },
      update: {},
    });
    const manage = await db.permission.upsert({
      where: { key: 'employer_candidate.manage' },
      create: { key: 'employer_candidate.manage', name: 'Manage employer candidates' },
      update: {},
    });
    await db.rolePermission.upsert({
      where: { roleId_permissionId: { roleId: employerRole.id, permissionId: manage.id } },
      create: { roleId: employerRole.id, permissionId: manage.id },
      update: {},
    });
    const { hashPassword } = await import('../src/auth/password.js');
    const userA = await db.user.create({
      data: {
        email: `emp-a-${mark}@example.com`,
        username: `emp-a-${mark}`,
        displayName: 'Employer A',
        passwordHash: await hashPassword(fixtures.admin.password),
        employerId: employerA.id,
      },
    });
    await db.userRole.create({ data: { userId: userA.id, roleId: employerRole.id } });
    const sessionA = await openSession(userA.id);
    const response = await auth(request(app).post('/api/v1/employer-candidates'), sessionA).send({
      employerId: employerB.id,
      candidateId: candidateAId,
      purpose: 'FAVORITE',
    });
    expect(response.status).toBe(404);
  });
});
