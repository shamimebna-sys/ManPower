import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  PartnerIdParams,
  PartnerListQuerySchema,
  PartnerTypeParams,
  PartnerUpdateSchema,
  PartnerWriteSchema,
} from '@manpower/shared';
import type { ApiResponse, PartnerListResult, PartnerRecord, PartnerType } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { toPartnerRecord, toPartnerSummary } from '../recruitment/serialize.js';

export const partnersRouter: ExpressRouter = Router();

partnersRouter.use(requireAuth);

function isUniqueConflict(error: unknown): boolean {
  return typeof error === 'object' && error !== null && 'code' in error && error.code === 'P2002';
}

async function findPartner(type: PartnerType, id: string) {
  if (type === 'agent') return prisma.agent.findUnique({ where: { id } });
  if (type === 'sub_agent') return prisma.subAgent.findUnique({ where: { id } });
  if (type === 'agencier') return prisma.agencier.findUnique({ where: { id } });
  return prisma.companier.findUnique({ where: { id } });
}

async function listPartners(
  type: PartnerType,
  where: object,
  countWhere: object,
  take: number
) {
  const orderBy = [{ createdAt: 'desc' as const }, { id: 'desc' as const }];
  if (type === 'agent') {
    return Promise.all([
      prisma.agent.findMany({ where: where as Prisma.AgentWhereInput, orderBy, take }),
      prisma.agent.count({ where: countWhere as Prisma.AgentWhereInput }),
    ]);
  }
  if (type === 'sub_agent') {
    return Promise.all([
      prisma.subAgent.findMany({ where: where as Prisma.SubAgentWhereInput, orderBy, take }),
      prisma.subAgent.count({ where: countWhere as Prisma.SubAgentWhereInput }),
    ]);
  }
  if (type === 'agencier') {
    return Promise.all([
      prisma.agencier.findMany({ where: where as Prisma.AgencierWhereInput, orderBy, take }),
      prisma.agencier.count({ where: countWhere as Prisma.AgencierWhereInput }),
    ]);
  }
  return Promise.all([
    prisma.companier.findMany({ where: where as Prisma.CompanierWhereInput, orderBy, take }),
    prisma.companier.count({ where: countWhere as Prisma.CompanierWhereInput }),
  ]);
}

function toBigInt(value: string | undefined): bigint | undefined {
  if (value === undefined) return undefined;
  return BigInt(value);
}

function partnerWriteData(type: PartnerType, input: Record<string, unknown>): Record<string, unknown> {
  const data: Record<string, unknown> = {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.code !== undefined ? { code: input.code } : {}),
    ...(input.name !== undefined ? { name: input.name } : {}),
    ...(input.email !== undefined ? { email: input.email } : {}),
    ...(input.mobile !== undefined ? { mobile: input.mobile } : {}),
    ...(input.address !== undefined ? { address: input.address } : {}),
    ...(input.status !== undefined ? { status: input.status } : {}),
    ...(input.countryId !== undefined ? { countryId: toBigInt(input.countryId as string) } : {}),
    ...(input.logoFileRef !== undefined ? { logoFileRef: input.logoFileRef } : {}),
  };
  if (type === 'sub_agent' && input.agentId !== undefined) {
    data.agentId = input.agentId;
  }
  if (type === 'agencier' || type === 'companier') {
    if (input.licenseNo !== undefined) data.licenseNo = input.licenseNo;
    if (input.vatNo !== undefined) data.vatNo = input.vatNo;
    if (input.ownerName !== undefined) data.ownerName = input.ownerName;
    if (input.ownerMobile !== undefined) data.ownerMobile = input.ownerMobile;
    if (input.ownerEmail !== undefined) data.ownerEmail = input.ownerEmail;
  }
  if (type === 'agencier' && input.signatureFileRef !== undefined) {
    data.signatureFileRef = input.signatureFileRef;
  }
  return data;
}

partnersRouter.get('/:type', requirePermission('partners.read'), async (req: Request, res: Response) => {
  const { type } = PartnerTypeParams.parse(req.params);
  const query = PartnerListQuerySchema.parse(req.query);
  const filters: Prisma.AgentWhereInput = {
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
  const [items, total] = await listPartners(type, where, filters, query.limit + 1);
  const hasMore = items.length > query.limit;
  const page = hasMore ? items.slice(0, query.limit) : items;
  const body: ApiResponse<PartnerListResult> = {
    success: true,
    data: { items: page.map((row) => toPartnerSummary(type, row)) },
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

partnersRouter.get('/:type/:id', requirePermission('partners.read'), async (req: Request, res: Response) => {
  const { type, id } = PartnerIdParams.parse(req.params);
  const row = await findPartner(type, id);
  if (!row) throw AppError.notFound('Partner');
  const body: ApiResponse<PartnerRecord> = { success: true, data: toPartnerRecord(type, row) };
  res.status(200).json(body);
});

partnersRouter.post(
  '/:type',
  requireCsrf,
  requirePermission('partners.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { type } = PartnerTypeParams.parse(req.params);
    const input = PartnerWriteSchema.parse(req.body);
    if (type === 'sub_agent' && !input.agentId) {
      throw AppError.badRequest('agentId is required for sub_agent');
    }
    if (type === 'sub_agent' && input.agentId) {
      const parent = await prisma.agent.findUnique({ where: { id: input.agentId }, select: { id: true } });
      if (!parent) throw AppError.notFound('Agent');
    }
    try {
      const created = await prisma.$transaction(async (tx) => {
        const data = partnerWriteData(type, input);
        const row =
          type === 'agent'
            ? await tx.agent.create({ data: data as Prisma.AgentUncheckedCreateInput })
            : type === 'sub_agent'
              ? await tx.subAgent.create({ data: data as Prisma.SubAgentUncheckedCreateInput })
              : type === 'agencier'
                ? await tx.agencier.create({ data: data as Prisma.AgencierUncheckedCreateInput })
                : await tx.companier.create({ data: data as Prisma.CompanierUncheckedCreateInput });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.RECRUITMENT_PARTNER_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: `partner.${type}`,
            targetId: row.id,
            metadata: { type, status: row.status },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<PartnerRecord> = { success: true, data: toPartnerRecord(type, created) };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A partner with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);

partnersRouter.patch(
  '/:type/:id',
  requireCsrf,
  requirePermission('partners.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { type, id } = PartnerIdParams.parse(req.params);
    const input = PartnerUpdateSchema.parse(req.body);
    if (type === 'sub_agent' && input.agentId) {
      const parent = await prisma.agent.findUnique({ where: { id: input.agentId }, select: { id: true } });
      if (!parent) throw AppError.notFound('Agent');
    }
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing =
          type === 'agent'
            ? await tx.agent.findUnique({ where: { id }, select: { id: true } })
            : type === 'sub_agent'
              ? await tx.subAgent.findUnique({ where: { id }, select: { id: true } })
              : type === 'agencier'
                ? await tx.agencier.findUnique({ where: { id }, select: { id: true } })
                : await tx.companier.findUnique({ where: { id }, select: { id: true } });
        if (!existing) throw AppError.notFound('Partner');
        const data = partnerWriteData(type, input);
        const row =
          type === 'agent'
            ? await tx.agent.update({ where: { id }, data: data as Prisma.AgentUncheckedUpdateInput })
            : type === 'sub_agent'
              ? await tx.subAgent.update({ where: { id }, data: data as Prisma.SubAgentUncheckedUpdateInput })
              : type === 'agencier'
                ? await tx.agencier.update({ where: { id }, data: data as Prisma.AgencierUncheckedUpdateInput })
                : await tx.companier.update({
                    where: { id },
                    data: data as Prisma.CompanierUncheckedUpdateInput,
                  });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.RECRUITMENT_PARTNER_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: `partner.${type}`,
            targetId: row.id,
            metadata: { type, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      const body: ApiResponse<PartnerRecord> = { success: true, data: toPartnerRecord(type, updated) };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A partner with the same legacy identifier already exists');
      }
      throw error;
    }
  }
);
