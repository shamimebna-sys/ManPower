import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import { CandidateIdParams } from '@manpower/shared';
import type { ApiResponse, LiveStatusBadgeRecord, LiveStatusLookupRecord } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireAnyPermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { M6_OVERSEAS_READ_KEYS } from '../iam/permission-catalogue.js';
import {
  actorFromAuth,
  assertCandidateAccess,
  resolveCandidateAccess,
} from '../auth/candidate-access.js';
import { deriveLiveStatus } from '../overseas/live-status.js';
import { toLiveStatusLookupRecord } from '../overseas/serialize.js';

export const liveStatusRouter: ExpressRouter = Router();
liveStatusRouter.use(requireAuth);

const overseasRead = requireAnyPermission(...M6_OVERSEAS_READ_KEYS);

liveStatusRouter.get('/', overseasRead, async (_req: Request, res: Response) => {
  const items = await prisma.liveStatusLookup.findMany({ orderBy: { stepNo: 'asc' } });
  const body: ApiResponse<{ items: LiveStatusLookupRecord[] }> = {
    success: true,
    data: { items: items.map(toLiveStatusLookupRecord) },
  };
  res.status(200).json(body);
});

liveStatusRouter.get('/:id', overseasRead, async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const { id: candidateId } = CandidateIdParams.parse(req.params);
  const candidate = await prisma.candidate.findUnique({
    where: { id: candidateId },
    select: { id: true, agentId: true, subAgentId: true, agencierId: true, companierId: true },
  });
  if (!candidate) throw AppError.notFound('Candidate');
  const access = resolveCandidateAccess(actorFromAuth(req.auth.user));
  if (access.kind === 'none') throw AppError.notFound('Candidate');
  assertCandidateAccess(actorFromAuth(req.auth.user), candidate);
  const badge = await deriveLiveStatus(prisma, candidateId);
  const body: ApiResponse<LiveStatusBadgeRecord> = { success: true, data: badge };
  res.status(200).json(body);
});
