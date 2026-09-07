export interface OverseasDocumentRecord {
  id: string;
  sourceLegacyId: string | null;
  candidateId: string | null;
  issueDate: string | null;
  expireDate: string | null;
  status: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface MedicalRecord extends OverseasDocumentRecord {
  documentFileId: string | null;
  countryId: string | null;
}

export interface PoliceClearanceRecord extends OverseasDocumentRecord {
  thanaId: string | null;
  countryId: string | null;
  photoFileId: string | null;
}

export interface ArcRecord extends OverseasDocumentRecord {
  isLifetime: string | null;
  arcFileId: string | null;
  arcNumber: string | null;
}

export interface LabourContractRecord extends OverseasDocumentRecord {
  documentFileId: string | null;
}

export interface VisaRecord extends OverseasDocumentRecord {
  visaMpNo: string | null;
  documentFileId: string | null;
}

export interface FlightRecord {
  id: string;
  sourceLegacyId: string | null;
  candidateId: string | null;
  airlineceName: string | null;
  flightDate: string | null;
  flightTime: string | null;
  departureTime: string | null;
  arrivalTime: string | null;
  ticketFileId: string | null;
  arrivalSealPageFileId: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface LicensePositionRecord {
  id: string;
  sourceLegacyId: string | null;
  licenseId: string | null;
  positionId: string | null;
  quantity: number | null;
  createdAt: string;
  updatedAt: string;
}

export interface LicenseRecord {
  id: string;
  sourceLegacyId: string | null;
  licenseNo: string;
  status: string | null;
  companierId: string | null;
  licenseStartDate: string | null;
  licenseExpireDate: string | null;
  licenseFileId: string | null;
  positions: LicensePositionRecord[];
  createdAt: string;
  updatedAt: string;
}

export interface LicenseListResult {
  items: LicenseRecord[];
}

export interface LiveStatusLookupRecord {
  id: string;
  sourceLegacyId: string | null;
  name: string | null;
  description: string | null;
  stepNo: number | null;
  status: string | null;
}

export interface LiveStatusBadgeRecord {
  candidateId: string;
  stepNo: number;
  name: string;
  extraText: string | null;
  skippedStep5: true;
  medicalOnLadder: false;
  arcOnLadder: false;
  writesCandidateStatus: false;
}

export interface OverseasListResult<T> {
  items: T[];
}
