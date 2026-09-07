# M5-T8 — Production dump profile

**Profiling timestamp (UTC):** 2026-09-06T19:26:33Z → 2026-09-06T19:26:36Z  
**Dump filename:** `u410970153_eujobbd.sql`  
**Dump path (local, not in Git):** `docs/database-audit/u410970153_eujobbd.sql`  
**SHA-256 (verified on bytes):** `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`  
**SHA-256 match:** **PASS** (matches `docs/database-audit/production-dump.sha256`)  
**Method:** read-only INSERT-tuple parse. No MariaDB import. Dump file not modified. Parse errors: **0**.

This profile does **not** authorize M5 implementation.

---

## 1. Exact source tables and row counts

| Table | INSERT headers | Rows | PK unique | PK min–max | AUTO_INCREMENT | PK duplicate rows |
|---|---:|---:|---:|---|---:|---:|
| `candidates` | 29 | **1,679** | 1,679 | 18–4,634 | 4,635 | 0 |
| `users` | 14 | **2,154** | 2,154 | 1–5,127 | 5,128 | 0 |
| `teachers` | 1 | **2** | 2 | 1–3 | 9 | 0 |
| `class_groups` | 1 | **63** | 63 | 1–63 | 64 | 0 |
| `class_schedules` | 1 | **3** | 3 | 1–3 | 4 | 0 |
| `exams` | 1 | **28** | 28 | 1–28 | 29 | 0 |
| `exam_results` | 43 | **18,854** | 18,854 | 1–18,854 | 18,855 | 0 |
| `trainings` | 2 | **389** | 389 | 9–4,343 | 4,344 | 0 |
| `manpower_trainings` | 3 | **352** | 352 | 1–367 | 368 | 0 |
| `announcement_class_groups` | 2 | **3,060** | 3,060 | 1–3,093 | 3,094 | 0 |
| `notification_class_groups` | 373 | **600,830** | 600,830 | 1–629,561 | 629,562 | 0 |

Committed closeout counts that this dump re-confirms: `candidates` 1,679; `users` 2,154; `teachers` 2; `trainings` 389; `manpower_trainings` 352.

`teachers` ids present: **1**, **3** (id **2** is absent). `teachers.AUTO_INCREMENT = 9` implies deleted teacher rows.

---

## 2. Declared vs observed vs inferred vs unresolved

| Edge | Declared FK in dump? | Observed in data | Label |
|---|---|---|---|
| `users.role_id` → `roles.id` | **Yes** (`users_role_id_foreign`) | 2,154 users have a `role_id` | **DECLARED** (IAM, not M5-specific) |
| `candidates.replaced_by_candidate_id` → `candidates.id` | **Yes** (`fk_candidates_replaced_by`) | Not an M5 training/exam edge | **DECLARED** (out of M5) |
| `users.teacher_id` → `teachers.id` | No | 1 of 6 non-null `teacher_id` values exist in `teachers` | **OBSERVED** (partial; 5 orphans) |
| `users.candidate_id` → `candidates.id` | No | 1,679 users have a non-null `candidate_id` (equals candidate row count) | **OBSERVED** |
| `candidates.class_group_id` → `class_groups.id` | No | 1,679 / 1,679 resolve; 0 null / 0 / empty | **OBSERVED** |
| `class_schedules.teacher_id` → `teachers.id` | No | 2 of 3 rows resolve; schedule 1 → teacher 2 **missing** | **OBSERVED** (1 orphan) |
| `class_schedules.class_group_id` → `class_groups.id` | No | 3 / 3 resolve; 0 zero | **OBSERVED** |
| `exams.class_group_id` → `class_groups.id` | No | 28 / 28 are a **single** existing id (`1`/`2`/`10`) | **OBSERVED** (varchar, currently 1:1) |
| `exam_results.exam_id` → `exams.id` | No | 18,854 / 18,854 resolve | **OBSERVED** |
| `exam_results.candidate_id` → `candidates.id` | No | **1,775** resolve; **17,079** missing | **OBSERVED** (high orphan) |
| `exam_results.class_group_id` → `class_groups.id` | No | 18,854 resolve as a single id; 0 comma | **OBSERVED** |
| `trainings.user_id` → `users.id` | No | **116** resolve; **273** missing | **OBSERVED** (high orphan) |
| `manpower_trainings.candidate_id` → `candidates.id` | No | **347** resolve; **5** missing | **OBSERVED** |
| `teachers.id` → `candidates.*` | No | **No column.** Not present | **UNRESOLVED / ABSENT** |
| `teachers.id` → `exams.*` | No | **No column.** Not present | **UNRESOLVED / ABSENT** |
| Teacher → candidate via `class_schedules` | No | Only groups **1** and **3** have schedules; **79** candidates sit in those groups | **OBSERVED timetable**, not an ACL |
| Teacher → candidate via exams UNION | No | Exams have no `teacher_id`. 25/28 exams list group `1` | **NOT a teacher-owned edge** |

No M5 operational FK (`class_schedules`, `exams`, `exam_results`, `trainings`, `manpower_trainings`, `users.teacher_id`) is a declared MariaDB constraint.

---

## 3. Orphan and sentinel findings

### 3.1 `candidates.class_group_id`

- NULL 0 / empty 0 / zero 0 / orphan 0.
- Every candidate has a living group. Top groups: `42` 649, `40` 356, `44` 302, `43` 156, `1` 76.
- Group **1** exists: name `Admission For Interview`, code `801`, status `A`.
- Group **54** exists with **NULL name and NULL code** (data-quality row).
- Candidate `status`: `A` 1,077; `I` 477; `P` 125.
- `candidates` has **no `teacher_id` column**.

### 3.2 Teachers / users / schedules

| Finding | Count |
|---|---:|
| `teachers` rows | 2 (ids 1, 3) |
| `users.role_id = 103` | 6 |
| Role 103 with NULL `teacher_id` | **0** |
| `users.teacher_id` NULL | 2,148 |
| `users.teacher_id` non-null | 6 (values 1, 2, 5, 6, 7, 8 — one user each) |
| `users.teacher_id` missing from `teachers` | **5** (2, 5, 6, 7, 8) |
| Teacher 3 with no user | **1** |
| `class_schedules.teacher_id = 0` | **0** |
| `class_schedules.class_group_id = 0` | **0** |
| Schedule → missing teacher | **1** (schedule 1 → teacher_id 2) |
| `-1` on schedule FKs / candidate group / exam scores | **0** |

Schedule rows (complete):

| id | teacher_id | class_group_id | subject | week_day |
|---:|---:|---:|---|---|
| 1 | 2 (orphan) | 3 Waiter Final Group | Class | Sunday |
| 2 | 1 (exists) | 3 Waiter Final Group | Class | Wednesday |
| 3 | 3 (exists, no user) | 1 Admission For Interview | Interview | Friday |

### 3.3 Exams (`class_group_id` varchar)

| Pattern | Count |
|---|---:|
| NULL / empty | 0 / 0 |
| Contains comma | **0** |
| JSON-ish (`[…]`) | **0** |
| Multi-token | **0** |
| Distinct raw values | 3: `"1"` 25, `"2"` 2, `"10"` 1 |
| Orphan tokens | **0** |

Production currently stores **one group id per exam**. Code still writes a comma-separated list. T2 join-table lock is not reopened.

### 3.4 Exam results

| `result` | Rows |
|---|---:|
| NULL (ungraded) | **18,616** |
| `PASS` | **129** |
| `FAIL` | **109** |
| empty string | **0** |

Five marks (NULL / 0 / other):

| Field | NULL | ZERO | OTHER |
|---|---:|---:|---:|
| `abroad_ex` | 18,677 | 124 | 53 |
| `local_ex` | 18,627 | 32 | 195 |
| `bl` | 18,620 | 8 | 226 |
| `skill` | 18,622 | 17 | 215 |
| `english` | 18,624 | 5 | 225 |

- Score **0 is present** and distinct from NULL. Confirms T3: 0 is a mark, not “unknown”.
- Ungraded in this dump is **`result` NULL**, not `''`. Code UI still uses empty “Select One”. Treat both as ungraded on import.
- `exam_id` orphans: **0**.
- `class_group_id` orphans: **0**. No commas on result group.
- Unique `(exam_id, candidate_id)`: **0 duplicate pairs**.
- `candidate_id` orphans: **17,079 / 18,854** (deleted candidate ids; candidate PK starts at 18 and has large gaps).

Snapshot compare (not a write-path proof):

| Living-candidate subset | n | vs current `candidates.class_group_id` | current `candidates.status` |
|---|---:|---|---|
| PASS | 33 | **33 differ, 0 match** | A 31, I 2 |
| FAIL | 12 | **12 differ** | A 12 |
| NULL result | 1,730 | (not used for T4) | A 1,675, I 55 |

Dump cannot see historical writes. T4 remains locked from code. Current PASS rows do not equal the candidate’s present group, which is consistent with a later group move and does **not** prove PASS wrote `candidates.status`.

### 3.5 Trainings vs manpower_trainings

| | `trainings` | `manpower_trainings` |
|---|---|---|
| Rows | 389 | 352 |
| Owner column | `user_id` (all non-null) | `candidate_id` (all non-null) |
| Owner orphans | **273** missing `users.id` | **5** missing `candidates.id` |
| Owner present + `users.candidate_id` set | **116** | n/a (direct candidate) |
| Owner present + no `users.candidate_id` | **0** | n/a |
| Status | all `A` | all `A` |
| Extra | duration `0`: 363; title NULL: 49 | 18 candidates have 2–3 rows (max 3) |

No shared key, no teacher owner, no row-count collision that would justify a merge. **A06 KEEP SEPARATE stands.**

### 3.6 Related audience joins (M11, not M5 write path)

- `announcement_class_groups`: 2 rows with a class_group_id missing from `class_groups`.
- `notification_class_groups`: 0 group orphans; 600,830 rows (volume is an M11 concern).

---

## 4. Impact on previously locked M5 questions

| Item | Dump impact | Lock |
|---|---|---|
| **T1** | No `candidates.teacher_id`. Schedules cover 2 groups / 79 candidates / 1 orphan teacher. Exams have no teacher owner. **No dump evidence that teacher→candidate is an ACL.** Rule **C** remains correct. | **Still LOCKED C** |
| **T2** | 0 multi-id exam rows today. Encoding remains varchar; code still implodes arrays. Join table still required so a future `1,2,3` does not break the target. | **Still LOCKED `exam_class_groups`** |
| **T3** | Schedule `0` unused in this dump. Score `0` exists. Ungraded `result` is NULL (not `''`). Group 1 exists (`Admission For Interview`). No `-1`. | Semantics confirmed + frequencies now recorded |
| **T4** | 129 PASS / 109 FAIL / 18,616 NULL. Five marks present. No formula in data. Dump cannot prove writes; living PASS/FAIL groups differ from current candidate group. Candidate status stays A/I/P independently. | **Still LOCKED** (code + owner) |
| **T5** | `users.teacher_id` is the identity column. 6 role-103 users; 5 bind to missing teachers; teacher 3 has no user. Identity ≠ membership. | Binding **not added** |
| **T6 / A06** | Different owners, columns, orphan rates. Do not merge. | **Still LOCKED KEEP SEPARATE** |
| **T7** | Edges above are now **OBSERVED** or **ABSENT**. Still **not declared FKs**. Do not add NOT NULL / RESTRICT until M13 quarantines orphans. | Observed; constraints deferred |
| **T8** | Dump verified and profiled. | **PROFILED** (this document) |

---

## 5. Unresolved findings and recommendations

| Item | Finding | Recommendation |
|---|---|---|
| Exam-result candidate orphans | 17,079 result rows point at missing `candidates.id` | M13: quarantine; do not mint synthetic candidates. Keep lineage. |
| Training user orphans | 273 `trainings.user_id` missing | M13: quarantine profile trainings; do not attach to a guessed candidate. |
| Manpower candidate orphans | 5 rows | M13: quarantine those 5. |
| Teacher identity orphans | users `teacher_id` ∈ {2,5,6,7,8}; teacher 3 unbound | When T5 is implemented: bind only living `teachers.id`; quarantine the five user FKs; decide whether to recreate deleted teachers (Rule 3 / A11). |
| Schedule teacher 2 | 1 timetable row | Quarantine or map as unbound teacher; do not invent teacher 2. |
| Exam varchar currently single-valued | 0 commas in 28 rows | Keep T2 join table. Split on comma at import; today each exam yields one join row. |
| Result NULL vs UI `''` | Dump ungraded = NULL | Import both NULL and `''` as ungraded. Do not coerce to FAIL. |
| Score NULL vs 0 | NULL dominant; 0 used as a mark | Preserve both. Do not treat 0 as ungraded. |
| Group 54 unnamed | NULL name/code | Keep row (Rule 3). Label in UI as unnamed; do not drop. |
| Group 1 FAIL UI | Real group “Admission For Interview”, 76 candidates | Do not rename. FAIL still must not write candidate master (T4). |
| `liveStatus` “Rapid” | Several groups contain `Rapid`; group 52 is `RAPID…` (case mismatch vs PHP `strpos` `'Rapid'`) | A03/A04 / T10 — not an M5 schema lock. |
| Exact M5 permission matrix | Not in the dump | Still **unproven**. No runtime grants. No administrator exam widening. |

---

## 6. Impact on M5 gate

**T8 evidence: COMPLETE (dump verified + profiled).**

T8 profiling does **not** make M5 GO.

Remaining hard blockers:

1. **Exact M5 permission matrix** — proposed names only; no runtime grants; administrator exam widening **not approved**.
2. **Implementation still forbidden** — T1/T2/T4/A06 are design locks only.
3. **M13 quarantine** of T8 orphans is required before production import (not an M5 code start).

**M5 PRE-GATE STATUS: NO-GO**
