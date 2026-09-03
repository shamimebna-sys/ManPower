# M3 — Candidate Core / Candidate Master

## Scope

M3 implements the candidate master domain only. It does not migrate the 1,679 production
candidates, does not implement recruitment workflow, finance, documents storage, or any
later processing stage.

## Domain model

- PostgreSQL schema: `candidate`
- Table: `candidate.candidates`
- Primary key: UUID
- Status: legacy account codes `A` (active), `P` (pending), `I` (inactive)
- Hard delete is not implemented. Deactivation uses `PATCH /api/v1/candidates/:id/status`
  with `I`.
- Relationship IDs (`agent_id`, `class_group_id`, `agencier_id`, `companier_id`, payment IDs,
  address geography IDs) are stored as opaque integers. No speculative foreign keys.
- File fields store private references (legacy JSON/path text). They are never public URLs.
- `balance` is `NUMERIC(20,6)` and is preserved for later finance reconciliation. M3 does
  not post or calculate balances.
- Profile child tables (`educations`, `experiences`, `language_list`, `skill_list`, etc.)
  remain separate domains. Their JSON snapshots on the candidate row are preserved. Child
  CRUD is not part of M3.

## Traceability

`migration.legacy_key_map` exists for later migration:

- source_system
- source_table
- source_id
- target_type
- target_id
- migration_run_id
- source_row_hash
- migrated_at

M3 does not insert production lineage rows.

Existing M2 databases must apply `20260904004500_m3_candidate_core` and then run
`pnpm db:ensure-permissions` so candidate permission keys exist on `super_admin`.

## Code generation

Legacy codes are `agent.code + padded Candidate.max(id)`. Agent codes are unavailable until
the partners module. New records without an explicit code use the confirmed fallback prefix
`9` plus a 6-digit sequence from PostgreSQL `candidate.candidate_code_seq` (`nextval`).
This is not `COUNT+1`. Migrated legacy codes are stored as-is and are never reallocated.
The sequence starts at 1. If fallback codes already exist in an environment, operators
must `setval` to the current maximum numeric suffix of existing `9######` codes before
creating new rows. No live PostgreSQL 16 data existed for this gate, so no `setval`
value was computed from a database.

## Required create fields

Confirmed from `VoyagerCandidatesController::store()`:

- name
- email
- mobile
- passport_no
- agent_id
- class_group_id

`code` is generated when omitted. Unique constraints apply to code, email, mobile, and
passport number.
