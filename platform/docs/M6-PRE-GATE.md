# M6 PRE-GATE — Overseas Processing

**Investigation date:** 2026-09-07  
**Prerequisites:** M1–M5 COMPLETE. CI Run **#10** on PostgreSQL 16 PASS (`318f97c` + fixture-only `902f60f`). Working tree was clean at M5 closeout.  
**This document:** evidence and open questions only. No schema, migration, API, UI, permission, candidate-access, CI, or Laravel change.

Milestone numbering is unchanged. **M6 remains Overseas Processing.** Locked M1–M5 decisions are not reopened. `platform/docs/DESIGN-GATE.md` is not in this checkout. The authoritative pack is `modernization-design/final-design-gate/` (Revision 2), especially `00-project-rules.md`, `01-table-mapping.md`, `02-relationships.md`, `04-workflow.md`, `05-rbac.md`, `09-postgresql-prisma.md`, `11-milestones-risks-approvals.md`, and the companion CSVs.

Companion dump profile: `docs/M6-PRODUCTION-PROFILE.md`.

---

## Executive status

**M6 PRE-GATE OWNER APPROVAL STATUS: APPROVED** — signature: `docs/M6-OWNER-REVIEW.md` (2026-09-07 Owner)

**M6 IMPLEMENTATION STATUS: NOT STARTED**

**M6 GO/NO-GO IMPLEMENTATION GATE: NO-GO**

**M6 IMPLEMENTATION GO: NOT ISSUED** — checklist: `docs/M6-IMPLEMENTATION-GO.md`

Investigation of legacy code and the SHA-verified production dump is complete. PRE-GATE decisions (A03/A14/A04 M6 slices and confirmations) are **signed**. Implementation remains **BLOCKED** until a separate M6 IMPLEMENTATION GO is explicitly issued. Runtime M6 grants remain **NOT APPROVED**.

| Item | Status |
|---|---|
| M1–M5 locked decisions (A05, A07, A20, M5-T1/T2/T4/A06/T5-B, 12 M5 keys, teacher **no** `candidate.read`, exam_class_groups, manual PASS/FAIL, trainings ≠ manpower_trainings) | **LOCKED** — do not reopen |
| A03 M6 slice (step 5 skip; no overseas write gates; medical/ARC off ladder) | **APPROVED** — see `docs/M6-OWNER-REVIEW.md` |
| A04 M6 slice (preserve exact live-status strings if badge implemented) | **APPROVED** — see `docs/M6-OWNER-REVIEW.md` |
| A14 (Police Clearance / ARC; `acr_file_path` lineage; no government API; adapters only) | **APPROVED** — see `docs/M6-OWNER-REVIEW.md` |
| A01 / A02 finance hard gates | **OPEN** — M6 must not post ledger or payment-request writes (**CONFIRMED**) |
| A16 bill_title_code / FIN-BUG manpower tautology | **OPEN** — step 9 is M7, not M6 |
| 14-key M6 catalogue | **APPROVED** as catalogue only |
| Runtime M6 permission grants | **NOT APPROVED** |
| Production import (M13) | **BLOCKED** |
| M7 Finance / M8 Invoice / M9 Documents / M10 Reports | **Do not start** |

---

## Locked constraints carried forward

- Teacher: **NO** `candidate.read`. Self-scope remains `teacher.id = current_user.teacher_id`. Exclusive `iam.users.teacher_id`.
- `candidate.trainings` (M3 CV) stays separate from `workflow.manpower_training_events` (M5 BMET). Live-status step 9 is **not** that table.
- No `*.delete` permission keys.
- Administrator exam / exam_result keys stay as M5 approved (administrator has **NO** exam keys). Do not widen via M6.
- A07 role set is closed. Do not add Voyager role `110` (`others`) as a new platform role.
- No Laravel edits. No production import. No fabricated masters.
- Rule 4: no finance writes until A01 and A02.

---

## 1. Exact M6 scope and milestone mapping

Design Gate `11-milestones-risks-approvals.md` **M6 Overseas Processing**:

- **Outputs:** medical, police clearance, ARC, visa, labour contract, license, and flight workflows/documents.
- **Tests:** prerequisite transitions, expiry/date rules, live-status compatibility, document access.
- **Acceptance:** operations approves every transition, naming rule, and exception path.

**In scope (KEEP tables, Design Gate Revision 2):**

| Source | Target (CSV) | Schema | Rows |
|---|---|---|---:|
| `medicals` | `candidate.medicals` | candidate | 384 |
| `police_clearances` | `workflow.police_clearances` | workflow | 606 |
| `arcs` | `candidate.arcs` | candidate | 632 |
| `visa_immigrations` | `candidate.visa_immigrations` | candidate | 1,045 |
| `labour_contracts` | `candidate.labour_contracts` | candidate | 1,153 |
| `flight_schedules` | `candidate.flight_schedules` | candidate | 640 |
| `licenses` | `operations.licenses` | operations | 125 |
| `license_positions` | `operations.license_positions` | operations | 3,952 |

**In scope as a live-status lookup (not a candidate document):** `live_status` → `operations.live_status` (10 rows). Label replacement requires **A04**.

**Adjacent, not M6 outputs unless the owner expands scope:**

| Item | Why adjacent | Disposition |
|---|---|---|
| `selected_candidates` (207) | WF-05 / live-status step 5 “Selection”; `liveStatus()` never reads this table | **A03**. Do not implement as M6 core. |
| `manpower_trainings` | Pipeline tab “BMET Train.” / `bmetStatus()` | **Already M5.** Do not re-implement. |
| `payment_requests` / manpower fee | Live-status step 9; pipeline “Manpower” | **M7.** M6 must not write payments. |
| Ticket invoices / companies | Flight commercial documents | **M8.** |
| Object-store copy / MIME / checksum | File columns on M6 tables | **M9.** M6 stores refs only. |
| Golden report parity | police/ARC/visa/flight reports | **M10.** M6 must not silently change report contracts. |

**Explicitly out of M6:** Prisma/schema/CI/Laravel changes in this task; M7 start; merging `trainings` with `manpower_trainings`; granting teacher `candidate.read`; introducing `*.delete`.

---

## 2. Legacy source tables / controllers / models / routes

### 2.1 Models (`src/app/Models/`)

| Model | Table | Relations in code |
|---|---|---|
| `Medical` | `medicals` | **Empty stub** — no `candidate()` |
| `PoliceClearance` | `police_clearances` | `belongsTo` Candidate |
| `Arc` | `arcs` | `belongsTo` Candidate |
| `VisaImmigration` | `visa_immigrations` | `belongsTo` Candidate |
| `LabourContract` | `labour_contracts` | **Empty stub** |
| `FlightSchedule` | `flight_schedules` | **Empty stub** |
| `License` | `licenses` | `belongsTo` Companier; `hasMany` LicensePosition |
| `LicensePosition` | `license_positions` | `belongsTo` Position |
| `SelectedCandidate` | `selected_candidates` | `belongsTo` Candidate; **`classGroup()` on missing column** |
| `LiveStatus` | `live_status` | none |

`Candidate`: `VisaImmigration()` hasMany; `flightSchedules()` hasMany. No Eloquent relations for medical / police / labour / ARC.

### 2.2 Controllers

Candidate-scoped Voyager BREAD (index filters `where('candidate_id', $request->get('candidate_id'))` after `authorize('browse')`):

- `VoyagerLabourContractController`
- `VoyagerPoliceClearanceController`
- `VoyagerMedicalMinistryController` (slug `medicals`)
- `VoyagerVisaImmigrationController`
- `VoyagerArcController`
- `VoyagerFlightScheduleController`
- `VoyagerManpowerController` — **M5 domain** (`manpower_trainings`), not an M6 deliverable

Companier-scoped:

- `VoyagerLicenseController` — store/update syncs `license_positions` (delete-all then insert). Unused import `Liense` (typo).

Reports (`VoyagerReportController`): `policeClearanceReport`, `arcReport`, `visaReport`, `flightReport`, `flightSchedule`, `selectedCandidateReport`.

Ajax (`VoyagerAjaxController`): `classGroupCandidates` eager-loads `VisaImmigration`; `candidateApproval` / `candidatePaymentApproval` have **no** `authorize()`. Payment branch `bill_title = 'Medical Fee'` is assignment-not-comparison (**FIN-BUG**, A01/A02/A16 — not an M6 medicals write).

### 2.3 Routes

Voyager BREAD routes are dynamic (`voyager.medicals.index`, `voyager.police-clearances.index`, `voyager.labour-contracts.index`, `voyager.visa-immigrations.index`, `voyager.arcs.index`, `voyager.flight-schedules.index`, `voyager.licenses.*`). Explicit report POSTs in `src/routes/admin.php`: `police-clearance-report`, plus flight / selected-candidate report names in the report catalog. Ajax: `GET /panel/ajax/class-group-candidates/{id}`, `ANY /panel/ajax/candidate-approval/{id}`.

### 2.4 Helpers / UI

- `CommonClass::labourStatus`, `policeClearanceStatus`, `visaStatus`, `bmetStatus`, `manpowerStatus`, `flightStatus`, `arcStatus`, `liveStatus` (`src/app/Helpers/CommonClass.php`).
- Pipeline: `candidate-partial-top-menu.blade.php`.
- Sidebar: `candidate-partial-menu.blade.php`.
- Widgets: `SelectedCandidateSummary`, `SelectedCandidateSlider`, `UpcommingFlightSchedule`.

No HTTP client to an external police-clearance, ARC, or immigration API was found in application PHP. **A14 must not be closed by inventing a government contract.**

---

## 3. Production row counts and data-quality findings

Dump SHA **PASS**. Method and full tables: `docs/M6-PRODUCTION-PROFILE.md`. Counts match Design Gate INSERT tuples.

Status is uniformly `'A'` on medicals, police, arcs, visa, licenses. Labour has 1,152 `'A'` + **1 NULL status**. Flight and selected_candidates have **no status column**.

Zero dates (`0000-00-00`) on M6 issue/expire columns: **0**.

Expire-before-issue rows exist (labour 4, police 5, medicals 2, arcs 4, visa **27**). Large volumes are already past expire as of 2026-09-07 (visa 710, police 271, medicals 167). Legacy `liveStatus()` does **not** treat expiry as a demotion; `labourStatus(..., expireCheckColor)` only tints UI.

`arcs.is_lifetime`: Y 377 / N 255. `licenses.license_no` is unique in dump (0 duplicates). `airlinece_name` and `acr_file_path` are **source typos to preserve** (column-mapping already maps `acr_file_path` → `arc_file_id` with source-column lineage).

`selected_candidates.position` is a **string** column, not `positions.id` and not `class_group_id`.

---

## 4. Relationships and FK / logical relationships

Design Gate `relationships.csv`: all M6 edges are **INFERRED** (naming + target table presence). None are declared FKs.

Logical map:

```
candidates.id  <── labour_contracts.candidate_id
               <── police_clearances.candidate_id
               <── medicals.candidate_id
               <── arcs.candidate_id
               <── visa_immigrations.candidate_id
               <── flight_schedules.candidate_id
               <── selected_candidates.candidate_id   (adjacent)

thanas.id      <── police_clearances.thana_id
countries.id   <── police_clearances.country_id
               <── medicals.country_id
               ✗ visa_immigrations.country_id ABSENT

companiers.id  <── licenses.companier_id     (NOT candidate_id)
licenses.id    <── license_positions.license_id
positions.id   <── license_positions.position_id
```

`latest()` on candidate-scoped tables is the runtime “current” record for live-status helpers (no unique constraint on `candidate_id`).

Licenses are **companier masters**, not candidate overseas documents. Including them in M6 is Design Gate milestone text; they do not participate in `liveStatus()`.

---

## 5. Orphans and inconsistent records

Propose M13 quarantine codes (not applied):

| Code | Finding | Count |
|---|---|---:|
| Q-LC-CAND | `labour_contracts.candidate_id` missing candidate | 19 |
| Q-LC-NULL | `labour_contracts.candidate_id` NULL | 1 |
| Q-VISA-CAND | visa orphan candidate | 19 |
| Q-FLT-CAND | flight orphan candidate | 14 |
| Q-FLT-NULL | flight `candidate_id` NULL | 4 |
| Q-PC-CAND | police orphan candidate | 14 |
| Q-MED-CAND | medical orphan candidate | 13 |
| Q-ARC-CAND | ARC orphan candidate | 12 |
| Q-SEL-CAND | selected_candidates orphan (adjacent) | 8 |
| Q-LP-LIC | `license_positions.license_id = 1` missing license | 17 |
| Q-LIC-COMP | `licenses.companier_id` NULL | 1 |
| Q-PC-THANA | police `thana_id` NULL | 37 |
| Q-PC-CTY | police `country_id` NULL | 551 |
| Q-MED-CTY | medical `country_id` NULL | 33 |
| Q-DATE-* | expire_date < issue_date | see profile |
| Q-FILE-* | empty file path with living row | see profile |

**Inconsistencies (CONFIRMED, do not “fix” in M6 without approval):**

1. `live_status` step 5 = “Selection” but `liveStatus()` **never** reads `selected_candidates` (jumps 4 → 6).
2. Step 7 label = “Police Clearance & Medical” but runtime checks **police only**. Medical is absent from the waterfall. ARC is absent from the waterfall (`arcStatus()` exists, unused by `liveStatus()`).
3. Step 6 label spelling “Labour **Contact**”.
4. Step 8 spelling “VISA/Work **Permite**”.
5. Step 9 uses approved `payment_requests` with `bill_title LIKE '%Manpower%' OR bill_title = bill_title` (**tautology / FIN-BUG**). `bmetStatus()` / `manpower_trainings` is unused by `liveStatus()`.
6. Eloquent `SelectedCandidate::classGroup()` references `class_group_id` which **is not in DDL**.
7. WF-08 infers `visa_immigrations.country_id`; dump has **no such column**.
8. Duplicate Voyager keys: `browse_police_clearance` vs `browse_police_clearances` (Design Gate maps one to `candidate.police_clearance.*` and one to `workflow.police_clearances.*`).
9. Schema split: police → `workflow.*`, medical/ARC/labour/visa/flight → `candidate.*`. That is CSV proposal, not a signed “fix”.

Orphans must be quarantined, never silently deleted (Rule 1 / M13).

---

## 6. Existing business workflow / logic

Normative transition rows: `workflow-transitions.csv` WF-06 … WF-10. Runtime authority for the badge is `CommonClass::liveStatus()` (CONFIRMED `src/app/Helpers/CommonClass.php:539–576`).

Waterfall (highest wins):

1. Any `flight_schedules` row for the candidate → step **10** (+ extraText schedule date/time)
2. Else `manpowerStatus()` (approved payment, tautological bill_title) → step **9**
3. Else any `visa_immigrations` row → step **8**
4. Else any `police_clearances` row → step **7**
5. Else any `labour_contracts` row → step **6**
6. Else class group name contains `'Rapid'` → step **4**
7. Else any class group name → step **3**
8. Else `json_decode(cv_file_path)` count > 0 → step **2**
9. Else step **1**

**Skipped:** step **5**. **Not in ladder:** medical, ARC, licenses, `manpower_trainings`.

Helpers treat “completed” as **row existence** (`latest()`), not status enum, not unexpired date, not required file.

Pipeline UI (`candidate-partial-top-menu.blade.php`) marks Labour / Police / Visa / BMET / ARC / Manpower / Flight independently (ARC and BMET ticks do **not** move `liveStatus()`). Medical is **not** a pipeline step; it is a sidebar item “Medical & Ministry”.

Labour URL hidden for role_id **101, 107** in the pipeline; sidebar also hides Labour for **101, 107, 109**. Payment/manpower URL hidden for **101, 107, 108**.

WF-06/07/08 **INFER** that a prior live-status step must exist. Legacy BREAD **does not enforce** that. New engine must not invent enforcement until **A03**.

`employer_candidates.status` schema drift remains A03 (M4/WF-05/WF-11), not an M6 table.

---

## 7. Existing permissions and role behavior

Voyager BREAD keys (CONFIRMED in `rbac-permissions.csv` / `rbac-role-grants.csv`): browse/read/edit/add/delete per resource, including `delete_medicals`. Target platform **must not** add `*.delete` (M1–M5 lock).

**CONFIRMED browse grants (permission_role INSERT):**

| Permission | sadmin (1) | Agent (101) | Employee (104) | owner (106) | Agency (108) | Sub Agent (109) | others (110) | Teacher (103) | Company (107) |
|---|---|---|---|---|---|---|---|---|---|
| `browse_medicals` | yes | yes | yes | yes | yes | yes | yes | **no** | **no** |
| `browse_police_clearance` / `browse_police_clearances` | yes | yes | yes | yes | yes | yes | yes | **no** | **no** |
| `browse_visa_immigrations` | yes | yes | yes | yes | yes | yes | yes | **no** | **no** |
| `browse_arcs` | yes | yes | yes | yes | yes | yes | yes | **no** | **no** |
| `browse_flight_schedules` | yes | yes | yes | yes | yes | yes | yes | **no** | **no** |
| `browse_labour_contracts` | yes | **no** | yes | yes | yes | **no** | yes | **no** | **no** |
| `add_labour_contracts` | yes | **no** | yes | yes | yes | **no** | yes | **no** | **no** |
| `browse_licenses` | yes | **no** | yes | yes | **no** | **no** | **no** | **no** | **no** |

Role **110 others** is **not** an A07 platform role — do not port it as a new role.

Hardcoded UI exclusions (role_id arrays) are **in addition to** BREAD grants. Frontend hiding is not authorization (M4 rule).

Reports: `browse_reports` exists; per-route execution middleware is **UNVERIFIED** (report-catalog). Treat report execute/export as **M10**, not an M6 grant invention.

---

## 8. Security / IDOR risks

| Risk | Evidence | M6 requirement (**PROPOSED**, not implemented) |
|---|---|---|
| Candidate-id query parameter | Every overseas BREAD index: `where('candidate_id', $request->get('candidate_id'))` after global `authorize('browse')` | Must use M4 `candidate-access.ts` scope. Missing `candidate_id` must not list all rows. |
| No row-level check on show/edit | Voyager `findOrFail($id)` with BREAD permission only | Read/update must verify the parent candidate is in actor scope. |
| Ajax IDOR | `candidateApproval` / `classGroupCandidates` / payment approval: **no `authorize()`** | Out of M6 writes; do not copy this pattern. Class-group list leaks visa MP numbers. |
| Teacher | No overseas BREAD grants; M5 lock forbids `candidate.read` | Teacher access to M6 resources: **none**. |
| Company (107) | `candidate.read` companier-scoped in M4; no Voyager overseas browse grants | **BLOCKED** — owner must say whether company may read candidate medical/visa/flight. Do not invent. |
| License | Companier-scoped master; browse limited to sadmin/employee/owner | Scope `companier_id` to bound company if company is later granted access. Never candidate-global. |
| Files | Paths in public Voyager storage (UNVERIFIED root) | Private-by-default; authorized download; never infer access from URL possession (Design Gate RBAC CSV). M9 owns bytes. |
| Reports | Filters by `candidate_id` / dates without documented row-scope | M10 + same candidate-access policy. |

Do not treat “user has browse_medicals” as “user may open any candidate_id”.

---

## 9. Existing UI / report behavior

**Candidate pipeline ticks:** Registration, CV Upload, Class Group, Labour, Police Clear., Visa, BMET Train., ARC, Manpower, Flight. Medical is sidebar-only.

**Sidebar:** Police Clearance, Medical & Ministry, Visa Immigration, Manpower, Flight Schedule, ARC; Labour Contract + Document if not roles 101/107/109; Payment if not 101/107/108/109/110.

**Live-status badge:** HTML from `liveStatus()` including unescaped extraText on flight (`{!! $liveStatus !!}`).

**ACTIVE reports (M10 contracts; M6 must not break columns):**

| Report | Tables | Notes |
|---|---|---|
| police-clearance-report | police_clearances, candidates | Document field `photo_file_path`; exact status |
| arc-report | arcs, candidates | Document field **`acr_file_path` typo**; ARC number |
| visa-report | visa_immigrations, candidates | `visa_mp_no`; exact status |
| flight-report | candidates + flight_schedules | Dates filter **candidate created_at**, not flight_date |
| flight-schedule | candidates, visa, flight, payments | In-memory payment-status filter |
| selected-candidate-report | candidates + visa + flight | `candidates.status='A'` only — **not** `selected_candidates` table |

Catalog flags PDF/XLSX defects (generic id/name XLSX). Defect acceptance is M10, not M6.

Widgets surface selected-candidate and upcoming flight lists with hardcoded role_id allow-lists.

---

## 10. Target PostgreSQL domain / schema proposal

**PROPOSED** from Design Gate column-mapping / table-mapping. **Not applied. Not owner-signed for M6.**

M5 already created PostgreSQL schemas `operations` and `workflow` (teachers, class groups, exams, `manpower_training_events`). `09-postgresql-prisma.md` originally listed `iam, candidate, workflow, partners, finance, documents, communications, reporting, migration, audit` and did not list `operations`. **Do not remove `operations`.** Licenses and `live_status` stay in `operations` per CSV.

| Target table | Schema | Notes |
|---|---|---|
| `medicals` | `candidate` | UUID PK; `candidate_id`; `country_id` nullable; `document_file_id` |
| `police_clearances` | `workflow` | CSV target. Placement vs medicals is an **open consistency question**, not a silent move. |
| `arcs` | `candidate` | `arc_file_id` ← `acr_file_path`; keep `arc_number`, `is_lifetime` |
| `visa_immigrations` | `candidate` | `visa_mp_no`; no `country_id` unless A03/A14 adds it |
| `labour_contracts` | `candidate` | `document_file_id`; `expire_date` |
| `flight_schedules` | `candidate` | Keep `airlinece_name` spelling; ticket + arrival-seal file ids |
| `licenses` | `operations` | Unique `license_no`; `companier_id` |
| `license_positions` | `operations` | `license_id`, `position_id`, `quantity` |
| `live_status` | `operations` | 10-row lookup until A04 says otherwise |

Identifiers: UUID business keys; deferred FKs after quarantine (09: load, profile orphans, then `NOT VALID`). Civil `date` for issue/expire; `timestamptz` for created/updated pending timezone approval. Status values stay exact legacy text until a lookup mapping is approved.

Do **not** add `selected_candidates` or re-create `manpower_training_events` in M6.

---

## 11. Migration mapping and `legacy_key_map` requirements

Rule 2 applies. Every migrated M6 row:

`legacy_key_map(source_system='manpower_mysql', source_table, source_id) → (target_type, target_id)` unique on the source triplet, same transaction as the business INSERT.

| source_table | source_id | target_type (PROPOSED) |
|---|---|---|
| `medicals` | `id` | `candidate.medicals` |
| `police_clearances` | `id` | `workflow.police_clearances` |
| `arcs` | `id` | `candidate.arcs` |
| `visa_immigrations` | `id` | `candidate.visa_immigrations` |
| `labour_contracts` | `id` | `candidate.labour_contracts` |
| `flight_schedules` | `id` | `candidate.flight_schedules` |
| `licenses` | `id` | `operations.licenses` |
| `license_positions` | `id` | `operations.license_positions` |
| `live_status` | `id` | `operations.live_status` |

FK columns resolve through `legacy_key_map` for `candidates`, `thanas`, `countries`, `companiers`, `positions`. Orphan FKs stay nullable + quarantine row — **no synthetic parent**.

Column transforms: ISO date reject zero dates (none seen on these issue/expire fields); preserve `status` / `is_lifetime` / `airlinece_name` / `acr_file_path` bytes until approved mapping. File JSON vs scalar: detect per value (document-fields.csv); M9 extracts objects.

**M6 implementation must not run production import.** Mapping is design-only until M13.

Validation: INSERT count parity vs 1,153 / 1,045 / 640 / 632 / 606 / 384 / 125 / 3,952 / 10; PK uniqueness; deterministic hashes excluding approved transforms (`table-mapping.csv` validation_rule).

---

## 12. Quarantine requirements

- Quarantine codes in §5. Never delete orphans to make FKs happy.
- Empty file path: inventory at M9 (`MISSING` if DB ref lacks object; `ORPHAN` if object lacks ref).
- Date inversion and expired-but-`'A'` rows: keep, flag; do not auto-void.
- License 1 gap + 17 license_positions: keep positions quarantined against missing license.
- NULL labour status / NULL candidate_id: keep.
- M5 quarantine set (Q-ER-CAND, Q-TR-USER, …) is unchanged.

M13 remains the import/quarantine gate. M6 pre-gate does not authorize load.

---

## 13. API requirements

**PROPOSED** after PRE-GATE + A03/A14 (+ A04 if labels are exposed). Not implemented.

- Candidate-scoped resources nested or filtered by candidate UUID, always through `candidate-access` (same helper as M4/M5 manpower). Teacher → `none`.
- CRUD without `DELETE` (soft-void only if operations later approves a compensating event).
- Licenses: companier-scoped, not nested under candidates.
- `live_status`: read-only lookup **or** derived view; writes **BLOCKED** on A04.
- Do not expose payment-request create/approve as M6.
- Do not expose manpower-training as M6 (already M5 `/manpower-trainings`).
- Validation: dates, optional FKs, file ids as UUID refs (not public paths).
- Prerequisite checks (labour before police, etc.): **BLOCKED** on A03 — default in this package is **document legacy (no enforcement)** unless owner signs enforcement.
- IDOR tests mandatory (see §19).
- No Laravel compatibility routes.

---

## 14. RBAC matrix

**Catalogue is APPROVED (2026-09-07 Owner). Runtime grants remain NOT APPROVED.** This section is evidence plus the signed 14-key list. Do not seed grants.

Follow M1–M5 key shape: `module.resource.read|manage`. No `*.delete`. Do not seed grants in this pre-gate.

| PROPOSED key | Intent |
|---|---|
| `overseas.medical.read` / `overseas.medical.manage` | candidate.medicals |
| `overseas.police_clearance.read` / `overseas.police_clearance.manage` | police (one key pair despite Voyager duplicate browse keys) |
| `overseas.arc.read` / `overseas.arc.manage` | arcs |
| `overseas.visa.read` / `overseas.visa.manage` | visa_immigrations |
| `overseas.labour_contract.read` / `overseas.labour_contract.manage` | labour_contracts |
| `overseas.flight.read` / `overseas.flight.manage` | flight_schedules |
| `operations.license.read` / `operations.license.manage` | licenses + license_positions |

**14 keys.** Dual Design Gate prefixes (`candidate.police_clearance.*` vs `workflow.police_clearances.*`) must be collapsed by owner (A14 / catalogue sign-off). Until then do not implement two parallel catalogues.

**PROPOSED grant sketch from legacy evidence — NOT APPROVED:**

| Role | Candidate overseas read | Overseas manage | License | Notes |
|---|---|---|---|---|
| super_admin | all | all | all | bypass |
| owner | all | all | all | legacy owner has browse |
| administrator | **UNRESOLVED** | **UNRESOLVED** | **UNRESOLVED** | Voyager sadmin ≠ administrator; do not copy exam-style denial or grant without sign-off |
| employee | all (legacy) | labour add yes; others UNVERIFIED add/edit | browse yes | |
| agent / sub_agent / agency | candidate-scoped | labour **no** for agent/sub_agent; agency **yes** labour | **no** | match BREAD + UI hide |
| company | **BLOCKED** | **BLOCKED** | **BLOCKED** | no Voyager overseas browse; M4 has candidate.read scoped |
| teacher | **none** | **none** | **none** | LOCKED |
| candidate / employer | **BLOCKED** | **BLOCKED** | **none** | no evidence they edit overseas BREAD |

Do not grant `overseas.*` by implementing this document.

---

## 15. Audit requirements

Append-only `audit.events` as M1–M5: actor, action, entity, before/after hash, correlation, reason. Forbidden metadata keys remain (passport/nid/token).

**PROPOSED event names** (not added to `audit.ts`):

- `overseas.medical.created` / `.updated` / `.status_changed`
- `overseas.police_clearance.created` / `.updated`
- `overseas.arc.created` / `.updated`
- `overseas.visa.created` / `.updated`
- `overseas.labour_contract.created` / `.updated`
- `overseas.flight.created` / `.updated`
- `operations.license.created` / `.updated`
- `operations.license_positions.replaced` (legacy deletes all lines then inserts)

Workflow engine events from WF CSV (`LABOUR_CONTRACT_RECORDED`, `POLICE_CLEARANCE_RECORDED`, `MEDICAL_RECORDED`, `VISA_RECORDED`, `FLIGHT_SCHEDULED`, compensating `*_VOIDED`) are **PROPOSED** in Design Gate and **BLOCKED** until A03 defines preconditions and rollback.

Do not log file bytes. Log `file_id` only.

---

## 16. Workflow / state-transition requirements

| Transition | Legacy | New system until A03 |
|---|---|---|
| WF-06 labour | INSERT labour_contracts; no selection check | Document only **or** enforce after A03 |
| WF-07 police + medical | Independent INSERTs; step 7 badge = police only | Do not require medical for step 7 without A03/A04 |
| WF-08 visa | INSERT; step 7 police required only in waterfall derivation, not in BREAD | Same |
| WF-09 manpower payment | **M7** | M6 must not implement |
| WF-10 flight | INSERT; step 9 from payment tautology | Do not call payment APIs |
| Step 5 selection | Skipped | Must not skip in a **new** engine without A03 backfill rule; must not rewrite historical labels without A04 |
| Expiry | Colour only on labour | Operations must approve whether expired labour/police/visa demotes live-status |
| `candidates.status` | Overseas BREAD does not write it | Keep M4/M5 rule: document modules do not write account status unless a signed transition says so |
| Exam PASS `class_group_id` | M5 lock | M6 must not write class_group or exam results |

Live-status compatibility: Design Gate says the event store is authoritative and labels are a view. **A04 OPEN** for exact strings and retirement schedule. Until A04, any M6 UI that shows the badge must reproduce the **legacy waterfall**, including skipped step 5 and police-only step 7 — changing that is a business decision, not a bugfix.

---

## 17. Finance / payment implications

M6 **must not** insert/update `payment_requests`, `payments`, ledgers, agent balances, or `candidates.medical_fee_payment_id` / admission / final-group payment ids.

- Step 9 / “Manpower” pipeline item → **M7** (A01, A02, A16).
- Medical fee assignment bug in ajax approval → **M7**, not medicals BREAD.
- `class_groups.fee_amount` stays M5/M7 lineage.
- License rows have no amount column in dump; bank fields on `companiers` are M4/M7.
- Flight-schedule report’s in-memory payment-status filter is **M10/M7**, not an M6 write.

Reading whether a manpower payment exists, for a **compatibility live-status view**, still depends on M7 data and FIN-BUG tautology. Do not “correct” the OR in M6.

---

## 18. File / document implications

Document-fields.csv (CONFIRMED field/BREAD; storage root UNVERIFIED):

| Table | Source column | Target (CSV) |
|---|---|---|
| medicals | `document_file_path` | `document_file_id` |
| police_clearances | `photo_file_path` | photo file id |
| arcs | `acr_file_path` | `arc_file_id` (keep source-column lineage) |
| visa_immigrations | `document_file_path` | `document_file_id` |
| labour_contracts | `document_file_path` (WF: often JSON) | `document_file_id` |
| flight_schedules | `ticket_file_path`, `arrival_seal_page_file_path` | two file ids |
| licenses | `license_file_path` (longtext / possible Voyager JSON) | `license_file_id` |

M6 stores metadata references only. Byte copy, MIME sniff, SHA-256, malware, retention → **M9**. Empty paths: 7–134 rows depending on table (profile). Do not fabricate files.

Classification: private-by-default; short-lived authorized download; resource policy + document classification (Design Gate). URL possession is not ACL.

---

## 19. Integration-test plan

**PG16 CI remains the only accepted database for later implementation tests.** Local PostgreSQL 18 is not a substitute (M5 closeout).

Plan is **PROPOSED**. Tests must not be written until implementation GO. When written, they should include:

1. **IDOR:** actor with `overseas.*.read` but out-of-scope candidate → 404/403, never another agent’s medical/visa/flight/labour/ARC/police.
2. **Teacher:** zero rows / 403 on all overseas and license candidate-nested routes; no `candidate.read`.
3. **Missing candidate_id:** list endpoints do not dump the table.
4. **License:** employee/owner can read; agent cannot; companier scope if later granted.
5. **No DELETE** permission and no destructive HTTP.
6. **No finance writes** from overseas handlers (assert no `payment_requests` / ledger inserts).
7. **No manpower_trainings reimplementation** (M5 routes remain the BMET API).
8. **Expiry:** persist expire-before-issue and expired `'A'` rows; do not auto-fail unless A03 says so.
9. **Live-status compatibility:** **BLOCKED** as an implementation assertion until A04. Fixture expectation today = legacy waterfall (skip 5; police without medical still step 7; ARC does not advance; flight wins).
10. **Prerequisite enforcement:** **BLOCKED** on A03. If owner chooses “document legacy”, tests must allow labour without `selected_candidates`.
11. **Orphan import:** not in M6 API tests; M13.
12. **Audit:** create/update emits events without passport/nid in metadata.
13. **Unique `license_no`** on create.
14. **`acr_file_path` lineage** preserved in mapping tests (when M13 exists).

Unit tests: access helpers, date parse, permission catalogue keys (once approved).

Web: permission-gated nav; no teacher overseas links.

---

## 20. Explicit unresolved questions

Do **not** answer these in implementation. Each needs owner / operations sign-off.

1. **A03:** Backfill rule for imported candidates with labour/visa/flight but no `selected_candidates` row. Does the new engine enforce labour-after-selection?
2. **A03:** Exact employer_candidates status/purpose enum (blocks WF-05; affects whether “Selection” is recorded).
3. **A03:** Must medical exist for step 7, or is police-only the compatibility behavior forever?
4. **A04:** Exact live-status strings (including “Labour Contact”, “Permite”) and retirement schedule. Event-sourced view vs reproduce waterfall?
5. **A14:** Official names: police clearance vs police verification vs PCC; ARC vs ACR (`acr_file_path`). Any **external API**? **None evidenced** in this repo.
6. Schema placement: keep police in `workflow` and medical in `candidate`, or colocate? CSV vs 09 narrative.
7. Collapse `browse_police_clearance` vs `browse_police_clearances` to one stable key?
8. Are **licenses** in M6 (milestone text) or a later operations-master slice? They are companier-scoped and off the live-status ladder.
9. Is `selected_candidates` in M6, M4 remainder, or A03-only?
10. Company role: overseas read or not? Legacy BREAD = no.
11. Administrator overseas grants? Do not copy or deny by analogy with exam keys.
12. Candidate self-service overseas documents? No BREAD evidence.
13. Expiry: demote live-status, warning only, or ignore (legacy ignore except labour tint)?
14. Multiple rows per candidate: `latest()` forever, or unique current + history?
15. Void/compensating events vs no-delete?
16. Timezone for `created_at`/`updated_at` (09 UNVERIFIED).
17. Role 110 `others` grants: drop at cutover (A07 closed set) or map to an existing role?
18. Flight report filtering by candidate `created_at` rather than `flight_date`: preserve defect (M10) or treat as M6 API filter bug?
19. `medical_fee_payment_id` on candidates: display-only in M6 or forbid even reads that imply fee state?
20. WF-08 `country_id` on visa: add column (new) or omit (dump)?

---

## 21. Required approvals / gates before implementation

PRE-GATE owner decisions are **signed** in `docs/M6-OWNER-REVIEW.md` (2026-09-07). PRE-GATE documentation ≠ IMPLEMENTATION GO (M5 pattern).

| # | Gate | Status |
|---|---|---|
| 1 | Owner review of this PRE-GATE package (including `M6-PRODUCTION-PROFILE.md`) | **APPROVED** |
| 2 | A03 M6 slice (preconditions, step-5 skip, medical off step 7) | **APPROVED** |
| 3 | A14 (naming; no API; adapters only) | **APPROVED** |
| 4 | A04 M6 slice (preserve exact labels if badge implemented) | **APPROVED** |
| 5 | Exact permission catalogue (14 keys; dual police keys collapsed) | **APPROVED** (catalogue) |
| 6 | Runtime grant matrix as a **separate** gate | **NOT APPROVED** |
| 7 | Company / administrator / candidate self-access | **Deferred — no grant** (default) |
| 8 | Schema placement (police `workflow` vs `candidate`) | **APPROVED** |
| 9 | License tables in M6 catalogue/domain | **Accepted via catalogue + domain split**; HTTP slice still for IMPLEMENTATION GO |
| 10 | Quarantine codes; **no production import** until M13 | **APPROVED** |
| 11 | No finance writes (A01/A02 still OPEN) | **APPROVED** |
| 12 | PostgreSQL **16** CI remains the implementation test gate | **LOCKED** |
| 13 | Separate **M6 IMPLEMENTATION GO** | **NOT ISSUED** — `docs/M6-IMPLEMENTATION-GO.md` |

Until IMPLEMENTATION GO is explicitly issued: no Prisma models, no migrations, no API routes, no UI, no catalogue seed, no CI job changes, no M7.

---

## M6 PRE-GATE: OWNER APPROVED / IMPLEMENTATION NO-GO

Investigation: **COMPLETE**. Owner PRE-GATE: **APPROVED**.  
Implementation: **NO-GO / BLOCKED** on an explicit M6 IMPLEMENTATION GO. Runtime grants remain **NOT APPROVED**.
