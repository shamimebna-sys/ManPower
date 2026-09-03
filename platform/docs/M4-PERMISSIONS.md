# M4 Permission Catalogue

**A07 status: DECISION LOCKED**

19 keys are unchanged. Runtime grants remain `super_admin` only.
Row-scope helper: `apps/api/src/auth/candidate-access.ts`.
Full lock: `docs/PRE-M4-HARD-GATE.md`.

Do not invent report, document, partner, or recruitment keys.

| Role key | Permission | Scope | Current runtime grant | Approved target policy | Unresolved item |
|---|---|---|---|---|---|
| `super_admin` | all 19 defined keys | `all` | granted + bypass | global | none |
| `administrator` | none yet | `all` if a key is later granted | none | global where permitted | grant list |
| `owner` | none yet | `all` if a key is later granted | none | global where permitted | grant list |
| `employee` | none yet | `all` only with that explicit key | none | global only with the key | grant list |
| `agent` | none yet | `candidates.agent_id = user.agent_id` | none | scoped | partner binding |
| `sub_agent` | none yet | `candidates.sub_agent_id = user.sub_agent_id` | none | scoped | partner binding |
| `agency` | none yet | `candidates.agencier_id = user.agencier_id` | none | scoped | partner binding |
| `candidate` | none yet | `candidates.id = user.candidate_id` | none | self | candidate UUID binding |
| `employer` | none; no `candidate.read` | none on candidate APIs | none | Recruitment workflow only | assignment keys |
| `company` | none | BLOCKED | none | blocked | Recruitment evidence |
| `teacher` | none | BLOCKED | none | blocked | training/exam evidence |

Defined keys (unchanged):

`iam.user.read`, `iam.user.manage`, `iam.user_role.manage`, `iam.role_permission.manage`, `iam.audit.read`, `candidate.read`, `candidate.create`, `candidate.update`, `candidate.status.manage`, `candidate.education.read`, `candidate.education.manage`, `candidate.experience.read`, `candidate.experience.manage`, `candidate.skills.read`, `candidate.skills.manage`, `candidate.languages.read`, `candidate.languages.manage`, `candidate.training.read`, `candidate.training.manage`

No `*.delete` keys. Frontend hiding is not authorization.
