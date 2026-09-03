import { prisma } from '../lib/prisma.js';
import { AppError } from '../middleware/errorHandler.js';
import {
  assertCandidateAccess,
  type CandidateScopeActor,
} from '../auth/candidate-access.js';

export async function requireCandidateId(candidateId: string): Promise<{ id: string }> {
  const candidate = await prisma.candidate.findUnique({
    where: { id: candidateId },
    select: { id: true },
  });
  if (!candidate) throw AppError.notFound('Candidate');
  return candidate;
}

export async function requireAccessibleCandidate(
  candidateId: string,
  actor: CandidateScopeActor
): Promise<{ id: string }> {
  const candidate = await prisma.candidate.findUnique({
    where: { id: candidateId },
    select: { id: true, agentId: true, subAgentId: true, agencierId: true },
  });
  if (!candidate) throw AppError.notFound('Candidate');
  assertCandidateAccess(actor, candidate);
  return { id: candidate.id };
}

export function assertChildOwnership(recordCandidateId: string, candidateId: string): void {
  if (recordCandidateId !== candidateId) {
    throw AppError.notFound('Record');
  }
}
