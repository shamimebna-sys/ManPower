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
});
