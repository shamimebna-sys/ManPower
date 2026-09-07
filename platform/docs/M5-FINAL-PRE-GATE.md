# M5 FINAL PRE-GATE — owner approval package

**Date:** 2026-09-07  
**Purpose:** Record owner PRE-GATE approvals. No implementation.  
**Prerequisites:** M1–M4 COMPLETE (`4160aae`). T8 **PROFILED**.  
**Companions:** `docs/M5-PRE-GATE.md`, `docs/M5-T8-PRODUCTION-PROFILE.md`.

Milestone numbering is unchanged. **M5 remains Training / Exam.** Previously locked decisions (T1, T2, T4, A06) are not reopened.

**M5 PRE-GATE OWNER APPROVAL STATUS: APPROVED**

**M5 IMPLEMENTATION STATUS: NOT STARTED**

**M5 GO/NO-GO IMPLEMENTATION GATE: NO-GO**

Owner approvals below are documentation only. They do **not** authorize Prisma, migrations, API, UI, runtime grants, Laravel edits, production import, or M5 implementation. A separate implementation GO decision is required after this package is recorded.

---

## Status vocabulary

| Label | Meaning |
|---|---|
| **LOCKED** | Owner-signed earlier. Do not reopen. Not applied unless stated. |
| **APPROVED** | Owner-signed yes. Catalogue / policy / test-plan only unless stated. |
| **NOT APPROVED** | Owner rejected or withheld. |
| **PROFILED** | Dump evidence recorded. Not an implementation grant. |
| **PROPOSED** | Presented for owner sign-off. Unused once the item is signed. |
| **BLOCKED** | Cannot proceed until a named later gate (implementation GO, M13, or tests). |
| **NOT STARTED** | Implementation has not begun. |

Administrator exam-grant widening remains **NOT APPROVED**.

---

## 1. T5 — teacher identity model (APPROVED — Option B)

**Status: APPROVED — Option B. No implementation in this task.**

Separate `operations.teachers` person master with exclusive `iam.users.teacher_id` UUID binding.

```
operations.teachers
        |
        | exclusive identity binding
        v
iam.users.teacher_id
```

This matches the M4 one-identity pattern (`agent` | `sub_agent` | `agencier` | `companier` | `candidate` | `employer`). Teacher is a person master, not a Partner/Tenant and not a candidate ACL.

| Rule | Statement |
|---|---|
| Cardinality user → teacher | A user may bind to **at most one** teacher |
| Cardinality teacher → user | A teacher may be bound to **at most one** user |
| Binding purpose | **Identity only** |
| Candidate access | Teacher identity is **NOT** candidate authorization. Teacher must **NOT** receive `candidate.read` (T1 **LOCKED** C) |
| Rule B | **Permanently forbidden.** Teacher → generic candidate browsing is not allowed |
| Class groups / schedules / exams / results | Identity does **NOT** automatically imply access. Those permissions require explicit target relationships |
| Teacher 3 | Legacy teacher ID **3** exists with no user. Migrate the teacher identity later; **leave it unbound** |
| Missing teachers 2, 5, 6, 7, 8 | **Do not invent** teacher records. Those user bindings are quarantined at M13 (Q-USR-TCH; Q-SCH-T2) |

T8 living teachers: id **1** (one user) and id **3** (no user).

### Teacher self-scope (APPROVED)

If `training.teacher.read` is later granted to `teacher`, it **MUST** be self-scoped:

```
teacher.id = current_user.teacher_id
```

- Unbound teacher login → **zero rows**.
- Future teacher reads **must** enforce `teacher.id = current_user.teacher_id`.
- This scope is **not** candidate access and **not** class-group, schedule, exam, or exam-result access.

### Options not chosen

| Option | Why not |
|---|---|
| **A** — `teacher_id` only on `iam.users` | Login is not the person. Teacher 3 has no user. A05: `teachers` is a live person master. |
| **C** — Partner/Tenant or teacher→candidate | A05 forbids generic Partner/Tenant. T1 forbids inventing teacher→candidate access. |

Do **not** add Prisma columns or models now.

---

## 2. T7 — M5 relationship map (APPROVED)

**Status: APPROVED — documented relationship map accepted. No implementation in this task.**  
Evidence remains **PROFILED**. Acceptance is now **APPROVED**.

Preserve the distinction between **declared**, **observed**, **inferred**, and **unresolved** relationships.

- Do **not** upgrade inferred relationships into declared facts.
- Do **not** invent `teacher → candidate`.
- No teacher→candidate relationship may be introduced.

**Do not enforce target NOT NULL / RESTRICT until M13 quarantine runs.**

| Edge | Declared FK? | Data / code | Label |
|---|---|---|---|
| `users.role_id` → `roles.id` | **Yes** (`users_role_id_foreign`) | All 2,154 users have `role_id` | **Declared FK** (IAM, not M5-specific) |
| `candidates.replaced_by_candidate_id` → `candidates.id` | **Yes** | Candidate replacement | **Declared FK** (out of M5) |
| `users.teacher_id` → `teachers.id` | No | 1 of 6 non-null values exist (teacher 1). Five orphans | **Observed relationship** (partial) |
| `users.candidate_id` → `candidates.id` | No | 1,679 users have a candidate id | **Observed relationship** |
| `candidates.class_group_id` → `class_groups.id` | No | 1,679 / 1,679 resolve; 0 null / 0 / empty | **Observed relationship** |
| `class_schedules.teacher_id` → `teachers.id` | No | 2 of 3 resolve; schedule 1 → missing teacher 2 | **Observed relationship** (1 orphan) |
| `class_schedules.class_group_id` → `class_groups.id` | No | 3 / 3 resolve | **Observed relationship** |
| `exams.class_group_id` → `class_groups.id` | No | 28 / 28 single existing ids (`1`/`2`/`10`); varchar, 0 commas | **Observed relationship** (not a 1:1 DDL FK; T2 still splits to `exam_class_groups`) |
| `exam_results.exam_id` → `exams.id` | No | 18,854 / 18,854 resolve | **Observed relationship** |
| `exam_results.candidate_id` → `candidates.id` | No | 1,775 resolve; **17,079** missing | **Observed relationship** (high orphan) |
| `exam_results.class_group_id` → `class_groups.id` | No | 18,854 single ids; 0 comma | **Observed relationship** |
| `trainings.user_id` → `users.id` | No | 116 resolve; **273** missing | **Observed relationship** (high orphan) |
| `trainings` → candidate | No | Via `users.candidate_id` when the user exists | **Observed relationship** (two hops; 116 rows) |
| `manpower_trainings.candidate_id` → `candidates.id` | No | 347 resolve; **5** missing | **Observed relationship** |
| `teachers.id` → `candidates.*` | No | **No column** | **Unresolved / ABSENT** |
| `teachers.id` → `exams.*` | No | **No column** | **Unresolved / ABSENT** |
| Teacher → candidate via `class_schedules` | No | Groups 1 and 3 only; 79 candidates in those groups | **Observed timetable — not an ACL** |
| Teacher → candidate via exams UNION | No | Exams have no `teacher_id` | **Unresolved / forbidden as ACL** (T1 Rule B) |

Design Gate `relationships.csv` still labels most of these **INFERRED** (naming). T8 upgraded the data-bearing edges to **Observed**. Inferred-only (no dump proof of a new edge) is **not** added here and is **not** upgraded to declared.

**ABSENT edges must not be created in the target.**

---

## 3. Orphan quarantine policy (APPROVED)

**Status: APPROVED — quarantine policy. No implementation in this task.**  
Counts unchanged from T8.  
SHA `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`.  
M13 owns execution. M5 does not import, delete, repair, fabricate, or reassign.

Never silently repair, delete, fabricate, or reassign orphan records.

### Required preservation (every quarantined row)

- `source_system`
- `source_table`
- source primary key
- complete source payload
- `migration_run_id`
- `source_row_hash`
- quarantine reason
- migration timestamp / status as appropriate

Live migrated rows keep the same source-table / source-PK / payload / run-id / hash traceability (section 9).

**Reconciliation:** `LIVE TARGET ROWS + QUARANTINED ROWS = SOURCE TOTALS`. Counts must not disappear.

| ID | Source | T8 count | Category | Live target? | Quarantine? | Resolution |
|---|---|---:|---|---|---|---|
| Q-ER-CAND | `exam_results` where `candidate_id` ∉ `candidates.id` | **17,079** | `ORPHAN_FK` | No | Yes | Do not mint candidates. 18,854 = live + quarantined |
| Q-TR-USER | `trainings` where `user_id` ∉ `users.id` | **273** | `ORPHAN_FK` | No | Yes | Do not guess a candidate. 389 = live + quarantined |
| Q-MT-CAND | `manpower_trainings` where `candidate_id` ∉ `candidates.id` | **5** | `ORPHAN_FK` | No | Yes | Review 5 source ids. 352 = live + quarantined |
| Q-USR-TCH | `users.teacher_id` ∈ {2,5,6,7,8} | **5** of 6 populated | `ORPHAN_BIND` | User may migrate **unbound** | Yes (the bind) | Do not create teachers 2/5/6/7/8. 6 binds = 1 live + 5 quarantined |
| Q-SCH-T2 | `class_schedules.id = 1` (`teacher_id = 2`) | **1** | `ORPHAN_FK` | No | Yes | Do not invent teacher 2. 3 schedules = 2 live + 1 quarantined |
| Q-TCH-3 | `teachers.id = 3` with no user | **1** | `UNBOUND_IDENTITY` | **Yes** teacher row, no bind | Bind empty | Teacher may exist without a user. 2 teachers live |

Not quarantine: group 54 (NULL name/code) **KEEP** live (Rule 3). Schedule `0`: **0** rows in this dump.

---

## 4. Exact permission matrix (APPROVED)

**Status: APPROVED as proposed (catalogue). Runtime grants: not seeded.**  
Current runtime: 24 M4 keys. `teacher: []`. IAM keys stay `super_admin` only.

The approved catalogue does **not** automatically activate runtime grants (section 5).

`candidate.training.read` / `candidate.training.manage` remain the **M3 CV/profile** domain. They are **not** M5 keys and are **not** merged with manpower training.

No **DELETE** permission is introduced.

### 4.1 Twelve approved M5 keys (not created, not seeded)

| Key | Purpose |
|---|---|
| `training.teacher.read` | Read teacher person master |
| `training.teacher.manage` | Create/update teacher person master (no delete) |
| `training.class_group.read` | Read class groups |
| `training.class_group.manage` | Create/update class groups (no delete) |
| `training.schedule.read` | Read class schedules |
| `training.schedule.manage` | Create/update class schedules (no delete) |
| `training.exam.read` | Read exams and exam↔group membership |
| `training.exam.manage` | Create/update exams and `exam_class_groups` (no delete) |
| `training.exam_result.read` | Read exam results |
| `training.exam_result.manage` | Publish (create missing rows) and grade (no delete) |
| `training.manpower.read` | Read manpower / BMET evidence |
| `training.manpower.manage` | Create/update manpower training evidence (no delete) |

### 4.2 Approved role matrix (not seeded)

`G` = approved catalogue grant. `—` = no. Cells are **APPROVED** as catalogue. They are **not** runtime.

| Key | super_admin | administrator | owner | employee | teacher | agent / agency / sub_agent | company | candidate | employer |
|---|---|---|---|---|---|---|---|---|---|
| `training.teacher.read` | G (all) | G (all) | G (all) | G (all) | G (**self only**) | — | — | — | — |
| `training.teacher.manage` | G | G | G | G | — | — | — | — | — |
| `training.class_group.read` | G | G | G | — | — | — | — | — | — |
| `training.class_group.manage` | G | G | G | — | — | — | — | — | — |
| `training.schedule.read` | G | G | G | — | — | — | — | — | — |
| `training.schedule.manage` | G | G | G | — | — | — | — | — | — |
| `training.exam.read` | G | **—** | G | — | **—** | — | — | — | — |
| `training.exam.manage` | G | **—** | G | — | **—** | — | — | — | — |
| `training.exam_result.read` | G | **—** | G | — | **—** | — | — | — | — |
| `training.exam_result.manage` | G | **—** | G | — | **—** | — | — | — | — |
| `training.manpower.read` | G | G | G | G | — | G (**candidate-scoped**) | — | — | — |
| `training.manpower.manage` | G | G | G | G | — | G (**candidate-scoped**) | — | — | — |
| `candidate.read` (existing M4) | granted | granted | granted | granted | **NO** | granted (scoped) | granted (scoped) | self | **NO** |

**Preserved constraints**

- **SUPER_ADMIN:** all 12.
- **OWNER:** all 12.
- **ADMINISTRATOR:** teacher, class_group, schedule, manpower. **NO** `training.exam.read` / `manage`. **NO** `training.exam_result.read` / `manage`. Widening remains **NOT APPROVED**.
- **EMPLOYEE:** `training.teacher.read` / `manage` and `training.manpower.read` / `manage` only.
- **TEACHER:** `training.teacher.read` **ONLY**, self-scoped. **NO** `candidate.read`. **NO** generic candidate browsing. **NO** exam. **NO** exam_result.
- **AGENT / SUB_AGENT / AGENCY:** `training.manpower.read` / `manage`, **candidate-scoped**.
- **COMPANY / CANDIDATE / EMPLOYER:** no new M5 permissions.
- `candidate.training.*` remains M3 CV/profile.
- Manpower training remains a **separate** domain (`training.manpower.*`).

### 4.3 Teacher self-read (explicit)

`training.teacher.read` for role `teacher` = **self-only**.

```
teacher.id = current_user.teacher_id
```

Any future teacher read of the teacher master **must** enforce that predicate. Unbound → no rows. This is not exam access, not schedule access, and not `candidate.read`.

---

## 5. Runtime grant seed list (APPROVED as a separate gate)

**Status: APPROVED as a separate gate. Not implemented. Not seeded.**

The approved permission catalogue does **not** automatically mean runtime grants are active.

Runtime grant seeding will occur only during M5 implementation **after** the corresponding authorization tests exist.

Administrator `training.exam.*` and `training.exam_result.*` remain **NOT APPROVED**. They must **not** be granted.

Current runtime stays at the M4 minimum. `teacher: []`.

---

## 6. PG16 integration readiness (APPROVED prerequisite)

**Status: APPROVED as a hard implementation prerequisite. CI not altered.**

M5 implementation / UAT cannot proceed unless:

- PostgreSQL 16 integration environment is available
- Prisma migrations apply cleanly
- M5 integration tests run against real PostgreSQL 16
- FK / constraint behavior is tested
- authorization tests run against PostgreSQL 16
- workflow tests run against PostgreSQL 16

Existing successful PostgreSQL 16 CI evidence from prior milestones (`4160aae`) must remain intact.

This approval records the prerequisite. It is **not** proof that M5 migrations or M5 PG16 tests exist. Those do not exist yet.

---

## 7. IDOR test plan (APPROVED)

**Status: APPROVED test-plan requirement. Tests not written, not run.**

M5 must test that:

1. A teacher cannot access another teacher's record.
2. A teacher cannot browse candidates through M5 endpoints.
3. A teacher cannot access another teacher's schedules unless an explicit target relationship permits it.
4. Scoped agent / sub-agent / agency / company users cannot access records outside their candidate scope.
5. Candidate IDs, teacher IDs, class-group IDs, exam IDs, and exam-result IDs cannot be used to bypass authorization.
6. Cross-user UUID substitution returns 404/403 according to the platform's established authorization convention.
7. Missing / unbound teacher identities cannot accidentally gain access.
8. No endpoint relies solely on UI permission hiding.

Also retained from the earlier proposed list: teacher `candidate.read` denied; teacher exam/result denied; administrator exam/result denied; teacher self-scope `teacher.id = current_user.teacher_id`; unbound teacher sees zero teacher rows; agent manpower IDOR.

---

## 8. Workflow test plan (APPROVED)

**Status: APPROVED test-plan requirement. Tests not written, not run.**

M5 must verify documented legacy workflow semantics before implementation is considered complete.

Tests must cover:

- class-group assignment
- exam publishing / opening
- result creation
- ungraded result state
- score `0` as a real score
- PASS
- FAIL
- five-mark handling
- no pass-mark formula
- PASS may update `candidate.class_group_id`
- FAIL does not perform that update
- neither PASS nor FAIL changes `candidates.status`
- legacy group-1 behavior must be explicitly tested / documented
- manpower training / BMET semantics
- step-9 PaymentRequest distinction
- workflow audit events
- invalid transitions
- unauthorized transitions
- rollback / error behavior

Do not invent a new business workflow until legacy parity is verified and explicitly approved.

T4 remains **LOCKED**: publish creates missing result rows; PASS may update `class_group_id`; FAIL does not; neither updates `candidates.status`.

---

## 9. M13 migration traceability (APPROVED)

**Status: APPROVED. M13 execution remains a separate gate. No import in this task.**

M13 must preserve complete source-to-target traceability.

Required concept:

```
legacy_key_map(
  source_system,
  source_table,
  source_id,
  target_type,
  target_id,
  migration_run_id,
  source_row_hash,
  migrated_at
)
```

Rules:

- source table + source ID must remain traceable
- every migrated M5 record must have a deterministic trace
- quarantined records must also be traceable
- source payload must remain available for quarantined rows
- no silent data loss
- no fabricated relationships
- reconciliation must prove source totals (`LIVE + QUARANTINED = SOURCE`)
- migration must be repeatable / idempotent
- migration approval remains a separate M13 gate

---

## 10. Final approval checklist

| Item | Status |
|---|---|
| T1 — teacher has NO `candidate.read` (C); Rule B forbidden | **LOCKED** |
| T2 — `exam_class_groups` | **LOCKED** (not applied) |
| T3 — sentinel / score / ungraded / group-1 evidence | **PROFILED** |
| T4 — PASS/FAIL, five marks, no formula, PASS group-move, FAIL does not, no status write, publish creates rows | **LOCKED** (not applied) |
| T5 — option B teacher identity / exclusive bind / self-scope | **APPROVED** (not implemented) |
| T6 / A06 — KEEP SEPARATE | **LOCKED** |
| T7 — relationship map (section 2) | **APPROVED** (map accepted; evidence PROFILED) |
| T8 — production dump profile | **PROFILED** |
| Orphan quarantine policy (section 3) | **APPROVED** (M13 executes later) |
| Exact permission matrix (section 4) | **APPROVED** (catalogue; not seeded) |
| Runtime grant seed list (section 5) | **APPROVED** as a separate gate (not seeded) |
| Administrator exam-grant widening | **NOT APPROVED** |
| PG16 integration readiness (section 6) | **APPROVED** as a hard implementation prerequisite |
| IDOR test plan (section 7) | **APPROVED** (tests not written) |
| Workflow test plan (section 8) | **APPROVED** (tests not written) |
| Migration traceability (section 9) | **APPROVED** (M13 gate separate) |

**M5 PRE-GATE OWNER APPROVAL STATUS: APPROVED**

**M5 IMPLEMENTATION STATUS: NOT STARTED**

**M5 GO/NO-GO IMPLEMENTATION GATE: NO-GO**

This recording task does **not** change the implementation gate to GO. The next step is a separate M5 implementation GO decision.

---

## 11. What this package does not do

- No implementation
- No Prisma / migration / API / UI / Laravel / dump change
- No runtime permission change / no `ensure-permissions`
- No production import
- No administrator exam/exam_result grants
- No automatic implementation GO

**M5 PRE-GATE OWNER APPROVAL STATUS: APPROVED**

**M5 IMPLEMENTATION STATUS: NOT STARTED**

**M5 GO/NO-GO IMPLEMENTATION GATE: NO-GO**
