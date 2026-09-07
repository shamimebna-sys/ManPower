# M5 — Training / Exam

**Status: IMPLEMENTED** (implementation GO 2026-09-07)  
**Prerequisites:** M1–M4 COMPLETE. M5 PRE-GATE / FINAL PRE-GATE APPROVED.  
**Normative decisions:** `docs/M5-PRE-GATE.md`, `docs/M5-FINAL-PRE-GATE.md`. Those locks are not reopened.

M5 is target-platform implementation only. No Laravel changes. No production import. M13 quarantine items remain: Q-ER-CAND, Q-TR-USER, Q-MT-CAND, Q-USR-TCH, Q-SCH-T2, Q-TCH-3.

## Models

PostgreSQL schemas `operations` and `workflow`.

| Model | Table | Notes |
|---|---|---|
| `Teacher` | `operations.teachers` | Person/identity master. No payments. |
| `ClassGroup` | `operations.class_groups` | Identity, name, code, status. `fee_amount` is lineage only. |
| `ClassSchedule` | `operations.class_schedules` | Optional FKs for M13 orphans. API create requires teacher + group. Rejects sentinel `0`. |
| `Exam` | `operations.exams` | No varchar multi-id. |
| `ExamClassGroup` | `operations.exam_class_groups` | Normalized exam ↔ group. Unique pair. |
| `ExamResult` | `operations.exam_results` | Unique `(exam_id, candidate_id)`. Scores nullable. `result` NULL = ungraded. |
| `ManpowerTrainingEvent` | `workflow.manpower_training_events` | BMET / process evidence. Not `candidate.trainings`. |

`iam.users.teacher_id` is exclusive (one user ↔ one teacher). Teacher identity is **not** candidate authorization.

`candidate.class_group_id` (BigInt lineage) is unchanged. `candidate.class_group_ref_id` is the target UUID FK used by PASS.

M13 maps source rows through existing `migration.legacy_key_map`.

## Workflow (T4 locked)

- Publish/open materializes missing result rows for candidates in the exam’s groups. Transactional and idempotent.
- Five marks: `abroadEx`, `localEx`, `bl`, `skill`, `english`. Score `0` is a real score. NULL is unentered.
- PASS / FAIL are manual. No pass-mark formula.
- PASS may update `candidates.class_group_ref_id` and, when present, `candidates.class_group_id` from the destination group’s `source_legacy_id`.
- FAIL does not update the candidate group. Legacy FAIL UI group 1 is UI-only.
- Neither PASS nor FAIL writes `candidates.status`.
- Manpower training is BMET evidence. Live-status step 9 remains PaymentRequest (not this table).

## Permissions

See `docs/M5-PERMISSIONS.md`. Administrator has no exam keys. Teacher has `training.teacher.read` self-scoped only and no `candidate.read`.

## API

| Method | Path |
|---|---|
| GET/POST/PATCH | `/api/v1/teachers` |
| GET/POST/PATCH | `/api/v1/class-groups` |
| GET/POST/PATCH | `/api/v1/class-schedules` |
| GET/POST/PATCH | `/api/v1/exams` |
| PUT | `/api/v1/exams/:id/class-groups` |
| POST | `/api/v1/exams/:id/publish` |
| GET/POST/PATCH | `/api/v1/exam-results` |
| GET/POST/PATCH | `/api/v1/manpower-trainings` |
| PUT | `/api/v1/iam/users/:userId/bindings` with `domain: "teacher"` |

IDOR uses the existing 404-when-hiding-existence convention. Missing permission is 403.

## UI

`/app/teachers`, `/app/class-groups`, `/app/schedules`, `/app/exams`, `/app/exam-results`, `/app/manpower-trainings`. Nav is permission-gated. UI hiding is not authorization.
