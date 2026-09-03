import type { Request, Response, Router } from 'express';
import { Prisma } from '@prisma/client';
import {
  EducationUpdateSchema,
  EducationWriteSchema,
  ExperienceUpdateSchema,
  ExperienceWriteSchema,
  LanguageUpdateSchema,
  LanguageWriteSchema,
  ProfileChildParams,
  ProfileCandidateParams,
  ProfileListQuerySchema,
  SkillTagUpdateSchema,
  SkillTagWriteSchema,
  SkillUpdateSchema,
  SkillWriteSchema,
  TrainingUpdateSchema,
  TrainingWriteSchema,
} from '@manpower/shared';
import type { ApiResponse, ProfileListResult } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { actorFromAuth } from '../auth/candidate-access.js';
import { assertChildOwnership, requireAccessibleCandidate } from '../profile/access.js';
import {
  toDate,
  toEducationRecord,
  toExperienceRecord,
  toLanguageRecord,
  toSkillRecord,
  toSkillTagRecord,
  toTrainingRecord,
} from '../profile/serialize.js';

function requireActor(req: Request) {
  if (!req.auth) throw AppError.unauthorized();
  return actorFromAuth(req.auth.user);
}

function listWhere(
  candidateId: string,
  status: 'A' | 'I' | undefined,
  cursor: string | undefined
) {
  const filters = status ? { candidateId, status } : { candidateId };
  return {
    filters,
    where: cursor ? { AND: [filters, { id: { lt: cursor } }] } : filters,
  };
}

function paginate<T extends { id: string }>(
  items: T[],
  limit: number,
  total: number
): { items: T[]; nextCursor: string | null; total: number } {
  const hasMore = items.length > limit;
  const page = hasMore ? items.slice(0, limit) : items;
  return { items: page, nextCursor: hasMore ? page[page.length - 1]?.id ?? null : null, total };
}

function sendList<T>(res: Response, items: T[], nextCursor: string | null, total: number): void {
  const body: ApiResponse<ProfileListResult<T>> = {
    success: true,
    data: { items },
    meta: {
      timestamp: new Date().toISOString(),
      version: 'v1',
      pagination: { nextCursor, prevCursor: null, total },
    },
  };
  res.status(200).json(body);
}

export function registerProfileRoutes(router: Router): void {
  router.get(
    '/:candidateId/educations',
    requirePermission('candidate.education.read'),
    async (req: Request, res: Response) => {
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const query = ProfileListQuerySchema.parse(req.query);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const { filters, where } = listWhere(candidateId, query.status, query.cursor);
      const [rows, total] = await Promise.all([
        prisma.candidateEducation.findMany({
          where,
          orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
          take: query.limit + 1,
        }),
        prisma.candidateEducation.count({ where: filters }),
      ]);
      const page = paginate(rows, query.limit, total);
      sendList(res, page.items.map(toEducationRecord), page.nextCursor, page.total);
    }
  );

  router.post(
    '/:candidateId/educations',
    requireCsrf,
    requirePermission('candidate.education.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const input = EducationWriteSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.candidateEducation.create({
          data: {
            candidateId,
            examName: input.examName,
            ...(input.instituteName !== undefined ? { instituteName: input.instituteName } : {}),
            ...(input.subjectGroupMajor !== undefined ? { subjectGroupMajor: input.subjectGroupMajor } : {}),
            ...(input.educationLabel !== undefined ? { educationLabel: input.educationLabel } : {}),
            ...(input.startDate !== undefined ? { startDate: toDate(input.startDate) } : {}),
            ...(input.endDate !== undefined ? { endDate: toDate(input.endDate) } : {}),
            ...(input.durationYear !== undefined ? { durationYear: input.durationYear } : {}),
            ...(input.resultType !== undefined ? { resultType: input.resultType } : {}),
            ...(input.result !== undefined ? { result: input.result } : {}),
            ...(input.achievements !== undefined ? { achievements: input.achievements } : {}),
            ...(input.certificateFileRef !== undefined ? { certificateFileRef: input.certificateFileRef } : {}),
            ...(input.boardName !== undefined ? { boardName: input.boardName } : {}),
            ...(input.scale !== undefined ? { scale: input.scale } : {}),
            ...(input.passingYear !== undefined ? { passingYear: input.passingYear } : {}),
            status: input.status ?? 'A',
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_EDUCATION_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_education',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(201).json({ success: true, data: toEducationRecord(created) });
    }
  );

  router.patch(
    '/:candidateId/educations/:id',
    requireCsrf,
    requirePermission('candidate.education.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId, id } = ProfileChildParams.parse(req.params);
      const input = EducationUpdateSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.candidateEducation.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('Education');
        assertChildOwnership(existing.candidateId, candidateId);
        const row = await tx.candidateEducation.update({
          where: { id },
          data: {
            ...(input.examName !== undefined ? { examName: input.examName } : {}),
            ...(input.instituteName !== undefined ? { instituteName: input.instituteName } : {}),
            ...(input.subjectGroupMajor !== undefined ? { subjectGroupMajor: input.subjectGroupMajor } : {}),
            ...(input.educationLabel !== undefined ? { educationLabel: input.educationLabel } : {}),
            ...(input.startDate !== undefined ? { startDate: toDate(input.startDate) } : {}),
            ...(input.endDate !== undefined ? { endDate: toDate(input.endDate) } : {}),
            ...(input.durationYear !== undefined ? { durationYear: input.durationYear } : {}),
            ...(input.resultType !== undefined ? { resultType: input.resultType } : {}),
            ...(input.result !== undefined ? { result: input.result } : {}),
            ...(input.achievements !== undefined ? { achievements: input.achievements } : {}),
            ...(input.certificateFileRef !== undefined ? { certificateFileRef: input.certificateFileRef } : {}),
            ...(input.boardName !== undefined ? { boardName: input.boardName } : {}),
            ...(input.scale !== undefined ? { scale: input.scale } : {}),
            ...(input.passingYear !== undefined ? { passingYear: input.passingYear } : {}),
            ...(input.status !== undefined ? { status: input.status } : {}),
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_EDUCATION_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_education',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(200).json({ success: true, data: toEducationRecord(updated) });
    }
  );

  router.get(
    '/:candidateId/experiences',
    requirePermission('candidate.experience.read'),
    async (req: Request, res: Response) => {
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const query = ProfileListQuerySchema.parse(req.query);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const { filters, where } = listWhere(candidateId, query.status, query.cursor);
      const [rows, total] = await Promise.all([
        prisma.candidateExperience.findMany({
          where,
          orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
          take: query.limit + 1,
        }),
        prisma.candidateExperience.count({ where: filters }),
      ]);
      const page = paginate(rows, query.limit, total);
      sendList(res, page.items.map(toExperienceRecord), page.nextCursor, page.total);
    }
  );

  router.post(
    '/:candidateId/experiences',
    requireCsrf,
    requirePermission('candidate.experience.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const input = ExperienceWriteSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.candidateExperience.create({
          data: {
            candidateId,
            companyName: input.companyName,
            ...(input.companyAddress !== undefined ? { companyAddress: input.companyAddress } : {}),
            ...(input.countryRef !== undefined ? { countryRef: input.countryRef } : {}),
            ...(input.designation !== undefined ? { designation: input.designation } : {}),
            ...(input.department !== undefined ? { department: input.department } : {}),
            ...(input.startDate !== undefined ? { startDate: toDate(input.startDate) } : {}),
            ...(input.endDate !== undefined ? { endDate: toDate(input.endDate) } : {}),
            ...(input.responsibilities !== undefined ? { responsibilities: input.responsibilities } : {}),
            ...(input.expertise !== undefined ? { expertise: input.expertise } : {}),
            ...(input.descriptions !== undefined ? { descriptions: input.descriptions } : {}),
            ...(input.achievements !== undefined ? { achievements: input.achievements } : {}),
            status: input.status ?? 'A',
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_EXPERIENCE_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_experience',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(201).json({ success: true, data: toExperienceRecord(created) });
    }
  );

  router.patch(
    '/:candidateId/experiences/:id',
    requireCsrf,
    requirePermission('candidate.experience.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId, id } = ProfileChildParams.parse(req.params);
      const input = ExperienceUpdateSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.candidateExperience.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('Experience');
        assertChildOwnership(existing.candidateId, candidateId);
        const row = await tx.candidateExperience.update({
          where: { id },
          data: {
            ...(input.companyName !== undefined ? { companyName: input.companyName } : {}),
            ...(input.companyAddress !== undefined ? { companyAddress: input.companyAddress } : {}),
            ...(input.countryRef !== undefined ? { countryRef: input.countryRef } : {}),
            ...(input.designation !== undefined ? { designation: input.designation } : {}),
            ...(input.department !== undefined ? { department: input.department } : {}),
            ...(input.startDate !== undefined ? { startDate: toDate(input.startDate) } : {}),
            ...(input.endDate !== undefined ? { endDate: toDate(input.endDate) } : {}),
            ...(input.responsibilities !== undefined ? { responsibilities: input.responsibilities } : {}),
            ...(input.expertise !== undefined ? { expertise: input.expertise } : {}),
            ...(input.descriptions !== undefined ? { descriptions: input.descriptions } : {}),
            ...(input.achievements !== undefined ? { achievements: input.achievements } : {}),
            ...(input.status !== undefined ? { status: input.status } : {}),
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_EXPERIENCE_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_experience',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(200).json({ success: true, data: toExperienceRecord(updated) });
    }
  );

  router.get(
    '/:candidateId/skills',
    requirePermission('candidate.skills.read'),
    async (req: Request, res: Response) => {
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const query = ProfileListQuerySchema.parse(req.query);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const { filters, where } = listWhere(candidateId, query.status, query.cursor);
      const [rows, total] = await Promise.all([
        prisma.candidateSkill.findMany({
          where,
          orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
          take: query.limit + 1,
        }),
        prisma.candidateSkill.count({ where: filters }),
      ]);
      const page = paginate(rows, query.limit, total);
      sendList(res, page.items.map(toSkillRecord), page.nextCursor, page.total);
    }
  );

  router.post(
    '/:candidateId/skills',
    requireCsrf,
    requirePermission('candidate.skills.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const input = SkillWriteSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.candidateSkill.create({
          data: {
            candidateId,
            title: input.title,
            ...(input.instituteName !== undefined ? { instituteName: input.instituteName } : {}),
            ...(input.details !== undefined ? { details: input.details } : {}),
            ...(input.resultScore !== undefined ? { resultScore: new Prisma.Decimal(input.resultScore) } : {}),
            ...(input.examScore !== undefined ? { examScore: new Prisma.Decimal(input.examScore) } : {}),
            ...(input.certificateFileRef !== undefined ? { certificateFileRef: input.certificateFileRef } : {}),
            status: input.status ?? 'A',
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_SKILL_ADDED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_skill',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(201).json({ success: true, data: toSkillRecord(created) });
    }
  );

  router.patch(
    '/:candidateId/skills/:id',
    requireCsrf,
    requirePermission('candidate.skills.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId, id } = ProfileChildParams.parse(req.params);
      const input = SkillUpdateSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.candidateSkill.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('Skill');
        assertChildOwnership(existing.candidateId, candidateId);
        const row = await tx.candidateSkill.update({
          where: { id },
          data: {
            ...(input.title !== undefined ? { title: input.title } : {}),
            ...(input.instituteName !== undefined ? { instituteName: input.instituteName } : {}),
            ...(input.details !== undefined ? { details: input.details } : {}),
            ...(input.resultScore !== undefined ? { resultScore: new Prisma.Decimal(input.resultScore) } : {}),
            ...(input.examScore !== undefined ? { examScore: new Prisma.Decimal(input.examScore) } : {}),
            ...(input.certificateFileRef !== undefined ? { certificateFileRef: input.certificateFileRef } : {}),
            ...(input.status !== undefined ? { status: input.status } : {}),
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_SKILL_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_skill',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(200).json({ success: true, data: toSkillRecord(updated) });
    }
  );

  router.get(
    '/:candidateId/skill-list',
    requirePermission('candidate.skills.read'),
    async (req: Request, res: Response) => {
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const query = ProfileListQuerySchema.parse(req.query);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const { filters, where } = listWhere(candidateId, query.status, query.cursor);
      const [rows, total] = await Promise.all([
        prisma.candidateSkillTag.findMany({
          where,
          orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
          take: query.limit + 1,
        }),
        prisma.candidateSkillTag.count({ where: filters }),
      ]);
      const page = paginate(rows, query.limit, total);
      sendList(res, page.items.map(toSkillTagRecord), page.nextCursor, page.total);
    }
  );

  router.post(
    '/:candidateId/skill-list',
    requireCsrf,
    requirePermission('candidate.skills.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const input = SkillTagWriteSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const created = await prisma.$transaction(async (tx) => {
        const existing = await tx.candidateSkillTag.findFirst({
          where: { candidateId, skillName: input.skillName, status: 'A' },
          select: { id: true },
        });
        if (existing) {
          throw AppError.conflict('An active skill tag with this name already exists for the candidate');
        }
        const row = await tx.candidateSkillTag.create({
          data: {
            candidateId,
            skillName: input.skillName,
            status: input.status ?? 'A',
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_SKILL_TAG_ADDED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_skill_tag',
            targetId: row.id,
            metadata: { candidateId },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(201).json({ success: true, data: toSkillTagRecord(created) });
    }
  );

  router.patch(
    '/:candidateId/skill-list/:id',
    requireCsrf,
    requirePermission('candidate.skills.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId, id } = ProfileChildParams.parse(req.params);
      const input = SkillTagUpdateSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.candidateSkillTag.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('Skill tag');
        assertChildOwnership(existing.candidateId, candidateId);
        const row = await tx.candidateSkillTag.update({
          where: { id },
          data: {
            ...(input.skillName !== undefined ? { skillName: input.skillName } : {}),
            ...(input.status !== undefined ? { status: input.status } : {}),
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_SKILL_TAG_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_skill_tag',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(200).json({ success: true, data: toSkillTagRecord(updated) });
    }
  );

  router.get(
    '/:candidateId/languages',
    requirePermission('candidate.languages.read'),
    async (req: Request, res: Response) => {
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const query = ProfileListQuerySchema.parse(req.query);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const { filters, where } = listWhere(candidateId, query.status, query.cursor);
      const [rows, total] = await Promise.all([
        prisma.candidateLanguage.findMany({
          where,
          orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
          take: query.limit + 1,
        }),
        prisma.candidateLanguage.count({ where: filters }),
      ]);
      const page = paginate(rows, query.limit, total);
      sendList(res, page.items.map(toLanguageRecord), page.nextCursor, page.total);
    }
  );

  router.post(
    '/:candidateId/languages',
    requireCsrf,
    requirePermission('candidate.languages.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const input = LanguageWriteSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.candidateLanguage.create({
          data: {
            candidateId,
            languageName: input.languageName,
            ...(input.languageStatus !== undefined ? { languageStatus: input.languageStatus } : {}),
            status: input.status ?? 'A',
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_LANGUAGE_ADDED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_language',
            targetId: row.id,
            metadata: { candidateId },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(201).json({ success: true, data: toLanguageRecord(created) });
    }
  );

  router.patch(
    '/:candidateId/languages/:id',
    requireCsrf,
    requirePermission('candidate.languages.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId, id } = ProfileChildParams.parse(req.params);
      const input = LanguageUpdateSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.candidateLanguage.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('Language');
        assertChildOwnership(existing.candidateId, candidateId);
        const row = await tx.candidateLanguage.update({
          where: { id },
          data: {
            ...(input.languageName !== undefined ? { languageName: input.languageName } : {}),
            ...(input.languageStatus !== undefined ? { languageStatus: input.languageStatus } : {}),
            ...(input.status !== undefined ? { status: input.status } : {}),
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_LANGUAGE_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_language',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(200).json({ success: true, data: toLanguageRecord(updated) });
    }
  );

  router.get(
    '/:candidateId/trainings',
    requirePermission('candidate.training.read'),
    async (req: Request, res: Response) => {
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const query = ProfileListQuerySchema.parse(req.query);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const { filters, where } = listWhere(candidateId, query.status, query.cursor);
      const [rows, total] = await Promise.all([
        prisma.candidateTraining.findMany({
          where,
          orderBy: [{ createdAt: 'desc' }, { id: 'desc' }],
          take: query.limit + 1,
        }),
        prisma.candidateTraining.count({ where: filters }),
      ]);
      const page = paginate(rows, query.limit, total);
      sendList(res, page.items.map(toTrainingRecord), page.nextCursor, page.total);
    }
  );

  router.post(
    '/:candidateId/trainings',
    requireCsrf,
    requirePermission('candidate.training.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId } = ProfileCandidateParams.parse(req.params);
      const input = TrainingWriteSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const created = await prisma.$transaction(async (tx) => {
        const row = await tx.candidateTraining.create({
          data: {
            candidateId,
            title: input.title,
            ...(input.instituteName !== undefined ? { instituteName: input.instituteName } : {}),
            ...(input.topics !== undefined ? { topics: input.topics } : {}),
            ...(input.startDate !== undefined ? { startDate: toDate(input.startDate) } : {}),
            ...(input.endDate !== undefined ? { endDate: toDate(input.endDate) } : {}),
            ...(input.duration !== undefined ? { duration: input.duration } : {}),
            ...(input.durationType !== undefined ? { durationType: input.durationType } : {}),
            ...(input.countryRef !== undefined ? { countryRef: input.countryRef } : {}),
            ...(input.descriptions !== undefined ? { descriptions: input.descriptions } : {}),
            ...(input.achievements !== undefined ? { achievements: input.achievements } : {}),
            ...(input.certificateFileRef !== undefined ? { certificateFileRef: input.certificateFileRef } : {}),
            ...(input.address !== undefined ? { address: input.address } : {}),
            status: input.status ?? 'A',
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_TRAINING_CREATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_training',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(201).json({ success: true, data: toTrainingRecord(created) });
    }
  );

  router.patch(
    '/:candidateId/trainings/:id',
    requireCsrf,
    requirePermission('candidate.training.manage'),
    async (req: Request, res: Response) => {
      if (!req.auth) throw AppError.unauthorized();
      const { candidateId, id } = ProfileChildParams.parse(req.params);
      const input = TrainingUpdateSchema.parse(req.body);
      await requireAccessibleCandidate(candidateId, requireActor(req));
      const updated = await prisma.$transaction(async (tx) => {
        const existing = await tx.candidateTraining.findUnique({ where: { id } });
        if (!existing) throw AppError.notFound('Training');
        assertChildOwnership(existing.candidateId, candidateId);
        const row = await tx.candidateTraining.update({
          where: { id },
          data: {
            ...(input.title !== undefined ? { title: input.title } : {}),
            ...(input.instituteName !== undefined ? { instituteName: input.instituteName } : {}),
            ...(input.topics !== undefined ? { topics: input.topics } : {}),
            ...(input.startDate !== undefined ? { startDate: toDate(input.startDate) } : {}),
            ...(input.endDate !== undefined ? { endDate: toDate(input.endDate) } : {}),
            ...(input.duration !== undefined ? { duration: input.duration } : {}),
            ...(input.durationType !== undefined ? { durationType: input.durationType } : {}),
            ...(input.countryRef !== undefined ? { countryRef: input.countryRef } : {}),
            ...(input.descriptions !== undefined ? { descriptions: input.descriptions } : {}),
            ...(input.achievements !== undefined ? { achievements: input.achievements } : {}),
            ...(input.certificateFileRef !== undefined ? { certificateFileRef: input.certificateFileRef } : {}),
            ...(input.address !== undefined ? { address: input.address } : {}),
            ...(input.status !== undefined ? { status: input.status } : {}),
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.CANDIDATE_TRAINING_UPDATED,
            actorUserId: req.auth?.user.id,
            targetType: 'candidate_training',
            targetId: row.id,
            metadata: { candidateId, fields: Object.keys(input).join(',') },
            request: req,
          },
          tx
        );
        return row;
      });
      res.status(200).json({ success: true, data: toTrainingRecord(updated) });
    }
  );
}
