import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  TeacherUpdateSchema,
  TeacherWriteSchema,
  TrainingIdParams,
  TrainingListQuerySchema,
} from '@manpower/shared';
import type { ApiResponse, TeacherListResult, TeacherRecord } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { assertTeacherReadAccess, resolveTeacherReadScope } from '../training/access.js';
import { parseDateOnly, toTeacherRecord } from '../training/serialize.js';
import { isUniqueConflict, toBigInt } from '../training/util.js';

export const teachersRouter: ExpressRouter = Router();

teachersRouter.use(requireAuth);

function teacherWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.code !== undefined ? { code: input.code as string } : {}),
    ...(input.name !== undefined ? { name: input.name as string } : {}),
    ...(input.email !== undefined ? { email: input.email as string } : {}),
    ...(input.secondaryEmail !== undefined ? { secondaryEmail: input.secondaryEmail as string } : {}),
    ...(input.mobile !== undefined ? { mobile: input.mobile as string } : {}),
    ...(input.secondaryMobile !== undefined ? { secondaryMobile: input.secondaryMobile as string } : {}),
    ...(input.emergencyMobile !== undefined ? { emergencyMobile: input.emergencyMobile as string } : {}),
    ...(input.nid !== undefined ? { nid: input.nid as string } : {}),
    ...(input.passportNo !== undefined ? { passportNo: input.passportNo as string } : {}),
    ...(input.dob !== undefined ? { dob: parseDateOnly(input.dob as string) } : {}),
    ...(input.gender !== undefined ? { gender: input.gender as string } : {}),
    ...(input.nationality !== undefined ? { nationality: input.nationality as string } : {}),
    ...(input.fatherName !== undefined ? { fatherName: input.fatherName as string } : {}),
    ...(input.motherName !== undefined ? { motherName: input.motherName as string } : {}),
    ...(input.bloodGroup !== undefined ? { bloodGroup: input.bloodGroup as string } : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
  };
}

teachersRouter.get('/', requirePermission('training.teacher.read'), async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const query = TrainingListQuerySchema.parse(req.query);
  const scope = resolveTeacherReadScope(req.auth.user);
  if (scope.kind === 'none') {
    const body: ApiResponse<TeacherListResult> = {
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
  const filters: Prisma.TeacherWhereInput = {
    ...(scope.kind === 'self' ? { id: scope.teacherId } : {}),
    ...(query.status ? { status: query.status } : {}),
    ...(query.q
      ? {
          OR: [
            { name: { contains: query.q, mode: 'insensitive' } },
            { code: { contains: query.q, mode: 'insensitive' } },
            { email: { contains: query.q, mode: 'insensitive' } },
            { mobile: { contains: query.q, mode: 'insensitive' } },
          ],
        }
      : {}),
  };
  const where = query.cursor ? { AND: [filters, { id: { lt: query.cursor } }] } : filters;
  const [items, total] = await Promise.all([
    prisma.teacher.findMany({
      where,
      orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
      take: query.limit + 1,
    }),
    prisma.teacher.count({ where: filters }),
  ]);
  const hasMore = items.length > query.limit;
  const page = hasMore ? items.slice(0, query.limit) : items;
  const body: ApiResponse<TeacherListResult> = {
    success: true,
    data: { items: page.map(toTeacherRecord) },
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

teachersRouter.get('/:id', requirePermission('training.teacher.read'), async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const { id } = TrainingIdParams.parse(req.params);
  const row = await prisma.teacher.findUnique({ where: { id } });
  if (!row) throw AppError.notFound('Teacher');
  assertTeacherReadAccess(req.auth.user, row.id);
  const body: ApiResponse<TeacherRecord> = { success: true, data: toTeacherRecord(row) };
  res.status(200).json(body);
});

teachersRouter.post(
  '/',
  requireCsrf,
  requirePermission('training.teacher.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = TeacherWriteSchema.parse(req.body);
    try {
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.teacher.create({ data: teacherWriteData(input) as Prisma.TeacherUncheckedCreateInput });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.TRAINING_TEACHER_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'teacher',
            targetId: row.id,
            metadata: { status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<TeacherRecord> = { success: true, data: toTeacherRecord(created) };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A teacher with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);

teachersRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('training.teacher.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = TrainingIdParams.parse(req.params);
    const input = TeacherUpdateSchema.parse(req.body);
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.teacher.findUnique({ where: { id }, select: { id: true } });
        if (!existing) throw AppError.notFound('Teacher');
        const row = await tx.teacher.update({
          where: { id },
          data: teacherWriteData(input) as Prisma.TeacherUncheckedUpdateInput,
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.TRAINING_TEACHER_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'teacher',
            targetId: row.id,
            metadata: { fields: Object.keys(input).join(','), status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<TeacherRecord> = { success: true, data: toTeacherRecord(updated) };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A teacher with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);
