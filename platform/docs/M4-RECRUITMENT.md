# M4 Recruitment

**Status: implemented on the target platform only.**

Live masters: Agent, SubAgent, Agencier, Companier.  
Snapshots (Agency, Company) are not modeled.  
Employer is a Rapid Interview person identity for `employer_candidates`.  
Teacher candidate access remains **BLOCKED**.

## Company scope — established

Evidence used:

- A05: `users.companier_id` and `candidates.companier_id` both point at Companier
- Candidate `belongsTo` Companier
- Role `company` profile is `companier_id`, not `companies.id`

Target rule:

`company` → `candidates.companier_id = bound Companier.source_legacy_id`

No global company visibility. Scope is empty until the UUID binding resolves a `source_legacy_id`.

## Teacher scope — BLOCKED

Missing safe M4 evidence:

- `Candidate::boot` does not row-scope role 103
- Teacher↔candidate would require `class_schedules` / exams / class groups (M5)
- A05 places Teacher outside Recruitment except as a login role
- Granting `candidate.read` would be global, which is forbidden

M4 therefore creates the `teacher` role with **zero** grants and leaves `resolveCandidateAccess({ roles: ['teacher'] })` as `none`.

## Binding rule

A user may bind to only one of:

`agent` | `sub_agent` | `agencier` | `companier` | `candidate` | `employer`

Bindings use UUID target IDs. Candidate row-scope still compares the existing M3 BigInt partner columns to `source_legacy_id`.
