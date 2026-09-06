# M4 Permission Catalogue

**A07 status: DECISION LOCKED**

24 keys. IAM keys remain `super_admin` only.  
Row-scope helper: `apps/api/src/auth/candidate-access.ts`.  
Recruitment record: `docs/M4-RECRUITMENT.md`.

| Role key | Permission | Scope | Runtime grant | Policy |
|---|---|---|---|---|
| `super_admin` | all 24 keys | `all` | granted + bypass | global |
| `administrator` | candidate + partners + employer_candidate (not IAM) | `all` | granted | global where permitted |
| `owner` | candidate + partners + employer_candidate (not binding, not IAM) | `all` | granted | global where permitted |
| `employee` | `candidate.read/create/update` | `all` | granted | global only with the key |
| `agent` | `candidate.read`, `candidate.update` | `candidates.agent_id = bound Agent.source_legacy_id` | granted | scoped |
| `sub_agent` | `candidate.read`, `candidate.update` | `candidates.sub_agent_id = bound SubAgent.source_legacy_id` | granted | scoped |
| `agency` | `candidate.read`, `candidate.update` | `candidates.agencier_id = bound Agencier.source_legacy_id` | granted | scoped |
| `company` | `candidate.read` | `candidates.companier_id = bound Companier.source_legacy_id` | granted | scoped |
| `candidate` | `candidate.read`, `employer_candidate.read` | own candidate UUID | granted | self |
| `employer` | `employer_candidate.read/manage` | own employer UUID | granted | assignment only |
| `teacher` | none | BLOCKED | none | blocked pending M5 |

Defined keys:

`iam.user.read`, `iam.user.manage`, `iam.user_role.manage`, `iam.role_permission.manage`, `iam.audit.read`, `candidate.read`, `candidate.create`, `candidate.update`, `candidate.status.manage`, `candidate.education.read`, `candidate.education.manage`, `candidate.experience.read`, `candidate.experience.manage`, `candidate.skills.read`, `candidate.skills.manage`, `candidate.languages.read`, `candidate.languages.manage`, `candidate.training.read`, `candidate.training.manage`, `partners.read`, `partners.manage`, `partners.user_binding.manage`, `employer_candidate.read`, `employer_candidate.manage`

No `*.delete` keys. Frontend hiding is not authorization.
