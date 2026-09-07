import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import type { ZodTypeAny } from 'zod';
import {
  ArcUpdateSchema,
  ArcWriteSchema,
  FlightUpdateSchema,
  FlightWriteSchema,
  LabourContractUpdateSchema,
  LabourContractWriteSchema,
  MedicalUpdateSchema,
  MedicalWriteSchema,
  OverseasIdParams,
  OverseasListQuerySchema,
  PoliceClearanceUpdateSchema,
  PoliceClearanceWriteSchema,
  VisaUpdateSchema,
  VisaWriteSchema,
} from '@manpower/shared';
import type { ApiResponse } from '@manpower/shared';
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
import { isUniqueConflict } from '../training/util.js';
import { LATEST_ORDER } from '../overseas/latest.js';
import { deriveLiveStatus } from '../overseas/live-status.js';
import {
  toArcRecord,
  toFlightRecord,
  toLabourContractRecord,
  toMedicalRecord,
  toPoliceClearanceRecord,
  toVisaRecord,
} from '../overseas/serialize.js';
import {
  arcWriteData,
  flightWriteData,
  labourWriteData,
  medicalWriteData,
  policeWriteData,
  visaWriteData,
} from '../overseas/write-data.js';

const candidateSelect = {
  id: true,
  agentId: true,
  subAgentId: true,
  agencierId: true,
  companierId: true,
} as const;

type DocDelegate = {
  findMany: (args: unknown) => Promise<Array<Record<string, unknown> & { id: string; candidateId: string | null }>>;
  findUnique: (args: unknown) => Promise<
    | (Record<string, unknown> & {
        id: string;
        candidateId: string | null;
        candidate: {
          id: string;
          agentId: bigint | null;
          subAgentId: bigint | null;
          agencierId: bigint | null;
          companierId: bigint | null;
        } | null;
      })
    | null
  >;
  count: (args: unknown) => Promise<number>;
  create: (args: unknown) => Promise<Record<string, unknown> & { id: string; candidateId: string | null }>;
  update: (args: unknown) => Promise<Record<string, unknown> & { id: string; candidateId: string | null }>;
};

interface DocumentConfig {
  path: string;
  notFoundName: string;
  readPermission: string;
  managePermission: string;
  createdEvent: string;
  updatedEvent: string;
  writeSchema: ZodTypeAny;
  updateSchema: ZodTypeAny;
  writeData: (input: Record<string, unknown>) => Record<string, unknown>;
  serialize: (row: Record<string, unknown>) => unknown;
  hasStatus: boolean;
  delegate: (client: typeof prisma | Prisma.TransactionClient) => DocDelegate;
}

async function loadScopedCandidate(candidateId: string, actor: ReturnType<typeof actorFromAuth>) {
  const candidate = await prisma.candidate.findUnique({
    where: { id: candidateId },
    select: candidateSelect,
  });
  if (!candidate) throw AppError.notFound('Candidate');
  assertCandidateAccess(actor, candidate);
  return candidate;
}

function assertRowAccess(
  actor: ReturnType<typeof actorFromAuth>,
  row: {
    candidateId: string | null;
    candidate: {
      id: string;
      agentId: bigint | null;
      subAgentId: bigint | null;
      agencierId: bigint | null;
      companierId: bigint | null;
    } | null;
  },
  notFoundName: string
) {
  if (!row.candidateId || !row.candidate) {
    const access = resolveCandidateAccess(actor);
    if (access.kind !== 'all') throw AppError.notFound(notFoundName);
    return;
  }
  assertCandidateAccess(actor, row.candidate);
}

async function maybeAuditStatusChange(
  tx: Prisma.TransactionClient,
  req: Request,
  candidateId: string | null,
  previousStep: number | null
): Promise<void> {
  if (!candidateId) return;
  const next = await deriveLiveStatus(tx, candidateId);
  if (previousStep === next.stepNo) return;
  await writeAuditEvent(
    {
      eventType: AUDIT_EVENTS.OVERSEAS_STATUS_CHANGED,
      actorUserId: req.auth?.user.id,
      targetType: 'candidate',
      targetId: candidateId,
      metadata: { fromStep: previousStep ?? '', toStep: next.stepNo, name: next.name },
      request: req,
    },
    tx
  );
}

function mountDocument(router: ExpressRouter, config: DocumentConfig): void {
  router.get(
    config.path,
    requirePermission(config.readPermission),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const query = OverseasListQuerySchema.parse(req.query);
      const access = resolveCandidateAccess(actorFromAuth(req.auth.user));
      const filters: Record<string, unknown> = {
        ...(query.candidateId ? { candidateId: query.candidateId } : {}),
        ...(config.hasStatus && (query.activeOnly || query.status)
          ? { status: query.activeOnly ? 'A' : query.status }
          : {}),
      };
      const candidateFilter = applyCandidateAccess({}, access);
      if (candidateFilter === null) {
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
      if (Object.keys(candidateFilter).length > 0) {
        filters.candidate = candidateFilter;
      }
      const where = query.cursor ? { AND: [filters, { id: { lt: query.cursor } }] } : filters;
      const delegate = config.delegate(prisma);
      const [items, total] = await Promise.all([
        delegate.findMany({
          where,
          orderBy: LATEST_ORDER,
          take: query.limit + 1,
        }),
        delegate.count({ where: filters }),
      ]);
      const hasMore = items.length > query.limit;
      const page = hasMore ? items.slice(0, query.limit) : items;
      const body: ApiResponse<{ items: unknown[] }> = {
        success: true,
        data: { items: page.map((row) => config.serialize(row)) },
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

  router.get(
    `${config.path}/:id`,
    requirePermission(config.readPermission),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { id } = OverseasIdParams.parse(req.params);
      const row = await config.delegate(prisma).findUnique({
        where: { id },
        include: { candidate: { select: candidateSelect } },
      });
      if (!row) throw AppError.notFound(config.notFoundName);
      assertRowAccess(actorFromAuth(req.auth.user), row, config.notFoundName);
      res.status(200).json({ success: true, data: config.serialize(row) });
    }
  );

  router.post(
    config.path,
    requireCsrf,
    requirePermission(config.managePermission),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const input = config.writeSchema.parse(req.body) as Record<string, unknown>;
      const actor = actorFromAuth(req.auth.user);
      await loadScopedCandidate(input.candidateId as string, actor);
      try {
        const created = await prisma.$transaction(async (tx) => {
          const previous = await deriveLiveStatus(tx, input.candidateId as string);
          const row = await config.delegate(tx).create({ data: config.writeData(input) });
          await writeAuditEvent(
            {
              eventType: config.createdEvent,
              actorUserId: req.auth?.user.id,
              targetType: config.notFoundName,
              targetId: row.id,
              metadata: { candidateId: row.candidateId, fileId: 'omitted' },
              request: req,
            },
            tx
          );
          await maybeAuditStatusChange(tx, req, row.candidateId, previous.stepNo);
          return row;
        });
        res.status(201).json({ success: true, data: config.serialize(created) });
      } catch (error) {
        if (isUniqueConflict(error)) {
          throw AppError.conflict(`A ${config.notFoundName} with the same legacy identifier already exists`);
        }
        throw error;
      }
    }
  );

  router.patch(
    `${config.path}/:id`,
    requireCsrf,
    requirePermission(config.managePermission),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { id } = OverseasIdParams.parse(req.params);
      const input = config.updateSchema.parse(req.body) as Record<string, unknown>;
      const actor = actorFromAuth(req.auth.user);
      try {
        const updated = await prisma.$transaction(async (tx) => {
          const existing = await config.delegate(tx).findUnique({
            where: { id },
            include: { candidate: { select: candidateSelect } },
          });
          if (!existing) throw AppError.notFound(config.notFoundName);
          assertRowAccess(actor, existing, config.notFoundName);
          if (input.candidateId && input.candidateId !== existing.candidateId) {
            await loadScopedCandidate(input.candidateId as string, actor);
          }
          const statusCandidateId = (input.candidateId as string | undefined) ?? existing.candidateId;
          const previous = statusCandidateId ? await deriveLiveStatus(tx, statusCandidateId) : null;
          const row = await config.delegate(tx).update({
            where: { id },
            data: config.writeData(input),
          });
          await writeAuditEvent(
            {
              eventType: config.updatedEvent,
              actorUserId: req.auth?.user.id,
              targetType: config.notFoundName,
              targetId: row.id,
              metadata: { fields: Object.keys(input).join(','), candidateId: row.candidateId },
              request: req,
            },
            tx
          );
          await maybeAuditStatusChange(tx, req, row.candidateId, previous?.stepNo ?? null);
          return row;
        });
        res.status(200).json({ success: true, data: config.serialize(updated) });
      } catch (error) {
        if (isUniqueConflict(error)) {
          throw AppError.conflict(`A ${config.notFoundName} with the same legacy identifier already exists`);
        }
        throw error;
      }
    }
  );
}

export function createOverseasDocumentsRouter(): ExpressRouter {
  const router: ExpressRouter = Router();
  router.use(requireAuth);

  const docs: DocumentConfig[] = [
    {
      path: '/medicals',
      notFoundName: 'Medical',
      readPermission: 'overseas.medical.read',
      managePermission: 'overseas.medical.manage',
      createdEvent: AUDIT_EVENTS.OVERSEAS_MEDICAL_CREATED,
      updatedEvent: AUDIT_EVENTS.OVERSEAS_MEDICAL_UPDATED,
      writeSchema: MedicalWriteSchema,
      updateSchema: MedicalUpdateSchema,
      writeData: medicalWriteData,
      serialize: (row) => toMedicalRecord(row as Parameters<typeof toMedicalRecord>[0]),
      hasStatus: true,
      delegate: (client) => client.candidateMedical as unknown as DocDelegate,
    },
    {
      path: '/police-clearances',
      notFoundName: 'Police Clearance',
      readPermission: 'overseas.police_clearance.read',
      managePermission: 'overseas.police_clearance.manage',
      createdEvent: AUDIT_EVENTS.OVERSEAS_POLICE_CLEARANCE_CREATED,
      updatedEvent: AUDIT_EVENTS.OVERSEAS_POLICE_CLEARANCE_UPDATED,
      writeSchema: PoliceClearanceWriteSchema,
      updateSchema: PoliceClearanceUpdateSchema,
      writeData: policeWriteData,
      serialize: (row) => toPoliceClearanceRecord(row as Parameters<typeof toPoliceClearanceRecord>[0]),
      hasStatus: true,
      delegate: (client) => client.policeClearance as unknown as DocDelegate,
    },
    {
      path: '/arcs',
      notFoundName: 'ARC',
      readPermission: 'overseas.arc.read',
      managePermission: 'overseas.arc.manage',
      createdEvent: AUDIT_EVENTS.OVERSEAS_ARC_CREATED,
      updatedEvent: AUDIT_EVENTS.OVERSEAS_ARC_UPDATED,
      writeSchema: ArcWriteSchema,
      updateSchema: ArcUpdateSchema,
      writeData: arcWriteData,
      serialize: (row) => toArcRecord(row as Parameters<typeof toArcRecord>[0]),
      hasStatus: true,
      delegate: (client) => client.candidateArc as unknown as DocDelegate,
    },
    {
      path: '/labour-contracts',
      notFoundName: 'Labour Contract',
      readPermission: 'overseas.labour_contract.read',
      managePermission: 'overseas.labour_contract.manage',
      createdEvent: AUDIT_EVENTS.OVERSEAS_LABOUR_CONTRACT_CREATED,
      updatedEvent: AUDIT_EVENTS.OVERSEAS_LABOUR_CONTRACT_UPDATED,
      writeSchema: LabourContractWriteSchema,
      updateSchema: LabourContractUpdateSchema,
      writeData: labourWriteData,
      serialize: (row) => toLabourContractRecord(row as Parameters<typeof toLabourContractRecord>[0]),
      hasStatus: true,
      delegate: (client) => client.labourContract as unknown as DocDelegate,
    },
    {
      path: '/visas',
      notFoundName: 'Visa',
      readPermission: 'overseas.visa.read',
      managePermission: 'overseas.visa.manage',
      createdEvent: AUDIT_EVENTS.OVERSEAS_VISA_CREATED,
      updatedEvent: AUDIT_EVENTS.OVERSEAS_VISA_UPDATED,
      writeSchema: VisaWriteSchema,
      updateSchema: VisaUpdateSchema,
      writeData: visaWriteData,
      serialize: (row) => toVisaRecord(row as Parameters<typeof toVisaRecord>[0]),
      hasStatus: true,
      delegate: (client) => client.visaImmigration as unknown as DocDelegate,
    },
    {
      path: '/flights',
      notFoundName: 'Flight',
      readPermission: 'overseas.flight.read',
      managePermission: 'overseas.flight.manage',
      createdEvent: AUDIT_EVENTS.OVERSEAS_FLIGHT_CREATED,
      updatedEvent: AUDIT_EVENTS.OVERSEAS_FLIGHT_UPDATED,
      writeSchema: FlightWriteSchema,
      updateSchema: FlightUpdateSchema,
      writeData: flightWriteData,
      serialize: (row) => toFlightRecord(row as Parameters<typeof toFlightRecord>[0]),
      hasStatus: false,
      delegate: (client) => client.flightSchedule as unknown as DocDelegate,
    },
  ];

  for (const config of docs) {
    mountDocument(router, config);
  }

  return router;
}
