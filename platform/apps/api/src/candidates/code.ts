import type { Prisma } from '@prisma/client';

/**
 * Allocates a new candidate code with PostgreSQL nextval().
 * Format is unchanged: fallback prefix "9" plus a 6-digit sequence.
 * Legacy agent.code + padded max(id) still waits for the partners module.
 */
export async function generateCandidateCode(
  tx: Prisma.TransactionClient
): Promise<string> {
  const rows = await tx.$queryRaw<Array<{ next: bigint }>>`
    SELECT nextval('candidate.candidate_code_seq') AS next
  `;
  const next = rows[0]?.next;
  if (next === undefined) {
    throw new Error('Candidate code sequence did not return a value');
  }
  return `9${next.toString().padStart(6, '0')}`;
}
