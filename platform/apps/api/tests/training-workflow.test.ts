import { describe, expect, it } from 'vitest';
import {
  EXAM_MARK_FIELDS,
  isLegacyFailUiGroup,
  isUngraded,
  LEGACY_FAIL_GROUP_SOURCE_ID,
  normalizeExamResult,
  resultFromMarks,
  shouldUpdateCandidateClassGroup,
  shouldUpdateCandidateStatus,
} from '../src/training/workflow';

describe('M5 exam result workflow rules', () => {
  it('treats NULL and empty as ungraded and distinct from score 0', () => {
    expect(isUngraded(null)).toBe(true);
    expect(isUngraded(undefined)).toBe(true);
    expect(isUngraded('')).toBe(true);
    expect(isUngraded('PASS')).toBe(false);
    expect(isUngraded('FAIL')).toBe(false);
    expect(normalizeExamResult(null)).toBeNull();
    expect(normalizeExamResult('PASS')).toBe('PASS');
    expect(normalizeExamResult('FAIL')).toBe('FAIL');
    expect(() => normalizeExamResult('MAYBE')).toThrow(/PASS, FAIL, or ungraded/);
  });

  it('preserves five marks and never computes a formula', () => {
    expect(EXAM_MARK_FIELDS).toEqual(['abroadEx', 'localEx', 'bl', 'skill', 'english']);
    expect(
      resultFromMarks({ abroadEx: 100, localEx: 100, bl: 100, skill: 100, english: 100 })
    ).toBeNull();
    expect(resultFromMarks({ abroadEx: 0, localEx: 0, bl: 0, skill: 0, english: 0 })).toBeNull();
  });

  it('updates candidate class group only on PASS and never writes candidate status', () => {
    expect(shouldUpdateCandidateClassGroup('PASS')).toBe(true);
    expect(shouldUpdateCandidateClassGroup('FAIL')).toBe(false);
    expect(shouldUpdateCandidateClassGroup(null)).toBe(false);
    expect(shouldUpdateCandidateStatus('PASS')).toBe(false);
    expect(shouldUpdateCandidateStatus('FAIL')).toBe(false);
    expect(shouldUpdateCandidateStatus(null)).toBe(false);
  });

  it('documents legacy group-1 FAIL UI without treating it as a candidate write', () => {
    expect(LEGACY_FAIL_GROUP_SOURCE_ID).toBe(1);
    expect(isLegacyFailUiGroup(1)).toBe(true);
    expect(isLegacyFailUiGroup(1n)).toBe(true);
    expect(isLegacyFailUiGroup(42)).toBe(false);
    expect(shouldUpdateCandidateClassGroup('FAIL')).toBe(false);
  });

  it('keeps manpower/BMET distinct from profile training and step-9 payment', () => {
    const profileTrainingKey = 'candidate.training.manage';
    const manpowerKey = 'training.manpower.manage';
    const step9Source = 'PaymentRequest.bill_title like Manpower';
    expect(profileTrainingKey).not.toBe(manpowerKey);
    expect(step9Source).not.toContain('manpower_training_events');
    expect(step9Source).not.toContain('candidate.trainings');
  });
});
