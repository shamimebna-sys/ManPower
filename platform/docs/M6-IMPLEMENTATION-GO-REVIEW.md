# M6 IMPLEMENTATION GO REVIEW — ISSUE RECORD

**Review date:** 2026-09-07  
**Owner gate decisions recorded:** 2026-09-07 — Owner  
**Implementation GO issued:** 2026-09-07 — Owner  
**Baseline commit:** `5cd3878` (TypeScript config cleanup; CI Run **#11** PASS)  
**Reviewed:** `docs/M6-IMPLEMENTATION-GO.md`  
**Companions:** `docs/M6-OWNER-REVIEW.md` (PRE-GATE **APPROVED**), `docs/M6-PRE-GATE.md`, `docs/M6-PRODUCTION-PROFILE.md`

This document verifies that the GO sheet **issues** M6 implementation authorization. This review step does **not** write application code, Prisma, migrations, tests, CI, or grants.

---

## Two separate states (do not collapse)

| State | Status |
|---|---|
| **1. Owner approval of G1–G12** | **APPROVED** 2026-09-07 Owner |
| **2. M6 IMPLEMENTATION GO issuance** | **ISSUED** 2026-09-07 Owner |

**M6 OWNER GO GATES: APPROVED**

**M6 IMPLEMENTATION GO: ISSUED**

**M6 IMPLEMENTATION: AUTHORIZED**

**M6 IMPLEMENTATION STATUS: NOT STARTED** (ISSUE record only)

**M7: OUT OF SCOPE / BLOCKED**

**M13 PRODUCTION MIGRATION: OUT OF SCOPE**

**A01/A02 FINANCE GATES: STILL HARD BLOCKS**

**Runtime grants: AUTHORIZED FOR IMPLEMENTATION — NOT APPLIED IN THIS ISSUE STEP**

The **ISSUE** row on the GO sheet is signed. Subsequent M6 work may implement the approved scope, including exact apply of the G3/G7 matrix. This documentation step seeded nothing.

---

## Numbering note

The first draft of `M6-IMPLEMENTATION-GO.md` used a different G1–G12. **This review uses the live numbering on the GO sheet.** Timezone (A08) stays deferred to the M5 `timestamptz` pattern and is not a numbered GO gate.

---

## How to read kinds

| Label | Meaning |
|---|---|
| **APPROVED (gate)** | Owner signed this G-gate. |
| **Already locked** | Signed earlier. |
| **ISSUED** | Implementation authorized for approved scope. |
| **Runtime grants AUTHORIZED FOR IMPLEMENTATION** | Later M6 work may seed/apply G3/G7 exactly. |
| **Runtime grants NOT APPLIED in this ISSUE step** | This record did not seed. |

---

## RBAC vocabulary (do not collapse)

| Layer | Status | Meaning |
|---|---|---|
| **APPROVED CATALOGUE** | **APPROVED** 2026-09-07 Owner | 14 keys. No `*.delete`. Teacher has no `candidate.read`. |
| **APPROVED RUNTIME-GRANT DESIGN** | **APPROVED** 2026-09-07 Owner (G3/G7) | Who receives which key. |
| **IMPLEMENTATION AUTHORIZATION TO APPLY GRANTS** | **AUTHORIZED** by ISSUE | Subsequent implementation may seed/apply exactly. No extra keys. |
| **ACTUALLY APPLIED RUNTIME GRANTS** | **NONE** (this ISSUE step) | `ensure-permissions` was not run. Super-admin upsert belongs to implementation, not this record. |

G7 labour for agent/sub_agent/agency is an **intentional target-system authorization decision; not legacy parity.** Do not silently remove or weaken. Do not disguise it as parity.

---

## Locked inputs (do not reopen)

| ID | Statement |
|---|---|
| A03 | All signed **A** decisions (skip Step 5; no create-time labour/police/visa/flight gates; medical and ARC off ladder; visa no `country_id`; flight no payment gate; in-place update; no hard delete) |
| A14 | Police Clearance + ARC names; `acr_file_path` → `arc_file_id` with lineage; no government API; adapters only |
| A04 | Exact dump strings including `Labour Contact`, `VISA/Work Permite`, Step 7 `Police Clearance & Medical`. G4 **SHIP**. |
| A05 / A06 / A07 / A20 | Locked. |
| Teacher | **NO** `candidate.read`. Rule B forbidden. |
| Delete | No `*.delete` |
| Finance | Zero finance writes. WF-09 is M7. A01/A02 remain **HARD BLOCKS**. |
| `selected_candidates` | Outside M6. Step 5 Selection is not implemented. |
| PG16 | Mandatory for integration CI |
| Quarantine | M5 policy + M6 codes. Production load is **M13**. |
| Architecture | Node/TS, Express, Next.js/React, Prisma, existing modular monolith, existing auth/RBAC/audit, existing candidate-access, existing private-file refs, UUID IDs. No Laravel. No unnecessary redesign. No CI change unless an implementation defect requires it. |

---

## Approved implementation scope (ISSUE)

1. Medical  
2. Police Clearance  
3. ARC  
4. Labour Contract  
5. Visa  
6. Flight  
7. License  
8. Overseas compatibility / live-status read model / badge  

Full HTTP/API + UI for those domains.

### Explicitly out of scope

`selected_candidates`; Step 5 Selection implementation; manpower fee; payment requests; invoices; wallet/ledger; any finance write; M7; external government API integration; public file storage; hard delete; automatic `candidates.status` writes; production dump import (M13).

---

## G1 — M6 scope and exact boundaries

| Field | Content |
|---|---|
| **Owner decision** | **APPROVED** 2026-09-07. Full HTTP/API + UI for medical, police clearance, ARC, labour contract, visa, flight, license, overseas status read. |
| **ISSUE** | Implementation authorized for that surface only. |
| **GO / NO-GO** | **GO** |

---

## G2 — overseas workflow implementation boundaries

| Field | Content |
|---|---|
| **Owner decision** | **Already locked** A03 all **A**. Use locked workflow decisions. |
| **GO / NO-GO** | **GO** |

---

## G3 — runtime RBAC grant matrix

| Field | Content |
|---|---|
| **Owner decision** | **APPROVED** design 2026-09-07. ISSUE authorizes exact apply in implementation: super_admin all 14; owner/administrator/employee all operational M6; agent/sub_agent/agency candidate-scoped overseas, no license; company/candidate/employer/teacher no M6 grant. Do not invent additional permissions or grants. |
| **This ISSUE step** | Grants **not applied**. |
| **GO / NO-GO** | **GO** |

---

## G4 — live-status badge decision

| Field | Content |
|---|---|
| **Owner decision** | **APPROVED SHIP**. Exact legacy labels. Step 5 skipped. Medical off waterfall. ARC off waterfall. Read-only compatibility. Must not introduce new prerequisites. Must not write `candidates.status`. |
| **GO / NO-GO** | **GO** |

---

## G5 — license HTTP/UI vs schema-only

| Field | Content |
|---|---|
| **Owner decision** | **APPROVED FULL HTTP/API + UI**. Companier-scoped. Do NOT repair, delete, merge, or silently modify the 17 orphan license-position rows. |
| **GO / NO-GO** | **GO** |

---

## G6 — `latest()` / current-row semantics

| Field | Content |
|---|---|
| **Owner decision** | **APPROVED.** `created_at DESC`, then source/legacy ID DESC. No implicit status filter. Duplicates allowed. Deterministic tie-break required. No hard delete. |
| **GO / NO-GO** | **GO** |

---

## G7 — overseas access scope by role

| Field | Content |
|---|---|
| **Owner decision** | **APPROVED WITH EXPLICIT EXCEPTION.** Use approved matrix. Candidate-scoped access MUST use existing candidate-access. Teacher forbidden from `candidate.read` and M6 candidate browsing. Company/candidate/employer no M6 grant. |
| **Explicit exception** | Agent/sub_agent/agency labour-contract access is an **intentional TARGET authorization decision and is NOT legacy parity.** Do not silently remove or weaken. |
| **This ISSUE step** | Matrix **not seeded**. Apply is authorized in implementation. |
| **GO / NO-GO** | **GO** |

---

## G8 — PostgreSQL 16 integration-test coverage

| Field | Content |
|---|---|
| **Owner decision** | **APPROVED.** Implement complete PG16 plan. All live DB tests MUST execute against PostgreSQL 16 in CI. Do not weaken tests to make them pass. Coverage: migration, FK, orphan/quarantine, IDOR, role authorization, candidate scoping, agent/sub-agent/agency scope, company/employer/teacher denial, deterministic `latest()`, duplicates, all M6 CRUD/update, license companier scope, live-status compatibility, audit events, private file refs, no public URLs, no finance writes, no M7, rollback/in-place update, `legacy_key_map`, all approved role/permission behavior. |
| **This ISSUE step** | Tests **not written**. |
| **GO / NO-GO** | **GO** |

---

## G9 — private file references / storage boundaries

| Field | Content |
|---|---|
| **Owner decision** | **Already locked.** Private UUID `*_file_id`-style refs. Bytes/storage remain outside M6. |
| **GO / NO-GO** | **GO** |

---

## G10 — audit / events

| Field | Content |
|---|---|
| **Owner decision** | **APPROVED** exact event list on the GO sheet. Prohibit `*_VOIDED`, hard-delete events, automatic `candidates.status` events. Legacy has no named event store. |
| **GO / NO-GO** | **GO** |

---

## G11 — migration, quarantine, `legacy_key_map`

| Field | Content |
|---|---|
| **Owner decision** | **Already locked.** Do not migrate production data in M6. Production migration remains **M13**. Use `legacy_key_map`/quarantine architecture where target structures require it; do not import the dump. |
| **GO / NO-GO** | **GO** |

---

## G12 — explicit M7 / payment boundary

| Field | Content |
|---|---|
| **Owner decision** | **Already locked.** Zero finance writes. No M6 handler may create/update/delete payment records, create/update invoices, alter wallet balances, create ledger entries, post manpower fees, or invoke M7 logic. A01/A02 remain **HARD BLOCKS**. M7 remains **OUT OF SCOPE / BLOCKED**. |
| **GO / NO-GO** | **GO** |

---

## Legacy parity rule (ISSUE)

Preserve legacy behavior where Owner Review requires parity.

Where an approved target-system authorization decision intentionally differs from legacy, document the difference explicitly. Do not disguise it as parity. Do not reinterpret approved gates.

---

## Decision board

| Gate | Topic | Kind | GO / NO-GO |
|---|---|---|---|
| G1 | Full HTTP/API + UI | **APPROVED** | **GO** |
| G2 | Overseas workflow (A03 A) | **Already locked** | **GO** |
| G3 | Runtime-grant design; apply authorized | **APPROVED**; not applied in this step | **GO** |
| G4 | SHIP live-status badge | **APPROVED** | **GO** |
| G5 | FULL license HTTP/API + UI | **APPROVED** | **GO** |
| G6 | Deterministic `latest()` | **APPROVED** | **GO** |
| G7 | Per-role matrix; labour **target** exception | **APPROVED WITH EXPLICIT EXCEPTION**; not applied in this step | **GO** |
| G8 | PG16 test plan | **APPROVED** | **GO** |
| G9 | Private file refs | **Already locked** | **GO** |
| G10 | Named audit events | **APPROVED** | **GO** |
| G11 | No dump import; M13 owns production load | **Already locked** | **GO** |
| G12 | No finance writes / no M7; A01/A02 hard blocks | **Already locked** | **GO** |

**M6 OWNER GO GATES: APPROVED**

**M6 IMPLEMENTATION GO: ISSUED**

**M6 IMPLEMENTATION: AUTHORIZED**

**M7: OUT OF SCOPE / BLOCKED**

**M13 PRODUCTION MIGRATION: OUT OF SCOPE**

**A01/A02 FINANCE GATES: STILL HARD BLOCKS**

This ISSUE review does **not** authorize work outside the approved M6 scope. It does **not** apply grants in this step.
