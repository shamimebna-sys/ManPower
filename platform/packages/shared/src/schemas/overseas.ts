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

const optionalLegacyId = z.string().trim().regex(/^\d+$/).optional();

const looksLikePublicUrl = /^(https?:)?\/\//i;

export const PrivateFileIdSchema = z.preprocess(
  (value) => (value === '' || value === null ? undefined : value),
  z
    .string()
    .trim()
    .optional()
    .superRefine((value, context) => {
      if (value === undefined) return;
      if (looksLikePublicUrl.test(value)) {
        context.addIssue({ code: z.ZodIssueCode.custom, message: 'Public document URLs are not allowed' });
        return;
      }
      if (!z.string().uuid().safeParse(value).success) {
        context.addIssue({
          code: z.ZodIssueCode.custom,
          message: 'File references must be UUID private identifiers',
        });
      }
    })
);

export const OverseasListQuerySchema = z.object({
  candidateId: z.string().uuid().optional(),
  status: z.string().trim().max(5).optional(),
  activeOnly: z
    .enum(['true', 'false'])
    .optional()
    .transform((value) => value === 'true'),
  cursor: z.string().uuid().optional(),
  limit: z.coerce.number().int().min(1).max(100).default(20),
});

export const OverseasIdParams = z.object({
  id: z.string().uuid(),
});

const datedDocumentBase = {
  sourceLegacyId: optionalLegacyId,
  candidateId: z.string().uuid(),
  issueDate: optionalDate,
  expireDate: optionalDate,
  status: optionalText(5),
};

export const MedicalWriteSchema = z.object({
  ...datedDocumentBase,
  documentFileId: PrivateFileIdSchema,
  countryId: optionalLegacyId,
});

export const MedicalUpdateSchema = MedicalWriteSchema.partial();

export const PoliceClearanceWriteSchema = z.object({
  ...datedDocumentBase,
  thanaId: optionalLegacyId,
  countryId: optionalLegacyId,
  photoFileId: PrivateFileIdSchema,
});

export const PoliceClearanceUpdateSchema = PoliceClearanceWriteSchema.partial();

export const ArcWriteSchema = z.object({
  ...datedDocumentBase,
  isLifetime: optionalText(5),
  arcFileId: PrivateFileIdSchema,
  arcNumber: optionalText(255),
});

export const ArcUpdateSchema = ArcWriteSchema.partial();

export const LabourContractWriteSchema = z.object({
  ...datedDocumentBase,
  documentFileId: PrivateFileIdSchema,
});

export const LabourContractUpdateSchema = LabourContractWriteSchema.partial();

export const VisaWriteSchema = z.object({
  ...datedDocumentBase,
  visaMpNo: optionalText(255),
  documentFileId: PrivateFileIdSchema,
});

export const VisaUpdateSchema = VisaWriteSchema.partial();

export const FlightWriteSchema = z.object({
  sourceLegacyId: optionalLegacyId,
  candidateId: z.string().uuid(),
  airlineceName: optionalText(255),
  flightDate: optionalDate,
  flightTime: optionalTime,
  departureTime: optionalTime,
  arrivalTime: optionalTime,
  ticketFileId: PrivateFileIdSchema,
  arrivalSealPageFileId: PrivateFileIdSchema,
});

export const FlightUpdateSchema = FlightWriteSchema.partial();

export const LicensePositionInputSchema = z.object({
  sourceLegacyId: optionalLegacyId,
  positionId: optionalLegacyId,
  quantity: z.coerce.number().int().min(0).optional(),
});

export const LicenseWriteSchema = z.object({
  sourceLegacyId: optionalLegacyId,
  licenseNo: z.string().trim().min(1).max(255),
  status: optionalText(5),
  companierId: z.string().uuid(),
  licenseStartDate: optionalDate,
  licenseExpireDate: optionalDate,
  licenseFileId: PrivateFileIdSchema,
  positions: z.array(LicensePositionInputSchema).optional(),
});

export const LicenseUpdateSchema = LicenseWriteSchema.partial().extend({
  licenseNo: z.string().trim().min(1).max(255).optional(),
  companierId: z.string().uuid().optional(),
});

export const LicenseListQuerySchema = z.object({
  companierId: z.string().uuid().optional(),
  status: z.string().trim().max(5).optional(),
  cursor: z.string().uuid().optional(),
  limit: z.coerce.number().int().min(1).max(100).default(20),
});
