export type CandidateStatus = 'A' | 'P' | 'I';

export interface CandidateSummary {
  id: string;
  code: string | null;
  name: string | null;
  email: string | null;
  mobile: string | null;
  passportNo: string | null;
  nid: string | null;
  status: string;
  agentId: string | null;
  classGroupId: string | null;
  createdAt: string;
}

export interface CandidateRecord extends CandidateSummary {
  secondaryEmail: string | null;
  secondaryMobile: string | null;
  emergencyMobile: string | null;
  bid: string | null;
  bidFileRef: string | null;
  nidFileRef: string | null;
  passportIssueDate: string | null;
  passportExpireDate: string | null;
  passportFileRef: string | null;
  dob: string | null;
  fullPhotoFileRef: string | null;
  halfPhotoFileRef: string | null;
  fatherName: string | null;
  motherName: string | null;
  nationality: string | null;
  gender: string | null;
  bloodGroup: string | null;
  presentAddressHouse: string | null;
  presentAddressRoad: string | null;
  presentAddressVillage: string | null;
  presentAddressPost: string | null;
  presentAddressThanaId: string | null;
  presentAddressDistrictId: string | null;
  presentAddressDivisionId: string | null;
  permanentAddressHouse: string | null;
  permanentAddressRoad: string | null;
  permanentAddressVillage: string | null;
  permanentAddressPost: string | null;
  permanentAddressThanaId: string | null;
  permanentAddressDistrictId: string | null;
  permanentAddressDivisionId: string | null;
  basicInfoCareer: string | null;
  basicInfoSpecial: string | null;
  otherSkills: string | null;
  balance: string;
  cvFileRef: string | null;
  remarks: string | null;
  replacementRemarks: string | null;
  admissionPaymentId: string | null;
  finalGroupPaymentId: string | null;
  medicalFeePaymentId: string | null;
  abroadEx: number;
  localEx: number;
  driveLink: string | null;
  facebookLink: string | null;
  youtubeLink: string | null;
  linkedinLink: string | null;
  twitterLink: string | null;
  instagramLink: string | null;
  position: string | null;
  skillCertificateFileRef: string | null;
  height: string | null;
  weight: string | null;
  maritalStatus: string | null;
  expertise: string | null;
  skills: string | null;
  extraCurricular: string | null;
  interest: string | null;
  attribute: string | null;
  companierId: string | null;
  agencierId: string | null;
  stampFileRef: string | null;
  positionId: string | null;
  companyStatus: string | null;
  subAgentId: string | null;
  countryId: string | null;
  replacedByLegacyId: string | null;
  updatedAt: string;
}

export interface CandidateListResult {
  items: CandidateSummary[];
}
