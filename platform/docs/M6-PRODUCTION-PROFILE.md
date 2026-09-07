# M6 production dump profile

**Profiling timestamp (UTC):** 2026-09-07T07:17:11Z → 2026-09-07T07:17:31Z  
**Dump filename:** `u410970153_eujobbd.sql`  
**Dump path (local, not in Git):** `docs/database-audit/u410970153_eujobbd.sql`  
**SHA-256 (verified on bytes):** `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`  
**SHA-256 match:** **PASS** (matches `docs/database-audit/production-dump.sha256`)  
**Method:** read-only INSERT-tuple parse. No MariaDB import. Dump file not modified. Quote-aware statement termination so candidate remarks containing `;` are not truncated.

This profile does **not** authorize M6 implementation or M13 import.

Parent tables used only to resolve FKs: `candidates` 1,679; `countries` 239; `thanas` 562; `companiers` 390; `positions` 136; `class_groups` 63. These counts re-confirm Design Gate Revision 2 and M5-T8 for `candidates` / `class_groups`.

---

## 1. Exact source tables and row counts

All M6 operational INSERT counts match `table-mapping.csv`.

| Table | INSERT headers | Rows | PK unique | PK min–max | PK duplicate rows |
|---|---:|---:|---:|---|---:|
| `labour_contracts` | 5 | **1,153** | 1,153 | 1–1,164 | 0 |
| `visa_immigrations` | 5 | **1,045** | 1,045 | 1–1,064 | 0 |
| `flight_schedules` | 4 | **640** | 640 | 1–764 | 0 |
| `arcs` | 3 | **632** | 632 | 1–638 | 0 |
| `police_clearances` | 3 | **606** | 606 | 1–620 | 0 |
| `medicals` | 2 | **384** | 384 | 1–392 | 0 |
| `selected_candidates` | 1 | **207** | 207 | 5–219 | 0 |
| `licenses` | 1 | **125** | 125 | 2–133 | 0 |
| `license_positions` | 3 | **3,952** | 3,952 | 86–4,322 | 0 |
| `live_status` | 1 | **10** | 10 | 1–10 | 0 |
| `payment_requests` (M7 adjacency) | 2 | **702** | 702 | 3–704 | 0 |

`licenses.id` starts at **2** (id 1 absent). `license_positions` still reference license **1** (see §3).

---

## 2. Declared vs observed vs inferred vs unresolved

No M6 operational FK is a declared MariaDB constraint in the dump (Design Gate: 9 declared FKs on the whole database; none of these edges).

| Edge | Declared FK? | Observed in data | Label |
|---|---|---|---|
| `labour_contracts.candidate_id` → `candidates.id` | No | 1,133 resolve; 19 missing; 1 NULL | **OBSERVED** |
| `police_clearances.candidate_id` → `candidates.id` | No | 592 resolve; 14 missing | **OBSERVED** |
| `police_clearances.thana_id` → `thanas.id` | No | 569 resolve; 37 NULL; 0 orphan | **OBSERVED** |
| `police_clearances.country_id` → `countries.id` | No | 55 resolve; 551 NULL; 0 orphan | **OBSERVED** (mostly unused) |
| `medicals.candidate_id` → `candidates.id` | No | 371 resolve; 13 missing | **OBSERVED** |
| `medicals.country_id` → `countries.id` | No | 351 resolve; 33 NULL; 0 orphan | **OBSERVED** |
| `arcs.candidate_id` → `candidates.id` | No | 620 resolve; 12 missing | **OBSERVED** |
| `visa_immigrations.candidate_id` → `candidates.id` | No | 1,026 resolve; 19 missing | **OBSERVED** |
| `visa_immigrations.country_id` | No column | — | **ABSENT** (WF-08 inferred a column that is not in DDL) |
| `flight_schedules.candidate_id` → `candidates.id` | No | 622 resolve; 14 missing; 4 NULL | **OBSERVED** |
| `licenses.companier_id` → `companiers.id` | No | 124 resolve; 1 NULL; 0 orphan | **OBSERVED** |
| `license_positions.license_id` → `licenses.id` | No | 3,935 resolve; **17** → missing license `1` | **OBSERVED** |
| `license_positions.position_id` → `positions.id` | No | 3,952 / 3,952 resolve | **OBSERVED** |
| `selected_candidates.candidate_id` → `candidates.id` | No | 199 resolve; 8 missing | **OBSERVED** |
| `selected_candidates.class_group_id` | **No column** | Eloquent `classGroup()` is dead | **ABSENT / SCHEMA DRIFT** |
| `live_status.step_no` 1–10 | n/a | 10 rows, unique step numbers | **CONFIRMED** |

---

## 3. Orphan and data-quality findings

Orphan `candidate_id` values are ids that do not exist in the 1,679 living `candidates` rows (deleted candidates; PK gaps). Do not silently drop these rows.

| Table | Resolve | Orphan | NULL `candidate_id` | Distinct living candidates | Multi-row candidates | Max rows / candidate |
|---|---:|---:|---:|---:|---:|---:|
| `labour_contracts` | 1,133 | **19** | **1** | 1,067 | 62 | 5 |
| `visa_immigrations` | 1,026 | **19** | 0 | 967 | 47 | 5 |
| `flight_schedules` | 622 | **14** | **4** | 553 | 60 | 4 |
| `arcs` | 620 | **12** | 0 | 581 | 36 | 4 |
| `police_clearances` | 592 | **14** | 0 | 565 | 26 | 3 |
| `medicals` | 371 | **13** | 0 | 302 | 69 | 2 |
| `selected_candidates` | 199 | **8** | 0 | 192 | 6 | 3 |

Date anomalies (`expire_date` < `issue_date`; zero dates were **0** on these tables):

| Table | expire before issue | expired before 2026-09-07 |
|---|---:|---:|
| `labour_contracts` | 4 | 89 |
| `police_clearances` | 5 | 271 |
| `medicals` | 2 | 167 |
| `arcs` | 4 | 12 |
| `visa_immigrations` | 27 | 710 |
| `licenses` (`license_expire_date`) | n/a | 12 |

Status: every `medicals` / `police_clearances` / `arcs` / `visa_immigrations` / `licenses` row is `'A'`. `labour_contracts`: `'A'` 1,152 + **1 NULL**. `flight_schedules` and `selected_candidates` have **no `status` column**.

`arcs.is_lifetime`: `Y` 377 / `N` 255.

`licenses.license_no`: **0** duplicate values among 125 rows. `licenses.companier_id` NULL: **1**.

`license_positions` orphan `license_id=1`: **17** rows (license 1 is missing).

File-path emptiness (NULL / empty / `[]` / `{}` / `null` string):

| Field | Empty | Present |
|---|---:|---:|
| `labour_contracts.document_file_path` | 25 | 1,128 |
| `police_clearances.photo_file_path` | 9 | 597 |
| `medicals.document_file_path` | 7 | 377 |
| `arcs.acr_file_path` | 46 | 586 |
| `visa_immigrations.document_file_path` | 134 | 911 |
| `licenses.license_file_path` | 1 | 124 |
| `flight_schedules` ticket + arrival-seal (two columns) | 662 | 618 |

Physical object store was **not** inventoried (M9). Empty path ≠ missing file on disk and vice versa.

---

## 4. `live_status` labels (verbatim)

| step_no | name |
|---:|---|
| 1 | Registration |
| 2 | Profile Update/CV |
| 3 | Group Name |
| 4 | Interview |
| 5 | Selection |
| 6 | Labour Contact |
| 7 | Police Clearance & Medical |
| 8 | VISA/Work Permite |
| 9 | Manpower Status |
| 10 | Flight |

Exact spelling (including “Contact”, “Permite”) is a compatibility artifact. **A04 is OPEN** — do not invent replacements.

---

## 5. Payment adjacency (not M6 writes)

`payment_requests`: 702 rows; status `A` 626 / `P` 66 / `R` 10. `bill_title` contains `Manpower`: 607; approved among those: **539**. `candidate_id` orphan: 18. Step-9 live-status uses approved payment requests, not `manpower_trainings` (M5 lock). Finance writes remain **A01/A02 HARD GATE**.
