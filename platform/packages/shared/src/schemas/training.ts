import { z } from 'zod';

const optionalText = (max: number) => z.string().trim().max(max).optional();

const optionalDate = z
  .string()
  .trim()
  .regex(/^\d{4}-\d{2}-\d{2}$/, 'Date must be YYYY-MM-DD')
  .optional()
  .or(z.literal('').transform(() => undefined));

const optionalTime = z
  .string()
  .trim()
  .regex(/^\d{2}:\d{2}(:\d{2})?$/, 'Time must be HH:mm or HH:mm:ss')
  .optional()
  .or(z.literal('').transform(() => undefined));

const optionalLegacyId = z
  .string()
  .trim()
  .regex(/^\d+$/)
  .optional();

export const TrainingStatusSchema = z.enum(['A', 'I']);

export const ExamResultOutcomeSchema = z.enum(['PASS', 'FAIL']);

export const TrainingListQuerySchema = z.object({
  q: z.string().trim().max(200).optional(),
  status: z.string().trim().max(5).optional(),
  cursor: z.string().uuid().optional(),
  limit: z.coerce.number().int().min(1).max(100).default(20),
});

export const TrainingIdParams = z.object({
  id: z.string().uuid(),
});

export const TeacherWriteSchema = z.object({
  sourceLegacyId: optionalLegacyId,
  code: optionalText(100),
  name: optionalText(255),
  email: z.string().trim().email().max(255).optional(),
  secondaryEmail: z.string().trim().email().max(255).optional(),
  mobile: optionalText(255),
  secondaryMobile: optionalText(255),
  emergencyMobile: optionalText(255),
  nid: optionalText(255),
  passportNo: optionalText(255),
  dob: optionalDate,
  gender: optionalText(20),
  nationality: optionalText(20),
  fatherName: optionalText(50),
  motherName: optionalText(50),
  bloodGroup: optionalText(20),
  status: TrainingStatusSchema.optional(),
});

export const TeacherUpdateSchema = TeacherWriteSchema.partial();

export const ClassGroupWriteSchema = z.object({
  sourceLegacyId: optionalLegacyId,
  name: optionalText(250),
  description: optionalText(250),
  code: optionalText(255),
  status: TrainingStatusSchema.optional(),
  feeAmount: z.string().regex(/^\d+(\.\d{1,6})?$/).optional(),
});

export const ClassGroupUpdateSchema = ClassGroupWriteSchema.partial();

export const ClassScheduleWriteSchema = z.object({
  sourceLegacyId: optionalLegacyId,
  teacherId: z.string().uuid(),
  classGroupId: z.string().uuid(),
  subject: optionalText(255),
  startTime: optionalTime,
  endTime: optionalTime,
  weekDay: optionalText(255),
  status: z.enum(['A', 'I']).optional(),
});

export const ClassScheduleUpdateSchema = ClassScheduleWriteSchema.partial().extend({
  teacherId: z.string().uuid().optional(),
  classGroupId: z.string().uuid().optional(),
});

export const ExamWriteSchema = z.object({
  sourceLegacyId: optionalLegacyId,
  name: optionalText(2000),
  examDate: optionalDate,
  examTime: optionalTime,
  examLink: optionalText(2000),
  remarks: optionalText(20000),
  status: TrainingStatusSchema.optional(),
  classGroupIds: z.array(z.string().uuid()).max(100).optional(),
});

export const ExamUpdateSchema = ExamWriteSchema.partial();

export const ExamClassGroupsSchema = z.object({
  classGroupIds: z.array(z.string().uuid()).max(100),
});

const optionalMark = z.number().int().min(0).max(100).nullable().optional();

export const ExamResultWriteSchema = z.object({
  examId: z.string().uuid(),
  candidateId: z.string().uuid(),
  classGroupId: z.string().uuid().nullable().optional(),
  abroadEx: optionalMark,
  localEx: optionalMark,
  bl: optionalMark,
  skill: optionalMark,
  english: optionalMark,
  result: ExamResultOutcomeSchema.nullable().optional(),
  remarks: optionalText(255),
  status: TrainingStatusSchema.optional(),
});

export const ExamResultUpdateSchema = z.object({
  classGroupId: z.string().uuid().nullable().optional(),
  abroadEx: optionalMark,
  localEx: optionalMark,
  bl: optionalMark,
  skill: optionalMark,
  english: optionalMark,
  result: ExamResultOutcomeSchema.nullable().optional(),
  remarks: optionalText(255),
  status: TrainingStatusSchema.optional(),
});

export const ExamResultListQuerySchema = TrainingListQuerySchema.extend({
  examId: z.string().uuid().optional(),
  candidateId: z.string().uuid().optional(),
  result: ExamResultOutcomeSchema.optional(),
});

export const ManpowerTrainingWriteSchema = z.object({
  sourceLegacyId: optionalLegacyId,
  candidateId: z.string().uuid(),
  certificateIssueDate: optionalDate,
  certificateExpireDate: optionalDate,
  certificateFileRef: optionalText(250),
  manpowerFileRef: optionalText(250),
  fingerPrintFileRef: optionalText(250),
  trainingStartDate: optionalDate,
  trainingEndDate: optionalDate,
  status: TrainingStatusSchema.optional(),
});

export const ManpowerTrainingUpdateSchema = ManpowerTrainingWriteSchema.partial().extend({
  candidateId: z.string().uuid().optional(),
});

export const ManpowerTrainingListQuerySchema = TrainingListQuerySchema.extend({
  candidateId: z.string().uuid().optional(),
});
