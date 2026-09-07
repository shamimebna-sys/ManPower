import { describe, expect, it } from 'vitest';
import {
  M4_ROLE_GRANTS,
  M5_PERMISSION_KEYS,
  M5_ROLE_GRANTS,
} from '../src/iam/permission-catalogue';

describe('M5 permission matrix', () => {
  it('defines exactly twelve M5 keys and no delete keys', () => {
    expect(M5_PERMISSION_KEYS).toHaveLength(12);
    expect(M5_PERMISSION_KEYS.every((key) => key.startsWith('training.'))).toBe(true);
    expect(M5_PERMISSION_KEYS.some((key) => key.includes('delete'))).toBe(false);
    expect(M5_PERMISSION_KEYS).toContain('training.teacher.read');
    expect(M5_PERMISSION_KEYS).toContain('training.manpower.manage');
  });

  it('does not grant administrator exam or exam_result keys', () => {
    const admin = M5_ROLE_GRANTS.administrator ?? [];
    expect(admin).not.toContain('training.exam.read');
    expect(admin).not.toContain('training.exam.manage');
    expect(admin).not.toContain('training.exam_result.read');
    expect(admin).not.toContain('training.exam_result.manage');
    expect(admin).toEqual(
      expect.arrayContaining([
        'training.teacher.read',
        'training.teacher.manage',
        'training.class_group.read',
        'training.class_group.manage',
        'training.schedule.read',
        'training.schedule.manage',
        'training.manpower.read',
        'training.manpower.manage',
      ])
    );
  });

  it('grants teacher only self-scoped teacher.read and no candidate.read', () => {
    expect(M5_ROLE_GRANTS.teacher).toEqual(['training.teacher.read']);
    expect(M4_ROLE_GRANTS.teacher).toEqual([]);
    expect(M5_ROLE_GRANTS.teacher).not.toContain('candidate.read');
    expect(M5_ROLE_GRANTS.teacher).not.toContain('training.exam.read');
    expect(M5_ROLE_GRANTS.teacher).not.toContain('training.exam_result.read');
  });

  it('grants owner all twelve keys and employee only teacher plus manpower', () => {
    expect(M5_ROLE_GRANTS.owner).toEqual(expect.arrayContaining([...M5_PERMISSION_KEYS]));
    expect(M5_ROLE_GRANTS.owner).toHaveLength(12);
    expect(M5_ROLE_GRANTS.employee).toEqual([
      'training.teacher.read',
      'training.teacher.manage',
      'training.manpower.read',
      'training.manpower.manage',
    ]);
  });

  it('scopes agent/sub_agent/agency to manpower only and grants company/candidate/employer nothing', () => {
    expect(M5_ROLE_GRANTS.agent).toEqual(['training.manpower.read', 'training.manpower.manage']);
    expect(M5_ROLE_GRANTS.sub_agent).toEqual(['training.manpower.read', 'training.manpower.manage']);
    expect(M5_ROLE_GRANTS.agency).toEqual(['training.manpower.read', 'training.manpower.manage']);
    expect(M5_ROLE_GRANTS.company).toBeUndefined();
    expect(M5_ROLE_GRANTS.candidate).toBeUndefined();
    expect(M5_ROLE_GRANTS.employer).toBeUndefined();
  });
});
