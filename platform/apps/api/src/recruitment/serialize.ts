import type { Prisma } from '@prisma/client';
import type {
  EmployerCandidateRecord,
  EmployerRecord,
  PartnerRecord,
  PartnerSummary,
  PartnerType,
} from '@manpower/shared';

export function toLegacyString(value: bigint | null | undefined): string | null {
  return value === null || value === undefined ? null : value.toString();
}

export function toPartnerSummary(
  type: PartnerType,
  row: {
    id: string;
    sourceLegacyId: bigint | null;
    code: string | null;
    name: string | null;
    email: string | null;
    mobile: string | null;
    status: string;
    createdAt: Date;
  }
): PartnerSummary {
  return {
    id: row.id,
    type,
    sourceLegacyId: toLegacyString(row.sourceLegacyId),
    code: row.code,
    name: row.name,
    email: row.email,
    mobile: row.mobile,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
  };
}

export function toPartnerRecord(
  type: PartnerType,
  row: {
    id: string;
    sourceLegacyId: bigint | null;
    code: string | null;
    name: string | null;
    email: string | null;
    mobile: string | null;
    address?: string | null;
    status: string;
    countryId?: bigint | null;
    balance?: Prisma.Decimal | null;
    logoFileRef?: string | null;
    createdAt: Date;
    agentId?: string;
    licenseNo?: string | null;
    vatNo?: string | null;
    ownerName?: string | null;
    ownerMobile?: string | null;
    ownerEmail?: string | null;
    signatureFileRef?: string | null;
  }
): PartnerRecord {
  return {
    ...toPartnerSummary(type, row),
    address: row.address ?? null,
    countryId: toLegacyString(row.countryId),
    balance: row.balance?.toString() ?? '0',
    logoFileRef: row.logoFileRef ?? null,
    ...(type === 'sub_agent' ? { agentId: row.agentId ?? null } : {}),
    ...(type === 'agencier' || type === 'companier'
      ? {
          licenseNo: row.licenseNo ?? null,
          vatNo: row.vatNo ?? null,
          ownerName: row.ownerName ?? null,
          ownerMobile: row.ownerMobile ?? null,
          ownerEmail: row.ownerEmail ?? null,
        }
      : {}),
    ...(type === 'agencier' ? { signatureFileRef: row.signatureFileRef ?? null } : {}),
  };
}

export function toEmployerRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  code: string | null;
  name: string | null;
  email: string | null;
  mobile: string | null;
  status: string;
  createdAt: Date;
}): EmployerRecord {
  return {
    id: row.id,
    sourceLegacyId: toLegacyString(row.sourceLegacyId),
    code: row.code,
    name: row.name,
    email: row.email,
    mobile: row.mobile,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
  };
}

export function toEmployerCandidateRecord(row: {
  id: string;
  employerId: string;
  candidateId: string;
  purpose: 'FAVORITE' | 'RESERVE' | 'SELECTED';
  status: string;
  createdAt: Date;
  updatedAt: Date;
}): EmployerCandidateRecord {
  return {
    id: row.id,
    employerId: row.employerId,
    candidateId: row.candidateId,
    purpose: row.purpose,
    status: row.status === 'I' ? 'I' : 'A',
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}
