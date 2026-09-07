# M6 OWNER REVIEW — decisions required before implementation

**Review date:** 2026-09-07  
**Signed:** 2026-09-07 — Owner  
**Sources reviewed:** `docs/M6-PRE-GATE.md`, `docs/M6-PRODUCTION-PROFILE.md`  
**Dump SHA:** `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8` (PASS)  
**This document:** owner-review checklist and signature record. No schema, migration, API, UI, permission seed, candidate-access, CI, Laravel, or M7 change.

**M6 OWNER REVIEW: APPROVED** (PRE-GATE decisions only)

**M6 IMPLEMENTATION STATUS: NOT STARTED**

**M6 GO/NO-GO IMPLEMENTATION GATE: NO-GO**

This signature records PRE-GATE decisions only. It does **not** authorize Prisma, migrations, APIs, UI, runtime grants, production import, or M6 implementation.

Implementation-GO owner sheet (unsigned): `docs/M6-IMPLEMENTATION-GO.md`. Review: `docs/M6-IMPLEMENTATION-GO-REVIEW.md`.

This PRE-GATE signature is **not** G1–G12 implementation GO. Recommended (unsigned) decisions for G1/G3/G4/G5/G6/G7/G8/G10 live only on the GO sheet. Catalogue approval is not a runtime grant. **Runtime grants NOT APPLIED.**

**M6 IMPLEMENTATION GO: NOT READY** until the owner explicitly signs G1, G3, G4, G5, G6, G7, G8, and G10 on the GO sheet.

---

## Implementation GO package (unsigned — pointer only)

Normative recommendations are in `docs/M6-IMPLEMENTATION-GO.md`. Do not treat this PRE-GATE file as having signed those gates.

| Gate | Recommended (unsigned) | Kind | Verdict |
|---|---|---|---|
| G1 | Full HTTP/UI: medical, police, ARC, labour, visa, flight, license, status read | Owner decision required — proposed only | **NO-GO** |
| G2 | A03 all A | Already locked | **GO** (content) |
| G3 | Proposed 14-key runtime matrix; do not apply | Owner decision required — proposed only; **runtime grants NOT applied** | **NO-GO** |
| G4 | SHIP live-status badge; exact dump strings; compatibility read-model | Owner decision required — proposed only | **NO-GO** |
| G5 | FULL license HTTP/UI; do not repair Q-LP-LIC | Owner decision required — proposed only | **NO-GO** |
| G6 | `latest()` = created_at DESC, then source id DESC; no status filter; duplicates allowed; no hard-delete | Owner decision required — proposed only | **NO-GO** |
| G7 | Per-role matrix; teacher/company/employer/candidate no M6 grant | Owner decision required — proposed only; **runtime grants NOT applied** | **NO-GO** |
| G8 | Concrete PG16 CI plan T-MIG…T-MAP | Owner decision required — proposed only | **NO-GO** |
| G9 | Private file refs | Already locked | **GO** (constraint) |
| G10 | Named `overseas.*.created/updated` + `overseas.status.changed`; no VOIDED | Owner decision required — proposed only | **NO-GO** |
| G11 | Quarantine / no dump import | Already locked | **GO** (policy) |
| G12 | No finance writes / no M7 | Already locked | **GO** (isolation) |

Sign the GO sheet, not this PRE-GATE document.

---

## Status vocabulary

| Label | Meaning |
|---|---|
| **LOCKED** | Owner-signed earlier (M1–M5). Do not reopen. |
| **APPROVED** | Owner-signed in this package (M6 PRE-GATE slice). Not an implementation grant. |
| **PROPOSED** | Recommended default. Unused once signed. |
| **CONFIRM** | Yes/no on a proposed constraint. Not an implementation grant. |
| **OPEN** | Blocks M6 until a named owner decision exists. |
| **NOT APPROVED** | Withheld. Must not be treated as granted. |

---

## Locked M1–M5 decisions (do not reopen)

| ID | Locked statement |
|---|---|
| A05 | Agency / company model: live masters Agent, SubAgent, Agencier, Companier; snapshots Agency, Company. No Organization / Tenant / generic Partner. |
| A07 | Closed role set: `super_admin`, `administrator`, `owner`, `agent`, `sub_agent`, `candidate`, `employer`, `company`, `agency`, `employee`, `teacher`. Do not add Voyager `110` (`others`). No numeric role IDs at runtime. |
| A20 | `employer_candidates.status` = `A`/`I`; `purpose` = `FAVORITE`/`RESERVE`/`SELECTED`. Table is **not** `selected_candidates`. Column/table not in target schema yet. |
| A06 / M5-T6 | `candidate.trainings` (M3 CV) stays separate from `workflow.manpower_training_events` (M5 BMET). Live-status step 9 is **not** that table. |
| M5-T1 | Teacher has **NO** `candidate.read`. Rule B (teacher → generic candidate browsing) permanently forbidden. Self-scope: `teacher.id = current_user.teacher_id`. Exclusive `iam.users.teacher_id`. |
| M5-T2 | `exam_class_groups` as approved. |
| M5-T4 | Manual PASS/FAIL; M6 must not write `class_group_id` or exam results. |
| M5-T5-B | Teacher person master + exclusive identity bind. |
| M5 catalogue | 12 training keys approved as catalogue only. Runtime grants remain a **separate** gate. No `*.delete`. |
| M5 administrator exams | Administrator exam / exam_result keys **NOT APPROVED**. Do not widen via M6. |
| M5 orphan / M13 | Quarantine policy **APPROVED**. Traceability via `legacy_key_map` **APPROVED**. M13 executes later. Never silently repair, delete, fabricate, or reassign. |
| Rule 4 / A01 / A02 | No finance writes until A01 and A02. A01/A02 remain OPEN HARD GATES. |
| PG16 | CI PostgreSQL 16 is the only accepted implementation test database. |
| A03 / A04 / A14 (M6 slice) | **APPROVED** below. Remaining Design Gate A03 (non-M6 candidate transitions) and A04 retirement schedule are not reopened here. A20 enum values stay locked. |

---

## Checklist index (only items that must be resolved before M6)

| # | Item | Kind | Blocks |
|---|---|---|---|
| 1 | A03 — overseas workflow / prerequisites | **APPROVED** | M6 engine, tests, APIs |
| 2 | A14 — terminology / integration | **APPROVED** | M6 naming, mapping, adapters |
| 3 | A04 — exact live-status labels | **APPROVED** | Badge UI and compatibility tests |
| 4 | M6 must not post payments / ledger | **APPROVED** | Finance isolation |
| 5 | `selected_candidates` outside M6 | **APPROVED** | Scope |
| 6 | 14-key RBAC catalogue (no runtime grants) | **APPROVED** (catalogue only) | Catalogue only |
| 7 | Target-domain separation | **APPROVED** | Schema placement |
| 8 | Orphan / quarantine / `legacy_key_map` | **APPROVED** | M13 policy extension |

Items **not** in this checklist (do not invent in M6): company/administrator/candidate self-access **grants**, license-in-M6 vs later slice, timezone (A08), file bytes (M9), report defects (M10), A16 bill_title_code, WF-08 visa `country_id` add-column.

---

## 1. A03 — exact overseas-processing workflow / prerequisites

**Status: APPROVED (M6 slice) — 2026-09-07 Owner. No implementation in this task.**

**Design Gate remainder:** non-M6 candidate transitions are not reopened. A20 remains LOCKED.  
**A20 remains LOCKED** and is not reopened. `employer_candidates` purpose/status enum is already decided; dump has **0** `employer_candidates` rows.

Normative runtime for the badge: `CommonClass::liveStatus()` (`src/app/Helpers/CommonClass.php:539–576`).  
Normative Design Gate rows: WF-06 … WF-10 in `workflow-transitions.csv`.  
Legacy BREAD `store()` on labour/police/medical/visa/ARC/flight: Voyager `validateBread` + `insertUpdateData` only — **no prerequisite check** of a prior step (`VoyagerLabourContractController.php:193–204` and siblings).

Helpers treat “completed” as **`latest()` row existence**, not status enum, not unexpired date, not required file.

**Derived volume (from `M6-PRODUCTION-PROFILE.md`, not a new dump parse):** living candidates with labour **1,067** vs `selected_candidates` **192**. Even if every selected row also has labour, at least **875** labour candidates have no `selected_candidates` row. Visa living **967**, flight living **553**. `employer_candidates` cannot backfill (0 rows).

### 1.1 Step 5 Selection behavior

| Field | Content |
|---|---|
| **Legacy evidence** | `live_status` step 5 name = `Selection` (10-row lookup, CONFIRMED). `liveStatus()` **never** reads `selected_candidates`; waterfall jumps 4 (Rapid group) → 6 (labour). WF-05 CONFIRMED public path is `employer_candidates` toggle (`purpose` FAVORITE/RESERVE/SELECTED), not `selected_candidates`. `selected_candidates` (207 rows) is used by dashboard slider/summary widgets (`SelectedCandidateSlider`, `selected-candidate-slider.blade.php`); `position` is a **string**; Eloquent `classGroup()` references **missing** `class_group_id`. A20 lock: do not treat `selected_candidates` as the employer join. |
| **Proposed decision** | **Do not implement `selected_candidates` in M6.** Do not treat the widget table as the Selection event. Historical compatibility continues to **skip step 5** (legacy waterfall). Do **not** fabricate `employer_candidates` or `selected_candidates` rows at M13 for labour/visa/flight without selection. If a future engine records Selection, it uses A20 `employer_candidates` purpose=`SELECTED` (M4 remainder), not M6. |
| **Options** | **A.** Compatibility forever: skip step 5 in any live-status view (legacy). **B.** New records only: require A20 `employer_candidates` purpose=`SELECTED` before labour; historical imported rows stay `LEGACY_UNKNOWN` / skip 5. **C.** Treat `selected_candidates` as formal selection (contradicts widget use + A20). **D.** Backfill synthetic selection for every labour+ candidate (invents history). |
| **Consequences** | A preserves dump and runtime. B is the Design Gate “new engine must add step 5” intent without rewriting history; couples M6 labour create to a table that is not in M6 and has 0 dump rows. C pulls a dashboard table into workflow and fights A20. D violates Rule 1 / quarantine (fabricated parents). |
| **Required approval** | Owner signs A, B, C, or D. **Proposed: A for live-status compatibility + no M6 `selected_candidates`; B only if owner later signs a non-M6 selection engine.** |

### 1.2 Labour prerequisites (WF-06)

| Field | Content |
|---|---|
| **Legacy evidence** | INSERT `labour_contracts` via BREAD; no selection check. `liveStatus()` step 6 = any labour row. Pipeline tick “Labour” = same `labourStatus()`. Hidden for role_id 101/107 (pipeline) and 101/107/109 (sidebar). 1,153 rows; 19 orphan candidate_id; 1 NULL candidate_id; 1 NULL status; 4 expire-before-issue. WF-06 **INFERRED** “selection should precede” — **not enforced**. |
| **Proposed decision** | M6 labour create/update **does not** require Selection / Rapid group / CV. Document-legacy. Expiry tints UI only (`labourStatus(..., expireCheckColor)`); do not demote live-status on expiry. |
| **Options** | **A.** No create-time prerequisite (legacy). **B.** Require A20 selection first (new engine). **C.** Require Rapid/interview group. **D.** Reject expired or expire-before-issue on write (new; dump has inversions). |
| **Consequences** | A can load 1,067 living labour candidates without inventing selection. B blocks most historical labour unless backfill (1.1 D) is also signed. D would fail existing rows at M13 if applied as a hard constraint. |
| **Required approval** | Owner signs A/B/C/D. **Proposed: A.** |

### 1.3 Police prerequisites (WF-07 police half)

| Field | Content |
|---|---|
| **Legacy evidence** | INSERT `police_clearances` independently. Step 7 badge = **police row only**. 606 rows; 14 orphan candidates; 37 NULL `thana_id`; 551 NULL `country_id`. Dual Voyager keys `browse_police_clearance` / `browse_police_clearances`. |
| **Proposed decision** | Police create does **not** require a labour row. Waterfall *display* order (labour before police) is a view, not a write gate, until owner signs enforcement. |
| **Options** | **A.** No labour prerequisite on write (legacy). **B.** Require labour before police create. **C.** Require labour + medical (label text, not runtime). |
| **Consequences** | B invents a gate BREAD never had; imported police without labour would be illegal in the new API. C also invents medical as a police gate. |
| **Required approval** | Owner signs A/B/C. **Proposed: A.** |

### 1.4 Medical placement in the ladder

| Field | Content |
|---|---|
| **Legacy evidence** | Step 7 **label** = `Police Clearance & Medical`. Runtime checks **police only**. `medicals` (384) has no `liveStatus()` call; pipeline ticks omit medical; sidebar item is “Medical & Ministry”. `Medical` model is an empty stub (no `candidate()` relation). WF-07 INFERRED both INSERTs; BREAD is independent. |
| **Proposed decision** | Medical is an **independent candidate document**, not a live-status step and **not** a prerequisite for step 7, visa, ARC, or flight. Do not add medical to the waterfall unless A03+A04 are signed together. |
| **Options** | **A.** Keep medical off the ladder (legacy runtime). **B.** Require medical AND police for step 7 (matches label; changes badge for police-without-medical candidates). **C.** Insert a new step for medical (changes 10-row `live_status` contract — also A04). |
| **Consequences** | B retcons the badge and fails live-status compatibility tests. C is a new product. A keeps 384 medicals as documents + M9 file refs. |
| **Required approval** | Owner signs A/B/C. **Proposed: A.** |

### 1.5 Visa prerequisites (WF-08)

| Field | Content |
|---|---|
| **Legacy evidence** | INSERT `visa_immigrations`; waterfall uses any visa row → step 8. No BREAD check that police exists. Dump has **no** `visa_immigrations.country_id` (WF-08 inferred a column that is ABSENT). 1,045 rows; 19 orphan candidates; 27 expire-before-issue; 710 already expired as of 2026-09-07. |
| **Proposed decision** | Visa create does **not** require police (or medical). Do **not** add `country_id` in M6. Expiry does not demote live-status. |
| **Options** | **A.** No police/medical prerequisite (legacy). **B.** Require police. **C.** Require police + medical. **D.** Add `country_id` (new column; not in dump). |
| **Consequences** | B/C invent write gates. D is a schema invention (A14/A03); omit unless signed. Expiry-as-fail would demote 710 visas. |
| **Required approval** | Owner signs A/B/C and separately D yes/no. **Proposed: A; no `country_id`.** |

### 1.6 ARC prerequisites

| Field | Content |
|---|---|
| **Legacy evidence** | `arcStatus()` exists; **unused** by `liveStatus()`. Pipeline tick “ARC” is independent. 632 rows; `is_lifetime` Y 377 / N 255; 12 orphan candidates. Not in WF-06–WF-10 ladder. |
| **Proposed decision** | ARC is an independent document. Recording ARC does **not** advance live-status. No labour/police/medical/visa prerequisite on create. |
| **Options** | **A.** Independent document (legacy). **B.** Place ARC on the ladder (new step or replace another step — A04). **C.** Require visa (or police) before ARC. |
| **Consequences** | B/C change compatibility and need A04. A matches code. |
| **Required approval** | Owner signs A/B/C. **Proposed: A.** |

### 1.7 Flight prerequisites (WF-10)

| Field | Content |
|---|---|
| **Legacy evidence** | INSERT `flight_schedules`; any row → step **10** (highest wins) + extraText date/time. BREAD does not check step 9 payment. `manpowerStatus()` is approved `payment_requests` with tautological `bill_title` (FIN-BUG). No `status` column on flights. 640 rows; 14 orphan; 4 NULL `candidate_id`. |
| **Proposed decision** | Flight create does **not** call payment APIs and does **not** require an approved manpower fee. M6 must not implement WF-09. Live-status step 9 remains M7-coupled; M6 must not “fix” the tautology. |
| **Options** | **A.** No payment prerequisite on flight write (legacy BREAD). **B.** Require step 9 payment (couples M6 to M7 / A01 / A16). **C.** Require visa only. |
| **Consequences** | B cannot be implemented until M7 and would encode FIN-BUG unless A16 is also signed. A keeps M6 finance-free. |
| **Required approval** | Owner signs A/B/C. **Proposed: A.** |

### 1.8 Rollback / state-transition rules

| Field | Content |
|---|---|
| **Legacy evidence** | No automated rollback on overseas BREAD. Updates overwrite. Voyager `delete_*` keys exist; target platform **must not** add `*.delete` (M1–M5 lock). Design Gate PROPOSED compensating `*_VOIDED` / `FLIGHT_CANCELLED` events — **not signed**. Overseas BREAD does not write `candidates.status`. |
| **Proposed decision** | No physical delete. No compensating workflow events until A03 signs event names. In-place update (legacy). Soft-void only if operations later approves a compensating event. Document modules do **not** write `candidates.status`. M6 does **not** write `class_group_id` or exam results. |
| **Options** | **A.** In-place update only; no void events in M6. **B.** Signed compensating events (`LABOUR_CONTRACT_VOIDED`, etc.) without DELETE. **C.** Allow Voyager-style hard delete (forbidden by lock). |
| **Consequences** | C reopens M1–M5. B needs event-store design and audit names. A is implementable after GO without inventing a workflow engine. |
| **Required approval** | Owner signs A or B. C is **not available**. **Proposed: A for M6; B deferred.** |

**A03 package approval:** owner must sign 1.1–1.8 together (or explicitly defer 1.1 to A20/M4 and 1.7 payment coupling to M7). Until signed: **no M6 implementation**.

---

## 2. A14 — terminology / integration

**Status: APPROVED — 2026-09-07 Owner. No implementation in this task.**

### 2.1 Police vs ARC naming

| Field | Content |
|---|---|
| **Legacy evidence** | Table `police_clearances`; UI “Police Clearance” / pipeline “Police Clear.”. Table `arcs`; UI “ARC”; report `arc-report`. No application string “PCC” / “police verification” as the resource name. Dual Design Gate prefixes: `candidate.police_clearance.*` vs `workflow.police_clearances.*`. |
| **Proposed decision** | Official resource names stay **Police Clearance** and **ARC**. One permission pair `overseas.police_clearance.*` (collapse dual Voyager keys). Do not rename to PCC / ACR / police verification in APIs or schema. |
| **Options** | **A.** Keep Police Clearance + ARC. **B.** Rename (owner supplies exact strings). **C.** Keep both dual catalogues (two parallel keys). |
| **Consequences** | B breaks report/UI parity until A04/M10 also change. C duplicates grants. |
| **Required approval** | Owner signs A or supplies B strings. **Proposed: A; collapse dual keys.** |

### 2.2 `acr_file_path` treatment

| Field | Content |
|---|---|
| **Legacy evidence** | Column `arcs.acr_file_path` (typo) — 46 empty / 586 present. `column-mapping.csv`: source `acr_file_path` → target `arc_file_id`, preserve source-column lineage. ARC report document field is the typo name. |
| **Proposed decision** | Keep source-column lineage: map `acr_file_path` → `arc_file_id`. Do not rename the source column in dumps. Do not invent `acr` as a second business entity. Bytes remain M9. |
| **Options** | **A.** Map as CSV (proposed). **B.** Keep target column name `acr_file_path`. **C.** Treat ACR as a distinct document type. |
| **Consequences** | C invents a product. B preserves the typo into PostgreSQL. A matches Design Gate mapping. |
| **Required approval** | Owner signs A/B/C. **Proposed: A.** |

### 2.3 No external government / API integration in the legacy repo

| Field | Content |
|---|---|
| **Legacy evidence** | Application PHP under `src/app` has **no** `GuzzleHttp`, `SoapClient`, `Http::`, or `curl_` client to a police-clearance, ARC, visa, or immigration API. `Illuminate\Support\Facades\Http` is only registered in `src/config/app.php`. Overseas writes are Voyager BREAD + local file paths. |
| **Proposed decision** | **Confirm: no external government/API integration exists in this legacy repo.** A14 must not be closed by inventing a government contract. |
| **Options** | **A.** Confirm none (evidence). **B.** Owner names an external system not in this repo (then M6 still cannot implement it without a contract). |
| **Consequences** | Inventing an API in M6 violates Rule 1 and A14. |
| **Required approval** | Owner signs **A** (or names the system for B). **Proposed: A.** |

### 2.4 Ports/adapters only vs actual integrations

| Field | Content |
|---|---|
| **Legacy evidence** | Same as 2.3 — there is nothing to port. |
| **Proposed decision** | If M6 later needs a seam, provide **internal ports/adapters only** (document store, candidate-access, audit). **No** live government/immigration/police/ARC integrations in M6. |
| **Options** | **A.** Ports/adapters only; no actual external integrations. **B.** Build real integrations now (blocked: no contract in repo). |
| **Consequences** | B cannot be evidenced. A keeps M6 a document/workflow module. |
| **Required approval** | Owner signs A. **Proposed: A.** |

---

## 3. A04 — exact live-status labels

**Status: APPROVED (M6 slice) — 2026-09-07 Owner. Preserve exact dump strings if a badge is implemented. Do not silently correct spelling. Retirement schedule not opened. No implementation in this task.**

Verbatim dump (`live_status`, 10 rows):

| step_no | name |
|---:|---|
| 1 | Registration |
| 2 | Profile Update/CV |
| 3 | Group Name |
| 4 | Interview |
| 5 | Selection |
| 6 | **Labour Contact** |
| 7 | Police Clearance & Medical |
| 8 | **VISA/Work Permite** |
| 9 | Manpower Status |
| 10 | Flight |

Pipeline ticks use different strings: Registration, CV Upload, Class Group, Labour, Police Clear., Visa, BMET Train., ARC, Manpower, Flight.

| Field | Content |
|---|---|
| **Legacy evidence** | Badge text = `LiveStatus.name` for the waterfall `step_no`, plus unescaped flight extraText (`{!! $liveStatus !!}`). Step 6 dump spelling is **Contact**, not Contract (pipeline/sidebar say “Labour” / “Labour Contract”). Step 8 dump spelling is **Permite**. Step 7 name includes Medical though runtime is police-only (1.4). |
| **Proposed decision** | If M6 ships a badge or compatibility test, **preserve dump strings exactly**, including “Labour Contact” and “VISA/Work Permite”. Do not silently correct spelling. Pipeline tick labels are a separate UI surface; do not merge them into `live_status` without a signed map. Retirement schedule can be “none in M6”. If A04 is deferred, M6 UI **omits** the badge. |
| **Options** | **A.** Preserve exact dump labels in any badge/test. **B.** Correct to “Labour Contract” / “Visa/Work Permit” now (breaks compatibility). **C.** Omit badge from M6; A04 later. **D.** Event-sourced view with new codes plus a compatibility adapter (Design Gate narrative) — still needs exact adapter strings = A or B. |
| **Consequences** | B fails golden badge tests and M10 contracts that echo these names. C unblocks document CRUD without closing A04. A is the compatibility path. |
| **Required approval** | Owner signs A, B, or C (D includes A or B). **Proposed: A if badge ships; otherwise C.** |

---

## 4. Confirm that M6 does NOT post payments or modify finance / ledger data

**Status: APPROVED — CONFIRM A. 2026-09-07 Owner.**

| Field | Content |
|---|---|
| **Legacy evidence** | Step 9 / pipeline “Manpower” uses `payment_requests` (702 rows; 539 approved with `bill_title` containing Manpower; tautology matches any approved request). Ajax `candidatePaymentApproval` has **no** `authorize()` and assignment-not-comparison `bill_title = 'Medical Fee'` (FIN-BUG, A01/A02/A16). That path writes `payment_requests`, `payments`, agent balances, and `candidates.medical_fee_payment_id`. Overseas BREAD does **not** insert payments. License rows have no amount column. |
| **Proposed decision** | **CONFIRM:** M6 must not insert/update `payment_requests`, `payments`, ledgers, agent/sub-agent balances, or candidate payment FK fields (`medical_fee_payment_id`, admission, final-group). Do not expose payment-request create/approve. Do not “correct” FIN-BUG in M6. WF-09 is **M7**. |
| **Options** | **A.** Confirm isolation (required by Rule 4). **B.** Allow M6 to post manpower/medical fees (forbidden while A01/A02 OPEN). |
| **Consequences** | B is a hard-gate violation. A may mean M6 live-status cannot faithfully show step 9 until M7 exists — use option 3.C (omit badge) or a read-only fixture later. |
| **Required approval** | Owner signs **A**. **Proposed: A.** |

---

## 5. Confirm whether `selected_candidates` remains outside M6 scope

**Status: APPROVED — CONFIRM A. 2026-09-07 Owner.**

| Field | Content |
|---|---|
| **Legacy evidence** | 207 rows; 8 orphan candidates; no `status` column. Widget/slider/summary SQL, not `liveStatus()`. `selected-candidate-report` queries `candidates.status='A'` + visa + flight — **not** this table. Design Gate table-mapping KEEP → `candidate.selected_candidates` (MEDIUM). PRE-GATE: adjacent, not M6 core. A20: different table from employer assignment. |
| **Proposed decision** | **CONFIRM: `selected_candidates` remains outside M6.** Do not add the table, API, or permission keys in M6. Disposition: dashboard/M12 or a later A03/M4 remainder — not this milestone. Q-SEL-CAND stays an M13 code if that table is imported later. |
| **Options** | **A.** Outside M6 (proposed). **B.** Pull into M6 as Selection (conflicts 1.1 proposed + A20). **C.** Import-only at M13 with no M6 API. |
| **Consequences** | B reopens 1.1 and A20. C is migration-only and still not an M6 API. |
| **Required approval** | Owner signs **A** (or B/C). **Proposed: A.** |

---

## 6. Review the proposed 14-key M6 RBAC catalogue (do not grant)

**Status: APPROVED as catalogue. Runtime grants: NOT APPROVED. 2026-09-07 Owner.**

**Catalogue is PROPOSED. Runtime grants are a separate gate (M5 T9 pattern). This review must not seed `ensure-permissions` or grant `overseas.*`.**

| PROPOSED key | Intent |
|---|---|
| `overseas.medical.read` / `overseas.medical.manage` | `candidate.medicals` |
| `overseas.police_clearance.read` / `overseas.police_clearance.manage` | police (one pair; collapse Voyager duplicate) |
| `overseas.arc.read` / `overseas.arc.manage` | `candidate.arcs` |
| `overseas.visa.read` / `overseas.visa.manage` | `candidate.visa_immigrations` |
| `overseas.labour_contract.read` / `overseas.labour_contract.manage` | `candidate.labour_contracts` |
| `overseas.flight.read` / `overseas.flight.manage` | `candidate.flight_schedules` |
| `operations.license.read` / `operations.license.manage` | `operations.licenses` + `license_positions` |

**14 keys.** No `*.delete`. Teacher: **none** (LOCKED). Role 110: do not port.

Grant **sketch** from BREAD (NOT APPROVED, not seeded): owner/employee/agency labour yes; agent/sub_agent labour **no**; company overseas **BLOCKED** (no Voyager browse); administrator **UNRESOLVED** (do not copy exam denial/grant by analogy).

| Field | Content |
|---|---|
| **Legacy evidence** | Voyager browse grants in PRE-GATE §7; hardcoded UI hides in addition to BREAD. Frontend hiding is not authorization. |
| **Proposed decision** | **CONFIRM the 14 keys as catalogue only.** Collapse dual police keys. **Do not grant at runtime.** Company / administrator / candidate self-access remain unanswered and default to **no grant**. |
| **Options** | **A.** Accept 14 keys; grants later. **B.** Rename/split keys (owner lists). **C.** Drop licenses from M6 catalogue (licenses stay a later operations slice). |
| **Consequences** | Accepting the catalogue is **not** a grant. Seeding now would violate the M5 runtime-grant-as-separate-gate rule. |
| **Required approval** | Owner signs A, B, or C. **Proposed: A; grants NOT APPROVED.** |

---

## 7. Confirm proposed target-domain separation

**Status: APPROVED — A keep CSV. 2026-09-07 Owner.**

CSV / PRE-GATE proposal (**not applied**):

| Target | Schema |
|---|---|
| `medicals`, `arcs`, `visa_immigrations`, `labour_contracts`, `flight_schedules` | `candidate` |
| `police_clearances` | `workflow` |
| `licenses`, `license_positions`, `live_status` | `operations` |

`09-postgresql-prisma.md` originally listed schemas without `operations`. M5 already created `operations` and `workflow`. **Do not remove `operations`.**

| Field | Content |
|---|---|
| **Legacy evidence** | Placement is Design Gate CSV, not a signed “fix”. Police in `workflow` while medical/ARC/labour/visa/flight in `candidate` is an open consistency question. Licenses are companier masters; they do not participate in `liveStatus()`. |
| **Proposed decision** | **CONFIRM CSV placement.** Do **not** silently move police/medical/ARC/labour/visa/flight between domains. Any restructuring requires a new owner approval. Keep `operations` for licenses + `live_status`. |
| **Options** | **A.** Keep CSV split (police `workflow`, others `candidate`, licenses `operations`). **B.** Colocate police with medical under `candidate`. **C.** Move all overseas documents into `workflow`. |
| **Consequences** | Silent moves break `legacy_key_map` target_type and M5 schema already shipped. B/C are allowed only as a signed change. |
| **Required approval** | Owner signs A, B, or C. **Proposed: A.** |

---

## 8. Confirm orphan / quarantine policy and migration traceability

**Status: APPROVED — A extend M5 policy. 2026-09-07 Owner.**

M5 owner package already **APPROVED** the quarantine policy and `legacy_key_map` traceability. M13 executes. M6 pre-gate **proposes additional codes**; it does not import.

Proposed M6 codes (counts from production profile; not applied):

| Code | Finding | Count |
|---|---|---:|
| Q-LC-CAND | labour `candidate_id` missing candidate | 19 |
| Q-LC-NULL | labour `candidate_id` NULL | 1 |
| Q-VISA-CAND | visa orphan candidate | 19 |
| Q-FLT-CAND | flight orphan candidate | 14 |
| Q-FLT-NULL | flight `candidate_id` NULL | 4 |
| Q-PC-CAND | police orphan candidate | 14 |
| Q-MED-CAND | medical orphan candidate | 13 |
| Q-ARC-CAND | ARC orphan candidate | 12 |
| Q-SEL-CAND | selected_candidates orphan (adjacent; out of M6 if §5 A) | 8 |
| Q-LP-LIC | `license_positions.license_id = 1` missing license | 17 |
| Q-LIC-COMP | `licenses.companier_id` NULL | 1 |
| Q-PC-THANA | police `thana_id` NULL | 37 |
| Q-PC-CTY | police `country_id` NULL | 551 |
| Q-MED-CTY | medical `country_id` NULL | 33 |
| Q-DATE-* | expire_date < issue_date | labour 4, police 5, medicals 2, arcs 4, visa 27 |
| Q-FILE-* | empty file path with living row | inventory at M9 |

Also keep: NULL labour status (1); expired-but-`'A'` rows; empty files as inventory not deletion. License id 1 gap: keep 17 positions quarantined. **No synthetic parent.**

Traceability (M5 §9, unchanged):

```
legacy_key_map(
  source_system, source_table, source_id,
  target_type, target_id,
  migration_run_id, source_row_hash, migrated_at
)
```

Proposed `target_type` values: `candidate.medicals`, `workflow.police_clearances`, `candidate.arcs`, `candidate.visa_immigrations`, `candidate.labour_contracts`, `candidate.flight_schedules`, `operations.licenses`, `operations.license_positions`, `operations.live_status`. Unique on source triplet; same transaction as business INSERT. Reconciliation: `LIVE + QUARANTINED = SOURCE`.

| Field | Content |
|---|---|
| **Legacy evidence** | No declared FKs on these edges. Orphans are missing candidate ids (deleted parents), not parse errors. Zero dates on these issue/expire columns: **0**. |
| **Proposed decision** | **CONFIRM:** extend the M5-approved quarantine policy with the M6 codes above. Never delete orphans to satisfy FKs. Do not auto-void date inversions or expired `'A'`. M6 must **not** run production import. M13 remains the load gate. File emptiness is M9 inventory. Q-SEL-CAND only if `selected_candidates` is later in scope. |
| **Options** | **A.** Accept codes + M5 policy extension (proposed). **B.** Drop/rename codes (owner lists). **C.** Delete or reassign orphans at load (forbidden by Rule 1 / M5 lock). |
| **Consequences** | C reopens M5. A does not authorize import. Deferred FKs `NOT VALID` after quarantine remain the 09 load sequence. |
| **Required approval** | Owner signs **A** (or B). C is **not available**. **Proposed: A.** |

---

## Owner sign-off grid

**Signed 2026-09-07 by Owner.** Decisions match `docs/M6-OWNER-REVIEW.md` as written. This is PRE-GATE approval only.

| # | Decision | Proposed | Owner | Date | Name |
|---|---|---|---|---|---|
| 1.1 | Step 5 Selection | A (+ no M6 `selected_candidates`) | **A** — skip Step 5 in compatibility/live-status view; `selected_candidates` remains outside M6 | 2026-09-07 | Owner |
| 1.2 | Labour prerequisites | A (none) | **A** — no create-time prerequisite | 2026-09-07 | Owner |
| 1.3 | Police prerequisites | A (none) | **A** — no labour prerequisite | 2026-09-07 | Owner |
| 1.4 | Medical on ladder | A (off ladder) | **A** — independent / off the live-status ladder | 2026-09-07 | Owner |
| 1.5 | Visa prerequisites | A; no `country_id` | **A** — no police/medical prerequisite; do not add `country_id` | 2026-09-07 | Owner |
| 1.6 | ARC prerequisites | A (independent) | **A** — independent process; does not advance the badge | 2026-09-07 | Owner |
| 1.7 | Flight prerequisites | A (no payment gate) | **A** — no payment prerequisite; no M7 coupling | 2026-09-07 | Owner |
| 1.8 | Rollback | A (in-place; no DELETE; no void events yet) | **A** — in-place updates; no hard delete | 2026-09-07 | Owner |
| 2.1 | Police / ARC names | A; collapse dual keys | **A** — keep Police Clearance and ARC; collapse dual keys | 2026-09-07 | Owner |
| 2.2 | `acr_file_path` | A → `arc_file_id` + lineage | **A** — map `acr_file_path` → `arc_file_id`; retain source-column lineage | 2026-09-07 | Owner |
| 2.3 | No government API in repo | A confirm | **A** — confirm none in the legacy repository | 2026-09-07 | Owner |
| 2.4 | Integrations | A ports/adapters only | **A** — internal ports/adapters only; no live external integrations | 2026-09-07 | Owner |
| 3 | Live-status labels | A if badge; else C omit | **A** — if badge implemented, preserve exact strings including “Labour Contact”, “VISA/Work Permite”, and existing Step 7 label; do not silently correct spelling | 2026-09-07 | Owner |
| 4 | No finance writes | A CONFIRM | **CONFIRM A** — M6 must not write `payment_requests`, payments, ledgers, or candidate payment FKs; WF-09/manpower fee remains M7 | 2026-09-07 | Owner |
| 5 | `selected_candidates` out of M6 | A CONFIRM | **CONFIRM A** | 2026-09-07 | Owner |
| 6 | 14-key catalogue | A; grants NOT APPROVED | **A** catalogue accepted; runtime grants **NOT APPROVED**; teacher remains without `candidate.read`; no `*.delete` | 2026-09-07 | Owner |
| 7 | Domain placement | A keep CSV | **A** — police → `workflow`; medical/ARC/labour/visa/flight → `candidate`; licenses/`live_status` → `operations` | 2026-09-07 | Owner |
| 8 | Quarantine + `legacy_key_map` | A extend M5 policy | **A** — extend M5-approved quarantine and `legacy_key_map`; never silently repair/delete orphans | 2026-09-07 | Owner |

---

## What this package does not do

- No M6 implementation
- No Prisma / migration / API / UI / Laravel / dump / CI change
- No runtime permission change / no `ensure-permissions`
- No production import
- No M7 start
- No reopening of A05, A07, A20, A06, teacher `candidate.read`, administrator exam keys, or `*.delete`

**M6 OWNER REVIEW: APPROVED**

**M6 IMPLEMENTATION STATUS: NOT STARTED**

**M6 GO/NO-GO IMPLEMENTATION GATE: NO-GO**

PRE-GATE decisions are signed. Implementation remains **NO-GO** until `docs/M6-IMPLEMENTATION-GO.md` is **explicitly issued**. That package is **NOT ISSUED**.

**M6 IMPLEMENTATION GO: NOT READY.** Runtime grants **NOT APPLIED**. Do not implement M6. Do not start M7.
