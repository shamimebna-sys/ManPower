export const FOUNDATION_PERMISSIONS = [
  ['iam.user.read', 'Read users'],
  ['iam.user.manage', 'Manage user account status'],
  ['iam.user_role.manage', 'Assign and remove user roles'],
  ['iam.role_permission.manage', 'Grant and revoke role permissions'],
  ['iam.audit.read', 'Read security audit events'],
  ['candidate.read', 'Read candidate master records'],
  ['candidate.create', 'Create candidate master records'],
  ['candidate.update', 'Update candidate master records'],
  ['candidate.status.manage', 'Change candidate account status'],
  ['candidate.education.read', 'Read candidate education records'],
  ['candidate.education.manage', 'Create and update candidate education records'],
  ['candidate.experience.read', 'Read candidate experience records'],
  ['candidate.experience.manage', 'Create and update candidate experience records'],
  ['candidate.skills.read', 'Read candidate skills and skill tags'],
  ['candidate.skills.manage', 'Create and update candidate skills and skill tags'],
  ['candidate.languages.read', 'Read candidate language records'],
  ['candidate.languages.manage', 'Create and update candidate language records'],
  ['candidate.training.read', 'Read candidate profile training records'],
  ['candidate.training.manage', 'Create and update candidate profile training records'],
  ['partners.read', 'Read recruitment partner masters'],
  ['partners.manage', 'Create and update recruitment partner masters'],
  ['partners.user_binding.manage', 'Bind and unbind user partner and self identities'],
  ['employer_candidate.read', 'Read employer-candidate assignments'],
  ['employer_candidate.manage', 'Create and update employer-candidate assignments'],
] as const;

export const APPROVED_ROLES = [
  ['super_admin', 'Super Administrator', 'Unrestricted platform administrator. Assign sparingly.'],
  ['administrator', 'Administrator', 'Global operations where explicitly granted.'],
  ['owner', 'Owner', 'Global operations where explicitly granted.'],
  ['agent', 'Agent', 'Recruiting partner scoped by bound agent.'],
  ['sub_agent', 'Sub Agent', 'Recruiting partner scoped by bound sub-agent.'],
  ['candidate', 'Candidate', 'Self-scoped candidate access.'],
  ['employer', 'Employer', 'Rapid Interview assignment actor.'],
  ['company', 'Company', 'Companier-scoped candidate access.'],
  ['agency', 'Agency', 'Agencier-scoped candidate access.'],
  ['employee', 'Employee', 'Internal staff with explicit module keys.'],
  ['teacher', 'Teacher', 'Training staff. Candidate access remains blocked in M4.'],
] as const;

/**
 * Explicit M4 grants. IAM keys stay super_admin-only.
 * Teacher receives no candidate or recruitment keys.
 */
export const M4_ROLE_GRANTS: Record<string, readonly string[]> = {
  administrator: [
    'candidate.read',
    'candidate.create',
    'candidate.update',
    'partners.read',
    'partners.manage',
    'partners.user_binding.manage',
    'employer_candidate.read',
    'employer_candidate.manage',
  ],
  owner: [
    'candidate.read',
    'candidate.create',
    'candidate.update',
    'partners.read',
    'partners.manage',
    'employer_candidate.read',
    'employer_candidate.manage',
  ],
  employee: ['candidate.read', 'candidate.create', 'candidate.update'],
  agent: ['candidate.read', 'candidate.update'],
  sub_agent: ['candidate.read', 'candidate.update'],
  agency: ['candidate.read', 'candidate.update'],
  company: ['candidate.read'],
  candidate: ['candidate.read', 'employer_candidate.read'],
  employer: ['employer_candidate.read', 'employer_candidate.manage'],
  teacher: [],
};
