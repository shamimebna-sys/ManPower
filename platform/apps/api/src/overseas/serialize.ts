import type {
  ArcRecord,
  FlightRecord,
  LabourContractRecord,
  LicensePositionRecord,
  LicenseRecord,
  LiveStatusLookupRecord,
  MedicalRecord,
  PoliceClearanceRecord,
  VisaRecord,
} from '@manpower/shared';

function bigintToString(value: bigint | null | undefined): string | null {
  return value === null || value === undefined ? null : value.toString();
}

function dateToIso(value: Date | null | undefined): string | null {
  return value ? value.toISOString().slice(0, 10) : null;
}

function timeToIso(value: Date | null | undefined): string | null {
  if (!value) return null;
  return value.toISOString().slice(11, 19);
}

type DatedRow = {
  id: string;
  sourceLegacyId: bigint | null;
  candidateId: string | null;
  issueDate: Date | null;
  expireDate: Date | null;
  status: string | null;
  createdAt: Date;
  updatedAt: Date;
};

function datedBase(row: DatedRow) {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    candidateId: row.candidateId,
    issueDate: dateToIso(row.issueDate),
    expireDate: dateToIso(row.expireDate),
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toMedicalRecord(
  row: DatedRow & { documentFileId: string | null; countryId: bigint | null }
): MedicalRecord {
  return {
    ...datedBase(row),
    documentFileId: row.documentFileId,
    countryId: bigintToString(row.countryId),
  };
}

export function toPoliceClearanceRecord(
  row: DatedRow & { thanaId: bigint | null; countryId: bigint | null; photoFileId: string | null }
): PoliceClearanceRecord {
  return {
    ...datedBase(row),
    thanaId: bigintToString(row.thanaId),
    countryId: bigintToString(row.countryId),
    photoFileId: row.photoFileId,
  };
}

export function toArcRecord(
  row: DatedRow & { isLifetime: string | null; arcFileId: string | null; arcNumber: string | null }
): ArcRecord {
  return {
    ...datedBase(row),
    isLifetime: row.isLifetime,
    arcFileId: row.arcFileId,
    arcNumber: row.arcNumber,
  };
}

export function toLabourContractRecord(row: DatedRow & { documentFileId: string | null }): LabourContractRecord {
  return {
    ...datedBase(row),
    documentFileId: row.documentFileId,
  };
}

export function toVisaRecord(
  row: DatedRow & { visaMpNo: string | null; documentFileId: string | null }
): VisaRecord {
  return {
    ...datedBase(row),
    visaMpNo: row.visaMpNo,
    documentFileId: row.documentFileId,
  };
}

export function toFlightRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  candidateId: string | null;
  airlineceName: string | null;
  flightDate: Date | null;
  flightTime: Date | null;
  departureTime: Date | null;
  arrivalTime: Date | null;
  ticketFileId: string | null;
  arrivalSealPageFileId: string | null;
  createdAt: Date;
  updatedAt: Date;
}): FlightRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    candidateId: row.candidateId,
    airlineceName: row.airlineceName,
    flightDate: dateToIso(row.flightDate),
    flightTime: timeToIso(row.flightTime),
    departureTime: timeToIso(row.departureTime),
    arrivalTime: timeToIso(row.arrivalTime),
    ticketFileId: row.ticketFileId,
    arrivalSealPageFileId: row.arrivalSealPageFileId,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toLicensePositionRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  licenseId: string | null;
  positionId: bigint | null;
  quantity: number | null;
  createdAt: Date;
  updatedAt: Date;
}): LicensePositionRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    licenseId: row.licenseId,
    positionId: bigintToString(row.positionId),
    quantity: row.quantity,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toLicenseRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  licenseNo: string;
  status: string | null;
  companierId: string | null;
  licenseStartDate: Date | null;
  licenseExpireDate: Date | null;
  licenseFileId: string | null;
  createdAt: Date;
  updatedAt: Date;
  positions?: Parameters<typeof toLicensePositionRecord>[0][];
}): LicenseRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    licenseNo: row.licenseNo,
    status: row.status,
    companierId: row.companierId,
    licenseStartDate: dateToIso(row.licenseStartDate),
    licenseExpireDate: dateToIso(row.licenseExpireDate),
    licenseFileId: row.licenseFileId,
    positions: (row.positions ?? []).map(toLicensePositionRecord),
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toLiveStatusLookupRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  name: string | null;
  description: string | null;
  stepNo: number | null;
  status: string | null;
}): LiveStatusLookupRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    name: row.name,
    description: row.description,
    stepNo: row.stepNo,
    status: row.status,
  };
}

export function parseDateOnly(value: string | undefined): Date | undefined {
  if (!value) return undefined;
  return new Date(`${value}T00:00:00.000Z`);
}

export function parseTimeOnly(value: string | undefined): Date | undefined {
  if (!value) return undefined;
  const normalized = value.length === 5 ? `${value}:00` : value;
  return new Date(`1970-01-01T${normalized}.000Z`);
}
