# M5 PRE-GATE — Training / Exam

**Investigation date:** 2026-09-07  
**Owner decisions recorded:** 2026-09-07  
**Owner PRE-GATE approvals recorded:** 2026-09-07  
**Prerequisites:** M1–M4 COMPLETE. PostgreSQL 16 CI PASS on commit `4160aae`.  
**This document:** evidence and decisions only. No schema, migration, API, UI, permission, candidate-access, or Laravel change.

Milestone numbering is unchanged. **M5 remains Training / Exam.** Previous locked decisions (A05, A07, A20, M4 Recruitment COMPLETE) are not reopened.

`platform/docs/DESIGN-GATE.md` is not in this checkout. The authoritative Design Gate pack used here is `modernization-design/final-design-gate/` (`00-project-rules.md`, `01-table-mapping.md`, `02-relationships.md`, `04-workflow.md`, `05-rbac.md`, `11-milestones-risks-approvals.md`, and the companion CSVs).

---

## 1. Executive status

**M5 PRE-GATE OWNER APPROVAL STATUS: APPROVED**

**M5 IMPLEMENTATION STATUS: NOT STARTED**

**M5 GO/NO-GO IMPLEMENTATION GATE: NO-GO**

Owner signed T1, T2, T4, and A06 on 2026-09-07. On the same date the owner also approved T5 option B, T7 map acceptance, orphan quarantine, the exact 12-key catalogue, the runtime-grant-as-separate-gate rule, PG16 as a hard implementation prerequisite, the IDOR test plan, the workflow test plan, and M13 traceability. Normative package: `docs/M5-FINAL-PRE-GATE.md`. Implementation must not begin. This recording task does **not** change the implementation gate to GO. Administrator exam-grant widening is **NOT APPROVED**. Runtime grants stay at the current M4 minimum. Catalogue approval is not a runtime grant.

| Item | Decision | Lock status |
|---|---|---|
| **M5-T1** Teacher → `candidate.read` | **C** — teacher has **NO** `candidate.read` | **DECISION LOCKED** (2026-09-07) |
| **M5-T2** `exams.class_group_id` | Normalize to **`exam_class_groups`** join table | **DECISION LOCKED** (2026-09-07). Not applied |
| **M5-T3** Sentinels | Empty schedule FKs use `0` in code; dump has **no** schedule `0`; score `0` is a mark; dump ungraded `result` is **NULL**; FAIL UI forces group `1` (exists: Admission For Interview); no `-1` | Semantics **CONFIRMED**; frequencies in T8 profile |
| **M5-T4** Exam result semantics | Manual PASS/FAIL; five marks; no automatic pass-mark formula; PASS may update `candidates.class_group_id`; FAIL does not; PASS/FAIL does not update `candidates.status`; publishing creates missing result rows | **DECISION LOCKED** (2026-09-07). Not applied |
| **M5-T5** Teacher binding | **APPROVED B** — `operations.teachers` + exclusive `iam.users.teacher_id`. Self-scope `teacher.id = current_user.teacher_id`. Identity ≠ ACL | **APPROVED** (2026-09-07) — not applied |
| **M5-T6 / A06** Training tables | **KEEP SEPARATE** — `trainings` and `manpower_trainings` remain separate domains | **DECISION LOCKED** (2026-09-07) |
| **M5-T7** Relationship map | Declared vs observed vs inferred vs unresolved — see `M5-FINAL-PRE-GATE.md` §2 | **APPROVED** (map accepted). Evidence **PROFILED**. Not applied |
| **M5-T8** Production dump | SHA verified; M5 tables profiled | **PROFILED** 2026-09-06 UTC. Import still blocked until M13 quarantine |
| **M5-T9** Permission matrix | Exact 12-key matrix **APPROVED** in `M5-FINAL-PRE-GATE.md`. Teacher **no** `candidate.read`. Administrator **no** exam keys | **APPROVED** catalogue — runtime grants **not seeded** |
| **M5-T10** Workflow | Class-group assignment / PASS can change the **live-status label**, not `candidates.status`. BMET checkbox ≠ live-status step 9 | Documented. No workflow-engine change |

Do not grant `teacher` `candidate.read`. Do not implement M5.

---

## 2. Evidence inventory

### 2.1 Sources actually read

| Kind | Path |
|---|---|
| Design Gate | `modernization-design/final-design-gate/{00,01,02,04,05,11}-*.md` |
| Design Gate CSVs | `column-mapping.csv`, `relationships.csv`, `source-references.csv`, `rbac-role-grants.csv`, `rbac-permissions.csv`, `rbac-hardcoded-role-ids.csv` |
| Metrics / register | `evidence-metrics.json`, `evidence-register.md` |
| Target closeout | `platform/docs/M4-RECRUITMENT.md`, `M4-PERMISSIONS.md`, `M4-CANDIDATE-SUPPORTING-DOMAINS.md`, `A05-AGENCY-COMPANY-EVIDENCE.md`, `PRE-M4-HARD-GATE.md` |
| Target schema (read-only) | `platform/apps/api/prisma/schema.prisma` — no Teacher / ClassGroup / Exam / ExamResult models; `iam.users` has agent / sub_agent / agencier / companier / candidate / employer UUIDs only — **no `teacher_id`**; `candidate.trainings` exists as `CandidateTraining` |
| Target grants (read-only) | `platform/apps/api/src/iam/permission-catalogue.ts` — `teacher: []` |
| Dump evidence file | `docs/database-audit/production-dump.sha256` = `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8` |
| Dump bytes | **Present locally** (Git-excluded): `docs/database-audit/u410970153_eujobbd.sql`. SHA-256 **verified on bytes** 2026-09-06. Profile: `docs/M5-T8-PRODUCTION-PROFILE.md`. |
| Teacher | `src/app/Models/Teacher.php` |
| ClassGroup | `src/app/Models/ClassGroup.php` |
| ClassSchedule | `src/app/Models/ClassSchedule.php` |
| Exam | `src/app/Models/Exam.php` |
| ExamResult | `src/app/Models/ExamResult.php` |
| Training / ManpowerTraining | `src/app/Models/Training.php`, `ManpowerTraining.php` |
| Candidate | `src/app/Models/Candidate.php` (`boot`, `classGroup`, `latestExamResult`, `latestExamResultAny`) |
| Controllers | `VoyagerExamController`, `VoyagerExamResultController`, `VoyagerClassScheduleController`, `VoyagerTeacherController`, `VoyagerMyPanelController`, `VoyagerNotificationsController`, `VoyagerCandidatesController`, `VoyagerReportController`, `VoyagerUserController` |
| Helpers | `src/app/Helpers/CommonClass.php` (`user()`, `liveStatus()`, `bmetStatus()`, `manpowerStatus()`) |
| Actions | `ExamResultPublishAction`, `ClassScheduleAction` |
| Views | `exams/edit-add.blade.php`, `exam-results/browse.blade.php`, `exam-results/pdf.blade.php`, `candidate-partial-top-menu.blade.php`, public `candidates.blade.php` / `candidate-details.blade.php` |
| Widgets | `TodaysCalss.php`, `UpcomingInterviewSchedule.php` |

### 2.2 Production SQL availability

**Available locally and SHA-verified** on 2026-09-06. File remains Git-excluded (~223 MB). Counts below are from the T8 byte parse (`docs/M5-T8-PRODUCTION-PROFILE.md`). They were **not invented**.

| Table | Rows | PK unique | Source |
|---|---:|---:|---|
| `candidates` | 1,679 | 1,679 | T8 dump parse (matches Design Gate) |
| `users` | 2,154 | 2,154 | T8 (matches A05) |
| `teachers` | 2 | 2 | T8 (ids 1, 3; matches A05 count) |
| `class_groups` | 63 | 63 | T8 |
| `class_schedules` | 3 | 3 | T8 |
| `exams` | 28 | 28 | T8 |
| `exam_results` | 18,854 | 18,854 | T8 |
| `trainings` | 389 | 389 | T8 (matches M4) |
| `manpower_trainings` | 352 | 352 | T8 (matches M4) |
| `announcement_class_groups` | 3,060 | 3,060 | T8 (M11) |
| `notification_class_groups` | 600,830 | 600,830 | T8 (M11) |
| `employer_candidates` | 0 | — | A20 / Design Gate (not re-parsed in T8) |

---

## 3. M5-T1 through M5-T10

### M5-T1 — Teacher → candidate access

**Lock status: DECISION LOCKED (2026-09-07).**  
**Owner decision: C — teacher has NO `candidate.read`.**  
**Do not implement. Do not grant at runtime.**

#### Competing rules (document only)

| Rule | Definition | Verdict |
|---|---|---|
| **A** | Teacher sees candidates only through **`class_schedules` membership** (`teacher_id` + `class_group_id` → candidates in that group) | Only **data** path Teacher → Group → Candidate. **Not necessarily an ACL.** Teacher has **no** Voyager `class_groups` or `class_schedules` grants. Those BREAD keys are sadmin + owner. Using A as `candidate.read` scope would be a **new** policy inferred from a timetable, not how legacy authorizes candidate lists. |
| **B** | Teacher sees candidates through **schedules UNION exams** (any group on a schedule **or** any group listed on an exam) | **Unsafe / rejected.** `exams` has **no `teacher_id`**. `VoyagerExamController` selects audience by exploded `class_group_id` only. A teacher with exam grants already browses every exam. Unioning exam groups would be equivalent to **global candidate access** whenever any exam lists those groups. |
| **C** | Teacher receives **no `candidate.read`** | **LOCKED.** Matches M4 fail-closed teacher role (`teacher: []`). Teacher’s evidenced job is exams + exam_results (+ announcements/notifications, M11), not the candidate master. The Exam Result Sheet already loads people via `exam_results.candidate_id` after publish materializes rows. |

#### Why B is unsafe

- `column-mapping.csv`: `exams` columns are `id`, `name`, `exam_date`, `exam_time`, `class_group_id`, `status`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`, `exam_link`. **No `teacher_id`.**
- Exam create/update writes a comma list of groups, then notifies every candidate in those groups. There is no teacher owner filter.
- Legacy teacher already has global `browse/read/edit/add` on `exams` and `exam_results` (`rbac-role-grants.csv`, role 103). Combining that with exam-group membership as a candidate ACL would leak the entire exam audience.

#### Why A is not necessarily an ACL

- The only Teacher ↔ Group join found is `class_schedules` (`Teacher::schedules()` `hasMany`; `ClassSchedule::teacher()` `belongsTo`; `ClassScheduleAction` opens create with `?teacher_id=`; widgets JOIN `class_schedules` → `teachers` + `class_groups`).
- There is **no** `candidates.teacher_id` and **no** `teacher_class_groups` table (90-table Design Gate inventory).
- Teacher has **zero** `class_groups` / `class_schedules` permission grants. Schedule is a timetable / report source, not an authorization table.
- `Candidate::boot` scopes only roles `101`, `108`, `109` — **not** `103` (`Candidate.php:85`).
- `Teacher::boot` self-scope is **commented out** (`Teacher.php:46–54`).
- Legacy teacher **can** `browse_candidates` / `edit_candidates` with **no** teacher `where` (Rule 1 vs Rule 5). That unscoped grant is a confirmed defect; do not silently reproduce it.

#### Identity vs access

`users.teacher_id` binds a login to a teacher person (`CommonClass::user()` sets `profile_id = teacher_id` for role 103; `VoyagerTeacherController` writes `teacher_id` on user create). That is **not** class-group or candidate access.

#### Decision (owner, 2026-09-07)

**C — Teacher has NO `candidate.read`.** Rule 5 wins over Rule 1 for the unscoped legacy grant. **B is forbidden.**

If a later **named** approval grants any teacher candidate visibility, the **only** acceptable membership join is **A** (active `class_schedules` with real `teacher_id` and `class_group_id`, not `0`). That later grant is not this decision.

Exam / result keys, when later considered, must still not imply `candidate.read`. Scope those keys separately (fail-closed if unbound).

#### Confidence

**High.** Owner signed C.

#### Remaining uncertainty

Dump would still show whether every teaching relationship is a schedule row. That does not reopen C.

---

### M5-T2 — `exams.class_group_id`

**Lock status: DECISION LOCKED (2026-09-07).**  
**Owner decision: APPROVE `exam_class_groups`.** Normalize exam → class group into a proper join table.  
**Do not implement in this task.**

#### Evidence

- DDL / `column-mapping.csv`: `exams.class_group_id` is `varchar(200)` NULL, **not** an integer FK. Design Gate incorrectly proposes target `bigint` FK for this column. Runtime contradicts that mapping.
- Create: `VoyagerExamController::store` takes an **array**, `implode(',', array_filter(...))`, merges back as one string (`VoyagerExamController.php:27–37`). Update does the same (`:91–94`).
- UI: `exams/edit-add.blade.php` `name="class_group_id[]"` `multiple="multiple"`. Reload accepts comma string **or** JSON array starting with `[` (`:85–91`).
- Read path: `Exam::classGroup()` `belongsTo` cannot represent many groups; `withDefault` explodes commas and concatenates `group_info` (`Exam.php:10–21`).
- Result sheet: `explode(",", $exam->class_group_id)` then `Candidate::whereIn('class_group_id', …)` (`VoyagerExamResultController.php:38`).
- Notifications on exam create: loop exploded ids (`VoyagerExamController.php:57–70`).
- `relationships.csv` still lists `exams.class_group_id` → `class_groups.id` as a **single** INFERRED edge. That is a naming hypothesis, not a 1:1 FK.

No other encoding (pipe, JSON-as-primary-store) is used on write. JSON decode is only a read fallback in the edit view.

#### Finding

The column represents **zero or more** class groups as a **comma-separated id list**. It is not a single FK.

#### Decision (owner, 2026-09-07)

**APPROVE `exam_class_groups`.** Target authority is `exam_class_groups (exam_id, class_group_id)` unique pair. Preserve the raw source varchar string in lineage if M13 hash parity requires it. Do **not** store a comma-separated varchar as the target authority.

This lock is a design decision only. No Prisma model or migration is created here.

#### Confidence

**High** on encoding and on the approved join table. T8: **0** of 28 exam rows contain a comma (values `"1"`×25, `"2"`×2, `"10"`×1).

#### Remaining uncertainty

A future exam may still store `1,2,3`. That does not reopen T2. Import must still split on comma.

---

### M5-T3 — Sentinel values

**Lock status:** code semantics **CONFIRMED**; dump frequencies **PROFILED** (T8, 2026-09-06).

#### Evidence and meaning (from code only)

| Location | Value | Meaning |
|---|---|---|
| `class_schedules.teacher_id` | `0` when empty | `VoyagerClassScheduleController.php:52` — **not a teacher**. Invalid FK. |
| `class_schedules.class_group_id` | `0` when empty | Same file `:53` — **not a class group**. |
| `class_schedules.teacher_id` / `class_group_id` | NULL allowed by DDL | Unused by that store path (writes `0` instead). |
| `exams.class_group_id` | `''` / filtered empties | `array_filter` + skip `empty($classGroupId)` — no audience. |
| `exam_results.result` | `''` | UI “Select One” (`browse.blade.php:197`). **Ungraded.** |
| `exam_results.result` | `PASS` / `FAIL` | Only two result options in the sheet UI and PDF. |
| `exam_results` scores | default `0`; UI `min=0` `max=100` | Integer marks, not “unknown”. **Score `0` is a legitimate mark.** |
| FAIL group UI | `group_id` forced to **`1`** | `changeResult()` sets `$('#group_id_'+id).val(1)` and disables other options (`browse.blade.php:487–494`). Group id `1` is a hard-coded FAIL destination in the browser. Server-side: if `result != 'PASS'`, `class_group_id` is forced back to the **existing result row** value (`VoyagerExamResultController.php:244–246`) — FAIL does **not** write the candidate master; the UI still snaps the dropdown to `1`. |
| Status columns (`class_groups`, `exams`, `exam_results`, `teachers`, `trainings`, `manpower_trainings`) | default `'A'` | Active. Parallel to candidate/partner `A`/`I`. No other status enum found in these controllers. |
| `class_schedules.status` | `char(1)` default `'A'` | Required on create. |
| `-1` | **Not found** in M5 controllers/models reviewed | Do not invent a −1 sentinel. |

T8 dump frequencies (this dump only):

- Schedule `teacher_id` / `class_group_id` = `0`: **0 / 3**.
- Candidate `class_group_id` NULL / empty / `0` / orphan: **0**.
- `exam_results.result`: NULL **18,616**; `PASS` **129**; `FAIL` **109**; `''` **0**.
- Score `0` present (e.g. `abroad_ex` ZERO 124). Score NULL is the ungraded majority.
- `-1`: **0** on schedule FKs, candidate group, and exam scores.
- Group `1` exists: `Admission For Interview` (code `801`), 76 candidates.

#### Finding

`0` on schedule FKs is an empty sentinel in **code**; this dump does not contain schedule `0`. Score `0` is a mark. Dump ungraded `result` is **NULL** (UI empty string is the same meaning). FAIL UI uses class group **1**, which exists as **Admission For Interview** — not a dedicated “fail batch” name.

#### Decision (import guidance only — not implemented)

On import: quarantine schedule `0`; do not mint UUIDs for `0`. Treat score `0` as `0`. Treat empty result as ungraded, not FAIL. Do not assume group `1` is universal without dump confirmation. Target must not persist schedule FK `0`.

#### Confidence

**High** on code semantics and on this dump’s frequencies. Another dump replacement would require a new T8 pass.

---

### M5-T4 — Exam result semantics

**Lock status: DECISION LOCKED (2026-09-07).**  
**Do not implement in this task.**

#### Evidence

1. **Manual PASS/FAIL.** UI options are empty / `PASS` / `FAIL` (`exam-results/browse.blade.php:196–199`). Result is **not** computed from marks.
2. **Five marks:** integers `abroad_ex`, `local_ex`, `bl`, `skill`, `english` (DDL default `0`). Sheet inputs `min=0` `max=100`. **No pass-mark formula** found in PHP (the only “passing” hit is education `passing_year` on the profile, unrelated).
3. **Public display:** public cards treat `abroad_ex` / `local_ex` as “years”; an English “Marks” line exists but is commented out (`candidate-details.blade.php:356–358`). Public `latestExamResult()` is `hasOne` where `result = 'PASS'` (`Candidate.php:66–68`).
4. **“Publish”** = `ExamResultPublishAction` navigates to `voyager.exam-results.index?exam_id=`. Opening the sheet **auto-inserts** missing result rows for every candidate in the exam’s exploded groups (`VoyagerExamResultController.php:28–45`). Status copied from `exam.status`. This is a write-on-browse side effect, not a separate published flag.
5. **PASS may update `candidates.class_group_id`.** JSON PUT: if `result == 'PASS'`, `candidates.class_group_id = $updatedData->class_group_id` (`:250–255`).
6. **FAIL does not.** If `result != 'PASS'`, request `class_group_id` is overwritten with the result’s existing value (`:244–246`). Candidate master is not updated.
7. **PASS/FAIL does not directly change `candidates.status`.** No assignment to `candidates.status` in exam/result controllers. Exam report filters `Candidate::where('status', 'A')` for display only.

#### Finding

Publication = open the sheet (materialize missing rows) + inline save. Grading is manual PASS/FAIL plus five marks. PASS **may** change `candidates.class_group_id`. Nothing in this path changes `candidates.status`. There is no separate published/unpublished flag beyond row `status` (copied from exam, default `A`).

#### Decision (owner, 2026-09-07)

**APPROVE** the following target business rules:

- Manual **PASS / FAIL** (plus empty = ungraded).
- **Five marks:** `abroad_ex`, `local_ex`, `bl`, `skill`, `english`.
- **No automatic pass-mark formula.** Marks do not compute result.
- **PASS may update** `candidates.class_group_id`.
- **FAIL does not update** `candidates.class_group_id`.
- **PASS/FAIL does not directly update** `candidates.status`.
- **Publishing creates missing result rows** (legacy open-sheet materialize is the approved publication behavior).

Do not apply these as schema or API work in this task.

#### Confidence

**High** on the signed rules. **Medium** on whether group `1` is the intended FAIL holding group (UI only; T8).

#### Remaining uncertainty

Whether operators always pick a new group on PASS, and whether group `1` exists, remain dump/operations questions. They do not reopen T4.

---

### M5-T5 — Teacher binding

**Lock status: APPROVED — Option B (2026-09-07).** Binding **not added**.  
See `docs/M5-FINAL-PRE-GATE.md` section 1. **Do not implement.**

#### Evidence

- `users.teacher_id` `int(11)` NULL (`column-mapping.csv`). `relationships.csv`: `users.teacher_id` → `teachers.id` **INFERRED**.
- Create: `VoyagerTeacherController` hard-codes `$role_id = 103`, creates user with `teacher_id = $data->id` (`:20`, `:52–60`).
- Update password: `User::where('teacher_id', $data->id)` (`:124`).
- `CommonClass::user()` maps 103 → `profile_id = teacher_id`, slug `teachers`.
- Target `iam.users` today: agent / sub_agent / agencier / companier / candidate / employer UUIDs only — **no `teacher_id`**.
- Class-group membership is **not** on the user or teacher row. It is on `class_schedules`.
- Multiple teachers per group: `class_schedules` is many rows of `(teacher_id, class_group_id, week_day, time, subject)`. Widgets order by group then teacher. **Yes, multiple teachers can share a group.** One teacher can have many groups. There is no unique `(teacher, group)` constraint in the mapping.

#### Finding

`users.teacher_id` is sufficient to bind a login to a **teacher person**. It is **not** sufficient to derive class-group or candidate access. Teacher identity must remain separate from class-group membership.

#### Decision (owner, 2026-09-07)

**APPROVED B:** separate `operations.teachers` master + exclusive `iam.users.teacher_id` UUID bind.

Rules: one user → at most one teacher; one teacher → at most one user; binding is identity only; teacher identity is **NOT** candidate authorization; teacher must **NOT** receive `candidate.read`; Rule B (teacher → generic candidate browsing) is **permanently forbidden**; identity does **not** imply class-group, schedule, exam, or exam-result access; teacher 3 may exist without a user (migrate unbound); missing teachers 2, 5, 6, 7, 8 are **not invented**; those legacy binds are quarantined at M13.

Teacher self-scope (**APPROVED**): if `training.teacher.read` is later granted, it **MUST** be `teacher.id = current_user.teacher_id`.

Do **not** add the binding now. Full text: `docs/M5-FINAL-PRE-GATE.md` section 1.

#### Confidence

**High.**

#### Remaining uncertainty

T8: all 6 role-103 users have a `teacher_id`; 5 of those ids are missing from `teachers`. Teacher 3 has no user. One user per `teacher_id` value. Binding still must not be added in this recording task.

---

### M5-T6 / A06 — Training separation

**Lock status: DECISION LOCKED (2026-09-07) — KEEP SEPARATE.**

#### Evidence

| | `trainings` | `manpower_trainings` |
|---|---|---|
| Rows (committed) | **389** | **352** |
| Owner | `user_id` (candidate login) | `candidate_id` (candidate master) |
| Columns | title, institute, topics, dates, duration, country text, descriptions, achievements, certificate path, address | certificate / manpower / fingerprint **files**, certificate issue/expire, `training_start_date`, `traininig_end_date` (typo retained), status |
| Writer | `VoyagerMyPanelController` profile (role 102) | Voyager BREAD `manpower-trainings`; `bmetStatus()` = latest row exists |
| Teacher | None | None |
| Target today | `candidate.trainings` (`CandidateTraining`) | **Not modeled** |
| Design Gate | `profile.trainings` KEEP | `workflow.manpower_training_events` KEEP |
| UI | CV / profile | Candidate top menu **“BMET Train.”** |

`01-table-mapping.md` collision note: *“`trainings` is profile training; `manpower_trainings` is candidate process evidence. KEEP in separate modules.”*

`manpowerStatus()` used for live-status step 9 is a **PaymentRequest** (`bill_title` like Manpower), **not** `manpower_trainings`. BMET completion ≠ manpower payment.

Singular Voyager slugs `manpower_training` and plural `manpower_trainings` both have grants. No second model/table was found in `src/app/Models`. Treat singular as leftover BREAD name.

#### Finding

Different business concepts: profile CV history vs process/BMET file evidence. Different owners, columns, writers, and row counts.

#### Decision (owner, 2026-09-07)

**A06 KEEP SEPARATE.** `trainings` and `manpower_trainings` remain separate domains. Do not merge.

- `trainings` = candidate CV / profile training (already `candidate.trainings` / M3).
- `manpower_trainings` = BMET / process evidence (target `workflow.manpower_training_events` when M5 is GO).

This pre-gate records the owner decision. The Design Gate file `11-milestones-risks-approvals.md` is not edited here.

#### Confidence

**High.** Owner signed KEEP SEPARATE.

#### Remaining uncertainty

Whether manpower_trainings is delivered in M5 or M6 overseas (Design Gate M5 text includes it; live-status step 9 does not use the table). That is a delivery-packaging question, not a merge.

---

### M5-T7 — Ownership / FK profiling

**Lock status: APPROVED — documented relationship map accepted (2026-09-07).**  
T8 **OBSERVED** data edges remain evidence, not declared FKs. Do **not** upgrade inferred relationships into declared facts. Do **not** invent `teacher → candidate`. Do not enforce NOT NULL / RESTRICT until M13 quarantines orphans. Normative map: `docs/M5-FINAL-PRE-GATE.md` section 2.

Declared MariaDB FKs in the dump are **9** total and do **not** include these M5 edges.

| Table | Authoritative candidate / person link | How | Label |
|---|---|---|---|
| `users.teacher_id` | Teacher person | → `teachers.id` | **INFERRED** |
| `trainings.user_id` | Indirect candidate | → `users.id` → `users.candidate_id` → `candidates.id` | **INFERRED** |
| `manpower_trainings.candidate_id` | Direct | → `candidates.id` | **INFERRED** |
| `class_groups` | Inverse | `candidates.class_group_id` → `class_groups.id` (one group per candidate) | **INFERRED** |
| `class_schedules.teacher_id` | Teacher | → `teachers.id` | **INFERRED** |
| `class_schedules.class_group_id` | Group | → `class_groups.id` | **INFERRED** |
| `class_schedules` → candidates | None | Candidates attach only via the group | **INFERRED** (two hops) |
| `exams.class_group_id` | Indirect, many groups | exploded varchar list → `class_groups.id` | **INFERRED** (and not a single FK) |
| `exam_results.exam_id` | Exam | → `exams.id` | **INFERRED** |
| `exam_results.candidate_id` | Direct | → `candidates.id` | **INFERRED** |
| `exam_results.class_group_id` | Group snapshot | varchar used as one id | **INFERRED** |

**No** `teachers.id` → `candidates.*`.  
**No** `teachers.id` → `exams.*`.  
**No** `teacher_class_groups`.

#### Orphan risks (logical, not counted)

- `trainings.user_id` with no `users.candidate_id`
- `manpower_trainings.candidate_id` missing candidate
- `candidates.class_group_id` `0` / NULL / missing group
- `class_schedules.teacher_id` or `class_group_id` = `0`
- Comma tokens in `exams.class_group_id` that are not `class_groups.id`
- `exam_results` exam / candidate / group missing
- `users.teacher_id` missing teacher

T8 observed orphans (this dump):

- `exam_results.candidate_id` missing candidate: **17,079 / 18,854**
- `trainings.user_id` missing user: **273 / 389**
- `manpower_trainings.candidate_id` missing candidate: **5 / 352**
- `users.teacher_id` missing teacher: **5 / 6** non-null values (2, 5, 6, 7, 8)
- `class_schedules.teacher_id` missing teacher: **1 / 3** (teacher_id 2)
- `exams.class_group_id` orphan tokens: **0**
- `candidates.class_group_id` orphan / 0 / NULL: **0**

#### Decision (owner, 2026-09-07)

**APPROVED** — documented relationship map accepted. Preserve declared / observed / inferred / unresolved. No teacher→candidate edge. Document lineage through `legacy_key_map` at M13. Quarantine orphan rows. Do not enforce target FKs as NOT NULL on first load.

#### Confidence

**High** on this dump’s counts.

---

### M5-T8 — Production dump

**Lock status: PROFILED** (bytes verified + parsed 2026-09-06 UTC).  
Normative report: `docs/M5-T8-PRODUCTION-PROFILE.md`.

Dump filename `u410970153_eujobbd.sql`. SHA-256 **verified on bytes:** `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`. Parse errors: 0. No invented counts. Dump file not imported and not modified.

Headline counts: `class_groups` 63; `class_schedules` 3; `exams` 28 (varchar values `"1"`×25, `"2"`×2, `"10"`×1; **0 commas**); `exam_results` 18,854; teachers ids **1** and **3** only.

T8 complete as **evidence** does not authorize implementation or production import. M13 must quarantine the orphan rates in T7 / the T8 report before any load.

---

### M5-T9 — Permission matrix

**Lock status: APPROVED exact catalogue** in `docs/M5-FINAL-PRE-GATE.md` section 4. **Not created. Not granted.**  
A07 is **DECISION LOCKED**. Runtime keys only. No numeric IDs. Current target: `teacher: []`.

**Do not grant teacher exam permissions. Do not seed runtime grants in this task.**  
**Administrator exam grants: automatic widening is NOT APPROVED (2026-09-07).**

#### Legacy grants (CONFIRMED `permission_role`)

| Stable key | Class groups | Schedules | Teachers | Exams | Exam results | Manpower trainings | Candidates (M5-relevant) |
|---|---|---|---|---|---|---|---|
| `super_admin` (sadmin) | full | full | full | full | full | full | full |
| `administrator` (legacy admin **2**) | **none** for these BREAD slugs | none | none | **none** | **none** | none | (admin has users/BREAD, not these modules) |
| `owner` | full | browse/edit/add (not all read/delete) | browse/edit/add/delete | browse/read/edit/add | browse | full | browse/edit |
| `employee` | none | none | full CRUD | none | none | browse/read/edit/add | browse/read/edit/add |
| `teacher` | **none** | **none** | browse/read | browse/read/edit/add | browse/read/edit/add | **none** | browse/edit (**unscoped** — Rule 5 defect) |
| `agent` / `agency` / `sub_agent` | none | none | none | none | none | browse/edit/add (not always read) | browse/edit (already scoped in target) |
| `company` | none | none | none | none | none | none | `candidate.read` only (M4 companier scope) |
| `candidate` | none | none | none | none | none | none | self + profile `trainings` (M3) |
| `employer` | none | none | none | none | none | none | none (employer_candidate only) |

Teacher also has announcement/notification grants (M11, not M5).

#### Approved catalogue (names only — not created, not granted)

Do not grant `teacher` `candidate.read` (**T1 LOCKED**).  
Do not grant `teacher` exam / exam_result keys in this gate.  
Do **not** give target `administrator` exam keys by automatic widening (legacy admin 2 has **none**). Owner rejected automatic widening on 2026-09-07. The exact catalogue is **APPROVED**; runtime seeding remains a separate gate.

| Key (approved names only) | `super_admin` | `administrator` | `owner` | `employee` | `teacher` | agent / agency / sub_agent | others |
|---|---|---|---|---|---|---|---|
| `training.teacher.read` | yes | yes | yes | yes | self only | no | no |
| `training.teacher.manage` | yes | yes | yes | yes | no | no | no |
| `training.class_group.read` | yes | yes | yes | no | no | no | no |
| `training.class_group.manage` | yes | yes | yes | no | no | no | no |
| `training.schedule.read` | yes | yes | yes | no | no until GO + T1 + scope | no | no |
| `training.schedule.manage` | yes | yes | yes | no | no | no | no |
| `training.exam.read` / `manage` | yes | **no** (widening blocked) | yes | no | **no** | no | no |
| `training.exam_result.read` / `manage` | yes | **no** (widening blocked) | yes | no | **no** | no | no |
| `training.manpower.read` / `manage` | yes | yes | yes | yes | no | scoped to their candidates | no |

Legacy employee has no class-group/schedule/exam grants — default **no**.  
No `*.delete`. Profile `candidate.training.*` already exists (M3). IAM keys stay `super_admin` only.

#### Decision (owner, 2026-09-07)

Exact 12-key matrix and role cells are **APPROVED** as catalogue in `M5-FINAL-PRE-GATE.md` section 4. Runtime grant seeding is **APPROVED as a separate gate**: catalogue approval does **not** activate runtime grants. Seeding occurs only during M5 implementation after authorization tests exist. Teacher has no `candidate.read` (T1). Teacher exam keys stay ungranted. Administrator exam keys stay **NOT APPROVED**.

---

### M5-T10 — Workflow

#### Evidence

| Event | Writes `candidates.status`? | Other effect |
|---|---|---|
| Assign / change `class_group_id` (candidate form) | **No** | Notify candidate + agent (`VoyagerCandidatesController.php:431–438`). `class_group_id` required on create/update. |
| `liveStatus()` | **No** | If group name contains `Rapid` → step 4; any named group → step 3 (`CommonClass.php:564–568`). **Label only.** |
| Exam PASS | **No** | **Yes:** may set `candidates.class_group_id`, which can change the live-status **label** (e.g. into/out of Rapid). This is **not** a `candidates.status` transition. |
| Exam FAIL / empty | **No** | No candidate master group write. |
| Profile `trainings` save | **No** | CV only. |
| `manpower_trainings` row exists | **No** | Top-menu **BMET Train.** completed (`bmetStatus()`). **Not used in `liveStatus()`.** |
| Manpower **payment** | **No** | `liveStatus()` step 9 uses `manpowerStatus()` = PaymentRequest (`bill_title` like Manpower), **not** the training table. Finance / M6–M7. |

`04-workflow.md` already lists “Exam result PASS → group promotion (`class_group_id` change)” as a transition **not** on the live-status ladder, separate from account status P→A / A→I.

Downstream readers of exam/training:

- Public candidate cards: latest **PASS** scores
- Exam report / employer report: `latestExamResultAny`
- Teacher Schedule / Exam Result Sheet reports
- Exam-create notifications (M11)

#### Finding

Training/Exam does **not** change candidate account status (`A`/`P`/`I`). It **does** change class-group membership (manual or PASS), which can change the derived live-status **label**. BMET training existence is a process checkbox, not live-status step 9. Manpower payment is a different table. **Do not conflate BMET checkbox / payment logic with live-status step 9.**

#### Decision

Do not implement workflow-engine changes in M5. When exams are built, PASS group-move must stay an explicit, audited candidate-group update — not a status transition. The M5 workflow test plan is **APPROVED** in `docs/M5-FINAL-PRE-GATE.md` section 8 (tests not written). Do not invent a new business workflow until legacy parity is verified.

#### Confidence

**High.**

#### Remaining uncertainty

Business name of group 1 and Rapid groups (dump). A03/A04 live-status labels remain OPEN at Design Gate and are not M5’s to close.

---

## 4. Legacy table mapping

| Source | Decision | Target (proposed) | Evidence |
|---|---|---|---|
| `class_groups` | **KEEP** | `operations.class_groups` | Model, candidate FK, exams, schedules, reports, liveStatus |
| `class_schedules` | **KEEP** | `operations.class_schedules` | Model, controller, widgets, Teacher Schedule report |
| `exams` | **KEEP** (storage **SPLIT** approved: header + join) | `operations.exams` + `exam_class_groups` | T2 LOCKED 2026-09-07; not applied |
| `exam_results` | **KEEP** | `operations.exam_results` | Separate BREAD; scores + PASS/FAIL |
| `trainings` | **KEEP** | already `candidate.trainings` | Profile child; 389 rows |
| `manpower_trainings` | **KEEP** | `workflow.manpower_training_events` | 352 rows; BMET; do not MERGE with `trainings` |
| `teachers` | **KEEP** | `operations.teachers` | 2 rows; person master; `users.teacher_id` |
| `notification_class_groups` | **KEEP** | communications (M11) | Out of M5 write path |
| `announcement_class_groups` | **KEEP** | M11 | Out of M5 write path |
| `manpower_training` (slug only) | **IGNORE** as a second table | — | Permission slug leftover; no model |
| `temp_candidates` | **ARCHIVE** | already Design Gate ARCHIVE | Not M5 API |
| Agency / Company snapshots | **KEEP separate** (A05) | not M5 | Do not model here |
| Teacher `balance` / payments | **out of M5** | finance | A01/A02/A17 |

No MERGE of `trainings` with `manpower_trainings`. No generic Partner.

---

## 5. Relationship diagram (text)

```
iam/login
  users.teacher_id .............. teachers.id                 [INFERRED]
  users.candidate_id ............ candidates.id               [INFERRED]
  trainings.user_id ............. users.id                    [INFERRED]

teachers
  └── class_schedules.teacher_id                              [INFERRED]
        └── class_schedules.class_group_id --> class_groups.id [INFERRED]
              └── candidates.class_group_id                    [INFERRED]
                    ├── manpower_trainings.candidate_id        [INFERRED]
                    └── exam_results.candidate_id              [INFERRED]

exams.class_group_id  = "id1,id2,…" --> class_groups.id       [NOT a single FK; INFERRED list]
exam_results.exam_id ---------------> exams.id                [INFERRED]
exam_results.class_group_id --------> class_groups.id         [varchar; used as one id; INFERRED]

NO teachers.id --> candidates.*
NO teachers.id --> exams.*
NO teacher_class_groups

M11 (not M5):  announcements/notifications <-> class_groups
M7  (not M5):  payments.teacher_id; payment_requests.group_id; class_groups.fee_amount
```

Teacher → Class Group → Candidate exists **only** through `class_schedules`. That path is not an ACL in legacy.

---

## 6. Data-quality findings

Supported by T8 dump profile + prior committed evidence:

- `teachers` = 2 rows (ids 1, 3). KEEP. Id 2 is referenced by a schedule and a user but has no teacher row.
- `trainings` = 389 vs `manpower_trainings` = 352: different owners; 273 vs 5 orphans.
- `candidates` = 1,679; **all** have a living `class_group_id` (0 null/0/orphan).
- Schedule store *can* write `0`; this dump has **no** schedule `0`. One schedule points at missing teacher 2.
- `exams.class_group_id`: 28 rows, **0 commas** in this dump; still varchar; T2 join table remains locked.
- `exam_results` = 18,854; 17,079 candidate orphans (deleted candidate ids).
- FAIL UI hard-codes group `1`, which exists as **Admission For Interview** (76 candidates).
- `manpower_trainings.traininig_end_date` typo must keep lineage.
- Teacher `boot` scope commented out; teacher candidate grants unscoped. T1 **C** still locked.

---

## 7. Proposed target model (documentation only)

PostgreSQL schema `operations` unless noted. **Not applied. No Prisma changes.**

| Table | Notes |
|---|---|
| `teachers` | UUID PK, `source_legacy_id`, person fields, status `A`/`I`. No payments. |
| `class_groups` | UUID PK, name, code, description, status, optional `fee_amount` lineage only. |
| `class_schedules` | UUID PK, `teacher_id` UUID FK, `class_group_id` UUID FK, subject, times, week_day, status. Reject `0`. |
| `exams` | UUID PK, name, date, time, link, remarks, status. **No** varchar multi-id. |
| `exam_class_groups` | T2 LOCKED. exam_id + class_group_id unique. **Not applied.** |
| `exam_results` | exam_id, candidate_id, class_group_id, five scores, result, remarks, status. Unique `(exam_id, candidate_id)` proposed. |
| `workflow.manpower_training_events` | candidate_id, dates, file refs, status. |
| `candidate.trainings` | **Already exists.** Do not recreate. |
| `iam.users.teacher_id` | T5 option B **APPROVED**. Column added only after a separate implementation GO. **Not applied.** |

No Partner/Tenant. No snapshots. No finance writes.

---

## 8. Approved permission matrix (documentation only)

See `docs/M5-FINAL-PRE-GATE.md` section 4 for the exact **APPROVED** catalogue. Summary:

- A07 keys only. No `103` at runtime.
- Teacher: **no** `candidate.read` (T1 LOCKED). `training.teacher.read` self-only. Exam/result keys **ungranted**.
- Owner + super_admin: all 12 keys (catalogue only).
- `administrator` exam / exam_result keys: **NOT APPROVED**. Teacher / class_group / schedule / manpower only.
- Employee: teachers + manpower training. Not class/exam.
- Agent / agency / sub_agent: manpower training inside existing candidate scope.
- Company / employer / candidate: no new M5 permissions.
- `candidate.training.*` remains M3. No delete keys. No runtime `ensure-permissions` change in this task.
- Runtime grant seeding is a **separate** approved gate and is **not** executed here.

---

## 9. Hard-gate decisions

### Locked / approved on 2026-09-07 (not applied)

| ID | Owner decision |
|---|---|
| **M5-T1** | **C** — teacher has NO `candidate.read` (**LOCKED**) |
| **M5-T2** | **APPROVE `exam_class_groups`** (**LOCKED**, not applied) |
| **M5-T4** | **APPROVE** manual PASS/FAIL; five marks; no pass-mark formula; PASS may update `class_group_id`; FAIL does not; PASS/FAIL does not update `candidates.status`; publishing creates missing result rows (**LOCKED**, not applied) |
| **A06** | **KEEP SEPARATE** — `trainings` ≠ `manpower_trainings` (**LOCKED**) |
| **M5-T5** | **APPROVED — Option B** (not implemented) |
| **M5-T7** | **APPROVED** — documented relationship map accepted |
| **Orphan quarantine policy** | **APPROVED** — M13 executes later; counts unchanged |
| **Exact M5 permission matrix** | **APPROVED** catalogue — 12 keys; not seeded |
| **Runtime grant seed list** | **APPROVED as a separate gate** — not seeded |
| **PG16 M5 integration readiness** | **APPROVED** as a hard implementation prerequisite |
| **IDOR test plan** | **APPROVED** — tests not written |
| **Workflow test plan** | **APPROVED** — tests not written |
| **M13 migration traceability** | **APPROVED** — M13 gate remains separate |
| **Administrator exam-grant widening** | **NOT APPROVED.** Must not be granted |

### Still blocking implementation GO

| ID | Why it remains open |
|---|---|
| **Implementation GO** | Owner PRE-GATE approvals are recorded. A **separate** M5 implementation GO is still required |
| **Runtime grant seeding** | Separate approved gate; not executed; authorization tests must exist first |
| **Administrator exam keys** | Remain **NOT APPROVED** |
| **M13 execution** | Quarantine + `legacy_key_map` approved as policy; import not started |
| **A08** | Timezone |
| **M5-T10 / A03 / A04** | Live-status label effects of group moves are label-only; formal A03/A04 remain Design Gate OPEN |

Non-blocking constraints already recorded here:

- No generic Partner/Tenant
- No Agency/Company snapshots in M5
- No finance / payroll / attendance / deployment
- No production import
- No Laravel or dump edits
- **B is rejected** as a teacher access rule
- **C is locked** — no teacher `candidate.read`

---

## 10. M5 GO / NO-GO statement

| Check | Result |
|---|---|
| M1–M4 complete; PG16 CI `4160aae` | PASS (prerequisite) |
| A05 / A07 / A20 untouched | PASS |
| T1 = **C** (no teacher `candidate.read`) | **LOCKED** 2026-09-07 |
| Rule B rejected | **LOCKED** (T1) |
| T2 = `exam_class_groups` | **LOCKED** 2026-09-07 — not applied |
| T4 publication / PASS / FAIL rules | **LOCKED** 2026-09-07 — not applied |
| A06 KEEP SEPARATE | **LOCKED** 2026-09-07 |
| T5 teacher identity (option B) | **APPROVED** — not implemented |
| T7 relationship map | **APPROVED** — documented map accepted |
| T8 dump profile | **PROFILED** — SHA verified; see `docs/M5-T8-PRODUCTION-PROFILE.md` |
| Orphan quarantine policy | **APPROVED** — see `docs/M5-FINAL-PRE-GATE.md` §3 |
| Administrator exam-grant widening | **NOT APPROVED** |
| Exact M5 permission matrix | **APPROVED** catalogue — not seeded |
| Runtime grant seed list | **APPROVED** as a separate gate — not seeded |
| PG16 M5 integration readiness | **APPROVED** as a hard implementation prerequisite |
| IDOR test plan | **APPROVED** — tests not written |
| Workflow test plan | **APPROVED** — tests not written |
| M13 migration traceability | **APPROVED** — M13 gate separate |
| Runtime grants / schema / APIs unchanged (this task) | PASS |

**M5 PRE-GATE OWNER APPROVAL STATUS: APPROVED**

**M5 IMPLEMENTATION STATUS: NOT STARTED**

**M5 GO/NO-GO IMPLEMENTATION GATE: NO-GO**

T1, T2, T4, A06 remain **LOCKED**. T5, T7, orphan quarantine, the exact catalogue, the runtime-grant-as-separate-gate rule, PG16 prerequisite, IDOR plan, workflow plan, and M13 traceability are now **APPROVED**. T8 remains **PROFILED**. Administrator exam keys remain **NOT APPROVED**. Implementation is **not** authorized by this recording task.

Normative checklist: `docs/M5-FINAL-PRE-GATE.md` section 10.

### Remaining before implementation GO

- Separate owner **implementation GO** decision
- Runtime grant seeding only after authorization tests exist
- Administrator exam / exam_result keys stay **NOT APPROVED**
- M13 execution remains a later gate

### This task did not start implementation

- No implementation started
- No Prisma changes
- No migration
- No API changes
- No UI changes
- No permission runtime changes
- No Laravel changes
- No production import

Do not implement Prisma models, migrations, APIs, UI, permissions, teacher bindings, teacher `candidate.read`, exam APIs, training APIs, or workflow changes until the implementation gate is explicitly **GO**.
