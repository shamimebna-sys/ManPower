# Legacy Role Mapping Foundation

Legacy numeric IDs remain evidence for later migration; they are not new-system role IDs.
Each new role has a generated UUID and stable string key. Mapping will be recorded through
the approved `legacy_key_map` mechanism during M13.

| Legacy ID | Legacy label | Proposed stable key | Status |
|---:|---|---|---|
| 1 | Super Admin | `super_admin` | Foundation key confirmed for bootstrap |
| 2 | Administrator | `administrator` | Proposed; must be approved with final grants |
| 100 | Normal User | `normal_user` | Proposed; no merge assumed |
| 101 | Agent | `agent` | Proposed; business role not created in M2 |
| 102 | Candidate | `candidate` | Proposed; business role not created in M2 |
| 103 | Teacher | `teacher` | Proposed; business role not created in M2 |
| 104 | Employee | `employee` | Proposed; business role not created in M2 |
| 105 | Employer | `employer` | Proposed; business role not created in M2 |
| 106 | Owner | `owner` | Proposed; business role not created in M2 |
| 107 | Company | `company` | Proposed; zero grants does not imply unused |
| 108 | Agency | `agency` | Proposed; business role not created in M2 |
| 109 | Sub Agent | `sub_agent` | Proposed; business role not created in M2 |
| 110 | Others | `others` | Proposed; no merge assumed |

**A07 is DECISION LOCKED.** Target keys are `super_admin`, `administrator`, `owner`,
`agent`, `sub_agent`, `candidate`, `employer`, `company`, `agency`, `employee`, `teacher`.
Design Gate CSV spellings (`sadmin`, `admin`, `user`, `sub.agent`) are compatibility
aliases only. `normal_user` and `others` are not created. Roles 105 and 107 have zero
dump grants.

Numeric IDs are migration lineage only. They are never used in `requirePermission()`.

Row-scope lock: `docs/PRE-M4-HARD-GATE.md`.
