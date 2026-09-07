import type { Request } from 'express';
import type { Prisma, PrismaClient } from '@prisma/client';
import { prisma } from '../lib/prisma.js';

export const AUDIT_EVENTS = {
  LOGIN_SUCCESS: 'auth.login.success',
  LOGIN_FAILURE: 'auth.login.failure',
  LOGOUT: 'auth.logout',
  PASSWORD_CHANGED: 'auth.password.changed',
  ROLE_ASSIGNED: 'iam.role.assigned',
  ROLE_REMOVED: 'iam.role.removed',
  PERMISSION_GRANTED: 'iam.permission.granted',
  PERMISSION_REVOKED: 'iam.permission.revoked',
  ACCOUNT_STATUS_CHANGED: 'iam.account.status_changed',
  ACCOUNT_CREATED: 'iam.account.created',
  CANDIDATE_CREATED: 'candidate.created',
  CANDIDATE_UPDATED: 'candidate.updated',
  CANDIDATE_STATUS_CHANGED: 'candidate.status.changed',
  CANDIDATE_EDUCATION_CREATED: 'candidate.education.created',
  CANDIDATE_EDUCATION_UPDATED: 'candidate.education.updated',
  CANDIDATE_EXPERIENCE_CREATED: 'candidate.experience.created',
  CANDIDATE_EXPERIENCE_UPDATED: 'candidate.experience.updated',
  CANDIDATE_SKILL_ADDED: 'candidate.skill.added',
  CANDIDATE_SKILL_UPDATED: 'candidate.skill.updated',
  CANDIDATE_SKILL_TAG_ADDED: 'candidate.skill_tag.added',
  CANDIDATE_SKILL_TAG_UPDATED: 'candidate.skill_tag.updated',
  CANDIDATE_LANGUAGE_ADDED: 'candidate.language.added',
  CANDIDATE_LANGUAGE_UPDATED: 'candidate.language.updated',
  CANDIDATE_TRAINING_CREATED: 'candidate.training.created',
  CANDIDATE_TRAINING_UPDATED: 'candidate.training.updated',
  USER_PARTNER_BOUND: 'user.partner.bound',
  USER_PARTNER_UNBOUND: 'user.partner.unbound',
  RECRUITMENT_PARTNER_CREATED: 'recruitment.partner.created',
  RECRUITMENT_PARTNER_UPDATED: 'recruitment.partner.updated',
  EMPLOYER_CANDIDATE_CREATED: 'employer_candidate.created',
  EMPLOYER_CANDIDATE_UPDATED: 'employer_candidate.updated',
  EMPLOYER_CANDIDATE_STATUS_CHANGED: 'employer_candidate.status.changed',
  TRAINING_TEACHER_CREATED: 'training.teacher.created',
  TRAINING_TEACHER_UPDATED: 'training.teacher.updated',
  TRAINING_TEACHER_BOUND: 'training.teacher.bound',
  TRAINING_TEACHER_UNBOUND: 'training.teacher.unbound',
  TRAINING_CLASS_GROUP_CREATED: 'training.class_group.created',
  TRAINING_CLASS_GROUP_UPDATED: 'training.class_group.updated',
  TRAINING_CLASS_GROUP_STATUS_CHANGED: 'training.class_group.status.changed',
  TRAINING_SCHEDULE_CREATED: 'training.schedule.created',
  TRAINING_SCHEDULE_UPDATED: 'training.schedule.updated',
  TRAINING_SCHEDULE_STATUS_CHANGED: 'training.schedule.status.changed',
  TRAINING_EXAM_CREATED: 'training.exam.created',
  TRAINING_EXAM_UPDATED: 'training.exam.updated',
  TRAINING_EXAM_STATUS_CHANGED: 'training.exam.status.changed',
  TRAINING_EXAM_CLASS_GROUPS_CHANGED: 'training.exam.class_groups.changed',
  TRAINING_EXAM_PUBLISHED: 'training.exam.published',
  TRAINING_EXAM_RESULT_CREATED: 'training.exam_result.created',
  TRAINING_EXAM_RESULT_UPDATED: 'training.exam_result.updated',
  TRAINING_EXAM_RESULT_OUTCOME_CHANGED: 'training.exam_result.outcome.changed',
  TRAINING_MANPOWER_CREATED: 'training.manpower.created',
  TRAINING_MANPOWER_UPDATED: 'training.manpower.updated',
  TRAINING_MANPOWER_STATUS_CHANGED: 'training.manpower.status.changed',
} as const;

interface AuditInput {
  eventType: string;
  actorUserId?: string | undefined;
  targetType?: string | undefined;
  targetId?: string | undefined;
  metadata?: Record<string, string | number | boolean | null>;
  request?: Request | undefined;
}

type AuditClient = Pick<PrismaClient, 'auditEvent'> | Prisma.TransactionClient;

const FORBIDDEN_METADATA_KEYS = /password|token|secret|authorization|cookie|nid|passport/i;

function sanitizeMetadata(
  metadata?: Record<string, string | number | boolean | null>
): Prisma.InputJsonValue | undefined {
  if (!metadata) return undefined;
  return Object.fromEntries(
    Object.entries(metadata).filter(([key]) => !FORBIDDEN_METADATA_KEYS.test(key))
  );
}

export async function writeAuditEvent(
  input: AuditInput,
  client: AuditClient = prisma
): Promise<void> {
  const metadata = sanitizeMetadata(input.metadata);
  await client.auditEvent.create({
    data: {
      eventType: input.eventType,
      actorUserId: input.actorUserId ?? null,
      targetType: input.targetType ?? null,
      targetId: input.targetId ?? null,
      ipAddress: input.request?.ip ?? null,
      userAgent: input.request?.headers['user-agent']?.slice(0, 500) ?? null,
      requestId: (input.request?.headers['x-request-id'] as string | undefined) ?? null,
      ...(metadata ? { metadata } : {}),
    },
  });
}
