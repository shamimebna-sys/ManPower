import { z } from 'zod';

export const ProfileStatusSchema = z.enum(['A', 'I']);

const optionalText = (max: number) =>
  z
    .string()
    .trim()
    .max(max)
    .optional()
    .transform((value) => (value === '' ? undefined : value));

const optionalDate = z
  .string()
  .trim()
  .regex(/^\d{4}-\d{2}-\d{2}$/, 'Date must be YYYY-MM-DD')
  .optional()
  .or(z.literal('').transform(() => undefined));

const optionalScore = z
  .union([z.string(), z.number()])
  .optional()
  .transform((value) => {
    if (value === undefined || value === '') return undefined;
    const parsed = Number(value);
    if (!Number.isFinite(parsed)) throw new Error('Score must be numeric');
    return parsed.toString();
  });

export const ProfileListQuerySchema = z.object({
  status: ProfileStatusSchema.optional(),
  cursor: z.string().uuid().optional(),
  limit: z.coerce.number().int().min(1).max(100).default(50),
});

export const ProfileCandidateParams = z.object({
  candidateId: z.string().uuid(),
});

export const ProfileChildParams = z.object({
  candidateId: z.string().uuid(),
  id: z.string().uuid(),
});

function withDateOrder<T extends z.ZodRawShape>(schema: z.ZodObject<T>) {
  return schema.superRefine((value, ctx) => {
    const record = value as { startDate?: string; endDate?: string };
    if (record.startDate && record.endDate && record.startDate > record.endDate) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        path: ['endDate'],
        message: 'Start date cannot be after end date',
      });
    }
  });
}

const EducationFields = z.object({
  examName: z.string().trim().min(1).max(250),
  instituteName: optionalText(250),
  subjectGroupMajor: optionalText(250),
  educationLabel: optionalText(250),
  startDate: optionalDate,
  endDate: optionalDate,
  durationYear: z.coerce.number().int().min(0).max(80).optional(),
  resultType: optionalText(250),
  result: optionalText(250),
  achievements: optionalText(250),
  certificateFileRef: optionalText(250),
  boardName: optionalText(200),
  scale: optionalText(200),
  passingYear: optionalText(200),
  status: ProfileStatusSchema.optional(),
});

export const EducationWriteSchema = withDateOrder(EducationFields);
export const EducationUpdateSchema = withDateOrder(
  EducationFields.partial().extend({ examName: optionalText(250) })
);

const ExperienceFields = z.object({
  companyName: z.string().trim().min(1).max(250),
  companyAddress: optionalText(250),
  countryRef: optionalText(250),
  designation: optionalText(250),
  department: optionalText(250),
  startDate: optionalDate,
  endDate: optionalDate,
  responsibilities: optionalText(20000),
  expertise: optionalText(20000),
  descriptions: optionalText(20000),
  achievements: optionalText(250),
  status: ProfileStatusSchema.optional(),
});

export const ExperienceWriteSchema = withDateOrder(ExperienceFields);
export const ExperienceUpdateSchema = withDateOrder(
  ExperienceFields.partial().extend({ companyName: optionalText(250) })
);

export const SkillWriteSchema = z.object({
  title: z.string().trim().min(1).max(250),
  instituteName: optionalText(250),
  details: optionalText(250),
  resultScore: optionalScore,
  examScore: optionalScore,
  certificateFileRef: optionalText(250),
  status: ProfileStatusSchema.optional(),
});

export const SkillUpdateSchema = SkillWriteSchema.partial().extend({
  title: optionalText(250),
});

export const SkillTagWriteSchema = z.object({
  skillName: z.string().trim().min(1).max(255),
  status: ProfileStatusSchema.optional(),
});

export const SkillTagUpdateSchema = SkillTagWriteSchema.partial().extend({
  skillName: optionalText(255),
});

export const LanguageWriteSchema = z.object({
  languageName: z.string().trim().min(1).max(255),
  languageStatus: optionalText(255),
  status: ProfileStatusSchema.optional(),
});

export const LanguageUpdateSchema = LanguageWriteSchema.partial().extend({
  languageName: optionalText(255),
});

const TrainingFields = z.object({
  title: z.string().trim().min(1).max(250),
  instituteName: optionalText(250),
  topics: optionalText(250),
  startDate: optionalDate,
  endDate: optionalDate,
  duration: z.coerce.number().int().min(0).max(100).optional(),
  durationType: optionalText(10),
  countryRef: optionalText(250),
  descriptions: optionalText(250),
  achievements: optionalText(250),
  certificateFileRef: optionalText(250),
  address: optionalText(200),
  status: ProfileStatusSchema.optional(),
});

export const TrainingWriteSchema = withDateOrder(TrainingFields);
export const TrainingUpdateSchema = withDateOrder(
  TrainingFields.partial().extend({ title: optionalText(250) })
);
