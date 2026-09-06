import { afterAll, describe, expect, it } from 'vitest';
import { Prisma, PrismaClient } from '@prisma/client';
import {
  applyCandidateAccess,
  resolveCandidateAccess,
} from '../src/auth/candidate-access';
import { AUDIT_EVENTS, writeAuditEvent } from '../src/audit/audit';

const runDatabaseTests = process.env['RUN_DB_TESTS'] === 'true';
const db = new PrismaClient();

function stamp(): string {
  return `${Date.now()}${Math.floor(Math.random() * 1000)}`;
}

describe.skipIf(!runDatabaseTests)('M4 recruitment PostgreSQL integration', () => {
  afterAll(async () => {
    await db.$disconnect();
  });

  it('has applied the M4 recruitment migration and partners tables', async () => {
    const rows = await db.$queryRaw<Array<{ migration_name: string }>>`
      SELECT migration_name
      FROM _prisma_migrations
      WHERE rolled_back_at IS NULL
    `;
    expect(rows.map((row) => row.migration_name)).toEqual(
      expect.arrayContaining(['20260907010000_m4_recruitment'])
    );
    const tables = await db.$queryRaw<Array<{ table_name: string }>>`
      SELECT table_name
      FROM information_schema.tables
      WHERE table_schema = 'partners'
    `;
    expect(tables.map((row) => row.table_name)).toEqual(
      expect.arrayContaining([
        'agents',
        'sub_agents',
        'agenciers',
        'companiers',
        'employers',
        'employer_candidates',
      ])
    );
  });

  it('enforces user binding foreign keys and exclusive partner identity in application data', async () => {
    const mark = stamp();
    const agent = await db.agent.create({
      data: { name: `Agent ${mark}`, sourceLegacyId: BigInt(`1${mark.slice(-8)}`) },
    });
    const user = await db.user.create({
      data: {
        email: `bind-${mark}@example.com`,
        displayName: 'Bound User',
        passwordHash: 'x'.repeat(60),
        agentId: agent.id,
      },
    });
    expect(user.agentId).toBe(agent.id);
    await expect(db.agent.delete({ where: { id: agent.id } })).rejects.toBeInstanceOf(
      Prisma.PrismaClientKnownRequestError
    );
    await db.user.update({ where: { id: user.id }, data: { agentId: null } });
    await db.user.delete({ where: { id: user.id } });
    await db.agent.delete({ where: { id: agent.id } });
  });

  it('requires sub_agent.agent_id and keeps source_legacy_id unique', async () => {
    const mark = stamp();
    const agent = await db.agent.create({
      data: { name: `Parent ${mark}`, sourceLegacyId: BigInt(`2${mark.slice(-8)}`) },
    });
    const sub = await db.subAgent.create({
      data: {
        name: `Sub ${mark}`,
        agentId: agent.id,
        sourceLegacyId: BigInt(`3${mark.slice(-8)}`),
      },
    });
    await expect(
      db.subAgent.create({
        data: { name: 'Dup', agentId: agent.id, sourceLegacyId: sub.sourceLegacyId },
      })
    ).rejects.toMatchObject({ code: 'P2002' });
    await db.subAgent.delete({ where: { id: sub.id } });
    await db.agent.delete({ where: { id: agent.id } });
  });

  it('enforces active employer-candidate uniqueness and allows archived duplicates', async () => {
    const mark = stamp();
    const employer = await db.employer.create({ data: { name: `Emp ${mark}` } });
    const candidate = await db.candidate.create({
      data: {
        name: `Cand ${mark}`,
        email: `ec-${mark}@example.com`,
        mobile: `040${mark.slice(-8)}`,
        passportNo: `EC${mark}`,
        agentId: 1,
        classGroupId: 1,
        code: `9EC${mark}`.slice(0, 10),
      },
    });
    const active = await db.employerCandidate.create({
      data: {
        employerId: employer.id,
        candidateId: candidate.id,
        purpose: 'FAVORITE',
        status: 'A',
      },
    });
    await expect(
      db.employerCandidate.create({
        data: {
          employerId: employer.id,
          candidateId: candidate.id,
          purpose: 'FAVORITE',
          status: 'A',
        },
      })
    ).rejects.toMatchObject({ code: 'P2002' });
    await db.employerCandidate.update({ where: { id: active.id }, data: { status: 'I' } });
    const archived = await db.employerCandidate.create({
      data: {
        employerId: employer.id,
        candidateId: candidate.id,
        purpose: 'FAVORITE',
        status: 'A',
      },
    });
    expect(archived.status).toBe('A');
    const inactiveAgain = await db.employerCandidate.create({
      data: {
        employerId: employer.id,
        candidateId: candidate.id,
        purpose: 'FAVORITE',
        status: 'I',
      },
    });
    expect(inactiveAgain.status).toBe('I');
    await db.employerCandidate.deleteMany({
      where: { id: { in: [active.id, archived.id, inactiveAgain.id] } },
    });
    await db.candidate.delete({ where: { id: candidate.id } });
    await db.employer.delete({ where: { id: employer.id } });
  });

  it('scopes company A away from company B candidates and keeps teacher blocked', async () => {
    const mark = stamp();
    const visible = await db.candidate.create({
      data: {
        name: 'Company In',
        email: `co-in-${mark}@example.com`,
        mobile: `041${mark.slice(-8)}`,
        passportNo: `CIN${mark}`,
        companierId: 70,
        agentId: 1,
        classGroupId: 1,
        code: `9CI${mark}`.slice(0, 10),
      },
    });
    const hidden = await db.candidate.create({
      data: {
        name: 'Company Out',
        email: `co-out-${mark}@example.com`,
        mobile: `042${mark.slice(-8)}`,
        passportNo: `COUT${mark}`,
        companierId: 71,
        agentId: 1,
        classGroupId: 1,
        code: `9CO${mark}`.slice(0, 10),
      },
    });
    const access = resolveCandidateAccess({ roles: ['company'], companierId: '70' });
    const where = applyCandidateAccess({ id: { in: [visible.id, hidden.id] } }, access);
    const rows = await db.candidate.findMany({ where: where ?? { id: { in: [] } } });
    expect(rows.map((row) => row.id)).toContain(visible.id);
    expect(rows.map((row) => row.id)).not.toContain(hidden.id);
    expect(resolveCandidateAccess({ roles: ['teacher'] }).kind).toBe('none');
    await db.candidate.deleteMany({ where: { id: { in: [visible.id, hidden.id] } } });
  });

  it('scopes agent, sub-agent, and agency away from another partner of the same type', async () => {
    const mark = stamp();
    const a = await db.candidate.create({
      data: {
        name: 'A',
        email: `sc-a-${mark}@example.com`,
        mobile: `043${mark.slice(-8)}`,
        passportNo: `SCA${mark}`,
        agentId: 101,
        subAgentId: 201,
        agencierId: 301,
        classGroupId: 1,
        code: `9SA${mark}`.slice(0, 10),
      },
    });
    const b = await db.candidate.create({
      data: {
        name: 'B',
        email: `sc-b-${mark}@example.com`,
        mobile: `044${mark.slice(-8)}`,
        passportNo: `SCB${mark}`,
        agentId: 102,
        subAgentId: 202,
        agencierId: 302,
        classGroupId: 1,
        code: `9SB${mark}`.slice(0, 10),
      },
    });
    for (const actor of [
      { roles: ['agent'], agentId: '101' },
      { roles: ['sub_agent'], subAgentId: '201' },
      { roles: ['agency'], agencierId: '301' },
    ]) {
      const where = applyCandidateAccess(
        { id: { in: [a.id, b.id] } },
        resolveCandidateAccess(actor)
      );
      const rows = await db.candidate.findMany({ where: where ?? { id: { in: [] } } });
      const ids = rows.map((row) => row.id);
      expect(ids).toContain(a.id);
      expect(ids).not.toContain(b.id);
    }
    await db.candidate.deleteMany({ where: { id: { in: [a.id, b.id] } } });
  });

  it('writes recruitment audit events without secrets', async () => {
    const before = await db.auditEvent.count();
    await writeAuditEvent({
      eventType: AUDIT_EVENTS.USER_PARTNER_BOUND,
      targetType: 'user',
      targetId: '11111111-1111-4111-8111-111111111111',
      metadata: { domain: 'agent', bound: true, password: 'secret' },
    });
    const latest = await db.auditEvent.findFirst({
      where: { eventType: AUDIT_EVENTS.USER_PARTNER_BOUND },
      orderBy: { occurredAt: 'desc' },
    });
    expect(latest).toBeTruthy();
    expect(JSON.stringify(latest?.metadata)).not.toContain('secret');
    expect(await db.auditEvent.count()).toBe(before + 1);
  });
});
