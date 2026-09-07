import { z } from 'zod';

export const PartnerTypeSchema = z.enum(['agent', 'sub_agent', 'agencier', 'companier']);

export const BindingDomainSchema = z.enum([
  'agent',
  'sub_agent',
  'agencier',
  'companier',
  'candidate',
  'employer',
  'teacher',
]);

export const PartnerIdParams = z.object({
  type: PartnerTypeSchema,
  id: z.string().uuid(),
});

export const PartnerTypeParams = z.object({
  type: PartnerTypeSchema,
});

export const PartnerListQuerySchema = z.object({
  q: z.string().trim().max(200).optional(),
  status: z.string().trim().max(5).optional(),
  cursor: z.string().uuid().optional(),
  limit: z.coerce.number().int().min(1).max(100).default(20),
});

const optionalText = (max: number) => z.string().trim().max(max).optional();

export const PartnerWriteSchema = z.object({
  sourceLegacyId: z.string().regex(/^\d+$/).optional(),
  code: optionalText(100),
  name: optionalText(255),
  email: z.string().trim().email().max(255).optional(),
  mobile: optionalText(255),
  address: optionalText(255),
  status: z.enum(['A', 'I']).optional(),
  countryId: z.string().regex(/^\d+$/).optional(),
  logoFileRef: optionalText(2000),
  agentId: z.string().uuid().optional(),
  licenseNo: optionalText(255),
  vatNo: optionalText(255),
  ownerName: optionalText(255),
  ownerMobile: optionalText(255),
  ownerEmail: z.string().trim().email().max(255).optional(),
  signatureFileRef: optionalText(2000),
});

export const PartnerUpdateSchema = PartnerWriteSchema.partial();

export const UserBindingSchema = z
  .object({
    domain: BindingDomainSchema,
    targetId: z.string().uuid().nullable(),
  })
  .strict();

export const EmployerWriteSchema = z.object({
  sourceLegacyId: z.string().regex(/^\d+$/).optional(),
  code: optionalText(100),
  name: optionalText(255),
  email: z.string().trim().email().max(255).optional(),
  mobile: optionalText(255),
  status: z.enum(['A', 'I']).optional(),
});

export const EmployerUpdateSchema = EmployerWriteSchema.partial();

export const EmployerIdParams = z.object({
  id: z.string().uuid(),
});

export const EmployerCandidatePurposeSchema = z.enum(['FAVORITE', 'RESERVE', 'SELECTED']);

export const EmployerCandidateWriteSchema = z.object({
  employerId: z.string().uuid().optional(),
  candidateId: z.string().uuid(),
  purpose: EmployerCandidatePurposeSchema,
  status: z.enum(['A', 'I']).optional(),
});

export const EmployerCandidateUpdateSchema = z
  .object({
    purpose: EmployerCandidatePurposeSchema.optional(),
    status: z.enum(['A', 'I']).optional(),
  })
  .refine((value) => value.purpose !== undefined || value.status !== undefined, {
    message: 'At least one of purpose or status is required',
  });

export const EmployerCandidateStatusSchema = z.object({
  status: z.enum(['A', 'I']),
});

export const EmployerCandidateListQuerySchema = z.object({
  employerId: z.string().uuid().optional(),
  candidateId: z.string().uuid().optional(),
  purpose: EmployerCandidatePurposeSchema.optional(),
  status: z.enum(['A', 'I']).optional(),
  cursor: z.string().uuid().optional(),
  limit: z.coerce.number().int().min(1).max(100).default(20),
});

export const EmployerCandidateIdParams = z.object({
  id: z.string().uuid(),
});
