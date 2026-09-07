import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  ManpowerTrainingListQuerySchema,
  ManpowerTrainingUpdateSchema,
  ManpowerTrainingWriteSchema,
  TrainingIdParams,
} from '@manpower/shared';
import type { ApiResponse, ManpowerTrainingListResult, ManpowerTrainingRecord } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import {
  actorFromAuth,
  applyCandidateAccess,
  assertCandidateAccess,
  resolveCandidateAccess,
} from '../auth/candidate-access.js';
import { parseDateOnly, toManpowerTrainingRecord } from '../training/serialize.js';
import { isUniqueConflict, toBigInt } from '../training/util.js';

export const manpowerTrainingsRouter: ExpressRouter = Router();

manpowerTrainingsRouter.use(requireAuth);

async function loadScopedCandidate(candidateId: string, actor: ReturnType<typeof actorFromAuth>) {
  const candidate = await prisma.candidate.findUnique({
    where: { id: candidateId },
    select: { id: true, agentId: true, subAgentId: true, agencierId: true, companierId: true },
  });
  if (!candidate) throw AppError.notFound('Candidate');
  assertCandidateAccess(actor, candidate);
  return candidate;
}

function manpowerWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.candidateId !== undefined ? { candidateId: input.candidateId as string } : {}),
    ...(input.certificateIssueDate !== undefined
      ? { certificateIssueDate: parseDateOnly(input.certificateIssueDate as string) }
      : {}),
    ...(input.certificateExpireDate !== undefined
      ? { certificateExpireDate: parseDateOnly(input.certificateExpireDate as string) }
      : {}),
    ...(input.certificateFileRef !== undefined ? { certificateFileRef: input.certificateFileRef as string } : {}),
    ...(input.manpowerFileRef !== undefined ? { manpowerFileRef: input.manpowerFileRef as string } : {}),
    ...(input.fingerPrintFileRef !== undefined
      ? { fingerPrintFileRef: input.fingerPrintFileRef as string }
      : {}),
    ...(input.trainingStartDate !== undefined
      ? { trainingStartDate: parseDateOnly(input.trainingStartDate as string) }
      : {}),
    ...(input.trainingEndDate !== undefined
      ? { trainingEndDate: parseDateOnly(input.trainingEndDate as string) }
      : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
  };
}

manpowerTrainingsRouter.get(
  '/',
  requirePermission('training.manpower.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const query = ManpowerTrainingListQuerySchema.parse(req.query);
    const access = resolveCandidateAccess(actorFromAuth(req.auth.user));
    const filters: Prisma.ManpowerTrainingEventWhereInput = {
      ...(query.candidateId ? { candidateId: query.candidateId } : {}),
      ...(query.status ? { status: query.status } : {}),
    };
    const candidateFilter = applyCandidateAccess({}, access);
    if (candidateFilter === null) {
      const body: ApiResponse<ManpowerTrainingListResult> = {
        success: true,
        data: { items: [] },
        meta: {
          timestamp: new Date().toISOString(),
          version: 'v1',
          pagination: { nextCursor: null, prevCursor: null, total: 0 },
        },
      };
      res.status(200).json(body);
      return;
    }
    if (Object.keys(candidateFilter).length > 0) {
      filters.candidate = candidateFilter;
    }
    const where = query.cursor ? { AND: [filters, { id: { lt: query.cursor } }] } : filters;
    const [items, total] = await Promise.all([
      prisma.manpowerTrainingEvent.findMany({
        where,
        orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
        take: query.limit + 1,
      }),
      prisma.manpowerTrainingEvent.count({ where: filters }),
    ]);
    const hasMore = items.length > query.limit;
    const page = hasMore ? items.slice(0, query.limit) : items;
    const body: ApiResponse<ManpowerTrainingListResult> = {
      success: true,
      data: { items: page.map(toManpowerTrainingRecord) },
      meta: {
        timestamp: new Date().toISOString(),
        version: 'v1',
        pagination: {
          nextCursor: hasMore ? page[page.length - 1]?.id ?? null : null,
          prevCursor: null,
          total,
        },
      },
    };
    res.status(200).json(body);
  }
);

manpowerTrainingsRouter.get(
  '/:id',
  requirePermission('training.manpower.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const row = await prisma.manpowerTrainingEvent.findUnique({
      where: { id },
      include: {
        candidate: {
          select: { id: true, agentId: true, subAgentId: true, agencierId: true, companierId: true },
        },
      },
    });
    if (!row) throw AppError.notFound('Manpower training');
    assertCandidateAccess(actorFromAuth(req.auth.user), row.candidate);
    const body: ApiResponse<ManpowerTrainingRecord> = {
      success: true,
      data: toManpowerTrainingRecord(row),
    };
    res.status(200).json(body);
  }
);

manpowerTrainingsRouter.post(
  '/',
  requireCsrf,
  requirePermission('training.manpower.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = ManpowerTrainingWriteSchema.parse(req.body);
    await loadScopedCandidate(input.candidateId, actorFromAuth(req.auth.user));
    try {
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.manpowerTrainingEvent.create({
          data: manpowerWriteData(input) as Prisma.ManpowerTrainingEventUncheckedCreateInput,
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.TRAINING_MANPOWER_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'manpower_training',
            targetId: row.id,
            metadata: { candidateId: row.candidateId, status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<ManpowerTrainingRecord> = {
        success: true,
        data: toManpowerTrainingRecord(created),
      };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A manpower training record with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);

manpowerTrainingsRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('training.manpower.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const input = ManpowerTrainingUpdateSchema.parse(req.body);
    const actor = actorFromAuth(req.auth.user);
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.manpowerTrainingEvent.findUnique({
          where: { id },
          include: {
            candidate: {
              select: { id: true, agentId: true, subAgentId: true, agencierId: true, companierId: true },
            },
          },
        });
        if (!existing) throw AppError.notFound('Manpower training');
        assertCandidateAccess(actor, existing.candidate);
        if (input.candidateId && input.candidateId !== existing.candidateId) {
          const next = await tx.candidate.findUnique({
            where: { id: input.candidateId },
            select: { id: true, agentId: true, subAgentId: true, agencierId: true, companierId: true },
          });
          if (!next) throw AppError.notFound('Candidate');
          assertCandidateAccess(actor, next);
        }
        const row = await tx.manpowerTrainingEvent.update({
          where: { id },
          data: manpowerWriteData(input) as Prisma.ManpowerTrainingEventUncheckedUpdateInput,
        });
        await writeAuditEvent(
          {
            eventType:
              input.status !== undefined && input.status !== existing.status
                ? AUDIT_EVENTS.TRAINING_MANPOWER_STATUS_CHANGED
                : AUDIT_EVENTS.TRAINING_MANPOWER_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'manpower_training',
            targetId: row.id,
            metadata: { fields: Object.keys(input).join(','), status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<ManpowerTrainingRecord> = {
        success: true,
        data: toManpowerTrainingRecord(updated),
      };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A manpower training record with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);
