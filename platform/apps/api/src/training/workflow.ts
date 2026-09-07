import type { ExamResultOutcome } from '@manpower/shared';

export const EXAM_RESULT_OUTCOMES = ['PASS', 'FAIL'] as const;

export const EXAM_MARK_FIELDS = ['abroadEx', 'localEx', 'bl', 'skill', 'english'] as const;

export type ExamMarkField = (typeof EXAM_MARK_FIELDS)[number];

export function isUngraded(result: string | null | undefined): boolean {
  return result === null || result === undefined || result === '';
}

export function normalizeExamResult(result: string | null | undefined): ExamResultOutcome | null {
  if (isUngraded(result)) {
    return null;
  }
  if (result === 'PASS' || result === 'FAIL') {
    return result;
  }
  throw new Error('Exam result must be PASS, FAIL, or ungraded');
}

/** Marks never compute PASS/FAIL. There is no pass-mark formula. */
export function resultFromMarks(_marks: Partial<Record<ExamMarkField, number | null>>): null {
  void _marks;
  return null;
}

export function shouldUpdateCandidateClassGroup(result: string | null | undefined): boolean {
  return result === 'PASS';
}

/** PASS/FAIL never write candidates.status. */
export function shouldUpdateCandidateStatus(_result: string | null | undefined): false {
  void _result;
  return false;
}

/**
 * Legacy FAIL UI snaps the dropdown to group 1 (Admission For Interview).
 * Server-side FAIL does not write the candidate master group.
 */
export const LEGACY_FAIL_GROUP_SOURCE_ID = 1;

export function isLegacyFailUiGroup(sourceLegacyId: bigint | number | null | undefined): boolean {
  if (sourceLegacyId === null || sourceLegacyId === undefined) {
    return false;
  }
  return BigInt(sourceLegacyId) === BigInt(LEGACY_FAIL_GROUP_SOURCE_ID);
}
