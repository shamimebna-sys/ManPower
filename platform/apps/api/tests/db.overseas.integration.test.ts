import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import request from 'supertest';
import { PrismaClient } from '@prisma/client';
import type { Application } from 'express';
import { hashToken, createSecureToken } from '../src/auth/tokens';
import { M4_ROLE_GRANTS, M6_ROLE_GRANTS } from '../src/iam/permission-catalogue';

const runDatabaseTests = process.env['RUN_DB_TESTS'] === 'true';

describe.skipIf(!runDatabaseTests)('PostgreSQL 16 M6 overseas integration', () => {
  let db!: PrismaClient;
  let app!: Application;
  let ownerSession = { token: '', csrf: '' };
  let adminSession = { token: '', csrf: '' };
  let employeeSession = { token: '', csrf: '' };
  let superAdminSession = { token: '', csrf: '' };
  let agentSession = { token: '', csrf: '' };
  let subAgentSession = { token: '', csrf: '' };
  let agencySession = { token: '', csrf: '' };
  let unboundAgentSession = { token: '', csrf: '' };
  let companySession = { token: '', csrf: '' };
  let teacherSession = { token: '', csrf: '' };
  let employerSession = { token: '', csrf: '' };
  let candidateSession = { token: '', csrf: '' };
  let candidateAId = '';
  let candidateBId = '';
  let subCandidateId = '';
  let agencyCandidateId = '';
  let companierId = '';
  let secondCompanierId = '';
  let paymentFkBefore = { admission: null as bigint | null, medical: null as bigint | null };

  async function openSession(userId: string) {
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
    for (const grants of [M4_ROLE_GRANTS, M6_ROLE_GRANTS]) {
      for (const [roleKey, keys] of Object.entries(grants)) {
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
    }

    const stamp = `${Date.now()}`;
    const agentMaster = await db.agent.create({ data: { name: 'M6 Agent', sourceLegacyId: BigInt(`${stamp}1`) } });
    const subAgentMaster = await db.subAgent.create({
      data: { name: 'M6 Sub', sourceLegacyId: BigInt(`${stamp}11`), agentId: agentMaster.id },
    });
    const agencierMaster = await db.agencier.create({
      data: { name: 'M6 Agency', sourceLegacyId: BigInt(`${stamp}12`) },
    });
    const companier = await db.companier.create({
      data: { name: 'M6 Co', sourceLegacyId: BigInt(`${stamp}2`) },
    });
    const companierB = await db.companier.create({
      data: { name: 'M6 Co B', sourceLegacyId: BigInt(`${stamp}22`) },
    });
    companierId = companier.id;
    secondCompanierId = companierB.id;
    const ownerUser = await db.user.create({
      data: { email: `m6-owner-${stamp}@example.com`, username: `m6-owner-${stamp}`, displayName: 'M6 Owner', passwordHash },
    });
    const adminUser = await db.user.create({
      data: { email: `m6-admin-${stamp}@example.com`, username: `m6-admin-${stamp}`, displayName: 'M6 Admin', passwordHash },
    });
    const employeeUser = await db.user.create({
      data: { email: `m6-emp-${stamp}@example.com`, username: `m6-emp-${stamp}`, displayName: 'M6 Emp', passwordHash },
    });
    const superAdminUser = await db.user.create({
      data: { email: `m6-sa-${stamp}@example.com`, username: `m6-sa-${stamp}`, displayName: 'M6 SA', passwordHash },
    });
    const agentUser = await db.user.create({
      data: {
        email: `m6-agent-${stamp}@example.com`,
        username: `m6-agent-${stamp}`,
        displayName: 'M6 Agent',
        passwordHash,
        agentId: agentMaster.id,
      },
    });
    const subAgentUser = await db.user.create({
      data: {
        email: `m6-sub-${stamp}@example.com`,
        username: `m6-sub-${stamp}`,
        displayName: 'M6 Sub',
        passwordHash,
        subAgentId: subAgentMaster.id,
      },
    });
    const agencyUser = await db.user.create({
      data: {
        email: `m6-agency-${stamp}@example.com`,
        username: `m6-agency-${stamp}`,
        displayName: 'M6 Agency',
        passwordHash,
        agencierId: agencierMaster.id,
      },
    });
    const unboundAgent = await db.user.create({
      data: { email: `m6-unbound-${stamp}@example.com`, username: `m6-unbound-${stamp}`, displayName: 'Unbound', passwordHash },
    });
    const companyUser = await db.user.create({
      data: {
        email: `m6-co-${stamp}@example.com`,
        username: `m6-co-${stamp}`,
        displayName: 'Company',
        passwordHash,
        companierId: companier.id,
      },
    });
    const teacherUser = await db.user.create({
      data: { email: `m6-t-${stamp}@example.com`, username: `m6-t-${stamp}`, displayName: 'Teacher', passwordHash },
    });
    const employerUser = await db.user.create({
      data: { email: `m6-e-${stamp}@example.com`, username: `m6-e-${stamp}`, displayName: 'Employer', passwordHash },
    });
    const selfCandidate = await db.candidate.create({
      data: { name: 'Self', email: `m6-self-${stamp}@example.com`, mobile: `070${stamp.slice(-8)}`, passportNo: `PS${stamp}` },
    });
    const candidateUser = await db.user.create({
      data: {
        email: `m6-cand-${stamp}@example.com`,
        username: `m6-cand-${stamp}`,
        displayName: 'Candidate',
        passwordHash,
        candidateId: selfCandidate.id,
      },
    });
    const requireRole = (key: string) => {
      const role = roleByKey.get(key);
      if (!role) throw new Error(`role ${key} missing`);
      return role.id;
    };
    await db.userRole.create({ data: { userId: ownerUser.id, roleId: requireRole('owner') } });
    await db.userRole.create({ data: { userId: adminUser.id, roleId: requireRole('administrator') } });
    await db.userRole.create({ data: { userId: employeeUser.id, roleId: requireRole('employee') } });
    await db.userRole.create({ data: { userId: superAdminUser.id, roleId: requireRole('super_admin') } });
    await db.userRole.create({ data: { userId: agentUser.id, roleId: requireRole('agent') } });
    await db.userRole.create({ data: { userId: subAgentUser.id, roleId: requireRole('sub_agent') } });
    await db.userRole.create({ data: { userId: agencyUser.id, roleId: requireRole('agency') } });
    await db.userRole.create({ data: { userId: unboundAgent.id, roleId: requireRole('agent') } });
    await db.userRole.create({ data: { userId: companyUser.id, roleId: requireRole('company') } });
    await db.userRole.create({ data: { userId: teacherUser.id, roleId: requireRole('teacher') } });
    await db.userRole.create({ data: { userId: employerUser.id, roleId: requireRole('employer') } });
    await db.userRole.create({ data: { userId: candidateUser.id, roleId: requireRole('candidate') } });

    const candidateA = await db.candidate.create({
      data: {
        name: 'M6 A',
        email: `m6-a-${stamp}@example.com`,
        mobile: `071${stamp.slice(-8)}`,
        passportNo: `PA${stamp}`,
        agentId: agentMaster.sourceLegacyId,
        admissionPaymentId: 41n,
        medicalFeePaymentId: 42n,
      },
    });
    const candidateB = await db.candidate.create({
      data: {
        name: 'M6 B',
        email: `m6-b-${stamp}@example.com`,
        mobile: `072${stamp.slice(-8)}`,
        passportNo: `PB${stamp}`,
        agentId: 888888n,
      },
    });
    const subCandidate = await db.candidate.create({
      data: {
        name: 'M6 SubCand',
        email: `m6-sc-${stamp}@example.com`,
        mobile: `074${stamp.slice(-8)}`,
        passportNo: `PC${stamp}`,
        subAgentId: subAgentMaster.sourceLegacyId,
      },
    });
    const agencyCandidate = await db.candidate.create({
      data: {
        name: 'M6 AgCand',
        email: `m6-ac-${stamp}@example.com`,
        mobile: `075${stamp.slice(-8)}`,
        passportNo: `PD${stamp}`,
        agencierId: agencierMaster.sourceLegacyId,
      },
    });
    candidateAId = candidateA.id;
    candidateBId = candidateB.id;
    subCandidateId = subCandidate.id;
    agencyCandidateId = agencyCandidate.id;
    paymentFkBefore = { admission: candidateA.admissionPaymentId, medical: candidateA.medicalFeePaymentId };
    ownerSession = await openSession(ownerUser.id);
    adminSession = await openSession(adminUser.id);
    employeeSession = await openSession(employeeUser.id);
    superAdminSession = await openSession(superAdminUser.id);
    agentSession = await openSession(agentUser.id);
    subAgentSession = await openSession(subAgentUser.id);
    agencySession = await openSession(agencyUser.id);
    unboundAgentSession = await openSession(unboundAgent.id);
    companySession = await openSession(companyUser.id);
    teacherSession = await openSession(teacherUser.id);
    employerSession = await openSession(employerUser.id);
    candidateSession = await openSession(candidateUser.id);
  });

  afterAll(async () => {
    await db.$disconnect();
  });

  it('applies the M6 migration on PostgreSQL 16', async () => {
    const version = await db.$queryRaw<Array<{ version: string }>>`SELECT version()`;
    expect(version[0]?.version).toMatch(/PostgreSQL 16\./i);
    const rows = await db.$queryRaw<Array<{ migration_name: string }>>`
      SELECT migration_name FROM _prisma_migrations WHERE rolled_back_at IS NULL
    `;
    expect(rows.map((row) => row.migration_name)).toEqual(
      expect.arrayContaining(['20260907160000_m6_overseas_processing'])
    );
  });

  it('seeds exact live-status labels including legacy spelling', async () => {
    const labels = await db.liveStatusLookup.findMany({ orderBy: { stepNo: 'asc' } });
    expect(labels.map((row) => row.name)).toEqual([
      'Registration',
      'Profile Update/CV',
      'Group Name',
      'Interview',
      'Selection',
      'Labour Contact',
      'Police Clearance & Medical',
      'VISA/Work Permite',
      'Manpower Status',
      'Flight',
    ]);
  });

  it('creates medical/police/ARC/labour/visa/flight without invented prerequisites', async () => {
    const labour = await auth(request(app).post('/api/v1/overseas/labour-contracts'), ownerSession)
      .send({ candidateId: candidateAId })
      .expect(201);
    expect(labour.body.data.candidateId).toBe(candidateAId);
    await auth(request(app).post('/api/v1/overseas/police-clearances'), ownerSession)
      .send({ candidateId: candidateAId })
      .expect(201);
    await auth(request(app).post('/api/v1/overseas/medicals'), ownerSession)
      .send({ candidateId: candidateAId, status: 'A' })
      .expect(201);
    await auth(request(app).post('/api/v1/overseas/arcs'), ownerSession)
      .send({ candidateId: candidateAId })
      .expect(201);
    await auth(request(app).post('/api/v1/overseas/visas'), ownerSession)
      .send({ candidateId: candidateAId, visaMpNo: 'MP-1' })
      .expect(201);
    await auth(request(app).post('/api/v1/overseas/flights'), ownerSession)
      .send({ candidateId: candidateAId, airlineceName: 'Test Air' })
      .expect(201);
  });

  it('rejects DELETE and public file URLs', async () => {
    await auth(request(app).delete(`/api/v1/overseas/medicals/${candidateAId}`), ownerSession).expect(404);
    await auth(request(app).post('/api/v1/overseas/medicals'), ownerSession)
      .send({ candidateId: candidateAId, documentFileId: 'https://example.com/file.pdf' })
      .expect(422);
  });

  it('orders latest() by created_at then source_legacy_id and allows duplicates', async () => {
    const stamp = Date.now();
    const createdAt = new Date('2024-01-01T00:00:00.000Z');
    const low = await db.labourContract.create({
      data: { candidateId: candidateAId, sourceLegacyId: BigInt(`${stamp}10`), createdAt, updatedAt: createdAt },
    });
    const high = await db.labourContract.create({
      data: { candidateId: candidateAId, sourceLegacyId: BigInt(`${stamp}20`), createdAt, updatedAt: createdAt },
    });
    const listed = await auth(request(app).get(`/api/v1/overseas/labour-contracts?candidateId=${candidateAId}`), ownerSession)
      .expect(200);
    const ids = listed.body.data.items.map((row: { id: string }) => row.id);
    expect(ids.indexOf(high.id)).toBeLessThan(ids.indexOf(low.id));
  });

  it('derives live-status skipping step 5 and ignoring medical/ARC', async () => {
    const onlyMedical = await db.candidate.create({
      data: {
        name: 'Med only',
        email: `m6-med-${Date.now()}@example.com`,
        mobile: `073${String(Date.now()).slice(-8)}`,
        passportNo: `PM${Date.now()}`,
      },
    });
    await db.candidateMedical.create({ data: { candidateId: onlyMedical.id } });
    await db.candidateArc.create({ data: { candidateId: onlyMedical.id } });
    const badge = await auth(request(app).get(`/api/v1/live-status/${onlyMedical.id}`), ownerSession).expect(200);
    expect(badge.body.data.stepNo).toBe(1);
    expect(badge.body.data.name).toBe('Registration');
    expect(badge.body.data.skippedStep5).toBe(true);
    expect(badge.body.data.medicalOnLadder).toBe(false);
    expect(badge.body.data.arcOnLadder).toBe(false);
    expect(badge.body.data.writesCandidateStatus).toBe(false);
    const after = await db.candidate.findUniqueOrThrow({ where: { id: onlyMedical.id } });
    expect(after.status).toBe('A');
  });

  it('enforces candidate-access IDOR, unbound agents, and role denials', async () => {
    await auth(request(app).post('/api/v1/overseas/labour-contracts'), agentSession)
      .send({ candidateId: candidateAId })
      .expect(201);
    await auth(request(app).get(`/api/v1/overseas/medicals?candidateId=${candidateBId}`), agentSession).expect(200)
      .then((res) => expect(res.body.data.items).toEqual([]));
    const created = await db.candidateMedical.create({ data: { candidateId: candidateBId } });
    await auth(request(app).get(`/api/v1/overseas/medicals/${created.id}`), agentSession).expect(404);
    await auth(request(app).post('/api/v1/overseas/labour-contracts'), unboundAgentSession)
      .send({ candidateId: candidateAId })
      .expect(404);
    await auth(request(app).get('/api/v1/overseas/medicals'), companySession).expect(403);
    await auth(request(app).get('/api/v1/overseas/medicals'), teacherSession).expect(403);
    await auth(request(app).get('/api/v1/candidates'), teacherSession).expect(403);
    await auth(request(app).get('/api/v1/overseas/medicals'), employerSession).expect(403);
    await auth(request(app).get('/api/v1/overseas/medicals'), candidateSession).expect(403);
    await auth(request(app).get('/api/v1/licenses'), agentSession).expect(403);
    await auth(request(app).get('/api/v1/licenses'), companySession).expect(403);
    await auth(request(app).get('/api/v1/live-status'), companySession).expect(403);
  });

  it('creates companier-scoped licenses and preserves orphan positions', async () => {
    const created = await auth(request(app).post('/api/v1/licenses'), ownerSession)
      .send({ licenseNo: `LIC-${Date.now()}`, companierId, positions: [{ positionId: '9', quantity: 2 }] })
      .expect(201);
    expect(created.body.data.companierId).toBe(companierId);
    expect(created.body.data.positions).toHaveLength(1);
    const orphan = await db.licensePosition.create({ data: { positionId: 1n, quantity: 1 } });
    expect(orphan.licenseId).toBeNull();
    const still = await db.licensePosition.findUniqueOrThrow({ where: { id: orphan.id } });
    expect(still.licenseId).toBeNull();
  });

  it('writes approved audit events and leaves candidate payment FKs unchanged', async () => {
    const events = await db.auditEvent.findMany({
      where: { eventType: { startsWith: 'overseas.' } },
    });
    expect(events.map((row) => row.eventType)).toEqual(
      expect.arrayContaining([
        'overseas.labour_contract.created',
        'overseas.police_clearance.created',
        'overseas.medical.created',
        'overseas.arc.created',
        'overseas.visa.created',
        'overseas.flight.created',
        'overseas.license.created',
        'overseas.status.changed',
      ])
    );
    expect(events.some((row) => row.eventType.includes('VOIDED'))).toBe(false);
    expect(events.some((row) => row.eventType.endsWith('.deleted'))).toBe(false);
    expect(events.some((row) => row.eventType === 'candidate.status.changed')).toBe(false);
    const candidate = await db.candidate.findUniqueOrThrow({ where: { id: candidateAId } });
    expect(candidate.admissionPaymentId).toBe(paymentFkBefore.admission);
    expect(candidate.medicalFeePaymentId).toBe(paymentFkBefore.medical);
    const financeTables = await db.$queryRaw<Array<{ table_name: string }>>`
      SELECT table_name FROM information_schema.tables
      WHERE table_name IN ('payment_requests', 'payments', 'invoices', 'wallets', 'ledgers')
    `;
    expect(financeTables).toEqual([]);
  });

  it('accepts a legacy_key_map row without importing production data', async () => {
    const sourceId = `fixture-${Date.now()}`;
    await db.legacyKeyMap.create({
      data: {
        sourceSystem: 'manpower_mysql',
        sourceTable: 'medicals',
        sourceId,
        targetType: 'candidate.medicals',
        targetId: '00000000-0000-4000-8000-000000000001',
      },
    });
    await expect(
      db.legacyKeyMap.create({
        data: {
          sourceSystem: 'manpower_mysql',
          sourceTable: 'medicals',
          sourceId,
          targetType: 'candidate.medicals',
          targetId: '00000000-0000-4000-8000-000000000002',
        },
      })
    ).rejects.toThrow();
  });

  it('grants staff M6 access and scopes sub_agent/agency candidates', async () => {
    await auth(request(app).get('/api/v1/overseas/medicals'), adminSession).expect(200);
    await auth(request(app).get('/api/v1/overseas/medicals'), employeeSession).expect(200);
    await auth(request(app).get('/api/v1/licenses'), superAdminSession).expect(200);
    const subCreated = await auth(request(app).post('/api/v1/overseas/labour-contracts'), subAgentSession)
      .send({ candidateId: subCandidateId })
      .expect(201);
    expect(subCreated.body.data.candidateId).toBe(subCandidateId);
    await auth(request(app).post('/api/v1/overseas/labour-contracts'), subAgentSession)
      .send({ candidateId: candidateAId })
      .expect(404);
    const agencyCreated = await auth(request(app).post('/api/v1/overseas/medicals'), agencySession)
      .send({ candidateId: agencyCandidateId })
      .expect(201);
    expect(agencyCreated.body.data.candidateId).toBe(agencyCandidateId);
    await auth(request(app).get(`/api/v1/overseas/medicals?candidateId=${candidateAId}`), agencySession)
      .expect(200)
      .then((res) => expect(res.body.data.items).toEqual([]));
    await auth(request(app).get('/api/v1/licenses'), subAgentSession).expect(403);
    await auth(request(app).get('/api/v1/licenses'), agencySession).expect(403);
  });

  it('updates in place, lists inactive rows, and does not filter status implicitly', async () => {
    const created = await auth(request(app).post('/api/v1/overseas/medicals'), ownerSession)
      .send({ candidateId: candidateAId, status: 'I' })
      .expect(201);
    const patched = await auth(request(app).patch(`/api/v1/overseas/medicals/${created.body.data.id}`), ownerSession)
      .send({ status: 'A', issueDate: '2026-01-02' })
      .expect(200);
    expect(patched.body.data.id).toBe(created.body.data.id);
    expect(patched.body.data.status).toBe('A');
    expect(patched.body.data.issueDate).toBe('2026-01-02');
    const listed = await auth(
      request(app).get(`/api/v1/overseas/medicals?candidateId=${candidateAId}`),
      ownerSession
    ).expect(200);
    const ids = listed.body.data.items.map((row: { id: string }) => row.id);
    expect(ids).toContain(created.body.data.id);
    const inactive = await db.candidateMedical.create({
      data: { candidateId: candidateAId, status: 'X' },
    });
    const unfiltered = await auth(
      request(app).get(`/api/v1/overseas/medicals?candidateId=${candidateAId}`),
      ownerSession
    ).expect(200);
    expect(unfiltered.body.data.items.map((row: { id: string }) => row.id)).toContain(inactive.id);
    const updatedEvents = await db.auditEvent.findMany({
      where: { eventType: 'overseas.medical.updated', targetId: created.body.data.id },
    });
    expect(updatedEvents.length).toBeGreaterThan(0);
  });

  it('filters licenses by companier and allows staff cross-companier reads', async () => {
    const other = await auth(request(app).post('/api/v1/licenses'), ownerSession)
      .send({ licenseNo: `LIC-B-${Date.now()}`, companierId: secondCompanierId })
      .expect(201);
    const filtered = await auth(
      request(app).get(`/api/v1/licenses?companierId=${companierId}`),
      ownerSession
    ).expect(200);
    expect(filtered.body.data.items.every((row: { companierId: string }) => row.companierId === companierId)).toBe(
      true
    );
    await auth(request(app).get(`/api/v1/licenses/${other.body.data.id}`), ownerSession).expect(200);
    await auth(request(app).delete(`/api/v1/licenses/${other.body.data.id}`), ownerSession).expect(404);
    await auth(request(app).patch(`/api/v1/licenses/${other.body.data.id}`), ownerSession)
      .send({ status: 'I' })
      .expect(200);
  });
});
