export type TrainingStatus = 'A' | 'I';

export type ExamResultOutcome = 'PASS' | 'FAIL';

export interface TeacherRecord {
  id: string;
  sourceLegacyId: string | null;
  code: string | null;
  name: string | null;
  email: string | null;
  secondaryEmail: string | null;
  mobile: string | null;
  secondaryMobile: string | null;
  emergencyMobile: string | null;
  nid: string | null;
  passportNo: string | null;
  dob: string | null;
  gender: string | null;
  nationality: string | null;
  fatherName: string | null;
  motherName: string | null;
  bloodGroup: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface TeacherListResult {
  items: TeacherRecord[];
}

export interface ClassGroupRecord {
  id: string;
  sourceLegacyId: string | null;
  name: string | null;
  description: string | null;
  code: string | null;
  status: string;
  feeAmount: string;
  createdAt: string;
  updatedAt: string;
}

export interface ClassGroupListResult {
  items: ClassGroupRecord[];
}

export interface ClassScheduleRecord {
  id: string;
  sourceLegacyId: string | null;
  teacherId: string | null;
  classGroupId: string | null;
  subject: string | null;
  startTime: string | null;
  endTime: string | null;
  weekDay: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface ClassScheduleListResult {
  items: ClassScheduleRecord[];
}

export interface ExamRecord {
  id: string;
  sourceLegacyId: string | null;
  name: string | null;
  examDate: string | null;
  examTime: string | null;
  examLink: string | null;
  remarks: string | null;
  status: string;
  classGroupIds: string[];
  createdAt: string;
  updatedAt: string;
}

export interface ExamListResult {
  items: ExamRecord[];
}

export interface ExamPublishResult {
  examId: string;
  created: number;
  existing: number;
}

export interface ExamResultRecord {
  id: string;
  sourceLegacyId: string | null;
  examId: string;
  candidateId: string;
  classGroupId: string | null;
  abroadEx: number | null;
  localEx: number | null;
  bl: number | null;
  skill: number | null;
  english: number | null;
  result: ExamResultOutcome | null;
  remarks: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface ExamResultListResult {
  items: ExamResultRecord[];
}

export interface ManpowerTrainingRecord {
  id: string;
  sourceLegacyId: string | null;
  candidateId: string;
  certificateIssueDate: string | null;
  certificateExpireDate: string | null;
  certificateFileRef: string | null;
  manpowerFileRef: string | null;
  fingerPrintFileRef: string | null;
  trainingStartDate: string | null;
  trainingEndDate: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface ManpowerTrainingListResult {
  items: ManpowerTrainingRecord[];
}
