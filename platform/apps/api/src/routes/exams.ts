import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  ExamClassGroupsSchema,
  ExamUpdateSchema,
  ExamWriteSchema,
  TrainingIdParams,
  TrainingListQuerySchema,
} from '@manpower/shared';
import type { ApiResponse, ExamListResult, ExamPublishResult, ExamRecord } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { publishExamResults } from '../training/publish.js';
import { parseDateOnly, parseTimeOfDay, toExamRecord } from '../training/serialize.js';
import { isUniqueConflict, toBigInt } from '../training/util.js';

export const examsRouter: ExpressRouter = Router();

examsRouter.use(requireAuth);

async function assertClassGroupsExist(ids: string[]): Promise<void> {
  if (ids.length === 0) return;
  const found = await prisma.classGroup.findMany({
    where: { id: { in: ids } },
    select: { id: true },
  });
  if (found.length !== ids.length) {
    throw AppError.notFound('Class group');
  }
}

async function replaceExamClassGroups(
  tx: Prisma.TransactionClient,
  examId: string,
  classGroupIds: string[]
): Promise<void> {
  const uniqueIds = [...new Set(classGroupIds)];
  await tx.examClassGroup.deleteMany({
    where: { examId, classGroupId: { notIn: uniqueIds } },
  });
  for (const classGroupId of uniqueIds) {
    await tx.examClassGroup.upsert({
      where: { examId_classGroupId: { examId, classGroupId } },
      create: { examId, classGroupId },
      update: {},
    });
  }
}

function examWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.name !== undefined ? { name: input.name as string } : {}),
    ...(input.examDate !== undefined ? { examDate: parseDateOnly(input.examDate as string) } : {}),
    ...(input.examTime !== undefined ? { examTime: parseTimeOfDay(input.examTime as string) } : {}),
    ...(input.examLink !== undefined ? { examLink: input.examLink as string } : {}),
    ...(input.remarks !== undefined ? { remarks: input.remarks as string } : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
  };
}

async function loadExamRecord(id: string): Promise<ExamRecord> {
  const row = await prisma.exam.findUnique({
    where: { id },
    include: { classGroups: { select: { classGroupId: true } } },
  });
  if (!row) throw AppError.notFound('Exam');
  return toExamRecord(
    row,
    row.classGroups.map((item) => item.classGroupId)
  );
}

examsRouter.get('/', requirePermission('training.exam.read'), async (req: Request, res: Response) => {
  const query = TrainingListQuerySchema.parse(req.query);
  const filters: Prisma.ExamWhereInput = {
    ...(query.status ? { status: query.status } : {}),
    ...(query.q ? { name: { contains: query.q, mode: 'insensitive' } } : {}),
  };
  const where = query.cursor ? { AND: [filters, { id: { lt: query.cursor } }] } : filters;
  const [items, total] = await Promise.all([
    prisma.exam.findMany({
      where,
      include: { classGroups: { select: { classGroupId: true } } },
      orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
      take: query.limit + 1,
    }),
    prisma.exam.count({ where: filters }),
  ]);
  const hasMore = items.length > query.limit;
  const page = hasMore ? items.slice(0, query.limit) : items;
  const body: ApiResponse<ExamListResult> = {
    success: true,
    data: {
      items: page.map((row) => toExamRecord(row, row.classGroups.map((item) => item.classGroupId))),
    },
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
});

examsRouter.get('/:id', requirePermission('training.exam.read'), async (req: Request, res: Response) => {
  const { id } = TrainingIdParams.parse(req.params);
  const body: ApiResponse<ExamRecord> = { success: true, data: await loadExamRecord(id) };
  res.status(200).json(body);
});

examsRouter.post(
  '/',
  requireCsrf,
  requirePermission('training.exam.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = ExamWriteSchema.parse(req.body);
    const classGroupIds = input.classGroupIds ?? [];
    await assertClassGroupsExist(classGroupIds);
    try {
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.exam.create({ data: examWriteData(input) as Prisma.ExamUncheckedCreateInput });
        if (classGroupIds.length > 0) {
          await replaceExamClassGroups(tx, row.id, classGroupIds);
        }
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.TRAINING_EXAM_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'exam',
            targetId: row.id,
            metadata: { status: row.status, classGroups: classGroupIds.length },
            request: req,
          },
          tx
        );
        if (classGroupIds.length > 0) {
          await writeAuditEvent(
            {
              eventType: AUDIT_EVENTS.TRAINING_EXAM_CLASS_GROUPS_CHANGED,
              actorUserId: req.auth?.user.id,
              targetType: 'exam',
              targetId: row.id,
              metadata: { classGroups: classGroupIds.length },
              request: req,
            },
            tx
          );
        }
        return row.id;
      });
      const body: ApiResponse<ExamRecord> = { success: true, data: await loadExamRecord(created) };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('An exam with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);

examsRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('training.exam.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const input = ExamUpdateSchema.parse(req.body);
    if (input.classGroupIds) {
      await assertClassGroupsExist(input.classGroupIds);
    }
    try {
      await prisma.$transaction(async (tx) => {
        const existing = await tx.exam.findUnique({ where: { id }, select: { id: true, status: true } });
        if (!existing) throw AppError.notFound('Exam');
        const row = await tx.exam.update({
          where: { id },
          data: examWriteData(input) as Prisma.ExamUncheckedUpdateInput,
        });
        if (input.classGroupIds) {
          await replaceExamClassGroups(tx, id, input.classGroupIds);
          await writeAuditEvent(
            {
              eventType: AUDIT_EVENTS.TRAINING_EXAM_CLASS_GROUPS_CHANGED,
              actorUserId: req.auth?.user.id,
              targetType: 'exam',
              targetId: id,
              metadata: { classGroups: input.classGroupIds.length },
              request: req,
            },
            tx
          );
        }
        await writeAuditEvent(
          {
            eventType:
              input.status !== undefined && input.status !== existing.status
                ? AUDIT_EVENTS.TRAINING_EXAM_STATUS_CHANGED
                : AUDIT_EVENTS.TRAINING_EXAM_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'exam',
            targetId: row.id,
            metadata: { fields: Object.keys(input).join(','), status: row.status },
            request: req,
          },
          tx
        );
      });
      const body: ApiResponse<ExamRecord> = { success: true, data: await loadExamRecord(id) };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('An exam with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);

examsRouter.put(
  '/:id/class-groups',
  requireCsrf,
  requirePermission('training.exam.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const input = ExamClassGroupsSchema.parse(req.body);
    await assertClassGroupsExist(input.classGroupIds);
    await prisma.$transaction(async (tx) => {
      const existing = await tx.exam.findUnique({ where: { id }, select: { id: true } });
      if (!existing) throw AppError.notFound('Exam');
      await replaceExamClassGroups(tx, id, input.classGroupIds);
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.TRAINING_EXAM_CLASS_GROUPS_CHANGED,
          actorUserId: req.auth?.user.id,
          targetType: 'exam',
          targetId: id,
          metadata: { classGroups: input.classGroupIds.length },
          request: req,
        },
        tx
      );
    });
    const body: ApiResponse<ExamRecord> = { success: true, data: await loadExamRecord(id) };
    res.status(200).json(body);
  }
);

examsRouter.post(
  '/:id/publish',
  requireCsrf,
  requirePermission('training.exam_result.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const counts = await prisma.$transaction(async (tx) => {
      const existing = await tx.exam.findUnique({ where: { id }, select: { id: true } });
      if (!existing) throw AppError.notFound('Exam');
      const result = await publishExamResults(tx, id);
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.TRAINING_EXAM_PUBLISHED,
          actorUserId: req.auth?.user.id,
          targetType: 'exam',
          targetId: id,
          metadata: { created: result.created, existing: result.existing },
          request: req,
        },
        tx
      );
      return result;
    });
    const body: ApiResponse<ExamPublishResult> = {
      success: true,
      data: { examId: id, created: counts.created, existing: counts.existing },
    };
    res.status(200).json(body);
  }
);
