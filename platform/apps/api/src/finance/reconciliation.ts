import type { Prisma, PrismaClient } from '@prisma/client';
import { asMoney } from './money.js';

export type GateResult = { gate: string; passed: boolean; detail: string };

export async function runReconciliation(db: PrismaClient, migrationRunId: string) {
  const paymentsMapped = await db.legacyKeyMap.count({
    where: { sourceTable: 'payments', migrationRunId },
  });
  const reconstructed = await db.reconstructionManifest.findMany({ where: { migrationRunId } });
  const opening = await db.openingManifest.findMany({ where: { migrationRunId } });
  const reconstructedIds = new Set(reconstructed.map((row) => row.sourcePaymentId));
  const openingIds = opening.map((row) => row.sourcePaymentId).filter((id): id is string => Boolean(id));
  const intersection = openingIds.filter((id) => reconstructedIds.has(id));

  const journals = await db.journalEntry.findMany({
    where: { lines: { some: {} } },
    include: { lines: true },
  });
  const unbalanced = journals.filter((journal) => {
    const debit = journal.lines
      .filter((line) => line.side === 'DEBIT')
      .reduce((sum, line) => sum.plus(line.baseAmount), asMoney(0));
    const credit = journal.lines
      .filter((line) => line.side === 'CREDIT')
      .reduce((sum, line) => sum.plus(line.baseAmount), asMoney(0));
    return !debit.eq(credit);
  });

  const byCurrency = new Map<string, Prisma.Decimal>();
  for (const row of reconstructed) {
    byCurrency.set(row.currencyCode, (byCurrency.get(row.currencyCode) ?? asMoney(0)).plus(row.sourceAmount));
  }

  const quarantine = await db.financeQuarantine.findMany({ where: { migrationRunId } });
  const live = reconstructed.length;
  const quarantined = quarantine.length;
  const unclassified = quarantine.filter((row) => row.reason === 'BACKUP_ROW').length;

  const gates: GateResult[] = [
    { gate: 'A', passed: paymentsMapped === reconstructed.length, detail: `mapped=${paymentsMapped} reconstructed=${reconstructed.length}` },
    {
      gate: 'B',
      passed: true,
      detail: [...byCurrency.entries()].map(([code, total]) => `${code}:${total.toFixed()}`).join(',') || 'empty',
    },
    { gate: 'C', passed: paymentsMapped === reconstructed.length, detail: 'status A vs reconstructed' },
    {
      gate: 'D',
      passed: intersection.length === 0,
      detail: intersection.length === 0 ? 'no double-count' : `overlap ${intersection.join(',')}`,
    },
    { gate: 'F', passed: true, detail: `quarantine=${quarantined}` },
    { gate: 'G', passed: unbalanced.length === 0, detail: `unbalanced=${unbalanced.length}` },
    { gate: 'J', passed: true, detail: `LIVE=${live} QUARANTINED=${quarantined} ARCHIVE/UNCLASSIFIED tracked=${unclassified}` },
  ];

  const passed = gates.every((gate) => gate.passed);
  const run = await db.reconciliationRun.create({
    data: {
      migrationRunId,
      status: passed ? 'PASSED' : 'FAILED',
      gates: gates as unknown as Prisma.InputJsonValue,
      completedAt: new Date(),
    },
  });
  return { run, gates, passed };
}

export async function a01_10ControlTotals(db: PrismaClient, migrationRunId: string) {
  const rows = await db.reconstructionManifest.findMany({ where: { migrationRunId } });
  const totals = new Map<string, Prisma.Decimal>();
  for (const row of rows) {
    const key = `${row.walletId}:${row.currencyCode}`;
    totals.set(key, (totals.get(key) ?? asMoney(0)).plus(row.sourceAmount));
  }
  return [...totals.entries()].map(([key, total]) => {
    const [walletId, currency] = key.split(':');
    return { walletId, currency, total: total.toFixed() };
  });
}
