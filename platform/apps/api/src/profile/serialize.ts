import type {
  CandidateEducation,
  CandidateExperience,
  CandidateLanguage,
  CandidateSkill,
  CandidateSkillTag,
  CandidateTraining,
} from '@prisma/client';
import type {
  CandidateEducationRecord,
  CandidateExperienceRecord,
  CandidateLanguageRecord,
  CandidateSkillRecord,
  CandidateSkillTagRecord,
  CandidateTrainingRecord,
} from '@manpower/shared';

function dateToIso(value: Date | null): string | null {
  return value ? value.toISOString().slice(0, 10) : null;
}

export function toEducationRecord(row: CandidateEducation): CandidateEducationRecord {
  return {
    id: row.id,
    candidateId: row.candidateId,
    examName: row.examName,
    instituteName: row.instituteName,
    subjectGroupMajor: row.subjectGroupMajor,
    educationLabel: row.educationLabel,
    startDate: dateToIso(row.startDate),
    endDate: dateToIso(row.endDate),
    durationYear: row.durationYear,
    resultType: row.resultType,
    result: row.result,
    achievements: row.achievements,
    certificateFileRef: row.certificateFileRef,
    boardName: row.boardName,
    scale: row.scale,
    passingYear: row.passingYear,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toExperienceRecord(row: CandidateExperience): CandidateExperienceRecord {
  return {
    id: row.id,
    candidateId: row.candidateId,
    companyName: row.companyName,
    companyAddress: row.companyAddress,
    countryRef: row.countryRef,
    designation: row.designation,
    department: row.department,
    startDate: dateToIso(row.startDate),
    endDate: dateToIso(row.endDate),
    responsibilities: row.responsibilities,
    expertise: row.expertise,
    descriptions: row.descriptions,
    achievements: row.achievements,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toSkillRecord(row: CandidateSkill): CandidateSkillRecord {
  return {
    id: row.id,
    candidateId: row.candidateId,
    title: row.title,
    instituteName: row.instituteName,
    details: row.details,
    resultScore: row.resultScore === null ? null : row.resultScore.toString(),
    examScore: row.examScore === null ? null : row.examScore.toString(),
    certificateFileRef: row.certificateFileRef,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toSkillTagRecord(row: CandidateSkillTag): CandidateSkillTagRecord {
  return {
    id: row.id,
    candidateId: row.candidateId,
    skillName: row.skillName,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toLanguageRecord(row: CandidateLanguage): CandidateLanguageRecord {
  return {
    id: row.id,
    candidateId: row.candidateId,
    languageName: row.languageName,
    languageStatus: row.languageStatus,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toTrainingRecord(row: CandidateTraining): CandidateTrainingRecord {
  return {
    id: row.id,
    candidateId: row.candidateId,
    title: row.title,
    instituteName: row.instituteName,
    topics: row.topics,
    startDate: dateToIso(row.startDate),
    endDate: dateToIso(row.endDate),
    duration: row.duration,
    durationType: row.durationType,
    countryRef: row.countryRef,
    descriptions: row.descriptions,
    achievements: row.achievements,
    certificateFileRef: row.certificateFileRef,
    address: row.address,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toDate(value: string): Date {
  return new Date(`${value}T00:00:00.000Z`);
}
