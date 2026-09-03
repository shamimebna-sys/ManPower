import type { Candidate } from '@prisma/client';
import type { CandidateRecord, CandidateSummary } from '@manpower/shared';

function bigintToString(value: bigint | null): string | null {
  return value === null ? null : value.toString();
}

function dateToIso(value: Date | null): string | null {
  return value ? value.toISOString().slice(0, 10) : null;
}

export function toCandidateSummary(candidate: Candidate): CandidateSummary {
  return {
    id: candidate.id,
    code: candidate.code,
    name: candidate.name,
    email: candidate.email,
    mobile: candidate.mobile,
    passportNo: candidate.passportNo,
    nid: candidate.nid,
    status: candidate.status,
    agentId: bigintToString(candidate.agentId),
    classGroupId: bigintToString(candidate.classGroupId),
    createdAt: candidate.createdAt.toISOString(),
  };
}

export function toCandidateRecord(candidate: Candidate): CandidateRecord {
  return {
    ...toCandidateSummary(candidate),
    secondaryEmail: candidate.secondaryEmail,
    secondaryMobile: candidate.secondaryMobile,
    emergencyMobile: candidate.emergencyMobile,
    bid: candidate.bid,
    bidFileRef: candidate.bidFileRef,
    nidFileRef: candidate.nidFileRef,
    passportIssueDate: dateToIso(candidate.passportIssueDate),
    passportExpireDate: dateToIso(candidate.passportExpireDate),
    passportFileRef: candidate.passportFileRef,
    dob: dateToIso(candidate.dob),
    fullPhotoFileRef: candidate.fullPhotoFileRef,
    halfPhotoFileRef: candidate.halfPhotoFileRef,
    fatherName: candidate.fatherName,
    motherName: candidate.motherName,
    nationality: candidate.nationality,
    gender: candidate.gender,
    bloodGroup: candidate.bloodGroup,
    presentAddressHouse: candidate.presentAddressHouse,
    presentAddressRoad: candidate.presentAddressRoad,
    presentAddressVillage: candidate.presentAddressVillage,
    presentAddressPost: candidate.presentAddressPost,
    presentAddressThanaId: bigintToString(candidate.presentAddressThanaId),
    presentAddressDistrictId: bigintToString(candidate.presentAddressDistrictId),
    presentAddressDivisionId: bigintToString(candidate.presentAddressDivisionId),
    permanentAddressHouse: candidate.permanentAddressHouse,
    permanentAddressRoad: candidate.permanentAddressRoad,
    permanentAddressVillage: candidate.permanentAddressVillage,
    permanentAddressPost: candidate.permanentAddressPost,
    permanentAddressThanaId: bigintToString(candidate.permanentAddressThanaId),
    permanentAddressDistrictId: bigintToString(candidate.permanentAddressDistrictId),
    permanentAddressDivisionId: bigintToString(candidate.permanentAddressDivisionId),
    basicInfoCareer: candidate.basicInfoCareer,
    basicInfoSpecial: candidate.basicInfoSpecial,
    otherSkills: candidate.otherSkills,
    balance: candidate.balance.toString(),
    cvFileRef: candidate.cvFileRef,
    remarks: candidate.remarks,
    replacementRemarks: candidate.replacementRemarks,
    admissionPaymentId: bigintToString(candidate.admissionPaymentId),
    finalGroupPaymentId: bigintToString(candidate.finalGroupPaymentId),
    medicalFeePaymentId: bigintToString(candidate.medicalFeePaymentId),
    abroadEx: candidate.abroadEx,
    localEx: candidate.localEx,
    driveLink: candidate.driveLink,
    facebookLink: candidate.facebookLink,
    youtubeLink: candidate.youtubeLink,
    linkedinLink: candidate.linkedinLink,
    twitterLink: candidate.twitterLink,
    instagramLink: candidate.instagramLink,
    position: candidate.position,
    skillCertificateFileRef: candidate.skillCertificateFileRef,
    height: candidate.height,
    weight: candidate.weight,
    maritalStatus: candidate.maritalStatus,
    expertise: candidate.expertise,
    skills: candidate.skills,
    extraCurricular: candidate.extraCurricular,
    interest: candidate.interest,
    attribute: candidate.attribute,
    companierId: bigintToString(candidate.companierId),
    agencierId: bigintToString(candidate.agencierId),
    stampFileRef: candidate.stampFileRef,
    positionId: bigintToString(candidate.positionId),
    companyStatus: candidate.companyStatus,
    subAgentId: bigintToString(candidate.subAgentId),
    countryId: bigintToString(candidate.countryId),
    replacedByLegacyId: bigintToString(candidate.replacedByLegacyId),
    updatedAt: candidate.updatedAt.toISOString(),
  };
}

export function toBigInt(value: string): bigint {
  return BigInt(value);
}

export function toDate(value: string): Date {
  return new Date(`${value}T00:00:00.000Z`);
}
