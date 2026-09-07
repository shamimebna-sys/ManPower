import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import request from 'supertest';
import { PrismaClient } from '@prisma/client';
import type { Application } from 'express';
import { hashToken, createSecureToken } from '../src/auth/tokens';
import { M7_ROLE_GRANTS } from '../src/iam/permission-catalogue';

const runDatabaseTests = process.env['RUN_DB_TESTS'] === 'true';

describe.skipIf(!runDatabaseTests)('PostgreSQL 16 M7 finance integration', () => {
  let db!: PrismaClient;
  let app!: Application;
  let ownerSession = { token: '', csrf: '' };
  let teacherSession = { token: '', csrf: '' };
  let agentSession = { token: '', csrf: '' };
  let agentId = '';
  let candidateId = '';
  let walletId = '';
  let requestId = '';
  let approvedJournalId = '';
  const stamp = `${Date.now()}`;

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
    for (const [roleKey, keys] of Object.entries(M7_ROLE_GRANTS)) {
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

    const agent = await db.agent.create({
      data: { name: 'M7 Agent', sourceLegacyId: BigInt(`${stamp}1`) },
    });
    agentId = agent.id;
    const candidate = await db.candidate.create({
      data: {
        name: 'M7 Cand',
        email: `m7-cand-${stamp}@example.com`,
        mobile: `017${stamp.slice(-8)}`,
        agentId: agent.sourceLegacyId,
      },
    });
    candidateId = candidate.id;
    const outsider = await db.agent.create({
      data: { name: 'M7 Outsider', sourceLegacyId: BigInt(`${stamp}2`) },
    });
    const owner = await db.user.create({
      data: { email: `m7-owner-${stamp}@example.com`, username: `m7-owner-${stamp}`, displayName: 'M7 Owner', passwordHash },
    });
    const teacher = await db.user.create({
      data: { email: `m7-teacher-${stamp}@example.com`, username: `m7-teacher-${stamp}`, displayName: 'M7 Teacher', passwordHash },
    });
    const agentUser = await db.user.create({
      data: {
        email: `m7-agent-${stamp}@example.com`,
        username: `m7-agent-${stamp}`,
        displayName: 'M7 Agent User',
        passwordHash,
        agentId: outsider.id,
      },
    });
    const ownerRole = roleByKey.get('owner');
    const teacherRole = roleByKey.get('teacher');
    const agentRole = roleByKey.get('agent');
    if (!ownerRole || !teacherRole || !agentRole) throw new Error('required roles missing');
    await db.userRole.create({ data: { userId: owner.id, roleId: ownerRole.id } });
    await db.userRole.create({ data: { userId: teacher.id, roleId: teacherRole.id } });
    await db.userRole.create({ data: { userId: agentUser.id, roleId: agentRole.id } });
    ownerSession = await openSession(owner.id);
    teacherSession = await openSession(teacher.id);
    agentSession = await openSession(agentUser.id);
  }, 60_000);

  afterAll(async () => {
    await db?.$disconnect();
  });

  it('forbids teacher finance access', async () => {
    const response = await auth(request(app).get('/api/v1/finance/wallets'), teacherSession);
    expect(response.status).toBe(403);
  });

  it('enters administrative FX rate, funds a wallet, and posts a manpower fee', async () => {
    const fx = await auth(request(app).post('/api/v1/finance/fx-rates'), ownerSession).send({
      fromCurrency: 'BDT',
      toCurrency: 'EUR',
      rate: '140',
    });
    expect(fx.status).toBe(201);

    const ensured = await auth(request(app).post('/api/v1/finance/wallets/ensure'), ownerSession).send({
      agentId,
    });
    expect(ensured.status).toBe(201);
    walletId = ensured.body.data.id as string;

    const deposit = await auth(request(app).post('/api/v1/finance/deposits'), ownerSession).send({
      walletId,
      amount: '700000',
      currencyCode: 'BDT',
      fxRateEntryId: fx.body.data.id,
    });
    expect(deposit.status).toBe(201);
    const approvedDeposit = await auth(
      request(app).post(`/api/v1/finance/deposits/${deposit.body.data.id}/approve`),
      ownerSession
    ).send({});
    expect(approvedDeposit.status).toBe(200);
    const debit = approvedDeposit.body.data.journal.lines.find((line: { side: string }) => line.side === 'DEBIT');
    const credit = approvedDeposit.body.data.journal.lines.find((line: { side: string }) => line.side === 'CREDIT');
    expect(debit.baseAmount).toBe(credit.baseAmount);
    expect(approvedDeposit.body.data.journal.lines[0].fxDirection).toBeDefined();

    const created = await auth(request(app).post('/api/v1/finance/payment-requests'), ownerSession).send({
      candidateId,
      walletId,
      amount: '1000',
      billTitle: 'Manpower Fee',
    });
    expect(created.status).toBe(201);
    requestId = created.body.data.id as string;
    const approved = await auth(
      request(app).post(`/api/v1/finance/payment-requests/${requestId}/approve`),
      ownerSession
    ).send({});
    expect(approved.status).toBe(200);
    expect(approved.body.data.request.status).toBe('A');
    const again = await auth(
      request(app).post(`/api/v1/finance/payment-requests/${requestId}/approve`),
      ownerSession
    ).send({});
    expect(again.status).toBe(200);
    expect(again.body.data.journal.id).toBe(approved.body.data.journal.id);
    approvedJournalId = approved.body.data.journal.id as string;
  });

  it('rejects insufficient funds and posts A18 zero on reject', async () => {
    const created = await auth(request(app).post('/api/v1/finance/payment-requests'), ownerSession).send({
      candidateId,
      walletId,
      amount: '999999',
      billTitle: 'Panelty Fee',
    });
    expect(created.status).toBe(201);
    expect(created.body.data.billTypeCode).toBe('100');
    const failed = await auth(
      request(app).post(`/api/v1/finance/payment-requests/${created.body.data.id}/approve`),
      ownerSession
    ).send({});
    expect(failed.status).toBe(409);

    const pending = await auth(request(app).post('/api/v1/finance/payment-requests'), ownerSession).send({
      candidateId,
      walletId,
      amount: '50',
      billTitle: 'Medical Fee',
    });
    const rejected = await auth(
      request(app).post(`/api/v1/finance/payment-requests/${pending.body.data.id}/reject`),
      ownerSession
    ).send({});
    expect(rejected.status).toBe(200);
    expect(rejected.body.data.request.status).toBe('R');
    expect(rejected.body.data.journal.entryType).toBe('FEE_REJECTION_ZERO');
    expect(rejected.body.data.journal.lines.every((line: { amount: string }) => line.amount === '0.000000')).toBe(
      true
    );
  });

  it('blocks IDOR, unauthorized FX entry, concurrent double-post, mutation, and preserves historical R', async () => {
    const hidden = await auth(request(app).get(`/api/v1/finance/wallets/${walletId}`), agentSession);
    expect(hidden.status).toBe(404);

    const fxDenied = await auth(request(app).post('/api/v1/finance/fx-rates'), agentSession).send({
      fromCurrency: 'BDT',
      toCurrency: 'EUR',
      rate: '99',
    });
    expect(fxDenied.status).toBe(403);

    const teacherFx = await auth(request(app).post('/api/v1/finance/fx-rates'), teacherSession).send({
      fromCurrency: 'BDT',
      toCurrency: 'EUR',
      rate: '99',
    });
    expect(teacherFx.status).toBe(403);

    const pending = await auth(request(app).post('/api/v1/finance/payment-requests'), ownerSession).send({
      candidateId,
      walletId,
      amount: '10',
      billTitle: 'Final Group Approval',
    });
    expect(pending.status).toBe(201);
    const [first, second] = await Promise.all([
      auth(request(app).post(`/api/v1/finance/payment-requests/${pending.body.data.id}/approve`), ownerSession).send({}),
      auth(request(app).post(`/api/v1/finance/payment-requests/${pending.body.data.id}/approve`), ownerSession).send({}),
    ]);
    expect(first.status).toBe(200);
    expect(second.status).toBe(200);
    expect(first.body.data.journal.id).toBe(second.body.data.journal.id);
    const feeCount = await db.journalEntry.count({
      where: { paymentRequestId: pending.body.data.id as string, entryType: 'FEE_APPROVAL' },
    });
    expect(feeCount).toBe(1);

    const line = await db.journalLine.findFirst({ where: { journalId: approvedJournalId } });
    expect(line).toBeTruthy();
    if (!line) throw new Error('expected posted journal line');
    await expect(
      db.journalLine.update({ where: { id: line.id }, data: { amount: '1' } })
    ).rejects.toThrow();
    await expect(db.journalEntry.delete({ where: { id: approvedJournalId } })).rejects.toThrow();

    const reversed = await auth(
      request(app).post(`/api/v1/finance/journals/${approvedJournalId}/reverse`),
      ownerSession
    ).send({ reason: 'correction' });
    expect(reversed.status).toBe(201);
    expect(reversed.body.data.entryType).toBe('REVERSAL');
    const again = await auth(
      request(app).post(`/api/v1/finance/journals/${approvedJournalId}/reverse`),
      ownerSession
    ).send({ reason: 'correction' });
    expect(again.status).toBe(201);
    expect(again.body.data.id).toBe(reversed.body.data.id);

    const runId = `m7-rate-${stamp}`;
    const reconstruct = await auth(request(app).post('/api/v1/finance/reconstruction'), ownerSession).send({
      migrationRunId: runId,
      payments: [
        {
          id: `${stamp}-rate`,
          status: 'A',
          type: 'DR',
          amount: '140000',
          currencyId: 1,
          exchangeRate: '140',
          agentId: Number(`${stamp}1`),
          subAgentId: 0,
          teacherId: 0,
          sourceHash: 'hash-rate',
        },
        {
          id: `${stamp}-rejected`,
          status: 'R',
          type: 'DR',
          amount: '10',
          currencyId: 1,
          exchangeRate: '140',
          agentId: Number(`${stamp}1`),
          subAgentId: 0,
          teacherId: 0,
          sourceHash: 'hash-rejected',
        },
      ],
    });
    expect(reconstruct.status).toBe(200);
    expect(reconstruct.body.data.reconstructed).toBe(1);
    expect(reconstruct.body.data.quarantined).toBe(1);
    const journal = await db.journalEntry.findUnique({
      where: { id: reconstruct.body.data.journals[0] as string },
      include: { lines: true },
    });
    expect(journal?.lines.every((row) => row.fxRate.toFixed(8) === '140.00000000')).toBe(true);
    expect(journal?.lines.every((row) => row.amount.toFixed(6) === '140000.000000')).toBe(true);
    const map = await db.legacyKeyMap.findUnique({
      where: {
        sourceSystem_sourceTable_sourceId: {
          sourceSystem: 'manpower_mysql',
          sourceTable: 'payments',
          sourceId: `${stamp}-rate`,
        },
      },
    });
    expect(map?.targetId).toBe(journal?.id);

    const workflow = await auth(request(app).post('/api/v1/finance/reconstruction'), ownerSession).send({
      migrationRunId: `${runId}-requests`,
      requests: [
        {
          id: `${stamp}91`,
          status: 'R',
          amount: '10',
          billTitle: 'Medical Fee',
          candidateId,
          agentId: Number(`${stamp}1`),
          sourceHash: 'hash-pr-r',
        },
      ],
    });
    expect(workflow.status).toBe(200);
    expect(workflow.body.data.requestsImported).toBe(1);
    const imported = await db.paymentRequest.findFirst({
      where: { sourceLegacyId: BigInt(`${stamp}91`) },
    });
    expect(imported?.status).toBe('R');
    expect(imported?.journalId).toBeNull();
  });

  it('reconstructs qualifying payments once, quarantines teacher/backup, and blocks double-count', async () => {
    const runId = `m7-${stamp}`;
    const reconstruct = await auth(request(app).post('/api/v1/finance/reconstruction'), ownerSession).send({
      migrationRunId: runId,
      payments: [
        {
          id: `${stamp}-ok`,
          status: 'A',
          type: 'CR',
          amount: '140000',
          currencyId: 1,
          exchangeRate: '140',
          agentId: Number(`${stamp}1`),
          subAgentId: 0,
          teacherId: 0,
          sourceHash: 'hash-ok',
        },
        {
          id: `${stamp}-teacher`,
          status: 'A',
          type: 'CR',
          amount: '10',
          currencyId: 1,
          exchangeRate: '140',
          agentId: Number(`${stamp}1`),
          subAgentId: 0,
          teacherId: 9,
          sourceHash: 'hash-teacher',
        },
        {
          id: `${stamp}-backup`,
          status: 'A',
          type: 'CR',
          amount: '10',
          currencyId: 1,
          exchangeRate: '140',
          agentId: Number(`${stamp}1`),
          subAgentId: 0,
          teacherId: 0,
          sourceHash: 'hash-backup',
          isBackup: true,
        },
      ],
    });
    expect(reconstruct.status).toBe(200);
    expect(reconstruct.body.data.reconstructed).toBe(1);
    expect(reconstruct.body.data.quarantined).toBe(2);

    const again = await auth(request(app).post('/api/v1/finance/reconstruction'), ownerSession).send({
      migrationRunId: runId,
      payments: [
        {
          id: `${stamp}-ok`,
          status: 'A',
          type: 'CR',
          amount: '140000',
          currencyId: 1,
          exchangeRate: '140',
          agentId: Number(`${stamp}1`),
          subAgentId: 0,
          teacherId: 0,
          sourceHash: 'hash-ok',
        },
      ],
    });
    expect(again.body.data.reconstructed).toBe(0);

    const recon = await auth(request(app).post('/api/v1/finance/reconciliation'), ownerSession).send({
      migrationRunId: runId,
    });
    expect(recon.status).toBe(200);
    expect(recon.body.data.gates.some((gate: { gate: string; passed: boolean }) => gate.gate === 'D' && gate.passed)).toBe(
      true
    );

    const batch = await db.openingBalanceBatch.create({
      data: { migrationRunId: runId },
    });
    await expect(
      db.openingManifest.create({
        data: {
          batchId: batch.id,
          sourcePaymentId: `${stamp}-ok`,
          migrationRunId: runId,
          walletId,
          currencyCode: 'BDT',
          sourceAmount: '140000',
          sourceExchangeRate: '140',
        },
      })
    ).rejects.toThrow();
  });

  it('uses exact Manpower Fee title for live-status step 9', async () => {
    const { deriveLiveStatus } = await import('../src/overseas/live-status.js');
    const badge = await deriveLiveStatus(db, candidateId);
    expect(badge.stepNo).toBe(9);
    expect(badge.name).toBe('Manpower Status');
  });

  it('enforces M7 accounting invariants in PostgreSQL', async () => {
    const version = await db.$queryRaw<Array<{ version: string }>>`SELECT version()`;
    expect(version[0]?.version).toMatch(/PostgreSQL 16\./);
    expect(version[0]?.version).not.toMatch(/PostgreSQL 1[78]/);

    const unbalanced = await db.$queryRaw<Array<{ id: string }>>`
      SELECT j.id
      FROM finance.journal_entries j
      JOIN finance.journal_lines l ON l.journal_id = j.id
      WHERE j.status = 'POSTED'
      GROUP BY j.id
      HAVING COALESCE(SUM(CASE WHEN l.side = 'DEBIT' THEN l.base_amount ELSE 0 END), 0)
           <> COALESCE(SUM(CASE WHEN l.side = 'CREDIT' THEN l.base_amount ELSE 0 END), 0)
          OR COUNT(*) < 2
    `;
    expect(unbalanced).toEqual([]);

    const reconstructed = await db.$queryRaw<Array<{ source_payment_id: string; journals: bigint }>>`
      SELECT source_payment_id, COUNT(DISTINCT journal_id) AS journals
      FROM finance.reconstruction_manifests
      GROUP BY source_payment_id
      HAVING COUNT(DISTINCT journal_id) <> 1
    `;
    expect(reconstructed).toEqual([]);

    const overlap = await db.$queryRaw<Array<{ source_payment_id: string }>>`
      SELECT r.source_payment_id
      FROM finance.reconstruction_manifests r
      JOIN finance.opening_manifests o ON o.source_payment_id = r.source_payment_id
    `;
    expect(overlap).toEqual([]);

    const historicalRates = await db.$queryRaw<Array<{ n: bigint }>>`
      SELECT COUNT(*)::bigint AS n
      FROM finance.reconstruction_manifests r
      JOIN finance.journal_lines l ON l.journal_id = r.journal_id
      WHERE r.currency_code = 'BDT'
        AND l.fx_rate <> r.source_exchange_rate
    `;
    expect(Number(historicalRates[0]?.n ?? 1)).toBe(0);

    const m8 = await db.$queryRaw<Array<{ table_name: string }>>`
      SELECT table_name
      FROM information_schema.tables
      WHERE table_name IN (
        'invoices', 'invoice_receipts', 'ticket_invoices', 'ticket_receipts', 'ticket_invoice_lines'
      )
    `;
    expect(m8).toEqual([]);

    const currencies = await db.$queryRaw<Array<{ code: string }>>`
      SELECT code FROM finance.currencies ORDER BY code
    `;
    expect(currencies.map((row) => row.code)).toEqual(['BDT', 'EUR']);

    const fx = await db.$queryRaw<Array<{ n: bigint }>>`
      SELECT COUNT(*)::bigint AS n FROM finance.fx_rate_entries
    `;
    expect(Number(fx[0]?.n ?? 0)).toBeGreaterThan(0);

    const posted = await db.$queryRaw<Array<{ id: string }>>`
      SELECT id FROM finance.journal_entries WHERE status = 'POSTED' LIMIT 1
    `;
    expect(posted[0]?.id).toBeTruthy();
    const postedId = posted[0]!.id;
    await expect(
      db.$executeRaw`UPDATE finance.journal_entries SET entry_type = 'REVERSAL' WHERE id = ${postedId}::uuid`
    ).rejects.toThrow(/immutable|cannot be deleted/i);
    await expect(
      db.$executeRaw`DELETE FROM finance.journal_entries WHERE id = ${postedId}::uuid`
    ).rejects.toThrow(/cannot be deleted|immutable/i);
    await expect(
      db.$executeRaw`UPDATE finance.journal_lines SET amount = amount + 1 WHERE journal_id = ${postedId}::uuid`
    ).rejects.toThrow(/immutable|cannot be deleted/i);

    const driftedWallets = await db.$queryRaw<Array<{ id: string }>>`
      SELECT w.id
      FROM finance.wallets w
      WHERE w.projected_available_eur IS NOT NULL
        AND w.projected_available_eur <> (
          SELECT COALESCE(SUM(
            CASE WHEN l.side = 'CREDIT' THEN l.base_amount ELSE -l.base_amount END
          ), 0)
          FROM finance.wallet_accounts a
          JOIN finance.journal_lines l ON l.wallet_account_id = a.id
          JOIN finance.journal_entries j ON j.id = l.journal_id
          WHERE a.wallet_id = w.id AND j.status = 'POSTED'
        )
    `;
    expect(driftedWallets).toEqual([]);

    const newBdtWithoutAdmin = await db.$queryRaw<Array<{ id: string }>>`
      SELECT j.id
      FROM finance.journal_entries j
      WHERE j.status = 'POSTED'
        AND j.source_type = 'wallet_deposit'
        AND j.entry_type = 'WALLET_DEPOSIT'
        AND j.fx_rate_entry_id IS NULL
        AND EXISTS (
          SELECT 1 FROM finance.journal_lines l
          WHERE l.journal_id = j.id AND l.currency_code = 'BDT'
        )
    `;
    expect(newBdtWithoutAdmin).toEqual([]);
  });
});
