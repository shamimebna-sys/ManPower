import type { Prisma, PrismaClient } from '@prisma/client';

type PublishClient = Pick<
  PrismaClient,
  'exam' | 'examClassGroup' | 'classGroup' | 'candidate' | 'examResult'
> | Prisma.TransactionClient;

export interface PublishCounts {
  created: number;
  existing: number;
}

/**
 * Legacy-compatible publish/open: materialize missing result rows for every
 * candidate currently in the exam's class groups. Idempotent. Does not grade.
 */
export async function publishExamResults(client: PublishClient, examId: string): Promise<PublishCounts> {
  const exam = await client.exam.findUnique({
    where: { id: examId },
    include: { classGroups: true },
  });
  if (!exam) {
    return { created: 0, existing: 0 };
  }

  const groupIds = exam.classGroups.map((row) => row.classGroupId);
  if (groupIds.length === 0) {
    const existing = await client.examResult.count({ where: { examId } });
    return { created: 0, existing };
  }

  const groups = await client.classGroup.findMany({
    where: { id: { in: groupIds } },
    select: { id: true, sourceLegacyId: true },
  });
  const legacyIds = groups
    .map((group) => group.sourceLegacyId)
    .filter((value): value is bigint => value !== null);

  const candidates = await client.candidate.findMany({
    where: {
      OR: [
        { classGroupRefId: { in: groupIds } },
        ...(legacyIds.length > 0 ? [{ classGroupId: { in: legacyIds } }] : []),
      ],
    },
    select: { id: true, classGroupRefId: true, classGroupId: true },
  });

  const existingRows = await client.examResult.findMany({
    where: { examId, candidateId: { in: candidates.map((row) => row.id) } },
    select: { candidateId: true },
  });
  const existingIds = new Set(existingRows.map((row) => row.candidateId));

  let created = 0;
  for (const candidate of candidates) {
    if (existingIds.has(candidate.id)) {
      continue;
    }
    const snapshotGroupId =
      candidate.classGroupRefId && groupIds.includes(candidate.classGroupRefId)
        ? candidate.classGroupRefId
        : (groups.find((group) => group.sourceLegacyId === candidate.classGroupId)?.id ??
          groupIds[0] ??
          null);
    await client.examResult.create({
      data: {
        examId,
        candidateId: candidate.id,
        classGroupId: snapshotGroupId,
        status: exam.status,
        result: null,
        abroadEx: null,
        localEx: null,
        bl: null,
        skill: null,
        english: null,
      },
    });
    created += 1;
  }

  const existing = await client.examResult.count({ where: { examId } });
  return { created, existing: existing - created };
}
