# M4 — Candidate Supporting Domains

## Scope

M4 adds candidate-owned profile children on top of the M3 candidate master:

- Education (`educations`)
- Experience (`experiences`)
- Skills (`skills`) and skill tags (`skill_list`)
- Languages (`language_list`)
- Profile training (`trainings`)

M4 does **not** implement:

- `manpower_trainings` (BMET/manpower process evidence — later workflow milestone)
- document upload/storage
- agents/agencies/companies
- recruitment workflow
- finance, labour, police, medical, visa, BMET, ARC, flight, invoices, payments

No production rows are imported.

## Legacy evidence (authoritative)

Profile children are written by `VoyagerMyPanelController` for role_id=102 (candidate)
against `user_id = Auth::user()->id`. `Candidate::user()` is `hasOne(User, candidate_id)`.
Target records therefore FK to `candidate.candidates.id`. Migration later resolves
`educations.user_id → users.candidate_id → candidate UUID`.

| Source table | Rows | Owner column | Role |
|---|---|---|---|
| `educations` | 907 | `user_id` | Candidate education history |
| `experiences` | 1003 | `user_id` | Candidate work history |
| `skills` | 289 | `user_id` | Candidate skill/certificate rows (title, institute, scores) |
| `skill_list` | 0 | `user_id` | User-scoped skill **names**, not a global master |
| `language_list` | 1403 | `user_id` | Candidate language + free-text `language_status` |
| `trainings` | 389 | `user_id` | Profile training history |
| `manpower_trainings` | 352 | `candidate_id` | Process/BMET files — **out of M4** |

Confirmed distinctions:

- `skills` and `skill_list` are different tables and are not merged.
- `skill_list` is **not** a skill catalogue. A Skill master → CandidateSkill design is
  not supported by evidence (table is user-scoped and empty).
- `language_list` is **not** a language master. There are no speaking/reading/writing
  columns. Proficiency is free text (`Very Good`, `Good`, `Basic`, `Little`,
  `Only For Speaking`).
- `trainings.country_id` and `experiences.country_id` are `varchar(250)` and store
  values such as `Bangladesh`. They are **not** foreign keys.
- Legacy profile update **deletes and re-inserts** child rows. Target M4 does not
  hard-delete; it uses status `A`/`I` and PATCH.

## Target schema

PostgreSQL schema `candidate`:

- `educations`
- `experiences`
- `skills`
- `skill_list`
- `language_list`
- `trainings`

Each has a UUID PK, `candidate_id` UUID FK (`ON DELETE RESTRICT`), timestamps,
optional `source_user_id` (legacy `user_id`), and status `A`/`I`.
`status` on `skill_list` and `language_list` is a **target-only** lifecycle field
(source tables had none) so archive is possible without hard delete.

### Target-only status on `skill_list` and `language_list`

| Item | Rule |
|---|---|
| Source column | None. Do not invent a source value. |
| Migration default | `A` (active) for every imported row |
| New API default | `A` |
| Allowed values | `A` (active), `I` (inactive / archived) |
| Hard delete | Not implemented |
| Semantics | `I` hides the row from normal profile use without destroying it. Imported empty or dirty rows stay `A` unless a later cleanse sets `I`. |
| `language_status` | Unrelated free-text proficiency (`Very Good`, …). Do not confuse with lifecycle `status`. |

## API

Nested under `/api/v1/candidates/:candidateId/...`

Lists are cursor-paginated (`limit` 1–100, default 50). Candidate existence is
checked first. Child PATCH verifies the row belongs to that candidate (404 otherwise).

## Permissions

See `docs/M4-PERMISSIONS.md` and `docs/PRE-M4-HARD-GATE.md`.
**A07 status: DECISION LOCKED.** Runtime grants: `super_admin` only.
Row-scope helper is implemented; partner IDs are not attached yet.

## Audit

Mutations write events in the same transaction. Metadata is IDs and field names only.

## Known limitations

- Agent-scoped candidate access is not implemented.
- Date order on PATCH is validated only when both dates are in the request body.
- `skill_list` duplicate rejection applies to **new active** writes only; no unique
  index (dirty legacy rows must remain importable).
- Certificate file refs are stored as private text. No upload in M4.
- `manpower_trainings` is not modeled.
