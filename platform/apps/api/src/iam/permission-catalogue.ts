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
  ['training.teacher.read', 'Read teacher person master'],
  ['training.teacher.manage', 'Create and update teacher person master'],
  ['training.class_group.read', 'Read class groups'],
  ['training.class_group.manage', 'Create and update class groups'],
  ['training.schedule.read', 'Read class schedules'],
  ['training.schedule.manage', 'Create and update class schedules'],
  ['training.exam.read', 'Read exams and exam class-group membership'],
  ['training.exam.manage', 'Create and update exams and exam class groups'],
  ['training.exam_result.read', 'Read exam results'],
  ['training.exam_result.manage', 'Publish and grade exam results'],
  ['training.manpower.read', 'Read manpower / BMET training evidence'],
  ['training.manpower.manage', 'Create and update manpower / BMET training evidence'],
  ['overseas.medical.read', 'Read candidate medical records'],
  ['overseas.medical.manage', 'Create and update candidate medical records'],
  ['overseas.police_clearance.read', 'Read police clearance records'],
  ['overseas.police_clearance.manage', 'Create and update police clearance records'],
  ['overseas.arc.read', 'Read ARC records'],
  ['overseas.arc.manage', 'Create and update ARC records'],
  ['overseas.labour_contract.read', 'Read labour contract records'],
  ['overseas.labour_contract.manage', 'Create and update labour contract records'],
  ['overseas.visa.read', 'Read visa immigration records'],
  ['overseas.visa.manage', 'Create and update visa immigration records'],
  ['overseas.flight.read', 'Read flight schedule records'],
  ['overseas.flight.manage', 'Create and update flight schedule records'],
  ['operations.license.read', 'Read companier licenses'],
  ['operations.license.manage', 'Create and update companier licenses'],
  ['finance.read', 'Read finance requests, wallets, and recon reports'],
  ['finance.wallet.read', 'Read derived wallet balances and history'],
  ['finance.payment_request.create', 'Create pending payment requests'],
  ['finance.payment_request.approve', 'Approve payment requests and post fee journals'],
  ['finance.payment_request.reject', 'Reject payment requests and post A18 zero journals'],
  ['finance.journal.read', 'Read posted and reversed journals'],
  ['finance.reconciliation.read', 'Read reconciliation gates and quarantine'],
  ['finance.administration', 'Opening sign-off, reversal, quarantine resolve'],
  ['finance.fx_rate.manage', 'Enter administrative FX rates for new posts'],
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
  ['teacher', 'Teacher', 'Training staff. Self-scoped teacher read only. No candidate.read.'],
] as const;

/**
 * Explicit M4 grants. IAM keys stay super_admin-only.
 * Teacher receives no candidate or recruitment keys.
 * M5 keys are granted only through M5_ROLE_GRANTS.
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

/**
 * Exact approved M5 runtime matrix.
 * Administrator has NO exam / exam_result keys.
 * Teacher has teacher.read only (self-scoped in access helpers) and NO candidate.read.
 * Agent / sub_agent / agency manpower access is candidate-scoped in access helpers.
 */
export const M5_ROLE_GRANTS: Record<string, readonly string[]> = {
  owner: [
    'training.teacher.read',
    'training.teacher.manage',
    'training.class_group.read',
    'training.class_group.manage',
    'training.schedule.read',
    'training.schedule.manage',
    'training.exam.read',
    'training.exam.manage',
    'training.exam_result.read',
    'training.exam_result.manage',
    'training.manpower.read',
    'training.manpower.manage',
  ],
  administrator: [
    'training.teacher.read',
    'training.teacher.manage',
    'training.class_group.read',
    'training.class_group.manage',
    'training.schedule.read',
    'training.schedule.manage',
    'training.manpower.read',
    'training.manpower.manage',
  ],
  employee: [
    'training.teacher.read',
    'training.teacher.manage',
    'training.manpower.read',
    'training.manpower.manage',
  ],
  teacher: ['training.teacher.read'],
  agent: ['training.manpower.read', 'training.manpower.manage'],
  sub_agent: ['training.manpower.read', 'training.manpower.manage'],
  agency: ['training.manpower.read', 'training.manpower.manage'],
};

export const M5_PERMISSION_KEYS = [
  'training.teacher.read',
  'training.teacher.manage',
  'training.class_group.read',
  'training.class_group.manage',
  'training.schedule.read',
  'training.schedule.manage',
  'training.exam.read',
  'training.exam.manage',
  'training.exam_result.read',
  'training.exam_result.manage',
  'training.manpower.read',
  'training.manpower.manage',
] as const;

export const M6_PERMISSION_KEYS = [
  'overseas.medical.read',
  'overseas.medical.manage',
  'overseas.police_clearance.read',
  'overseas.police_clearance.manage',
  'overseas.arc.read',
  'overseas.arc.manage',
  'overseas.labour_contract.read',
  'overseas.labour_contract.manage',
  'overseas.visa.read',
  'overseas.visa.manage',
  'overseas.flight.read',
  'overseas.flight.manage',
  'operations.license.read',
  'operations.license.manage',
] as const;

export const M6_OVERSEAS_READ_KEYS = [
  'overseas.medical.read',
  'overseas.police_clearance.read',
  'overseas.arc.read',
  'overseas.labour_contract.read',
  'overseas.visa.read',
  'overseas.flight.read',
] as const;

export const M6_OVERSEAS_KEYS = M6_PERMISSION_KEYS.filter((key) => key.startsWith('overseas.'));

const M6_OPERATIONAL_KEYS = [...M6_PERMISSION_KEYS];
const M6_SCOPED_OVERSEAS_KEYS = M6_OVERSEAS_KEYS;

/**
 * Exact approved M6 runtime matrix (G3/G7).
 * Agent/sub_agent/agency labour-contract access is an intentional TARGET
 * authorization decision; not legacy parity. Do not remove or weaken.
 * Company/candidate/employer/teacher receive no M6 grant.
 * Teacher remains without candidate.read.
 */
export const M6_ROLE_GRANTS: Record<string, readonly string[]> = {
  owner: M6_OPERATIONAL_KEYS,
  administrator: M6_OPERATIONAL_KEYS,
  employee: M6_OPERATIONAL_KEYS,
  agent: M6_SCOPED_OVERSEAS_KEYS,
  sub_agent: M6_SCOPED_OVERSEAS_KEYS,
  agency: M6_SCOPED_OVERSEAS_KEYS,
};

export const M7_PERMISSION_KEYS = [
  'finance.read',
  'finance.wallet.read',
  'finance.payment_request.create',
  'finance.payment_request.approve',
  'finance.payment_request.reject',
  'finance.journal.read',
  'finance.reconciliation.read',
  'finance.administration',
  'finance.fx_rate.manage',
] as const;

const M7_STAFF_KEYS = [...M7_PERMISSION_KEYS];
const M7_AGENT_KEYS = [
  'finance.read',
  'finance.wallet.read',
  'finance.payment_request.create',
] as const;

/**
 * Exact M7 runtime matrix. No *.delete. Teacher/company/candidate/employer/agency
 * receive no finance keys. Agent/sub_agent are wallet-scoped in access helpers.
 */
export const M7_ROLE_GRANTS: Record<string, readonly string[]> = {
  owner: M7_STAFF_KEYS,
  administrator: M7_STAFF_KEYS,
  employee: [
    'finance.read',
    'finance.wallet.read',
    'finance.payment_request.create',
    'finance.payment_request.approve',
    'finance.payment_request.reject',
    'finance.journal.read',
    'finance.reconciliation.read',
  ],
  agent: M7_AGENT_KEYS,
  sub_agent: M7_AGENT_KEYS,
};
