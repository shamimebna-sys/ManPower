import { z } from 'zod';

export const CandidateStatusSchema = z.enum(['A', 'P', 'I']);

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

const optionalLegacyId = z
  .union([z.string(), z.number()])
  .optional()
  .transform((value) => {
    if (value === undefined || value === '') return undefined;
    const parsed = BigInt(value);
    if (parsed < BigInt(0)) throw new Error('Legacy identifier must be non-negative');
    return parsed.toString();
  });

export const CandidateWriteSchema = z.object({
  code: optionalText(100),
  name: z.string().trim().min(1).max(100),
  email: z.string().trim().email().max(250).transform((value) => value.toLowerCase()),
  mobile: z.string().trim().min(1).max(250),
  passportNo: z.string().trim().min(1).max(250),
  agentId: z.union([z.string(), z.number()]).transform((value) => BigInt(value).toString()),
  classGroupId: z.union([z.string(), z.number()]).transform((value) => BigInt(value).toString()),
  secondaryEmail: optionalText(250),
  secondaryMobile: optionalText(250),
  emergencyMobile: optionalText(250),
  bid: optionalText(250),
  nid: optionalText(250),
  passportIssueDate: optionalDate,
  passportExpireDate: optionalDate,
  dob: optionalDate,
  fatherName: optionalText(250),
  motherName: optionalText(250),
  nationality: optionalText(250),
  gender: optionalText(20),
  bloodGroup: optionalText(20),
  presentAddressHouse: optionalText(200),
  presentAddressRoad: optionalText(20),
  presentAddressVillage: optionalText(20),
  presentAddressPost: optionalText(20),
  presentAddressThanaId: optionalLegacyId,
  presentAddressDistrictId: optionalLegacyId,
  presentAddressDivisionId: optionalLegacyId,
  permanentAddressHouse: optionalText(200),
  permanentAddressRoad: optionalText(20),
  permanentAddressVillage: optionalText(20),
  permanentAddressPost: optionalText(20),
  permanentAddressThanaId: optionalLegacyId,
  permanentAddressDistrictId: optionalLegacyId,
  permanentAddressDivisionId: optionalLegacyId,
  basicInfoCareer: optionalText(20000),
  basicInfoSpecial: optionalText(20000),
  otherSkills: optionalText(20000),
  remarks: optionalText(255),
  replacementRemarks: optionalText(20000),
  abroadEx: z.coerce.number().int().min(0).max(80).optional(),
  localEx: z.coerce.number().int().min(0).max(80).optional(),
  driveLink: optionalText(250),
  facebookLink: optionalText(250),
  youtubeLink: optionalText(250),
  linkedinLink: optionalText(250),
  twitterLink: optionalText(250),
  instagramLink: optionalText(250),
  position: optionalText(250),
  height: optionalText(100),
  weight: optionalText(100),
  maritalStatus: optionalText(100),
  expertise: optionalText(20000),
  skills: optionalText(20000),
  extraCurricular: optionalText(20000),
  interest: optionalText(20000),
  attribute: optionalText(20000),
  companierId: optionalLegacyId,
  agencierId: optionalLegacyId,
  positionId: optionalLegacyId,
  companyStatus: optionalText(200),
  subAgentId: optionalLegacyId,
  countryId: optionalLegacyId,
  replacedByLegacyId: optionalLegacyId,
  status: CandidateStatusSchema.optional(),
  bidFileRef: optionalText(20000),
  nidFileRef: optionalText(20000),
  passportFileRef: optionalText(20000),
  fullPhotoFileRef: optionalText(20000),
  halfPhotoFileRef: optionalText(20000),
  cvFileRef: optionalText(20000),
  skillCertificateFileRef: optionalText(20000),
  stampFileRef: optionalText(20000),
});

export const CandidateUpdateSchema = CandidateWriteSchema.partial().extend({
  name: optionalText(100),
  email: z
    .string()
    .trim()
    .email()
    .max(250)
    .transform((value) => value.toLowerCase())
    .optional(),
  mobile: optionalText(250),
  passportNo: optionalText(250),
  agentId: optionalLegacyId,
  classGroupId: optionalLegacyId,
});

export const CandidateStatusChangeSchema = z.object({
  status: CandidateStatusSchema,
  remarks: optionalText(255),
});

export const CandidateListQuerySchema = z.object({
  q: optionalText(200),
  id: z.string().uuid().optional(),
  name: optionalText(100),
  passportNo: optionalText(250),
  nid: optionalText(250),
  mobile: optionalText(250),
  email: optionalText(250),
  status: CandidateStatusSchema.optional(),
  cursor: z.string().uuid().optional(),
  limit: z.coerce.number().int().min(1).max(100).default(20),
});

export const CandidateIdParams = z.object({
  id: z.string().uuid(),
});
