import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import type { Prisma } from '@prisma/client';
import {
  CandidateIdParams,
  CandidateListQuerySchema,
  CandidateStatusChangeSchema,
  CandidateUpdateSchema,
  CandidateWriteSchema,
} from '@manpower/shared';
import type { ApiResponse, CandidateListResult, CandidateRecord } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import {
  actorFromAuth,
  applyCandidateAccess,
  assertCanCreateCandidate,
  assertCandidateAccess,
  resolveCandidateAccess,
} from '../auth/candidate-access.js';
import { generateCandidateCode } from '../candidates/code.js';
import { toBigInt, toCandidateRecord, toCandidateSummary, toDate } from '../candidates/serialize.js';
import { registerProfileRoutes } from './profile.js';

export const candidatesRouter: ExpressRouter = Router();

candidatesRouter.use(requireAuth);

function isUniqueConflict(error: unknown): boolean {
  return typeof error === 'object' && error !== null && 'code' in error && error.code === 'P2002';
}

function writeData(input: Record<string, unknown>): Record<string, string | number | Date | bigint> {
  return {
    ...(input.code !== undefined ? { code: input.code as string } : {}),
    ...(input.name !== undefined ? { name: input.name as string } : {}),
    ...(input.email !== undefined ? { email: input.email as string } : {}),
    ...(input.mobile !== undefined ? { mobile: input.mobile as string } : {}),
    ...(input.passportNo !== undefined ? { passportNo: input.passportNo as string } : {}),
    ...(input.agentId !== undefined ? { agentId: toBigInt(input.agentId as string) } : {}),
    ...(input.classGroupId !== undefined ? { classGroupId: toBigInt(input.classGroupId as string) } : {}),
    ...(input.secondaryEmail !== undefined ? { secondaryEmail: input.secondaryEmail as string } : {}),
    ...(input.secondaryMobile !== undefined ? { secondaryMobile: input.secondaryMobile as string } : {}),
    ...(input.emergencyMobile !== undefined ? { emergencyMobile: input.emergencyMobile as string } : {}),
    ...(input.bid !== undefined ? { bid: input.bid as string } : {}),
    ...(input.nid !== undefined ? { nid: input.nid as string } : {}),
    ...(input.passportIssueDate !== undefined ? { passportIssueDate: toDate(input.passportIssueDate as string) } : {}),
    ...(input.passportExpireDate !== undefined ? { passportExpireDate: toDate(input.passportExpireDate as string) } : {}),
    ...(input.dob !== undefined ? { dob: toDate(input.dob as string) } : {}),
    ...(input.fatherName !== undefined ? { fatherName: input.fatherName as string } : {}),
    ...(input.motherName !== undefined ? { motherName: input.motherName as string } : {}),
    ...(input.nationality !== undefined ? { nationality: input.nationality as string } : {}),
    ...(input.gender !== undefined ? { gender: input.gender as string } : {}),
    ...(input.bloodGroup !== undefined ? { bloodGroup: input.bloodGroup as string } : {}),
    ...(input.presentAddressHouse !== undefined ? { presentAddressHouse: input.presentAddressHouse as string } : {}),
    ...(input.presentAddressRoad !== undefined ? { presentAddressRoad: input.presentAddressRoad as string } : {}),
    ...(input.presentAddressVillage !== undefined ? { presentAddressVillage: input.presentAddressVillage as string } : {}),
    ...(input.presentAddressPost !== undefined ? { presentAddressPost: input.presentAddressPost as string } : {}),
    ...(input.presentAddressThanaId !== undefined ? { presentAddressThanaId: toBigInt(input.presentAddressThanaId as string) } : {}),
    ...(input.presentAddressDistrictId !== undefined
      ? { presentAddressDistrictId: toBigInt(input.presentAddressDistrictId as string) }
      : {}),
    ...(input.presentAddressDivisionId !== undefined
      ? { presentAddressDivisionId: toBigInt(input.presentAddressDivisionId as string) }
      : {}),
    ...(input.permanentAddressHouse !== undefined ? { permanentAddressHouse: input.permanentAddressHouse as string } : {}),
    ...(input.permanentAddressRoad !== undefined ? { permanentAddressRoad: input.permanentAddressRoad as string } : {}),
    ...(input.permanentAddressVillage !== undefined
      ? { permanentAddressVillage: input.permanentAddressVillage as string }
      : {}),
    ...(input.permanentAddressPost !== undefined ? { permanentAddressPost: input.permanentAddressPost as string } : {}),
    ...(input.permanentAddressThanaId !== undefined
      ? { permanentAddressThanaId: toBigInt(input.permanentAddressThanaId as string) }
      : {}),
    ...(input.permanentAddressDistrictId !== undefined
      ? { permanentAddressDistrictId: toBigInt(input.permanentAddressDistrictId as string) }
      : {}),
    ...(input.permanentAddressDivisionId !== undefined
      ? { permanentAddressDivisionId: toBigInt(input.permanentAddressDivisionId as string) }
      : {}),
    ...(input.basicInfoCareer !== undefined ? { basicInfoCareer: input.basicInfoCareer as string } : {}),
    ...(input.basicInfoSpecial !== undefined ? { basicInfoSpecial: input.basicInfoSpecial as string } : {}),
    ...(input.otherSkills !== undefined ? { otherSkills: input.otherSkills as string } : {}),
    ...(input.remarks !== undefined ? { remarks: input.remarks as string } : {}),
    ...(input.replacementRemarks !== undefined ? { replacementRemarks: input.replacementRemarks as string } : {}),
    ...(input.abroadEx !== undefined ? { abroadEx: input.abroadEx as number } : {}),
    ...(input.localEx !== undefined ? { localEx: input.localEx as number } : {}),
    ...(input.driveLink !== undefined ? { driveLink: input.driveLink as string } : {}),
    ...(input.facebookLink !== undefined ? { facebookLink: input.facebookLink as string } : {}),
    ...(input.youtubeLink !== undefined ? { youtubeLink: input.youtubeLink as string } : {}),
    ...(input.linkedinLink !== undefined ? { linkedinLink: input.linkedinLink as string } : {}),
    ...(input.twitterLink !== undefined ? { twitterLink: input.twitterLink as string } : {}),
    ...(input.instagramLink !== undefined ? { instagramLink: input.instagramLink as string } : {}),
    ...(input.position !== undefined ? { position: input.position as string } : {}),
    ...(input.height !== undefined ? { height: input.height as string } : {}),
    ...(input.weight !== undefined ? { weight: input.weight as string } : {}),
    ...(input.maritalStatus !== undefined ? { maritalStatus: input.maritalStatus as string } : {}),
    ...(input.expertise !== undefined ? { expertise: input.expertise as string } : {}),
    ...(input.skills !== undefined ? { skills: input.skills as string } : {}),
    ...(input.extraCurricular !== undefined ? { extraCurricular: input.extraCurricular as string } : {}),
    ...(input.interest !== undefined ? { interest: input.interest as string } : {}),
    ...(input.attribute !== undefined ? { attribute: input.attribute as string } : {}),
    ...(input.companierId !== undefined ? { companierId: toBigInt(input.companierId as string) } : {}),
    ...(input.agencierId !== undefined ? { agencierId: toBigInt(input.agencierId as string) } : {}),
    ...(input.positionId !== undefined ? { positionId: toBigInt(input.positionId as string) } : {}),
    ...(input.companyStatus !== undefined ? { companyStatus: input.companyStatus as string } : {}),
    ...(input.subAgentId !== undefined ? { subAgentId: toBigInt(input.subAgentId as string) } : {}),
    ...(input.countryId !== undefined ? { countryId: toBigInt(input.countryId as string) } : {}),
    ...(input.replacedByLegacyId !== undefined ? { replacedByLegacyId: toBigInt(input.replacedByLegacyId as string) } : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
    ...(input.bidFileRef !== undefined ? { bidFileRef: input.bidFileRef as string } : {}),
    ...(input.nidFileRef !== undefined ? { nidFileRef: input.nidFileRef as string } : {}),
    ...(input.passportFileRef !== undefined ? { passportFileRef: input.passportFileRef as string } : {}),
    ...(input.fullPhotoFileRef !== undefined ? { fullPhotoFileRef: input.fullPhotoFileRef as string } : {}),
    ...(input.halfPhotoFileRef !== undefined ? { halfPhotoFileRef: input.halfPhotoFileRef as string } : {}),
    ...(input.cvFileRef !== undefined ? { cvFileRef: input.cvFileRef as string } : {}),
    ...(input.skillCertificateFileRef !== undefined
      ? { skillCertificateFileRef: input.skillCertificateFileRef as string }
      : {}),
    ...(input.stampFileRef !== undefined ? { stampFileRef: input.stampFileRef as string } : {}),
  };
}

candidatesRouter.get('/', requirePermission('candidate.read'), async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const query = CandidateListQuerySchema.parse(req.query);
  const filters: Prisma.CandidateWhereInput = {
    ...(query.id ? { id: query.id } : {}),
    ...(query.status ? { status: query.status } : {}),
    ...(query.name ? { name: { contains: query.name, mode: 'insensitive' } } : {}),
    ...(query.passportNo ? { passportNo: { contains: query.passportNo, mode: 'insensitive' } } : {}),
    ...(query.nid ? { nid: { contains: query.nid, mode: 'insensitive' } } : {}),
    ...(query.mobile ? { mobile: { contains: query.mobile, mode: 'insensitive' } } : {}),
    ...(query.email ? { email: { contains: query.email, mode: 'insensitive' } } : {}),
    ...(query.q
      ? {
          OR: [
            { name: { contains: query.q, mode: 'insensitive' } },
            { code: { contains: query.q, mode: 'insensitive' } },
            { email: { contains: query.q, mode: 'insensitive' } },
            { mobile: { contains: query.q, mode: 'insensitive' } },
            { passportNo: { contains: query.q, mode: 'insensitive' } },
            { nid: { contains: query.q, mode: 'insensitive' } },
          ],
        }
      : {}),
  };
  const scoped = applyCandidateAccess(filters, resolveCandidateAccess(actorFromAuth(req.auth.user)));
  if (scoped === null) {
    const empty: ApiResponse<CandidateListResult> = {
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
  const where: Prisma.CandidateWhereInput = query.cursor
    ? { AND: [scoped, { id: { lt: query.cursor } }] }
    : scoped;

  const [items, total] = await prisma.$transaction([
    prisma.candidate.findMany({
      where,
      orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
      take: query.limit + 1,
    }),
    prisma.candidate.count({ where: scoped }),
  ]);

  const hasMore = items.length > query.limit;
  const page = hasMore ? items.slice(0, query.limit) : items;
  const nextCursor = hasMore ? page[page.length - 1]?.id ?? null : null;

  const body: ApiResponse<CandidateListResult> = {
    success: true,
    data: { items: page.map(toCandidateSummary) },
    meta: {
      timestamp: new Date().toISOString(),
      version: 'v1',
      pagination: {
        nextCursor,
        prevCursor: null,
        total,
      },
    },
  };
  res.status(200).json(body);
});

candidatesRouter.get('/:id', requirePermission('candidate.read'), async (req: Request, res: Response) => {
  if (!req.auth) throw AppError.unauthorized();
  const { id } = CandidateIdParams.parse(req.params);
  const candidate = await prisma.candidate.findUnique({ where: { id } });
  if (!candidate) throw AppError.notFound('Candidate');
  assertCandidateAccess(actorFromAuth(req.auth.user), candidate);
  const body: ApiResponse<CandidateRecord> = { success: true, data: toCandidateRecord(candidate) };
  res.status(200).json(body);
});

candidatesRouter.post(
  '/',
  requireCsrf,
  requirePermission('candidate.create'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    assertCanCreateCandidate(actorFromAuth(req.auth.user));
    const input = CandidateWriteSchema.parse(req.body);
    try {
      const created = await prisma.$transaction(async (tx) => {
        const code = input.code ?? (await generateCandidateCode(tx));
        const candidate = await tx.candidate.create({
          data: {
            ...(writeData(input) as Prisma.CandidateUncheckedCreateInput),
            code,
            name: input.name,
            email: input.email,
            mobile: input.mobile,
            passportNo: input.passportNo,
            agentId: toBigInt(input.agentId),
            classGroupId: toBigInt(input.classGroupId),
            status: input.status ?? 'A',
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_CREATED,
            actorUserId,
            targetType: 'candidate',
            targetId: candidate.id,
            metadata: { status: candidate.status, hasCode: true },
            request: req,
          },
          tx
        );
        return candidate;
      });
      const body: ApiResponse<CandidateRecord> = { success: true, data: toCandidateRecord(created) };
      res.status(201).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A candidate with the same code, email, mobile, or passport already exists');
      }
      throw error;
    }
  }
);

candidatesRouter.patch(
  '/:id',
  requireCsrf,
  requirePermission('candidate.update'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const actor = actorFromAuth(req.auth.user);
    const { id } = CandidateIdParams.parse(req.params);
    const input = CandidateUpdateSchema.parse(req.body);
    if (input.status !== undefined) {
      throw AppError.badRequest('Use PATCH /api/v1/candidates/:id/status to change candidate status');
    }
    try {
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.candidate.findUnique({
          where: { id },
          select: { id: true, agentId: true, subAgentId: true, agencierId: true, companierId: true },
        });
        if (!existing) throw AppError.notFound('Candidate');
        assertCandidateAccess(actor, existing);
        const candidate = await tx.candidate.update({
          where: { id },
          data: writeData(input) as Prisma.CandidateUncheckedUpdateInput,
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_UPDATED,
            actorUserId,
            targetType: 'candidate',
            targetId: candidate.id,
            metadata: { fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return candidate;
      });
      const body: ApiResponse<CandidateRecord> = { success: true, data: toCandidateRecord(updated) };
      res.status(200).json(body);
    } catch (error) {
      if (isUniqueConflict(error)) {
        throw AppError.conflict('A candidate with the same code, email, mobile, or passport already exists');
      }
      throw error;
    }
  }
);

candidatesRouter.patch(
  '/:id/status',
  requireCsrf,
  requirePermission('candidate.status.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const actor = actorFromAuth(req.auth.user);
    const { id } = CandidateIdParams.parse(req.params);
    const input = CandidateStatusChangeSchema.parse(req.body);
    const updated = await prisma.$transaction(async (tx) => {
      const existing = await tx.candidate.findUnique({
        where: { id },
        select: { id: true, status: true, agentId: true, subAgentId: true, agencierId: true, companierId: true },
      });
      if (!existing) throw AppError.notFound('Candidate');
      assertCandidateAccess(actor, existing);
      const candidate = await tx.candidate.update({
        where: { id },
        data: {
          status: input.status,
          ...(input.remarks !== undefined ? { remarks: input.remarks } : {}),
        },
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.CANDIDATE_STATUS_CHANGED,
          actorUserId,
          targetType: 'candidate',
          targetId: candidate.id,
          metadata: { from: existing.status, to: input.status },
          request: req,
        },
        tx
      );
      return candidate;
    });
    const body: ApiResponse<CandidateRecord> = { success: true, data: toCandidateRecord(updated) };
    res.status(200).json(body);
  }
);

registerProfileRoutes(candidatesRouter);
