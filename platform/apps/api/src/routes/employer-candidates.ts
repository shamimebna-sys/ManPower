import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  EmployerCandidateIdParams,
  EmployerCandidateListQuerySchema,
  EmployerCandidateStatusSchema,
  EmployerCandidateUpdateSchema,
  EmployerCandidateWriteSchema,
  EmployerIdParams,
  EmployerUpdateSchema,
  EmployerWriteSchema,
} from '@manpower/shared';
import type {
  ApiResponse,
  EmployerCandidateListResult,
  EmployerCandidateRecord,
  EmployerListResult,
  EmployerRecord,
} from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import {
  toEmployerCandidateRecord,
  toEmployerRecord,
} from '../recruitment/serialize.js';
import {
  assertActiveMembershipAvailable,
  assertCandidateAndEmployerExist,
  assertCanManageAssignment,
  assertCanViewAssignment,
  assignmentListScope,
  resolveAssignmentEmployerId,
} from '../recruitment/employer-candidates.js';

export const employerCandidatesRouter: ExpressRouter = Router();

employerCandidatesRouter.use(requireAuth);

function isUniqueConflict(error: unknown): boolean {
  return typeof error === 'object' && error !== null && 'code' in error && error.code === 'P2002';
}

function toBigInt(value: string | undefined): bigint | undefined {
  if (value === undefined) return undefined;
  return BigInt(value);
}

employerCandidatesRouter.get(
  '/employers',
  requirePermission('partners.read'),
  async (req: Request, res: Response) => {
    const q = typeof req.query.q === 'string' ? req.query.q : undefined;
    const status = typeof req.query.status === 'string' ? req.query.status : undefined;
    const cursor = typeof req.query.cursor === 'string' ? req.query.cursor : undefined;
    const limit = Math.min(Math.max(Number(req.query.limit ?? 20) || 20, 1), 100);
    const filters: Prisma.EmployerWhereInput = {
      ...(status ? { status } : {}),
      ...(q
        ? {
            OR: [
              { name: { contains: q, mode: 'insensitive' } },
              { code: { contains: q, mode: 'insensitive' } },
              { email: { contains: q, mode: 'insensitive' } },
            ],
          }
        : {}),
    };
    const where = cursor ? { AND: [filters, { id: { lt: cursor } }] } : filters;
    const [items, total] = await prisma.$transaction([
      prisma.employer.findMany({
        where,
        orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
        take: limit + 1,
      }),
      prisma.employer.count({ where: filters }),
    ]);
    const hasMore = items.length > limit;
    const page = hasMore ? items.slice(0, limit) : items;
    const body: ApiResponse<EmployerListResult> = {
      success: true,
      data: { items: page.map(toEmployerRecord) },
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

employerCandidatesRouter.post(
  '/employers',
  requireCsrf,
  requirePermission('partners.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = EmployerWriteSchema.parse(req.body);
    const created = await prisma.$transaction(async (tx) => {
      const sourceLegacyId = toBigInt(input.sourceLegacyId);
      const row = await tx.employer.create({
        data: {
          ...(sourceLegacyId !== undefined ? { sourceLegacyId } : {}),
          ...(input.code !== undefined ? { code: input.code } : {}),
          ...(input.name !== undefined ? { name: input.name } : {}),
          ...(input.email !== undefined ? { email: input.email } : {}),
          ...(input.mobile !== undefined ? { mobile: input.mobile } : {}),
          ...(input.status !== undefined ? { status: input.status } : {}),
        },
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.RECRUITMENT_PARTNER_CREATED,
          actorUserId: req.auth?.user.id,
          targetType: 'employer',
          targetId: row.id,
          metadata: { type: 'employer', status: row.status },
          request: req,
        },
        tx
      );
      return row;
    });
    const body: ApiResponse<EmployerRecord> = { success: true, data: toEmployerRecord(created) };
    res.status(201).json(body);
  }
);

employerCandidatesRouter.patch(
  '/employers/:id',
  requireCsrf,
  requirePermission('partners.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = EmployerIdParams.parse(req.params);
    const input = EmployerUpdateSchema.parse(req.body);
    const updated = await prisma.$transaction(async (tx) => {
      const existing = await tx.employer.findUnique({ where: { id }, select: { id: true } });
      if (!existing) throw AppError.notFound('Employer');
      const sourceLegacyId = toBigInt(input.sourceLegacyId);
      const row = await tx.employer.update({
        where: { id },
        data: {
          ...(sourceLegacyId !== undefined ? { sourceLegacyId } : {}),
          ...(input.code !== undefined ? { code: input.code } : {}),
          ...(input.name !== undefined ? { name: input.name } : {}),
          ...(input.email !== undefined ? { email: input.email } : {}),
          ...(input.mobile !== undefined ? { mobile: input.mobile } : {}),
          ...(input.status !== undefined ? { status: input.status } : {}),
        },
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.RECRUITMENT_PARTNER_UPDATED,
          actorUserId: req.auth?.user.id,
          targetType: 'employer',
          targetId: row.id,
          metadata: { type: 'employer', fields: Object.keys(input).join(',') },
          request: req,
        },
        tx
      );
      return row;
    });
    const body: ApiResponse<EmployerRecord> = { success: true, data: toEmployerRecord(updated) };
    res.status(200).json(body);
  }
);

employerCandidatesRouter.get('/', requirePermission('employer_candidate.read'), async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const query = EmployerCandidateListQuerySchema.parse(req.query);
  const filters: Prisma.EmployerCandidateWhereInput = {
    ...(query.employerId ? { employerId: query.employerId } : {}),
    ...(query.candidateId ? { candidateId: query.candidateId } : {}),
    ...(query.purpose ? { purpose: query.purpose } : {}),
    ...(query.status ? { status: query.status } : {}),
  };
  const scoped = assignmentListScope(req.auth.user, filters);
  if (scoped === null) {
    const empty: ApiResponse<EmployerCandidateListResult> = {
      success: true,
      data: { items: [] },
      meta: {
        timestamp: new Date().toISOString(),
        version: 'v1',
        pagination: { nextCursor: null, prevCursor: null, total: 0 },
      },
    };
    res.status(200).json(empty);
    return;
  }
  const where = query.cursor ? { AND: [scoped, { id: { lt: query.cursor } }] } : scoped;
  const [items, total] = await prisma.$transaction([
    prisma.employerCandidate.findMany({
      where,
      orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
      take: query.limit + 1,
    }),
    prisma.employerCandidate.count({ where: scoped }),
  ]);
  const hasMore = items.length > query.limit;
  const page = hasMore ? items.slice(0, query.limit) : items;
  const body: ApiResponse<EmployerCandidateListResult> = {
    success: true,
    data: { items: page.map(toEmployerCandidateRecord) },
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

employerCandidatesRouter.post(
  '/',
  requireCsrf,
  requirePermission('employer_candidate.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = EmployerCandidateWriteSchema.parse(req.body);
    const employerId = resolveAssignmentEmployerId(req.auth.user, input.employerId);
    const status = input.status ?? 'A';
    try {
      const created = await prisma.$transaction(async (tx) => {
        await assertCandidateAndEmployerExist(tx, input.candidateId, employerId);
        if (status === 'A') {
          await assertActiveMembershipAvailable(tx, {
            employerId,
            candidateId: input.candidateId,
            purpose: input.purpose,
          });
        }
        const row = await tx.employerCandidate.create({
          data: {
            employerId,
            candidateId: input.candidateId,
            purpose: input.purpose,
            status,
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.EMPLOYER_CANDIDATE_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'employer_candidate',
            targetId: row.id,
            metadata: { purpose: row.purpose, status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<EmployerCandidateRecord> = {
        success: true,
        data: toEmployerCandidateRecord(created),
      };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('An active membership already exists for this employer, candidate, and purpose');
      }
      throw error;
    }
  }
);

employerCandidatesRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('employer_candidate.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actor = req.auth.user;
    const { id } = EmployerCandidateIdParams.parse(req.params);
    const input = EmployerCandidateUpdateSchema.parse(req.body);
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.employerCandidate.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('Employer candidate');
        assertCanManageAssignment(actor, existing);
        const nextPurpose = input.purpose ?? existing.purpose;
        const nextStatus = input.status ?? existing.status;
        if (nextStatus === 'A') {
          await assertActiveMembershipAvailable(tx, {
            employerId: existing.employerId,
            candidateId: existing.candidateId,
            purpose: nextPurpose,
            excludeId: existing.id,
          });
        }
        const row = await tx.employerCandidate.update({
          where: { id },
          data: {
            ...(input.purpose !== undefined ? { purpose: input.purpose } : {}),
            ...(input.status !== undefined ? { status: input.status } : {}),
          },
        });
        const statusChanged = input.status !== undefined && input.status !== existing.status;
        await writeAuditEvent(
          {
            eventType: statusChanged
              ? AUDIT_EVENTS.EMPLOYER_CANDIDATE_STATUS_CHANGED
              : AUDIT_EVENTS.EMPLOYER_CANDIDATE_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'employer_candidate',
            targetId: row.id,
            metadata: {
              purpose: row.purpose,
              ...(statusChanged ? { from: existing.status, to: row.status } : { status: row.status }),
            },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<EmployerCandidateRecord> = {
        success: true,
        data: toEmployerCandidateRecord(updated),
      };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('An active membership already exists for this employer, candidate, and purpose');
      }
      throw error;
    }
  }
);

employerCandidatesRouter.patch(
  '/:id/status',
  requireCsrf,
  requirePermission('employer_candidate.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actor = req.auth.user;
    const { id } = EmployerCandidateIdParams.parse(req.params);
    const input = EmployerCandidateStatusSchema.parse(req.body);
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.employerCandidate.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('Employer candidate');
        assertCanManageAssignment(actor, existing);
        if (input.status === 'A') {
          await assertActiveMembershipAvailable(tx, {
            employerId: existing.employerId,
            candidateId: existing.candidateId,
            purpose: existing.purpose,
            excludeId: existing.id,
          });
        }
        const row = await tx.employerCandidate.update({
          where: { id },
          data: { status: input.status },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.EMPLOYER_CANDIDATE_STATUS_CHANGED,
            actorUserId: req.auth?.user.id,
            targetType: 'employer_candidate',
            targetId: row.id,
            metadata: { from: existing.status, to: row.status, purpose: row.purpose },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<EmployerCandidateRecord> = {
        success: true,
        data: toEmployerCandidateRecord(updated),
      };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('An active membership already exists for this employer, candidate, and purpose');
      }
      throw error;
    }
  }
);

employerCandidatesRouter.get(
  '/:id',
  requirePermission('employer_candidate.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = EmployerCandidateIdParams.parse(req.params);
    const row = await prisma.employerCandidate.findUnique({ where: { id } });
    if (!row) throw AppError.notFound('Employer candidate');
    assertCanViewAssignment(req.auth.user, row);
    const body: ApiResponse<EmployerCandidateRecord> = {
      success: true,
      data: toEmployerCandidateRecord(row),
    };
    res.status(200).json(body);
  }
);
