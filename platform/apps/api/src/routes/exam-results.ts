import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  ExamResultListQuerySchema,
  ExamResultUpdateSchema,
  ExamResultWriteSchema,
  TrainingIdParams,
} from '@manpower/shared';
import type { ApiResponse, ExamResultListResult, ExamResultRecord } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { toExamResultRecord } from '../training/serialize.js';
import { shouldUpdateCandidateClassGroup, shouldUpdateCandidateStatus } from '../training/workflow.js';
import { isUniqueConflict } from '../training/util.js';

export const examResultsRouter: ExpressRouter = Router();

examResultsRouter.use(requireAuth);

function markData(input: Record<string, unknown>): Prisma.ExamResultUncheckedUpdateInput {
  return {
    ...(input.abroadEx !== undefined ? { abroadEx: input.abroadEx as number | null } : {}),
    ...(input.localEx !== undefined ? { localEx: input.localEx as number | null } : {}),
    ...(input.bl !== undefined ? { bl: input.bl as number | null } : {}),
    ...(input.skill !== undefined ? { skill: input.skill as number | null } : {}),
    ...(input.english !== undefined ? { english: input.english as number | null } : {}),
    ...(input.result !== undefined ? { result: input.result as string | null } : {}),
    ...(input.remarks !== undefined ? { remarks: input.remarks as string } : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
  };
}

examResultsRouter.get(
  '/',
  requirePermission('training.exam_result.read'),
  async (req: Request, res: Response) => {
    const query = ExamResultListQuerySchema.parse(req.query);
    const filters: Prisma.ExamResultWhereInput = {
      ...(query.examId ? { examId: query.examId } : {}),
      ...(query.candidateId ? { candidateId: query.candidateId } : {}),
      ...(query.result ? { result: query.result } : {}),
      ...(query.status ? { status: query.status } : {}),
    };
    const where = query.cursor ? { AND: [filters, { id: { lt: query.cursor } }] } : filters;
    const [items, total] = await Promise.all([
      prisma.examResult.findMany({
        where,
        orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
        take: query.limit + 1,
      }),
      prisma.examResult.count({ where: filters }),
    ]);
    const hasMore = items.length > query.limit;
    const page = hasMore ? items.slice(0, query.limit) : items;
    const body: ApiResponse<ExamResultListResult> = {
      success: true,
      data: { items: page.map(toExamResultRecord) },
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

examResultsRouter.get(
  '/:id',
  requirePermission('training.exam_result.read'),
  async (req: Request, res: Response) => {
    const { id } = TrainingIdParams.parse(req.params);
    const row = await prisma.examResult.findUnique({ where: { id } });
    if (!row) throw AppError.notFound('Exam result');
    const body: ApiResponse<ExamResultRecord> = { success: true, data: toExamResultRecord(row) };
    res.status(200).json(body);
  }
);

examResultsRouter.post(
  '/',
  requireCsrf,
  requirePermission('training.exam_result.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = ExamResultWriteSchema.parse(req.body);
    const exam = await prisma.exam.findUnique({ where: { id: input.examId }, select: { id: true } });
    if (!exam) throw AppError.notFound('Exam');
    const candidate = await prisma.candidate.findUnique({
      where: { id: input.candidateId },
      select: { id: true },
    });
    if (!candidate) throw AppError.notFound('Candidate');
    if (input.classGroupId) {
      const group = await prisma.classGroup.findUnique({
        where: { id: input.classGroupId },
        select: { id: true },
      });
      if (!group) throw AppError.notFound('Class group');
    }
    try {
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.examResult.create({
          data: {
            examId: input.examId,
            candidateId: input.candidateId,
            classGroupId: input.classGroupId ?? null,
            abroadEx: input.abroadEx ?? null,
            localEx: input.localEx ?? null,
            bl: input.bl ?? null,
            skill: input.skill ?? null,
            english: input.english ?? null,
            result: input.result ?? null,
            remarks: input.remarks ?? null,
            status: input.status ?? 'A',
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.TRAINING_EXAM_RESULT_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'exam_result',
            targetId: row.id,
            metadata: { examId: row.examId, result: row.result },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<ExamResultRecord> = { success: true, data: toExamResultRecord(created) };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('An exam result for this candidate and exam already exists');
      }
      throw error;
    }
  }
);

examResultsRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('training.exam_result.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const input = ExamResultUpdateSchema.parse(req.body);
    const updated = await prisma.$transaction(async (tx) => {
      const existing = await tx.examResult.findUnique({
        where: { id },
        include: { candidate: { select: { id: true, status: true, classGroupId: true, classGroupRefId: true } } },
      });
      if (!existing) throw AppError.notFound('Exam result');

      const nextResult = input.result !== undefined ? input.result : existing.result;
      let nextClassGroupId = existing.classGroupId;
      if (shouldUpdateCandidateClassGroup(nextResult) && input.classGroupId !== undefined) {
        nextClassGroupId = input.classGroupId;
      }

      if (nextClassGroupId) {
        const group = await tx.classGroup.findUnique({
          where: { id: nextClassGroupId },
          select: { id: true, sourceLegacyId: true },
        });
        if (!group) throw AppError.notFound('Class group');
        if (shouldUpdateCandidateClassGroup(nextResult) && input.classGroupId !== undefined) {
          await tx.candidate.update({
            where: { id: existing.candidateId },
            data: {
              classGroupRefId: group.id,
              ...(group.sourceLegacyId !== null ? { classGroupId: group.sourceLegacyId } : {}),
            },
          });
        }
      }

      if (shouldUpdateCandidateStatus(nextResult)) {
        throw AppError.internal('Candidate status must not change from exam results');
      }

      const row = await tx.examResult.update({
        where: { id },
        data: {
          ...markData(input),
          classGroupId: shouldUpdateCandidateClassGroup(nextResult)
            ? nextClassGroupId
            : existing.classGroupId,
        },
      });

      const outcomeChanged = input.result !== undefined && input.result !== existing.result;
      await writeAuditEvent(
        {
          eventType: outcomeChanged
            ? AUDIT_EVENTS.TRAINING_EXAM_RESULT_OUTCOME_CHANGED
            : AUDIT_EVENTS.TRAINING_EXAM_RESULT_UPDATED,
          actorUserId: req.auth?.user.id,
          targetType: 'exam_result',
          targetId: row.id,
          metadata: {
            examId: row.examId,
            result: row.result,
            previousResult: existing.result,
            candidateStatusUnchanged: existing.candidate.status,
          },
          request: req,
        },
        tx
      );
      return row;
    });
    const body: ApiResponse<ExamResultRecord> = { success: true, data: toExamResultRecord(updated) };
    res.status(200).json(body);
  }
);
