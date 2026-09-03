# M3 Candidate Permission Catalogue

These keys are stable strings. Legacy numeric role IDs are never used at runtime.

**A07 status: DECISION LOCKED**

Grant/scope table: `docs/M4-PERMISSIONS.md` and `docs/PRE-M4-HARD-GATE.md`.

| Permission key | Allows | Locked target access |
|---|---|---|
| `candidate.read` | List and read candidate master records | `super_admin` / `administrator` / `owner` global; `employee` only if this key is granted; `agent` / `sub_agent` / `agency` scoped; `candidate` self; `employer` / `company` / `teacher` not this key |
| `candidate.create` | Create candidate master records | staff + scoped partners when later granted; not employer/company/teacher |
| `candidate.update` | Update candidate fields except status | same row-scope as read for granted roles |
| `candidate.status.manage` | Change A/P/I status; no hard delete | staff when granted |

Runtime grants: `super_admin` only. Helper enforces the locked scopes. Partner IDs are not stored on IAM users yet.

No `candidate.delete` permission exists.

Existing M2 databases should run `pnpm db:ensure-permissions` so the keys exist on `super_admin`. The script does not grant these keys to any other role.
