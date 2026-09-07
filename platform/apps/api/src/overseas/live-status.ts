import type { LiveStatusBadgeRecord } from '@manpower/shared';
import type { Prisma, PrismaClient } from '@prisma/client';
import { LATEST_ORDER } from './latest.js';

export const LIVE_STATUS_LABELS: Record<number, string> = {
  1: 'Registration',
  2: 'Profile Update/CV',
  3: 'Group Name',
  4: 'Interview',
  5: 'Selection',
  6: 'Labour Contact',
  7: 'Police Clearance & Medical',
  8: 'VISA/Work Permite',
  9: 'Manpower Status',
  10: 'Flight',
};

type Db = PrismaClient | Prisma.TransactionClient;

function formatFlightExtra(row: {
  flightDate: Date | null;
  flightTime: Date | null;
}): string | null {
  if (!row.flightDate && !row.flightTime) return null;
  const date = row.flightDate ? row.flightDate.toISOString().slice(0, 10) : '';
  const time = row.flightTime ? row.flightTime.toISOString().slice(11, 19) : '';
  return [date, time].filter(Boolean).join(' ');
}

/**
 * Compatibility waterfall matching CommonClass::liveStatus().
 * Step 5 is skipped. Medical and ARC are off the ladder.
 * Step 9 (manpower payment) is M7 and is not evaluated here.
 * Finance tables are out of M6. This is a read-only derivation; it never writes
 * candidates.status.
 */
export async function deriveLiveStatus(db: Db, candidateId: string): Promise<LiveStatusBadgeRecord> {
  const args = { where: { candidateId }, orderBy: LATEST_ORDER };
  const flight = await db.flightSchedule.findFirst(args);
  if (flight) {
    return badge(candidateId, 10, formatFlightExtra(flight));
  }

  const visa = await db.visaImmigration.findFirst(args);
  if (visa) return badge(candidateId, 8, null);

  const police = await db.policeClearance.findFirst(args);
  if (police) return badge(candidateId, 7, null);

  const labour = await db.labourContract.findFirst(args);
  if (labour) return badge(candidateId, 6, null);

  const candidate = await db.candidate.findUnique({
    where: { id: candidateId },
    select: { cvFileRef: true, classGroup: { select: { name: true } } },
  });
  const groupName = candidate?.classGroup?.name ?? '';
  if (groupName.includes('Rapid')) return badge(candidateId, 4, null);
  if (groupName) return badge(candidateId, 3, null);
  if (candidate?.cvFileRef) return badge(candidateId, 2, null);
  return badge(candidateId, 1, null);
}

function badge(candidateId: string, stepNo: number, extraText: string | null): LiveStatusBadgeRecord {
  return {
    candidateId,
    stepNo,
    name: LIVE_STATUS_LABELS[stepNo] ?? 'Registration',
    extraText,
    skippedStep5: true,
    medicalOnLadder: false,
    arcOnLadder: false,
    writesCandidateStatus: false,
  };
}
