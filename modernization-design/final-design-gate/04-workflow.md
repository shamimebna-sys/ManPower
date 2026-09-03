# 04 — Candidate Workflow Transition Matrix

`workflow-transitions.csv` is the normative artifact. This document explains the structure,
evidence basis, key findings, and open questions.

Every transition row contains the 10 mandatory fields specified by the project sponsor:

| # | Field | Meaning |
|---|-------|---------|
| 1 | current_state | State the entity is in before the event |
| 2 | next_state | State the entity moves into after a successful event |
| 3 | allowed_actor_roles | Which legacy role IDs and proposed stable keys may initiate |
| 4 | preconditions | What must already exist or be true for the event to be accepted |
| 5 | required_documents | File/document records that must exist before the transition |
| 6 | required_payment | Payment request or ledger condition that must be satisfied |
| 7 | validation_rules | Field, uniqueness, and business-logic checks applied before commit |
| 8 | database_changes | Which tables are written and in what order |
| 9 | audit_event | What must be recorded in the audit/workflow event log |
| 10 | rollback_correction_rule | How to reverse, compensate, or correct a mistaken transition |

---

## Evidence basis

- `live_status` table INSERT data in dump: 10 rows, step_no 1–10 — **CONFIRMED**
- `CommonClass::liveStatus()` (`src/app/Helpers/CommonClass.php:539–576`) — **CONFIRMED** code
- Processing-stage models and controllers (`VoyagerLabourContractController`,
  `VoyagerPoliceClearanceController`, `VoyagerMedicalMinistryController`,
  `VoyagerVisaImmigrationController`, `VoyagerManpowerController`, `VoyagerArcController`,
  `VoyagerFlightScheduleController`) — **CONFIRMED** existence; per-stage preconditions
  **INFERRED**
- Payment approval flow in `VoyagerAjaxController.php:184–284` — **CONFIRMED**
- Candidate account approval in `VoyagerAjaxController.php:146–177` — **CONFIRMED**
- Auto-inactivation in `ApiController::inactivateCandidates()` — **CONFIRMED**
- Exam result publication in `VoyagerExamResultController`, `ExamResultPublishAction` — **CONFIRMED**

---

## Critical gaps (CONFIRMED from source)

### Step 5 — Selection is skipped in liveStatus()
The `live_status` table seeds step 5 as "Selection". However, `CommonClass::liveStatus()`
never queries `selected_candidates`; it jumps directly from step 4 (Rapid group) to step 6
(labour contract). Step 5 is therefore **CONFIRMED visible in the DB label** but
**CONFIRMED absent from the runtime derivation**.

The new system must:
1. Preserve the "Selection" label in legacy-compatibility mode.
2. Expose an explicit `selected_candidates` transition in the workflow engine.
3. Not advance a candidate past step 5 in the new system without a `selected_candidates`
   row, even if legacy imported candidates have none.
4. Backfill rule for legacy records: **REQUIRES APPROVAL (A03)**.

### employer_candidates schema drift
Code queries `where('status', 'A')` and writes `status = 'I'`, but the DDL (confirmed from
dump lines 11364–11371) has no `status` column. The insert path (`EmployerCandidate::insert`)
does not include `status`. Therefore the filter on `status = 'A'` silently returns all rows
when `status` is absent.

New system: add `status` column with explicit ACTIVE/INACTIVE values and a `purpose` ENUM
(FAVOURITE / RESERVE / SELECTED). **REQUIRES APPROVAL (A03)** for exact value set.

---

## Live-status compatibility rule
Legacy live-status labels must be derivable from new system workflow events so that existing
reports and UI that display the 10 labels continue to work. The new event store is
authoritative; the labels are a view over it. Historical imported candidates carry
`LEGACY_UNKNOWN` where a stage record exists in the new system but the legacy source data is
ambiguous.

---

## Transitions not covered by the live-status ladder

The following cross-cutting transitions also require workflow event records:

- Candidate account status P → A (admin approval of registration)
- Candidate account status A → I (manual inactivation / auto-inactivation cron)
- Candidate replacement (A replaces B: company handoff, remarks, companier_id transfer)
- Exam result PASS → group promotion (class_group_id change)
- Payment request P → A / P → R (agent wallet debit or rejection)
- Wallet deposit P → A / P → R (balance credit or rejection)

These are documented as separate rows in `workflow-transitions.csv`.
