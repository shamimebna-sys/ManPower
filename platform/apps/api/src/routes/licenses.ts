import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import type { AuthenticatedUser } from '@manpower/shared';
import {
  LicenseListQuerySchema,
  LicenseUpdateSchema,
  LicenseWriteSchema,
  OverseasIdParams,
} from '@manpower/shared';
import type { ApiResponse, LicenseListResult } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { isUniqueConflict, toBigInt } from '../training/util.js';
import { parseDateOnly, toLicenseRecord } from '../overseas/serialize.js';
import { optionalFileId } from '../overseas/write-data.js';

type LicensePositionInput = {
  sourceLegacyId?: string | undefined;
  positionId?: string | undefined;
  quantity?: number | undefined;
};

function isLicenseStaff(roles: readonly string[]): boolean {
  return (
    roles.includes('super_admin') ||
    roles.includes('administrator') ||
    roles.includes('owner') ||
    roles.includes('employee')
  );
}

function boundCompanierId(user: AuthenticatedUser): string | null {
  return user.bindings?.companierId ?? null;
}

function assertLicenseRowAccess(user: AuthenticatedUser, row: { companierId: string | null }): void {
  if (isLicenseStaff(user.roles)) return;
  const bound = boundCompanierId(user);
  if (!bound || !row.companierId || bound !== row.companierId) {
    throw AppError.notFound('License');
  }
}

function licenseListWhere(
  user: AuthenticatedUser,
  query: { companierId?: string | undefined; status?: string | undefined }
): Prisma.LicenseWhereInput | null {
  const filters: Prisma.LicenseWhereInput = {
    ...(query.companierId ? { companierId: query.companierId } : {}),
    ...(query.status ? { status: query.status } : {}),
  };
  if (isLicenseStaff(user.roles)) return filters;
  const bound = boundCompanierId(user);
  if (!bound) return null;
  return { AND: [filters, { companierId: bound }] };
}

export const licensesRouter: ExpressRouter = Router();
licensesRouter.use(requireAuth);

function licenseWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.licenseNo !== undefined ? { licenseNo: input.licenseNo as string } : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
    ...(input.companierId !== undefined ? { companierId: input.companierId as string } : {}),
    ...(input.licenseStartDate !== undefined
      ? { licenseStartDate: parseDateOnly(input.licenseStartDate as string) }
      : {}),
    ...(input.licenseExpireDate !== undefined
      ? { licenseExpireDate: parseDateOnly(input.licenseExpireDate as string) }
      : {}),
    ...(input.licenseFileId !== undefined ? { licenseFileId: optionalFileId(input.licenseFileId) ?? null } : {}),
  };
}

async function assertCompanierExists(companierId: string, client: typeof prisma | Prisma.TransactionClient) {
  const companier = await client.companier.findUnique({ where: { id: companierId }, select: { id: true } });
  if (!companier) throw AppError.notFound('Companier');
}

async function replacePositions(
  tx: Prisma.TransactionClient,
  licenseId: string,
  positions: LicensePositionInput[] | undefined
) {
  if (positions === undefined) return;
  await tx.licensePosition.deleteMany({ where: { licenseId } });
  if (positions.length === 0) return;
  await tx.licensePosition.createMany({
    data: positions.map((position) => ({
      licenseId,
      sourceLegacyId: position.sourceLegacyId ? BigInt(position.sourceLegacyId) : null,
      positionId: position.positionId ? BigInt(position.positionId) : null,
      quantity: position.quantity ?? 0,
    })),
  });
}

licensesRouter.get(
  '/',
  requirePermission('operations.license.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const query = LicenseListQuerySchema.parse(req.query);
    const filters = licenseListWhere(req.auth.user, query);
    if (filters === null) {
      res.status(200).json({
        success: true,
        data: { items: [] },
        meta: {
          timestamp: new Date().toISOString(),
          version: 'v1',
          pagination: { nextCursor: null, prevCursor: null, total: 0 },
        },
      });
      return;
    }
    const where = query.cursor ? { AND: [filters, { id: { lt: query.cursor } }] } : filters;
    const [items, total] = await Promise.all([
      prisma.license.findMany({
        where,
        include: { positions: true },
        orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
        take: query.limit + 1,
      }),
      prisma.license.count({ where: filters }),
    ]);
    const hasMore = items.length > query.limit;
    const page = hasMore ? items.slice(0, query.limit) : items;
    const body: ApiResponse<LicenseListResult> = {
      success: true,
      data: { items: page.map(toLicenseRecord) },
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

licensesRouter.get(
  '/:id',
  requirePermission('operations.license.read'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { id } = OverseasIdParams.parse(req.params);
    const row = await prisma.license.findUnique({ where: { id }, include: { positions: true } });
    if (!row) throw AppError.notFound('License');
    assertLicenseRowAccess(req.auth.user, row);
    res.status(200).json({ success: true, data: toLicenseRecord(row) });
  }
);

licensesRouter.post(
  '/',
  requireCsrf,
  requirePermission('operations.license.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const input = LicenseWriteSchema.parse(req.body);
    await assertCompanierExists(input.companierId, prisma);
    try {
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.license.create({
          data: licenseWriteData(input) as Prisma.LicenseUncheckedCreateInput,
        });
        await replacePositions(tx, row.id, input.positions);
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.OVERSEAS_LICENSE_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'license',
            targetId: row.id,
            metadata: { companierId: row.companierId, licenseNo: row.licenseNo },
            request: req,
          },
          tx
        );
        return tx.license.findUniqueOrThrow({ where: { id: row.id }, include: { positions: true } });
      });
      res.status(201).json({ success: true, data: toLicenseRecord(created) });
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A license with the same number or legacy identifier already exists');
      }
      throw error;
    }
  }
);

licensesRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('operations.license.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actor = req.auth.user;
    const { id } = OverseasIdParams.parse(req.params);
    const input = LicenseUpdateSchema.parse(req.body);
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.license.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('License');
        assertLicenseRowAccess(actor, existing);
        if (input.companierId) await assertCompanierExists(input.companierId, tx);
        await tx.license.update({
          where: { id },
          data: licenseWriteData(input) as Prisma.LicenseUncheckedUpdateInput,
        });
        await replacePositions(tx, id, input.positions);
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.OVERSEAS_LICENSE_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'license',
            targetId: id,
            metadata: { fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return tx.license.findUniqueOrThrow({ where: { id }, include: { positions: true } });
      });
      res.status(200).json({ success: true, data: toLicenseRecord(updated) });
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A license with the same number or legacy identifier already exists');
      }
      throw error;
    }
  }
);
