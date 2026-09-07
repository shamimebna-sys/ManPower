# M6 IMPLEMENTATION GO — owner-approval sheet

**Package date:** 2026-09-07  
**Owner gate decisions recorded:** 2026-09-07 — Owner  
**Implementation GO issued:** 2026-09-07 — Owner  
**Baseline:** M1–M5 PASS. PG16 CI PASS (`5cd3878`, Run #11). PRE-GATE **APPROVED**.  
**Review:** `docs/M6-IMPLEMENTATION-GO-REVIEW.md`  
**Companions:** `docs/M6-OWNER-REVIEW.md`, `docs/M6-PRE-GATE.md`, `docs/M6-PRODUCTION-PROFILE.md`

---

## Two separate states (do not collapse)

| State | Status |
|---|---|
| **1. Owner approval of G1–G12** | **APPROVED** 2026-09-07 Owner |
| **2. M6 IMPLEMENTATION GO issuance** | **ISSUED** 2026-09-07 Owner |

**M6 OWNER GO GATES: APPROVED**

**M6 IMPLEMENTATION GO: ISSUED**

**M6 IMPLEMENTATION: AUTHORIZED**

**M6 IMPLEMENTATION STATUS: NOT STARTED** (this ISSUE record does not write code)

**M7: OUT OF SCOPE / BLOCKED**

**M13 PRODUCTION MIGRATION: OUT OF SCOPE**

**A01/A02 FINANCE GATES: STILL HARD BLOCKS**

**Runtime grants: AUTHORIZED FOR IMPLEMENTATION — NOT APPLIED IN THIS ISSUE STEP**

This document **issues** M6 implementation authorization for the approved scope only. This ISSUE step itself does **not** implement code, modify Prisma, create migrations, write tests, apply runtime grants, or change CI.

---

## Implementation authorization

M6 implementation is now authorized for the approved scope only. Subsequent implementation work may:

- implement HTTP/API + UI for the eight in-scope domains
- apply/seed the approved G3/G7 runtime-grant matrix **exactly** (no extra keys)
- implement G8 PostgreSQL 16 tests
- use `legacy_key_map` / quarantine structures where target schema requires them

Subsequent implementation work must **not**:

- start M7
- import the production dump (M13)
- write finance / ledger / payment records (A01/A02 remain hard gates)
- invent permissions, grants, or ACL systems
- reinterpret approved gates

---

## Approved implementation scope

1. Medical  
2. Police Clearance  
3. ARC  
4. Labour Contract  
5. Visa  
6. Flight  
7. License  
8. Overseas compatibility / live-status read model / badge  

Full HTTP/API + UI for all approved M6 operational domains (G1).

### Explicitly out of scope

- `selected_candidates`
- Step 5 Selection implementation
- manpower fee
- payment requests
- invoices
- wallet / ledger
- any finance write
- M7
- external government API integration
- public file storage
- hard delete
- automatic `candidates.status` writes
- production dump import (M13)
- object bytes / storage migration (M9)
- reports (M10)
- visa `country_id`
- `*.delete`
- exam / `class_group_id` writes
- extra catalogue keys beyond the approved 14

---

## How to read this sheet

| Label | Meaning |
|---|---|
| **APPROVED (gate)** | Owner signed this G-gate. Content is locked. |
| **Already locked** | Signed earlier (PRE-GATE or M1–M5). |
| **ISSUED** | M6 implementation is authorized for the approved scope. |
| **Runtime grants AUTHORIZED FOR IMPLEMENTATION** | Subsequent M6 work may seed/apply the G3/G7 matrix exactly. |
| **Runtime grants NOT APPLIED in this ISSUE step** | This documentation step seeded nothing. Applied grants remain **NONE** until implementation applies them. |
| **M7 / M13 / A01/A02 blocked** | Remain out of scope regardless of ISSUE. |

---

## RBAC — three layers (do not collapse)

| Layer | Status | Meaning |
|---|---|---|
| **APPROVED CATALOGUE** | **APPROVED** 2026-09-07 Owner | The 14 key *names* exist. |
| **APPROVED RUNTIME-GRANT DESIGN** | **APPROVED** 2026-09-07 Owner (G3/G7) | Who receives which key, with which scope. |
| **IMPLEMENTATION AUTHORIZATION TO APPLY GRANTS** | **AUTHORIZED** by this ISSUE | Subsequent M6 implementation may seed/apply the design exactly. Do not invent additional permissions or grants. |
| **ACTUALLY APPLIED RUNTIME GRANTS** | **NONE** (this ISSUE step) | Zero `overseas.*` and zero `operations.license.*` rows were seeded by this record. Super-admin upsert is a grant apply and belongs to the implementation step, not this ISSUE record. |

---

## APPROVED CATALOGUE (14 keys) — do not invent more

| Key | Domain / tables |
|---|---|
| `overseas.medical.read` | `candidate.medicals` |
| `overseas.medical.manage` | `candidate.medicals` |
| `overseas.police_clearance.read` | `workflow.police_clearances` |
| `overseas.police_clearance.manage` | `workflow.police_clearances` |
| `overseas.arc.read` | `candidate.arcs` |
| `overseas.arc.manage` | `candidate.arcs` |
| `overseas.labour_contract.read` | `candidate.labour_contracts` |
| `overseas.labour_contract.manage` | `candidate.labour_contracts` |
| `overseas.visa.read` | `candidate.visa_immigrations` |
| `overseas.visa.manage` | `candidate.visa_immigrations` |
| `overseas.flight.read` | `candidate.flight_schedules` |
| `overseas.flight.manage` | `candidate.flight_schedules` |
| `operations.license.read` | `operations.licenses`, `operations.license_positions` |
| `operations.license.manage` | `operations.licenses`, `operations.license_positions` |

No `*.delete`. No live-status catalogue key (badge authorization is a G3/G7 *rule*, not a 15th key). Teacher remains without `candidate.read`.

---

## Gate ledger

| Gate | Topic | Kind | Verdict |
|---|---|---|---|
| G1 | Full HTTP/API + UI | **APPROVED** 2026-09-07 Owner | **GO** |
| G2 | Overseas workflow (A03 all A) | **Already locked** | **GO** |
| G3 | Runtime-grant design; **authorized to apply in implementation** | **APPROVED** 2026-09-07 Owner | **GO** |
| G4 | SHIP live-status badge | **APPROVED** 2026-09-07 Owner | **GO** |
| G5 | FULL license HTTP/API + UI | **APPROVED** 2026-09-07 Owner | **GO** |
| G6 | Deterministic `latest()` | **APPROVED** 2026-09-07 Owner | **GO** |
| G7 | Per-role matrix; labour **target** exception | **APPROVED WITH EXPLICIT EXCEPTION** 2026-09-07 Owner | **GO** |
| G8 | PG16 integration-test plan | **APPROVED** 2026-09-07 Owner | **GO** |
| G9 | Private file references | **Already locked** | **GO** |
| G10 | Named audit events | **APPROVED** 2026-09-07 Owner | **GO** |
| G11 | Quarantine / no dump import; M13 owns production load | **Already locked** | **GO** |
| G12 | No finance writes / no M7; A01/A02 hard blocks | **Already locked** | **GO** |

G1–G12 are **GO**. **M6 IMPLEMENTATION GO** is **ISSUED**.

---

## Locked (do not reopen / do not reinterpret)

| Item | State |
|---|---|
| G1–G12 owner gate decisions | **APPROVED** 2026-09-07 |
| **M6 IMPLEMENTATION GO** | **ISSUED** 2026-09-07 |
| G2 workflow (A03 all **A**) | **Already locked** |
| G7 labour for agent/sub_agent/agency | **Intentional target-system authorization decision; not legacy parity.** Do not silently remove or weaken. |
| G9 private file refs / M9 bytes | **Already locked** |
| G11 quarantine / `legacy_key_map` / no dump import | **Already locked**; production migration remains **M13** |
| G12 no finance writes / no M7 | **Already locked**; A01/A02 **still hard blocks** |
| A04 exact dump strings | **Already locked**; G4 **SHIP** |
| A14 names / `acr_file_path` / no government API / adapters only | **Already locked** |
| A05 / A06 / A07 / A20 | **Already locked** |
| Teacher **NO** `candidate.read`; no `*.delete` | **Already locked** |
| `selected_candidates` outside M6 | **Already locked** |
| PG16 integration CI | **Already locked** |
| 14-key catalogue names | **Already locked** |

---

## Owner sign-off grid

| Gate | Decision recorded | Kind | Owner | Date | Name |
|---|---|---|---|---|---|
| G1 | Full M6 HTTP/API + UI as §G1 | **APPROVED** | **SIGN** | 2026-09-07 | Owner |
| G3 | Runtime-grant design as §G3. ISSUE authorizes exact apply in implementation. | **APPROVED**; apply **authorized** | **SIGN** | 2026-09-07 | Owner |
| G4 | **SHIP** live-status badge; exact dump strings; compatibility read-model | **APPROVED** | **SIGN** | 2026-09-07 | Owner |
| G5 | **FULL** license HTTP/API + UI; companier scope; do not repair Q-LP-LIC | **APPROVED** | **SIGN** | 2026-09-07 | Owner |
| G6 | Deterministic `latest()` as §G6 | **APPROVED** | **SIGN** | 2026-09-07 | Owner |
| G7 | Per-role matrix in §G7, **with explicit labour-contract target exception** | **APPROVED WITH EXPLICIT EXCEPTION**; apply **authorized** | **SIGN** | 2026-09-07 | Owner |
| G8 | PG16 case list in §G8; live DB tests on PostgreSQL 16 in CI | **APPROVED** | **SIGN** | 2026-09-07 | Owner |
| G10 | Named audit events in §G10; no VOIDED; no hard-delete events; no `candidates.status` writes | **APPROVED** | **SIGN** | 2026-09-07 | Owner |
| **ISSUE** | I issue **M6 IMPLEMENTATION GO** | **ISSUED** | **SIGN** | 2026-09-07 | Owner |

---

## G1 — SCOPE — **APPROVED** 2026-09-07 Owner — **GO**

Full HTTP/API + UI implementation for all approved M6 operational domains.

| Area | Tables / surface | Notes |
|---|---|---|
| Medical | `candidate.medicals` | Candidate-scoped CRUD, no DELETE |
| Police clearance | `workflow.police_clearances` | Candidate-scoped CRUD, no DELETE |
| ARC | `candidate.arcs` | Candidate-scoped CRUD, no DELETE |
| Labour contract | `candidate.labour_contracts` | Candidate-scoped CRUD, no DELETE |
| Visa | `candidate.visa_immigrations` | Candidate-scoped CRUD, no DELETE; **no** `country_id` |
| Flight | `candidate.flight_schedules` | Candidate-scoped CRUD, no DELETE |
| License | `operations.licenses`, `operations.license_positions` | Companier-scoped HTTP/API + UI (G5) |
| Overseas status read | Compatibility live-status **read** (G4) | Badge/read API only; **not** a write/prerequisite engine |

Exclusions: see **Explicitly out of scope** above.

---

## G2 — Workflow — **already locked** — **GO**

Use the already locked M6 workflow decisions (A03 all **A**). Document-legacy. No invented write gates. Skip Step 5 in any compatibility view. Medical and ARC off the waterfall. In-place update; no hard delete.

---

## G3 — RUNTIME GRANTS — **APPROVED** 2026-09-07 Owner — **GO** — **AUTHORIZED TO APPLY IN IMPLEMENTATION**

The approved runtime-grant **DESIGN** may now be implemented/applied **exactly as approved**. Do not invent additional permissions or grants.

This ISSUE record does **not** seed grants. Applied grants remain **NONE** until the implementation step applies them.

### Authorized runtime grants (exact)

| Role | Grant |
|---|---|
| `super_admin` | All 14 M6 permissions (global) |
| `owner` | All operational M6 permissions (all 14 keys, global) |
| `administrator` | All operational M6 permissions (all 14 keys, global) |
| `employee` | All operational M6 permissions (all 14 keys, global) |
| `agent` | Candidate-scoped overseas permissions; **no license permission** |
| `sub_agent` | Candidate-scoped overseas permissions; **no license permission** |
| `agency` | Candidate-scoped overseas permissions; **no license permission** |
| `company` | **No M6 grant** |
| `candidate` | **No M6 grant** |
| `employer` | **No M6 grant** |
| `teacher` | **No M6 grant** |

**Live-status authorization (no 15th catalogue key):** serve the badge/read surface only when the actor holds at least one `overseas.*.read` **and** (for scoped roles) existing candidate-access allows that candidate. Do **not** serve live-status from M4 `candidate.read` alone.

Normative per-key cells: **§G7**.

---

## G4 — LIVE-STATUS BADGE — **APPROVED** 2026-09-07 Owner — **GO**

**SHIP** the compatibility / live-status badge as a **read-only compatibility** model.

Preserve exact legacy labels (do not correct spelling), including `Labour Contact`, `Police Clearance & Medical`, `VISA/Work Permite`, and all other existing legacy status labels:

| step_no | name (verbatim) |
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

**Rules:**

- Step 5 remains skipped (`selected_candidates` is not read; Step 5 Selection is not implemented).
- Medical remains off the waterfall.
- ARC remains off the waterfall.
- Step 7 is **police row existence only**, despite the label including Medical.
- Badge is read-only compatibility behavior.
- Badge must **not** introduce new prerequisites.
- Badge must **not** write `candidates.status`.
- Step 9 may **read** existing `payment_requests` for compatibility only. **No finance writes** (G12). Do not “fix” FIN-BUG tautology in M6.

---

## G5 — LICENSE — **APPROVED** 2026-09-07 Owner — **GO**

Implement **full License HTTP/API + UI** in M6.

- Schema: `operations.licenses`, `operations.license_positions`.
- Access is **companier-scoped**; not nested under candidates.
- Unique `license_no` on create.
- Preserve source relationships.
- **Do NOT** repair, delete, merge, or silently modify the 17 orphan license-position rows (Q-LP-LIC). Keep the gap. Quarantine at M13.
- `licenses.companier_id` NULL (1 row): quarantine; no synthetic companier.

---

## G6 — `latest()` — **APPROVED** 2026-09-07 Owner — **GO**

For latest-record compatibility:

**ORDER BY:**

1. `created_at` **DESC**
2. then source/legacy ID **DESC** (`source_legacy_id`; persist lineage — UUID creation order is not a substitute)

**Rules:**

- No implicit status filter
- An endpoint may explicitly request active-only semantics
- Duplicates are allowed
- Deterministic tie-break is required
- No hard delete (in-place update only)

---

## G7 — ROLE ACCESS MATRIX — **APPROVED WITH EXPLICIT EXCEPTION** 2026-09-07 Owner — **GO** — **AUTHORIZED TO APPLY IN IMPLEMENTATION**

Use the approved authorization matrix. Subsequent implementation may seed it **exactly**. This ISSUE step does not seed.

Candidate-scoped access **MUST** use the existing candidate-access mechanism (`agent_id` / `sub_agent_id` / `agencier_id`). Unbound partner id → zero rows. **Do not create a separate ACL system.**

Teacher remains forbidden from `candidate.read` and M6 candidate browsing.

Company receives no M6 grant unless separately approved.

Candidate and employer receive no M6 grant.

Do not grant `operations.license.*` to agent / sub_agent / agency.

### G7 labour authorization exception

The approved `overseas.labour_contract.read|manage` cells for `agent`, `sub_agent`, and `agency` (candidate-scoped) **differ from legacy Voyager grant behavior**.

**Intentional target-system authorization decision; not legacy parity.**

Do **not** hide this exception. Do **not** describe it as historical behavior. Do **not** silently remove or weaken this access.

`G` = approved grant to apply in implementation. `—` = no grant. **Applied in this ISSUE step: none.**

| Key | super_admin | administrator | owner | employee | agent | sub_agent | agency | company | candidate | employer | teacher |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `overseas.medical.read` | G (all) | G (all) | G (all) | G (all) | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.medical.manage` | G | G | G | G | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.police_clearance.read` | G (all) | G (all) | G (all) | G (all) | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.police_clearance.manage` | G | G | G | G | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.arc.read` | G (all) | G (all) | G (all) | G (all) | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.arc.manage` | G | G | G | G | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.labour_contract.read` | G (all) | G (all) | G (all) | G (all) | G (scoped)† | G (scoped)† | G (scoped)† | — | — | — | — |
| `overseas.labour_contract.manage` | G | G | G | G | G (scoped)† | G (scoped)† | G (scoped)† | — | — | — | — |
| `overseas.visa.read` | G (all) | G (all) | G (all) | G (all) | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.visa.manage` | G | G | G | G | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.flight.read` | G (all) | G (all) | G (all) | G (all) | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `overseas.flight.manage` | G | G | G | G | G (scoped) | G (scoped) | G (scoped) | — | — | — | — |
| `operations.license.read` | G (all) | G (all) | G (all) | G (all) | — | — | — | — | — | — | — |
| `operations.license.manage` | G | G | G | G | — | — | — | — | — | — | — |

† **Intentional target-system authorization decision; not legacy parity.**

UI hiding is not authorization. IDOR tests use `candidate-access`, not nav.

---

## G8 — PG16 TEST PLAN — **APPROVED** 2026-09-07 Owner — **GO**

Implement the complete PostgreSQL 16 integration-test plan.

All live DB tests **MUST** execute against **PostgreSQL 16** in CI. Do **not** weaken tests to make them pass. No production dump load. No M7 tests. Tests are authorized to be written in the implementation step, **not** in this ISSUE record.

| ID | Case |
|---|---|
| T-MIG | Every M6 migration applies on PG16; `SELECT version()` matches `PostgreSQL 16.` |
| T-FK | All M6 foreign keys nullable/`NOT VALID` as designed; no NOT NULL that would drop M13 orphans |
| T-ORPH | Orphan/quarantine behavior: API/schema accept orphan-shaped data; no silent parent mint; Q-LP-LIC 17 positions not auto-repaired |
| T-IDOR | Candidate IDOR: scoped actor with overseas.read cannot read another agent’s medical/police/ARC/labour/visa/flight |
| T-RBAC | Role authorization: missing key → 403; all approved role/permission behavior |
| T-SCOPE | Candidate scope enforcement for lists/reads/writes |
| T-SCOPE-A | Agent / sub-agent / agency scope: lists only their `candidate-access` rows |
| T-DENY-C | Company denial: company with M4 `candidate.read` still 403 on overseas, license, and live-status |
| T-DENY-E | Employer denial: 403 on overseas, license, and live-status |
| T-DENY-T | Teacher denial: 403/empty; no `candidate.read`; no M6 candidate browsing |
| T-LATEST | Deterministic `latest()`: two rows same `created_at` → higher `source_legacy_id` wins (G6) |
| T-DUP | Duplicate historical rows: second labour/visa/flight for same candidate persists; no unique-current rejection |
| T-CRUD | All M6 CRUD/update flows: medical / police / ARC / labour / visa / flight create + in-place update; DELETE rejected |
| T-LIC | License companier scope; unique `license_no`; agent/sub_agent/agency 403 on license |
| T-LIVE | Live-status compatibility: skip 5; police-only step 7; ARC/medical do not advance; flight wins; exact A04 strings |
| T-AUD | Each write emits the G10 named event; no passport/nid/token metadata |
| T-FILE | Private file references: UUID refs only; public path is not authorization; empty ref allowed; **no public document URLs** |
| T-FIN | No finance writes: zero inserts to `payment_requests` / `payments` / ledgers / candidate payment FKs |
| T-M7 | No M7 behavior: no manpower-fee approve/create; M5 manpower-training routes unchanged |
| T-TX | Rollback / in-place update semantics: failed write rolls back row + audit (no partial commit) |
| T-MAP | `legacy_key_map` compatibility: fixture unique source triplet; **no** dump import |

---

## G9 — Files — **already locked** — **GO**

Use private file references only. Use UUID `*_file_id`-style references where applicable. No public document URLs as ACL. Actual file bytes / storage migration remain **outside M6** (M9).

---

## G10 — AUDIT EVENTS — **APPROVED** 2026-09-07 Owner — **GO**

Implement **exactly** these approved audit events.

**Legacy has no equivalent named event store.** These are target-system audit events.

| Event | When |
|---|---|
| `overseas.medical.created` | medical insert |
| `overseas.medical.updated` | medical in-place update |
| `overseas.police_clearance.created` | police insert |
| `overseas.police_clearance.updated` | police in-place update |
| `overseas.arc.created` | ARC insert |
| `overseas.arc.updated` | ARC in-place update |
| `overseas.labour_contract.created` | labour insert |
| `overseas.labour_contract.updated` | labour in-place update |
| `overseas.visa.created` | visa insert |
| `overseas.visa.updated` | visa in-place update |
| `overseas.flight.created` | flight insert |
| `overseas.flight.updated` | flight in-place update |
| `overseas.license.created` | license insert |
| `overseas.license.updated` | license in-place update (positions replace counts as `.updated`) |
| `overseas.status.changed` | Derived live-status **step** changes after a document write. **Does not** write `candidates.status`. |

**Do NOT create:**

- `*_VOIDED` events
- hard-delete events
- automatic `candidates.status` events

Catalogue keys remain `operations.license.*`. Audit names follow `overseas.license.*` (prefix mismatch is intentional and approved).

No passport / nid / token in metadata. Log `file_id` only, not bytes.

---

## G11 — Migration / quarantine — **already locked** — **GO**

Do **not** migrate production data during M6 implementation. Production migration remains **M13** and is **out of scope**.

Use the approved `legacy_key_map` / quarantine architecture where target structures require it. Do not import the production dump. `LIVE + QUARANTINED = SOURCE`. Never silently repair/delete/fabricate orphans.

---

## G12 — M7 / payments — **already locked** — **GO**

**Zero finance writes.** M7 remains **out of scope / blocked**. A01/A02 remain **hard gates**.

No M6 handler may:

- create/update/delete payment records
- create/update invoices
- alter wallet balances
- create ledger entries
- post manpower fees
- invoke M7 logic

---

## Architectural rules

- PostgreSQL 16 target
- Node.js + TypeScript
- Express REST API
- Next.js + React
- Prisma
- existing modular-monolith architecture
- existing auth / RBAC / audit infrastructure
- existing candidate-access helper
- existing private-file reference pattern
- existing UUID target IDs
- no Laravel
- no production-data modification
- no unnecessary architecture redesign
- no CI changes unless explicitly required by an implementation defect

---

## Legacy parity rule

Preserve legacy behavior where the Owner Review explicitly requires parity.

Where an approved target-system authorization decision intentionally differs from legacy behavior, document the difference explicitly rather than disguising it as parity.

Do not reinterpret approved gates. The G7 labour-contract grant for agent / sub_agent / agency is **not legacy parity**.

---

## What this ISSUE record does and does not do

**Does:**

- Record G1–G12 as **APPROVED / GO**
- **Issue** **M6 IMPLEMENTATION GO**
- Authorize M6 implementation for the approved scope
- Authorize exact apply of the G3/G7 runtime-grant design in a later implementation step

**Does not (this step):**

- Implement application code
- Modify Prisma schema
- Create migrations
- Write or change tests
- Apply or seed runtime grants
- Modify CI
- Start M7
- Import production data (M13)

---

## Final board

| Gate | Verdict | Kind |
|---|---|---|
| **G1** | **GO** | APPROVED — full HTTP/API + UI |
| **G2** | **GO** | Already locked workflow |
| **G3** | **GO** | APPROVED design; apply **authorized** in implementation |
| **G4** | **GO** | APPROVED SHIP badge |
| **G5** | **GO** | APPROVED full license HTTP/API + UI |
| **G6** | **GO** | APPROVED `latest()` |
| **G7** | **GO** | APPROVED WITH EXPLICIT EXCEPTION; apply **authorized** |
| **G8** | **GO** | APPROVED PG16 test plan |
| **G9** | **GO** | Already locked private file refs |
| **G10** | **GO** | APPROVED named audit events |
| **G11** | **GO** | Already locked; M13 owns production load |
| **G12** | **GO** | Already locked; A01/A02 hard blocks |

**M6 OWNER GO GATES: APPROVED**

**M6 IMPLEMENTATION GO: ISSUED**

**M6 IMPLEMENTATION: AUTHORIZED**

**M7: OUT OF SCOPE / BLOCKED**

**M13 PRODUCTION MIGRATION: OUT OF SCOPE**

**A01/A02 FINANCE GATES: STILL HARD BLOCKS**
