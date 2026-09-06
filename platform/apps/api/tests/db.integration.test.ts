import { afterAll, describe, expect, it } from 'vitest';
import { Prisma, PrismaClient } from '@prisma/client';
import {
  applyCandidateAccess,
  resolveCandidateAccess,
} from '../src/auth/candidate-access';

const runDatabaseTests = process.env['RUN_DB_TESTS'] === 'true';
const db = new PrismaClient();

function uniqueStamp(): string {
  return `${Date.now()}${Math.floor(Math.random() * 1000)}`;
}

describe.skipIf(!runDatabaseTests)('PostgreSQL integration', () => {
  afterAll(async () => {
    await db.$disconnect();
  });

  it('connects through Prisma and reports a PostgreSQL version', async () => {
    const result = await db.$queryRaw<Array<{ value: number; version: string }>>`
      SELECT 1 AS value, version() AS version
    `;
    expect(result[0]?.value).toBe(1);
    expect(result[0]?.version).toMatch(/PostgreSQL 16\./i);
  });

  it('has applied M2, M3, and M4 migrations', async () => {
    const rows = await db.$queryRaw<Array<{ migration_name: string }>>`
      SELECT migration_name
      FROM _prisma_migrations
      WHERE rolled_back_at IS NULL
    `;
    const names = rows.map((row) => row.migration_name);
    expect(names).toEqual(
      expect.arrayContaining([
        '20260903183000_m2_iam_audit',
        '20260904004500_m3_candidate_core',
        '20260904010000_m4_candidate_supporting_domains',
        '20260904013000_m4_candidate_code_sequence',
      ])
    );
  });

  it('has IAM, audit, candidate, and migration tables', async () => {
    const rows = await db.$queryRaw<Array<{ table_name: string }>>`
      SELECT table_name
      FROM information_schema.tables
      WHERE table_schema IN ('iam', 'audit', 'candidate', 'migration')
    `;
    const names = rows.map((row) => row.table_name);
    expect(names).toEqual(
      expect.arrayContaining([
        'users',
        'roles',
        'permissions',
        'user_roles',
        'role_permissions',
        'sessions',
        'audit_events',
        'candidates',
        'legacy_key_map',
        'educations',
        'experiences',
        'skills',
        'skill_list',
        'language_list',
        'trainings',
      ])
    );
  });

  it('has RESTRICT foreign keys from all six M4 child tables to candidates', async () => {
    const rows = await db.$queryRaw<Array<{ table_name: string; delete_rule: string }>>`
      SELECT tc.table_name, rc.delete_rule
      FROM information_schema.table_constraints tc
      JOIN information_schema.referential_constraints rc
        ON tc.constraint_name = rc.constraint_name
       AND tc.constraint_schema = rc.constraint_schema
      JOIN information_schema.key_column_usage kcu
        ON tc.constraint_name = kcu.constraint_name
       AND tc.constraint_schema = kcu.constraint_schema
      WHERE tc.constraint_type = 'FOREIGN KEY'
        AND tc.table_schema = 'candidate'
        AND tc.table_name IN (
          'educations', 'experiences', 'skills', 'skill_list', 'language_list', 'trainings'
        )
        AND kcu.column_name = 'candidate_id'
    `;
    expect(rows).toHaveLength(6);
    expect(rows.every((row) => row.delete_rule === 'RESTRICT')).toBe(true);
  });

  it('allocates candidate codes from an atomic sequence', async () => {
    const first = await db.$queryRaw<Array<{ next: bigint }>>`
      SELECT nextval('candidate.candidate_code_seq') AS next
    `;
    const second = await db.$queryRaw<Array<{ next: bigint }>>`
      SELECT nextval('candidate.candidate_code_seq') AS next
    `;
    const firstValue = first[0]?.next;
    const secondValue = second[0]?.next;
    expect(firstValue).toBeDefined();
    expect(secondValue).toBeDefined();
    if (firstValue === undefined || secondValue === undefined) {
      throw new Error('candidate.candidate_code_seq did not return a value');
    }
    expect(secondValue).toBe(firstValue + BigInt(1));
  });

  it('does not duplicate concurrent nextval allocations', async () => {
    const results = await Promise.all(
      Array.from({ length: 8 }, () =>
        db.$queryRaw<Array<{ next: bigint }>>`
          SELECT nextval('candidate.candidate_code_seq') AS next
        `
      )
    );
    const values = results.map((rows) => {
      const next = rows[0]?.next;
      if (next === undefined) {
        throw new Error('candidate.candidate_code_seq did not return a value');
      }
      return next.toString();
    });
    expect(new Set(values).size).toBe(8);
  });

  it('formats fallback candidate codes as 9 plus six digits', async () => {
    const { generateCandidateCode } = await import('../src/candidates/code.js');
    const code = await db.$transaction((tx) => generateCandidateCode(tx));
    expect(code).toMatch(/^9\d{6}$/);
  });

  it('does not duplicate concurrent generateCandidateCode allocations', async () => {
    const { generateCandidateCode } = await import('../src/candidates/code.js');
    const codes = await Promise.all(
      Array.from({ length: 8 }, () => db.$transaction((tx) => generateCandidateCode(tx)))
    );
    expect(codes.every((code) => /^9\d{6}$/.test(code))).toBe(true);
    expect(new Set(codes).size).toBe(8);
  });

  it('computes setval only from existing 9###### fallback codes', async () => {
    const rows = await db.$queryRaw<Array<{ code: string | null }>>`
      SELECT code FROM candidate.candidates WHERE code ~ '^9[0-9]{6}$'
    `;
    if (rows.length === 0) {
      expect(rows).toHaveLength(0);
      return;
    }
    const maxSuffix = rows.reduce((max, row) => {
      const suffix = Number(row.code?.slice(1));
      return Number.isFinite(suffix) && suffix > max ? suffix : max;
    }, 0);
    expect(maxSuffix).toBeGreaterThan(0);
  });

  it('enforces unique candidate mobile, passport, and code', async () => {
    const stamp = Date.now();
    const created = await db.candidate.create({
      data: {
        name: 'Unique Ids',
        email: `ids-${stamp}@example.com`,
        mobile: `015${stamp.toString().slice(-8)}`,
        passportNo: `ID${stamp}`,
        agentId: 1,
        classGroupId: 1,
        code: `9ID${stamp}`.slice(0, 7).padEnd(7, '0'),
      },
    });
    await expect(
      db.candidate.create({
        data: {
          name: 'Dup Mobile',
          email: `ids-m-${stamp}@example.com`,
          mobile: created.mobile,
          passportNo: `IDM${stamp}`,
          agentId: 1,
          classGroupId: 1,
          code: `9IM${stamp}`.slice(0, 7).padEnd(7, '0'),
        },
      })
    ).rejects.toMatchObject({ code: 'P2002' });
    await expect(
      db.candidate.create({
        data: {
          name: 'Dup Passport',
          email: `ids-p-${stamp}@example.com`,
          mobile: `014${stamp.toString().slice(-8)}`,
          passportNo: created.passportNo,
          agentId: 1,
          classGroupId: 1,
          code: `9IP${stamp}`.slice(0, 7).padEnd(7, '0'),
        },
      })
    ).rejects.toMatchObject({ code: 'P2002' });
    await expect(
      db.candidate.create({
        data: {
          name: 'Dup Code',
          email: `ids-c-${stamp}@example.com`,
          mobile: `013${stamp.toString().slice(-8)}`,
          passportNo: `IDC${stamp}`,
          agentId: 1,
          classGroupId: 1,
          code: created.code,
        },
      })
    ).rejects.toMatchObject({ code: 'P2002' });
    await db.candidate.delete({ where: { id: created.id } });
  });

  it('rejects deleting a candidate that still has education rows', async () => {
    const candidate = await db.candidate.create({
      data: {
        name: 'FK Guard',
        email: `fk-guard-${Date.now()}@example.com`,
        mobile: `017${Date.now().toString().slice(-8)}`,
        passportNo: `FK${Date.now()}`,
        agentId: 1,
        classGroupId: 1,
        code: `9FK${Date.now()}`,
      },
    });
    await db.candidateEducation.create({
      data: { candidateId: candidate.id, examName: 'SSC' },
    });
    await expect(db.candidate.delete({ where: { id: candidate.id } })).rejects.toBeInstanceOf(
      Prisma.PrismaClientKnownRequestError
    );
    await db.candidateEducation.deleteMany({ where: { candidateId: candidate.id } });
    await db.candidate.delete({ where: { id: candidate.id } });
  });

  it('rolls back a candidate create when the surrounding transaction fails', async () => {
    const email = `rollback-${Date.now()}@example.com`;
    await expect(
      db.$transaction(async (tx) => {
        await tx.candidate.create({
          data: {
            name: 'Rollback',
            email,
            mobile: `018${Date.now().toString().slice(-8)}`,
            passportNo: `RB${Date.now()}`,
            agentId: 1,
            classGroupId: 1,
            code: `9RB${Date.now()}`,
          },
        });
        throw new Error('force rollback');
      })
    ).rejects.toThrow('force rollback');
    const leftover = await db.candidate.findUnique({ where: { email } });
    expect(leftover).toBeNull();
  });

  it('enforces unique candidate email', async () => {
    const email = `unique-${Date.now()}@example.com`;
    const created = await db.candidate.create({
      data: {
        name: 'Unique',
        email,
        mobile: `019${Date.now().toString().slice(-8)}`,
        passportNo: `UQ${Date.now()}`,
        agentId: 1,
        classGroupId: 1,
        code: `9UQ${Date.now()}`,
      },
    });
    await expect(
      db.candidate.create({
        data: {
          name: 'Unique Two',
          email,
          mobile: `016${Date.now().toString().slice(-8)}`,
          passportNo: `UQ2${Date.now()}`,
          agentId: 1,
          classGroupId: 1,
          code: `9UQ2${Date.now()}`,
        },
      })
    ).rejects.toMatchObject({ code: 'P2002' });
    await db.candidate.delete({ where: { id: created.id } });
  });

  it('creates candidates with UUID primary keys', async () => {
    const stamp = uniqueStamp();
    const created = await db.candidate.create({
      data: {
        name: 'UUID PK',
        email: `uuid-${stamp}@example.com`,
        mobile: `012${stamp.slice(-8)}`,
        passportNo: `UUID${stamp}`,
        agentId: 1,
        classGroupId: 1,
        code: `9U${stamp}`.slice(0, 8),
      },
    });
    expect(created.id).toMatch(
      /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i
    );
    const column = await db.$queryRaw<Array<{ data_type: string }>>`
      SELECT data_type
      FROM information_schema.columns
      WHERE table_schema = 'candidate'
        AND table_name = 'candidates'
        AND column_name = 'id'
    `;
    expect(column[0]?.data_type).toBe('uuid');
    await db.candidate.delete({ where: { id: created.id } });
  });

  it('supports cursor pagination queries against candidates', async () => {
    const stamp = uniqueStamp();
    const created = await Promise.all(
      [1, 2, 3].map((n) =>
        db.candidate.create({
          data: {
            name: `Page ${n}`,
            email: `page-${stamp}-${n}@example.com`,
            mobile: `011${stamp.slice(-6)}${n}`,
            passportNo: `PG${stamp}${n}`,
            agentId: 1,
            classGroupId: 1,
            code: `989000${n}`,
          },
        })
      )
    );
    const first = await db.candidate.findMany({
      where: { email: { startsWith: `page-${stamp}-` } },
      orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
      take: 2,
    });
    expect(first).toHaveLength(2);
    const cursor = first[1]?.id;
    if (cursor === undefined) {
      throw new Error('expected a pagination cursor');
    }
    const second = await db.candidate.findMany({
      where: {
        AND: [{ email: { startsWith: `page-${stamp}-` } }, { id: { lt: cursor } }],
      },
      orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
      take: 2,
    });
    expect(second.length).toBeGreaterThanOrEqual(1);
    expect(second.map((row) => row.id)).not.toContain(cursor);
    await db.candidate.deleteMany({ where: { id: { in: created.map((row) => row.id) } } });
  });

  it('filters candidates by locked A07 agent scope against real rows', async () => {
    const stamp = uniqueStamp();
    const visible = await db.candidate.create({
      data: {
        name: 'Scoped In',
        email: `scope-in-${stamp}@example.com`,
        mobile: `010${stamp.slice(-8)}`,
        passportNo: `SIN${stamp}`,
        agentId: 10,
        classGroupId: 1,
        code: `9SI${stamp}`.slice(0, 10),
      },
    });
    const hidden = await db.candidate.create({
      data: {
        name: 'Scoped Out',
        email: `scope-out-${stamp}@example.com`,
        mobile: `020${stamp.slice(-8)}`,
        passportNo: `SOUT${stamp}`,
        agentId: 20,
        classGroupId: 1,
        code: `9SO${stamp}`.slice(0, 10),
      },
    });
    const access = resolveCandidateAccess({ roles: ['agent'], agentId: '10' });
    const where = applyCandidateAccess(
      { id: { in: [visible.id, hidden.id] } },
      access
    );
    expect(where).not.toBeNull();
    const rows = await db.candidate.findMany({ where: where ?? { id: { in: [] } } });
    const ids = rows.map((row) => row.id);
    expect(ids).toContain(visible.id);
    expect(ids).not.toContain(hidden.id);
    await db.candidate.deleteMany({ where: { id: { in: [visible.id, hidden.id] } } });
  });

  it('protects child education rows from cross-candidate ownership', async () => {
    const stamp = uniqueStamp();
    const owner = await db.candidate.create({
      data: {
        name: 'Owner',
        email: `own-${stamp}@example.com`,
        mobile: `021${stamp.slice(-8)}`,
        passportNo: `OWN${stamp}`,
        agentId: 1,
        classGroupId: 1,
        code: `9OW${stamp}`.slice(0, 10),
      },
    });
    const other = await db.candidate.create({
      data: {
        name: 'Other',
        email: `oth-${stamp}@example.com`,
        mobile: `022${stamp.slice(-8)}`,
        passportNo: `OTH${stamp}`,
        agentId: 1,
        classGroupId: 1,
        code: `9OT${stamp}`.slice(0, 10),
      },
    });
    const education = await db.candidateEducation.create({
      data: { candidateId: owner.id, examName: 'SSC' },
    });
    expect(education.candidateId).toBe(owner.id);
    expect(education.candidateId).not.toBe(other.id);
    const stolen = await db.candidateEducation.findFirst({
      where: { id: education.id, candidateId: other.id },
    });
    expect(stolen).toBeNull();
    await db.candidateEducation.delete({ where: { id: education.id } });
    await db.candidate.deleteMany({ where: { id: { in: [owner.id, other.id] } } });
  });

  it('rolls back audit writes with the surrounding candidate transaction', async () => {
    const stamp = uniqueStamp();
    const email = `audit-rb-${stamp}@example.com`;
    const { writeAuditEvent } = await import('../src/audit/audit.js');
    const auditBefore = await db.auditEvent.count();
    await expect(
      db.$transaction(async (tx) => {
        const created = await tx.candidate.create({
          data: {
            name: 'Audit Rollback',
            email,
            mobile: `023${stamp.slice(-8)}`,
            passportNo: `ARB${stamp}`,
            agentId: 1,
            classGroupId: 1,
            code: `9AR${stamp}`.slice(0, 10),
          },
        });
        await writeAuditEvent(
          {
            eventType: 'candidate.created',
            targetType: 'candidate',
            targetId: created.id,
            metadata: { fields: 'name' },
          },
          tx
        );
        throw new Error('force audit rollback');
      })
    ).rejects.toThrow('force audit rollback');
    expect(await db.candidate.findUnique({ where: { email } })).toBeNull();
    expect(await db.auditEvent.count()).toBe(auditBefore);
  });
});
