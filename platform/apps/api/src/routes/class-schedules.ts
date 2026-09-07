import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  ClassScheduleUpdateSchema,
  ClassScheduleWriteSchema,
  TrainingIdParams,
  TrainingListQuerySchema,
} from '@manpower/shared';
import type { ApiResponse, ClassScheduleListResult, ClassScheduleRecord } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { assertScheduleReadAccess, resolveScheduleReadScope } from '../training/access.js';
import { parseTimeOfDay, toClassScheduleRecord } from '../training/serialize.js';
import { isUniqueConflict, toBigInt } from '../training/util.js';

export const classSchedulesRouter: ExpressRouter = Router();

classSchedulesRouter.use(requireAuth);

async function assertScheduleTargets(teacherId?: string, classGroupId?: string): Promise<void> {
  if (teacherId) {
    const teacher = await prisma.teacher.findUnique({ where: { id: teacherId }, select: { id: true } });
    if (!teacher) throw AppError.notFound('Teacher');
  }
  if (classGroupId) {
    const group = await prisma.classGroup.findUnique({ where: { id: classGroupId }, select: { id: true } });
    if (!group) throw AppError.notFound('Class group');
  }
}

function scheduleWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.teacherId !== undefined ? { teacherId: input.teacherId as string } : {}),
    ...(input.classGroupId !== undefined ? { classGroupId: input.classGroupId as string } : {}),
    ...(input.subject !== undefined ? { subject: input.subject as string } : {}),
    ...(input.startTime !== undefined ? { startTime: parseTimeOfDay(input.startTime as string) } : {}),
    ...(input.endTime !== undefined ? { endTime: parseTimeOfDay(input.endTime as string) } : {}),
    ...(input.weekDay !== undefined ? { weekDay: input.weekDay as string } : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
  };
}

classSchedulesRouter.get(
  '/',
  requirePermission('training.schedule.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const query = TrainingListQuerySchema.parse(req.query);
    const scope = resolveScheduleReadScope(req.auth.user);
    if (scope.kind === 'none') {
      const body: ApiResponse<ClassScheduleListResult> = {
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
    const filters: Prisma.ClassScheduleWhereInput = {
      ...(scope.kind === 'teacher' ? { teacherId: scope.teacherId } : {}),
      ...(query.status ? { status: query.status } : {}),
      ...(query.q
        ? {
            OR: [
              { subject: { contains: query.q, mode: 'insensitive' } },
              { weekDay: { contains: query.q, mode: 'insensitive' } },
            ],
          }
        : {}),
    };
    const where = query.cursor ? { AND: [filters, { id: { lt: query.cursor } }] } : filters;
    const [items, total] = await Promise.all([
      prisma.classSchedule.findMany({
        where,
        orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
        take: query.limit + 1,
      }),
      prisma.classSchedule.count({ where: filters }),
    ]);
    const hasMore = items.length > query.limit;
    const page = hasMore ? items.slice(0, query.limit) : items;
    const body: ApiResponse<ClassScheduleListResult> = {
      success: true,
      data: { items: page.map(toClassScheduleRecord) },
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

classSchedulesRouter.get(
  '/:id',
  requirePermission('training.schedule.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const row = await prisma.classSchedule.findUnique({ where: { id } });
    if (!row) throw AppError.notFound('Class schedule');
    assertScheduleReadAccess(req.auth.user, row.teacherId);
    const body: ApiResponse<ClassScheduleRecord> = { success: true, data: toClassScheduleRecord(row) };
    res.status(200).json(body);
  }
);

classSchedulesRouter.post(
  '/',
  requireCsrf,
  requirePermission('training.schedule.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = ClassScheduleWriteSchema.parse(req.body);
    await assertScheduleTargets(input.teacherId, input.classGroupId);
    try {
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.classSchedule.create({
          data: scheduleWriteData(input) as Prisma.ClassScheduleUncheckedCreateInput,
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.TRAINING_SCHEDULE_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'class_schedule',
            targetId: row.id,
            metadata: { status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<ClassScheduleRecord> = { success: true, data: toClassScheduleRecord(created) };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A class schedule with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);

classSchedulesRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('training.schedule.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const input = ClassScheduleUpdateSchema.parse(req.body);
    await assertScheduleTargets(input.teacherId, input.classGroupId);
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.classSchedule.findUnique({
          where: { id },
          select: { id: true, status: true },
        });
        if (!existing) throw AppError.notFound('Class schedule');
        const row = await tx.classSchedule.update({
          where: { id },
          data: scheduleWriteData(input) as Prisma.ClassScheduleUncheckedUpdateInput,
        });
        await writeAuditEvent(
          {
            eventType:
              input.status !== undefined && input.status !== existing.status
                ? AUDIT_EVENTS.TRAINING_SCHEDULE_STATUS_CHANGED
                : AUDIT_EVENTS.TRAINING_SCHEDULE_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'class_schedule',
            targetId: row.id,
            metadata: { fields: Object.keys(input).join(','), status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<ClassScheduleRecord> = { success: true, data: toClassScheduleRecord(updated) };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A class schedule with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);
