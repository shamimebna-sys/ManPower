import type {
  ClassGroupRecord,
  ClassScheduleRecord,
  ExamRecord,
  ExamResultOutcome,
  ExamResultRecord,
  ManpowerTrainingRecord,
  TeacherRecord,
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

export function toTeacherRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  code: string | null;
  name: string | null;
  email: string | null;
  secondaryEmail: string | null;
  mobile: string | null;
  secondaryMobile: string | null;
  emergencyMobile: string | null;
  nid: string | null;
  passportNo: string | null;
  dob: Date | null;
  gender: string | null;
  nationality: string | null;
  fatherName: string | null;
  motherName: string | null;
  bloodGroup: string | null;
  status: string;
  createdAt: Date;
  updatedAt: Date;
}): TeacherRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    code: row.code,
    name: row.name,
    email: row.email,
    secondaryEmail: row.secondaryEmail,
    mobile: row.mobile,
    secondaryMobile: row.secondaryMobile,
    emergencyMobile: row.emergencyMobile,
    nid: row.nid,
    passportNo: row.passportNo,
    dob: dateToIso(row.dob),
    gender: row.gender,
    nationality: row.nationality,
    fatherName: row.fatherName,
    motherName: row.motherName,
    bloodGroup: row.bloodGroup,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toClassGroupRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  name: string | null;
  description: string | null;
  code: string | null;
  status: string;
  feeAmount: { toString(): string };
  createdAt: Date;
  updatedAt: Date;
}): ClassGroupRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    name: row.name,
    description: row.description,
    code: row.code,
    status: row.status,
    feeAmount: row.feeAmount.toString(),
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toClassScheduleRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  teacherId: string | null;
  classGroupId: string | null;
  subject: string | null;
  startTime: Date | null;
  endTime: Date | null;
  weekDay: string | null;
  status: string;
  createdAt: Date;
  updatedAt: Date;
}): ClassScheduleRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    teacherId: row.teacherId,
    classGroupId: row.classGroupId,
    subject: row.subject,
    startTime: timeToIso(row.startTime),
    endTime: timeToIso(row.endTime),
    weekDay: row.weekDay,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toExamRecord(
  row: {
    id: string;
    sourceLegacyId: bigint | null;
    name: string | null;
    examDate: Date | null;
    examTime: Date | null;
    examLink: string | null;
    remarks: string | null;
    status: string;
    createdAt: Date;
    updatedAt: Date;
  },
  classGroupIds: string[]
): ExamRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    name: row.name,
    examDate: dateToIso(row.examDate),
    examTime: timeToIso(row.examTime),
    examLink: row.examLink,
    remarks: row.remarks,
    status: row.status,
    classGroupIds,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toExamResultRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  examId: string;
  candidateId: string;
  classGroupId: string | null;
  abroadEx: number | null;
  localEx: number | null;
  bl: number | null;
  skill: number | null;
  english: number | null;
  result: string | null;
  remarks: string | null;
  status: string;
  createdAt: Date;
  updatedAt: Date;
}): ExamResultRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    examId: row.examId,
    candidateId: row.candidateId,
    classGroupId: row.classGroupId,
    abroadEx: row.abroadEx,
    localEx: row.localEx,
    bl: row.bl,
    skill: row.skill,
    english: row.english,
    result: (row.result as ExamResultOutcome | null) ?? null,
    remarks: row.remarks,
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function toManpowerTrainingRecord(row: {
  id: string;
  sourceLegacyId: bigint | null;
  candidateId: string;
  certificateIssueDate: Date | null;
  certificateExpireDate: Date | null;
  certificateFileRef: string | null;
  manpowerFileRef: string | null;
  fingerPrintFileRef: string | null;
  trainingStartDate: Date | null;
  trainingEndDate: Date | null;
  status: string;
  createdAt: Date;
  updatedAt: Date;
}): ManpowerTrainingRecord {
  return {
    id: row.id,
    sourceLegacyId: bigintToString(row.sourceLegacyId),
    candidateId: row.candidateId,
    certificateIssueDate: dateToIso(row.certificateIssueDate),
    certificateExpireDate: dateToIso(row.certificateExpireDate),
    certificateFileRef: row.certificateFileRef,
    manpowerFileRef: row.manpowerFileRef,
    fingerPrintFileRef: row.fingerPrintFileRef,
    trainingStartDate: dateToIso(row.trainingStartDate),
    trainingEndDate: dateToIso(row.trainingEndDate),
    status: row.status,
    createdAt: row.createdAt.toISOString(),
    updatedAt: row.updatedAt.toISOString(),
  };
}

export function parseTimeOfDay(value: string | undefined): Date | undefined {
  if (!value) return undefined;
  const normalized = value.length === 5 ? `${value}:00` : value;
  return new Date(`1970-01-01T${normalized}.000Z`);
}

export function parseDateOnly(value: string | undefined): Date | undefined {
  if (!value) return undefined;
  return new Date(`${value}T00:00:00.000Z`);
}
