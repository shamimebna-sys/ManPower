export type PartnerType = 'agent' | 'sub_agent' | 'agencier' | 'companier';

export type BindingDomain = PartnerType | 'candidate' | 'employer' | 'teacher';

export type EmployerCandidatePurpose = 'FAVORITE' | 'RESERVE' | 'SELECTED';

export type EmployerCandidateStatus = 'A' | 'I';

export interface PartnerSummary {
  id: string;
  type: PartnerType;
  sourceLegacyId: string | null;
  code: string | null;
  name: string | null;
  email: string | null;
  mobile: string | null;
  status: string;
  createdAt: string;
}

export interface PartnerRecord extends PartnerSummary {
  address: string | null;
  countryId: string | null;
  balance: string;
  logoFileRef: string | null;
  agentId?: string | null;
  licenseNo?: string | null;
  vatNo?: string | null;
  ownerName?: string | null;
  ownerMobile?: string | null;
  ownerEmail?: string | null;
  signatureFileRef?: string | null;
}

export interface PartnerListResult {
  items: PartnerSummary[];
}

export interface EmployerCandidateRecord {
  id: string;
  employerId: string;
  candidateId: string;
  purpose: EmployerCandidatePurpose;
  status: EmployerCandidateStatus;
  createdAt: string;
  updatedAt: string;
}

export interface EmployerCandidateListResult {
  items: EmployerCandidateRecord[];
}

export interface EmployerRecord {
  id: string;
  sourceLegacyId: string | null;
  code: string | null;
  name: string | null;
  email: string | null;
  mobile: string | null;
  status: string;
  createdAt: string;
}

export interface EmployerListResult {
  items: EmployerRecord[];
}
