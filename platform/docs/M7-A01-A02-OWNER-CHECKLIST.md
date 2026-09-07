# M7 A01 / A02 owner approval checklist

**Document type:** OWNER DECISION ONLY  
**Date opened:** 2026-09-07  
**Prerequisite:** M6 CLOSED / VERIFIED. M7 PRE-GATE COMPLETE.

Evidence (read-only):

- `docs/M7-A01-A02-DECISION-PACKAGE.md` — recommended options and unresolved items
- `docs/M7-PRE-GATE.md`
- `docs/M7-PRODUCTION-PROFILE.md`
- `docs/M7-FINANCE-RISK-REGISTER.md`
- Design Gate `06-finance.md`, `11-milestones-risks-approvals.md`

**A01 is recorded APPROVED WITH CONDITIONS (2026-09-07).** A02 is **not** approved until the owner marks the A02 final block.

Recording: `docs/M7-A01-A02-OWNER-DECISIONS.md`.

No code. No Prisma. No migration. No grants. No CI. No production data. No Laravel.

For every item, mark exactly one of **APPROVE**, **REJECT**, or **CHANGE**. If CHANGE, write the replacement decision in the space provided.

A01 and A02 remain **separate** gates. Approving line items does **not** issue M7 IMPLEMENTATION GO.

---

## Owner identity (required when signing)

| Field | Value |
|---|---|
| Approver name | |
| Date | |
| Role / title | |

---

# A01 — Authoritative finance source

## A01-01

**Owner recording 2026-09-07:** **APPROVED.**

**Decision:** `payments` is the authoritative legacy source for live wallet money movement.

Only `payments.status = 'A'` counts as an active legacy wallet transaction.

| | |
|---|---|
| APPROVE | **[x]** |
| REJECT | [ ] |
| CHANGE | [ ] |

## A01-02

**Owner recording 2026-09-07:** **APPROVED.**

**Decision:** Cached `agents.balance` and `sub_agents.balance` are **NOT** authoritative. They are legacy cached/mutable values. The target must not treat them as the source of financial truth.

| | |
|---|---|
| APPROVE | **[x]** |
| REJECT | [ ] |
| CHANGE | [ ] |

## A01-03

**Owner recording 2026-09-07:** **APPROVED WITH CONDITION.**

**Decision:** `payment_requests` is both (1) approval workflow and (2) legacy posting trigger upon approval. It is **NOT** itself the authoritative ledger. Target implementation must **NOT** blindly reproduce the legacy direct-balance mutation behavior.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | **[x] APPROVE WITH CONDITION** |

Condition:

Final target posting behavior remains subject to A02 and M7 implementation approval.

## A01-04

**Owner recording 2026-09-07:** **APPROVED.**

**Decision:** All six payment backup tables are **ARCHIVAL ONLY**. They must **NEVER** be automatically UNIONed with live payment data.

Tables: `payments_backup_01072026`, `payments_backup_18062026`, `payments_backup_20260625_0214pm`, `payments_backup_210620261147am`, `payments_bk`, `payment_requests_backup_01072026`.

| | |
|---|---|
| APPROVE | **[x]** |
| REJECT | [ ] |
| CHANGE | [ ] |

## A01-05

**Owner recording 2026-09-07:** **APPROVED.**

**Decision:** For the **1,268 / 1,275** backup overlap:

- LIVE records **WIN**.
- The 1,268 overlapping backup rows are treated as duplicates.
- The remaining **7** backup rows remain **UNCLASSIFIED**.
- **No silent repair.**
- **No automatic financial posting from backup rows.**

| | |
|---|---|
| APPROVE | **[x]** |
| REJECT | [ ] |
| CHANGE | [ ] |

## A01-06

**Owner recording 2026-09-07:** **APPROVED.**

**Decision:** The **14** duplicate receipt groups identified in R3 remain within System B. They do **NOT** create wallet postings. Extra/duplicate records are retained for quarantine/review, not silently deleted.

| | |
|---|---|
| APPROVE | **[x]** |
| REJECT | [ ] |
| CHANGE | [ ] |

## A01-07

**Owner recording 2026-09-07:** **APPROVED.**

**Decision:** Every migrated financial record must preserve, through `migration.legacy_key_map`:

- `source_system`
- `source_table`
- `source_id`
- `target_type`
- `target_id`
- `migration_run_id`
- `source_row_hash`
- `migrated_at`

No financial record may lose its legacy lineage.

| | |
|---|---|
| APPROVE | **[x]** |
| REJECT | [ ] |
| CHANGE | [ ] |

## A01-08

**Owner recording 2026-09-07:** **APPROVED.**

**Decision:** Do **NOT** repair or reinterpret these as financial authority:

- 318 `admission_payment_id` references
- only 3 Admission requests
- zero Final/Medical payment FKs

These inconsistencies must remain documented and, where applicable, quarantined during M13 migration. No invented relationship.

| | |
|---|---|
| APPROVE | **[x]** |
| REJECT | [ ] |
| CHANGE | [ ] |

## A01-09

**Owner recording 2026-09-07:** **APPROVED WITH CONDITION.**

**Decision:** Opening balance must be **STAGED** and must **NOT** become authoritative until explicitly approved.

A01-10 is the calculation principle: approved live payments, `status = A`, after A01 deduplication/exclusion, by wallet and currency, unexplained differences quarantined, cached balance not authority.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | **[x] APPROVE WITH CONDITION** |

Condition:

The final opening-balance calculation output must be reconciled and explicitly approved before production migration/cutover.

## A01-10 — REQUIRED

**Owner recording 2026-09-07:** **APPROVED** — see `docs/M7-A01-A02-OWNER-DECISIONS.md`.

Opening-balance **method** is approved. A01 as a whole is **APPROVED WITH CONDITIONS** (final A01 block).

| | |
|---|---|
| Calculate opening balance from approved legacy `payments` | **[x]** selected |
| Calculate from another explicitly identified source | [ ] |
| Manual owner-approved opening balance | [ ] |
| Other | [ ] |

Source: live `payments` only, `status='A'`, after A01 dedup/exclusion, **by wallet and by currency**.

**Exact calculation rule (recorded):**

Calculate from approved live payments (`status='A'`), after approved A01 deduplication/exclusion rules, separately by wallet and currency. Unexplained differences are quarantined. Cached balances are not authoritative.

| | |
|---|---|
| APPROVE | **[x]** |
| REJECT | [ ] |
| CHANGE | [ ] |

---

# A02 — Currency / FX / balance model

## A02-01

**Decision:** Target supports only **BDT** and **EUR**.

Legacy mapping: TAKA → BDT; Euro → EUR. No additional currencies.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

## A02-02

**Decision:** Historical payment FX must remain the per-row `payments.exchange_rate`. Historical transactions must **NOT** be rewritten using the legacy `euro_to_bdt_rates` table.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

## A02-03

**Decision:** `euro_to_bdt_rates = 117.346` is **NOT** the historical authority because legacy runtime behavior used per-payment rates.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

## A02-04

**Decision:** Target money representation: amount = `NUMERIC(20,6)`; rate = `NUMERIC(20,8)`.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

## A02-05

**Decision:** Wallet balance is **ledger-derived**. A controlled cached projection may exist for performance, but it is never the source of truth.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

## A02-06

**Decision:** Target finance uses an **APPEND-ONLY DOUBLE-ENTRY LEDGER**.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

## A02-07

**Decision:** Payment approval posts exactly **ONCE** inside a single database transaction.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

## A02-08

**Decision:** Payment rejection creates **NO** financial posting.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

## A02-09

**Decision:** Duplicate approval/posting is forbidden. The system must make repeated approval incapable of creating a second financial posting.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

---

# A02 — FX posting formula

Current recommended option in the decision package: **W1**.

- EUR is the base currency.
- TAKA/BDT conversion uses the approved transaction FX rate.

Owner must explicitly decide:

| | |
|---|---|
| APPROVE W1 | [ ] |
| REJECT W1 | [ ] |
| CHANGE | **[x] HOLD** |

**Recorded 2026-09-07:** CHANGE / HOLD. Do not lock FX direction until fee-debit currency is explicitly approved.

If CHANGE, specify exact formula:

**HOLD — formula not locked.**

Example only — **do not assume this is approved:**

`EUR amount = BDT amount / approved FX rate`

---

# Fee debit currency

**Required.** For manpower/fee debit posting, the DR amount is denominated in:

| | |
|---|---|
| EUR | [ ] |
| BDT | [ ] |
| Depends on source transaction currency | [ ] |
| Other | **[x] HOLD** |

**Recorded 2026-09-07:** CHANGE / HOLD. Must be explicitly defined by approved fee/request currency. **No hard-coded EUR.**

**Exact rule:**

**HOLD — not defined.** No hard-coded EUR. Await approved fee/request currency definition.

---

# Bill title / bill code

These items inform **A16**. Marking them here does **not** close A16 unless the owner also completes the A16 block below.

## A02-11 — `Panelty Fee`

Confirm all verified bill titles before implementation. Known additional title: `Panelty Fee` (12 request rows in the profiled dump). Original five: `Admission Group Approval`, `Final Group Approval`, `Medical Fee`, `Manpower Fee`, plus wallet title `New Payment request` on `payments` (not a request bill title).

Disposition of `Panelty Fee`:

| | |
|---|---|
| Include as a formal target bill-title code | [ ] |
| Treat as legacy-only/unmapped | [ ] |
| Other | **[x] distinct legacy bill-title concept** |

| | |
|---|---|
| APPROVE | **[x]** 2026-09-07 |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement / Other text:

**Preserve as a distinct legacy bill-title concept.** Exact `bill_title_code` remains A16 (UNRESOLVED). Do not fold into another title.

## A02-12 — `bill_code=101`

Legacy `bill_code=101` has **no persistence field** in `payment_requests`.

| | |
|---|---|
| Preserve 101 through a new explicit target bill-title reference | [ ] |
| Treat 101 as legacy-only/unmapped | [ ] |
| Map to an existing approved bill title | [ ] |
| Other | **[x] lineage only; mapping later** |

**Exact decision:**

**APPROVE WITH CONDITION (2026-09-07).** Preserve legacy value/lineage. Target business mapping requires explicit approval (A16). Do not hardcode `101` as a fee type.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | **[x] APPROVE WITH CONDITION** |

---

# A16 / A17 / A18

These remain **unresolved** until the owner writes a decision and marks APPROVE / REJECT / CHANGE.

Design Gate wording (for reference only — not pre-approved):

| ID | Decision blocked |
|---|---|
| **A16** | `bill_title_code` enum values and correction of FIN-BUG-01, FIN-BUG-02 linkage logic |
| **A17** | Teacher payment flow (FIN-BUG-05): in-use or deprecated? |
| **A18** | Zero-DR noise row on fee rejection (FIN-BUG-06): confirm not intentional; approve removal |

A02-08 (no posting on reject) is related to A18 but does **not** close A18 by itself.

## A16

Written decision:

**UNRESOLVED — exact definition required** (recorded 2026-09-07).

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

## A17

Written decision:

**UNRESOLVED — exact definition required** (recorded 2026-09-07).

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

## A18

Written decision:

**UNRESOLVED — exact definition required** (recorded 2026-09-07).

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

---

# Partner balances

**Agencier balances:** currently zero (13 rows).  
**Companier balances:** currently zero (390 rows).

**Decision:** Do **NOT** invent historical transactions.

| | |
|---|---|
| APPROVE | [ ] |
| REJECT | [ ] |
| CHANGE | [ ] |

Replacement if CHANGE:

________________________________________

---

# Final A01 approval

**Owner recording 2026-09-07.**

A01 status:

| | |
|---|---|
| APPROVED | [ ] |
| REJECTED | [ ] |
| APPROVED WITH CONDITIONS | **[x]** |

Conditions:

1. A02 remains open.
2. Opening-balance calculation output requires reconciliation.
3. No finance implementation is authorized by this document.
4. No production migration is authorized.
5. Finance runtime grants remain unauthorized.
6. Final target posting behavior remains subject to A02.
7. A01 approval does not constitute M7 Implementation GO.

A01-01 through A01-09 are marked APPROVE or APPROVE WITH CONDITION, and A01-10 has a selected method and an exact calculation rule.

---

# Final A02 approval

A02 status:

| | |
|---|---|
| APPROVED | [ ] |
| REJECTED | [ ] |
| APPROVED WITH CONDITIONS | [ ] |

Conditions:

________________________________________

________________________________________

A02 cannot be APPROVED unless A02-01 through A02-09 are marked APPROVE or CHANGE-with-replacement, **and** W1 is APPROVE / REJECT / CHANGE-with-formula, **and** fee debit currency has an exact rule.

A16 / A17 / A18 may remain OPEN. If OPEN, they continue to block the slices named in the Design Gate register (fee linkage, teacher path, reject-row policy). They do **not** substitute for A01/A02, and closing A01/A02 does **not** close them.

---

# M7 implementation authorization

**IMPORTANT:** A01/A02 approval does **NOT** automatically authorize implementation.

After owner decisions are recorded, the approved decisions must be reviewed and converted into a **separate** `M7 IMPLEMENTATION GO` document/instruction.

Until that happens:

| | |
|---|---|
| M7 IMPLEMENTATION | **NOT AUTHORIZED** |
| FINANCE RUNTIME GRANTS | **NOT AUTHORIZED** |
| PRODUCTION MIGRATION | **NOT AUTHORIZED** |
| M7 IMPLEMENTATION GO | **NOT ISSUED** |

---

## FINAL STATUS

**M6: CLOSED / VERIFIED**  
**M7 PRE-GATE: COMPLETE**  
**A01: APPROVED WITH CONDITIONS**  
**A02: CLOSED**  
**A16: Panelty Fee = 100**  
**A17: RETAIN (business requirement only)**  
**A18: INTENTIONAL**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS: NOT AUTHORIZED**
