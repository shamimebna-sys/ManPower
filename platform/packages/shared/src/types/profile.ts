export type ProfileStatus = 'A' | 'I';

export interface ProfileListMeta {
  nextCursor: string | null;
  total: number;
}

export interface CandidateEducationRecord {
  id: string;
  candidateId: string;
  examName: string | null;
  instituteName: string | null;
  subjectGroupMajor: string | null;
  educationLabel: string | null;
  startDate: string | null;
  endDate: string | null;
  durationYear: number | null;
  resultType: string | null;
  result: string | null;
  achievements: string | null;
  certificateFileRef: string | null;
  boardName: string | null;
  scale: string | null;
  passingYear: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface CandidateExperienceRecord {
  id: string;
  candidateId: string;
  companyName: string | null;
  companyAddress: string | null;
  countryRef: string | null;
  designation: string | null;
  department: string | null;
  startDate: string | null;
  endDate: string | null;
  responsibilities: string | null;
  expertise: string | null;
  descriptions: string | null;
  achievements: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface CandidateSkillRecord {
  id: string;
  candidateId: string;
  title: string | null;
  instituteName: string | null;
  details: string | null;
  resultScore: string | null;
  examScore: string | null;
  certificateFileRef: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface CandidateSkillTagRecord {
  id: string;
  candidateId: string;
  skillName: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface CandidateLanguageRecord {
  id: string;
  candidateId: string;
  languageName: string | null;
  languageStatus: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface CandidateTrainingRecord {
  id: string;
  candidateId: string;
  title: string | null;
  instituteName: string | null;
  topics: string | null;
  startDate: string | null;
  endDate: string | null;
  duration: number | null;
  durationType: string | null;
  countryRef: string | null;
  descriptions: string | null;
  achievements: string | null;
  certificateFileRef: string | null;
  address: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export interface ProfileListResult<T> {
  items: T[];
}
