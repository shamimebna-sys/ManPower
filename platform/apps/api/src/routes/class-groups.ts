import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  ClassGroupUpdateSchema,
  ClassGroupWriteSchema,
  TrainingIdParams,
  TrainingListQuerySchema,
} from '@manpower/shared';
import type { ApiResponse, ClassGroupListResult, ClassGroupRecord } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { toClassGroupRecord } from '../training/serialize.js';
import { isUniqueConflict, toBigInt } from '../training/util.js';

export const classGroupsRouter: ExpressRouter = Router();

classGroupsRouter.use(requireAuth);

function classGroupWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.name !== undefined ? { name: input.name as string } : {}),
    ...(input.description !== undefined ? { description: input.description as string } : {}),
    ...(input.code !== undefined ? { code: input.code as string } : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
    ...(input.feeAmount !== undefined ? { feeAmount: input.feeAmount as string } : {}),
  };
}

classGroupsRouter.get(
  '/',
  requirePermission('training.class_group.read'),
  async (req: Request, res: Response) => {
    const query = TrainingListQuerySchema.parse(req.query);
    const filters: Prisma.ClassGroupWhereInput = {
      ...(query.status ? { status: query.status } : {}),
      ...(query.q
        ? {
            OR: [
              { name: { contains: query.q, mode: 'insensitive' } },
              { code: { contains: query.q, mode: 'insensitive' } },
            ],
          }
        : {}),
    };
    const where = query.cursor ? { AND: [filters, { id: { lt: query.cursor } }] } : filters;
    const [items, total] = await Promise.all([
      prisma.classGroup.findMany({
        where,
        orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
        take: query.limit + 1,
      }),
      prisma.classGroup.count({ where: filters }),
    ]);
    const hasMore = items.length > query.limit;
    const page = hasMore ? items.slice(0, query.limit) : items;
    const body: ApiResponse<ClassGroupListResult> = {
      success: true,
      data: { items: page.map(toClassGroupRecord) },
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

classGroupsRouter.get(
  '/:id',
  requirePermission('training.class_group.read'),
  async (req: Request, res: Response) => {
    const { id } = TrainingIdParams.parse(req.params);
    const row = await prisma.classGroup.findUnique({ where: { id } });
    if (!row) throw AppError.notFound('Class group');
    const body: ApiResponse<ClassGroupRecord> = { success: true, data: toClassGroupRecord(row) };
    res.status(200).json(body);
  }
);

classGroupsRouter.post(
  '/',
  requireCsrf,
  requirePermission('training.class_group.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = ClassGroupWriteSchema.parse(req.body);
    try {
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.classGroup.create({
          data: classGroupWriteData(input) as Prisma.ClassGroupUncheckedCreateInput,
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.TRAINING_CLASS_GROUP_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'class_group',
            targetId: row.id,
            metadata: { status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<ClassGroupRecord> = { success: true, data: toClassGroupRecord(created) };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A class group with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);

classGroupsRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('training.class_group.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const input = ClassGroupUpdateSchema.parse(req.body);
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.classGroup.findUnique({ where: { id }, select: { id: true, status: true } });
        if (!existing) throw AppError.notFound('Class group');
        const row = await tx.classGroup.update({
          where: { id },
          data: classGroupWriteData(input) as Prisma.ClassGroupUncheckedUpdateInput,
        });
        await writeAuditEvent(
          {
            eventType:
              input.status !== undefined && input.status !== existing.status
                ? AUDIT_EVENTS.TRAINING_CLASS_GROUP_STATUS_CHANGED
                : AUDIT_EVENTS.TRAINING_CLASS_GROUP_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'class_group',
            targetId: row.id,
            metadata: { fields: Object.keys(input).join(','), status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<ClassGroupRecord> = { success: true, data: toClassGroupRecord(updated) };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A class group with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);
