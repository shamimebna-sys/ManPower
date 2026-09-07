# M7 Finance — Implementation Gate

**Document type:** IMPLEMENTATION GATE / TARGET DESIGN FREEZE  
**Date:** 2026-09-07  
This freeze package itself did not issue GO. **GO is issued in `docs/M7-IMPLEMENTATION-GO.md`.**

This package freezes **target architecture, accounting model, migration strategy, reconciliation, and implementation boundaries**. It is **not** authorization to code.

---

## Authoritative status

**M6 = CLOSED / VERIFIED**  
**M7 PRE-GATE = COMPLETE**  
**A01 = APPROVED WITH CONDITIONS**  
**A02 = CLOSED**

**M7 IMPLEMENTATION GATE = COMPLETE**  
**M7 DESIGN REVIEW = READY FOR IMPLEMENTATION GO** (`docs/M7-DESIGN-REVIEW.md`)  
DR-H1 / DR-FX1 record: `docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`

**M7 IMPLEMENTATION GO = ISSUED** (`docs/M7-IMPLEMENTATION-GO.md`)  
**M7 IMPLEMENTATION = AUTHORIZED (System A)**  
**M8 = OUT OF SCOPE**  
**FINANCE RUNTIME GRANTS = AUTHORIZED FOR APPROVED M7 KEYS ONLY**  
**PRODUCTION MIGRATION = NOT AUTHORIZED**

Owner decisions (binding):

| ID | Decision |
|---|---|
| A02-01 | Fee debit currency = **EUR** |
| A02-02 | W1 **APPROVED**: `EUR = BDT / R`; EUR identity `EUR = A` |
| A02-03 | `Panelty Fee` retained; target code = **100** |
| A02-04 | Teacher payment flow = **RETAIN** (business requirement only) |
| A02-05 | Zero-DR rejection = **INTENTIONAL** (preserve) |
| A02-06 | `bill_code=101` = **CONFIRM** (lineage only; no silent title mapping) |
| **DR-H1** | **A — FULL HISTORICAL JOURNALIZATION.** Qualifying approved `payments` → reconstructed journals. Same row **not** also an opening-balance post. Lineage via `legacy_key_map`. |
| **DR-FX1** | **ADMINISTRATIVE FX RATE ENTRY** for new-post `R`. Historical = `payments.exchange_rate`. `euro_to_bdt_rates` non-authoritative. |

A01-10 remains the **calculation / recon control** for qualifying live `payments` `status='A'`, after A01 dedup/exclusion, **by wallet and currency**. Those rows are reconstructed as journals (**DR-H1 A**), not posted as opening balances. Cache is not authority. Unexplained differences quarantined. History is not rewritten.

---

## Package contents

| File | Freeze |
|---|---|
| `docs/M7-TARGET-FINANCE-DESIGN.md` | Architecture, entities, boundaries, phases |
| `docs/M7-ACCOUNTING-MODEL.md` | Double-entry invariants, journals, reversal |
| `docs/M7-WALLET-PAYMENT-DESIGN.md` | Wallets, payment requests, fee debit, teacher, 101 |
| `docs/M7-FX-CURRENCY-DESIGN.md` | ISO map, W1, historical rates, administrative new-post `R` |
| `docs/M7-MIGRATION-RECONCILIATION-PLAN.md` | Extract→sign-off; DR-H1 reconstruction; gates A–J; quarantine |
| `docs/M7-SECURITY-AUDIT-DESIGN.md` | Conceptual RBAC, audit events, concurrency |
| `docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md` | DR-H1 A / DR-FX1 administrative rate-entry |
| `docs/M7-DESIGN-REVIEW.md` | Design review result |

Related locked evidence (not reopened):

- `docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`
- `docs/M7-DESIGN-REVIEW.md`
- `docs/M7-A02-CLOSURE-RECORD.md`
- `docs/M7-A01-A02-OWNER-DECISIONS.md`
- `docs/M7-PRE-GATE.md`
- Design Gate `06-finance.md`, `finance-operations.csv`, `00-project-rules.md`

---

## What this gate freezes

1. Target finance architecture (System A only)
2. Accounting model (append-only double-entry)
3. Wallet model (ledger-derived)
4. Payment-request workflow
5. Currency model (BDT/EUR)
6. FX model (W1; **DR-FX1** administrative `R` for new posts)
7. Opening-balance model (A01-09 / A01-10 as **recon control** + residual opening only; DR-H1 forbids posting the same qualifying payment as opening)
8. Teacher payment flow (retain; mapping unconfirmed)
9. Panelty Fee (code 100)
10. `bill_code=101` lineage
11. Historical migration strategy (**DR-H1 A**: reconstruct qualifying journals)
12. Reconciliation strategy
13. Authorization model (conceptual categories only)
14. Audit model
15. Concurrency / idempotency rules
16. Rollback strategy (non-destructive)
17. Implementation phases (not started)
18. Out-of-scope (M8 and later)

---

## What this gate does **not** do

- Does not create Prisma models or migrations.
- Does not add `finance.*` permission keys or runtime grants.
- Does not post journals or seed balances.
- Does not correct FIN-BUG-01 / FIN-BUG-02 without a later explicit implementation decision.
- Does not pull System B (employer invoices) or System C (tickets) into M7.
- Does not issue **M7 IMPLEMENTATION GO**.

---

## Design-review exit criteria (before any GO)

A named reviewer must confirm:

1. This package does not contradict A01 / A02.
2. W1 formula is unchanged: `EUR = BDT / R`; `EUR = A`.
3. Panelty Fee code **100** is distinct from lineage **101** and row count **12**.
4. Opening-balance output remains staged until reconciled and separately approved (A01-09).
5. No M8 objects are in the M7 write scope.
6. A separate **M7 IMPLEMENTATION GO** document will still be required after this review.

Until that GO exists, implementation remains **NOT AUTHORIZED**.  
**Update:** GO now exists — `docs/M7-IMPLEMENTATION-GO.md`. Production migration remains unauthorized.

---

## FINAL STATUS

**M6 = CLOSED / VERIFIED**  
**M7 PRE-GATE = COMPLETE**  
**A01 = APPROVED WITH CONDITIONS**  
**A02 = CLOSED**

**M7 IMPLEMENTATION GATE = COMPLETE**  
Review record: `docs/M7-DESIGN-REVIEW.md`  
DR-H1 / DR-FX1: `docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`  
**M7 DESIGN REVIEW = READY FOR IMPLEMENTATION GO**

**M7 IMPLEMENTATION GO = ISSUED** (`docs/M7-IMPLEMENTATION-GO.md`)  
**M7 IMPLEMENTATION = AUTHORIZED (System A)**  
**M8 = OUT OF SCOPE**  
**FINANCE RUNTIME GRANTS = AUTHORIZED FOR APPROVED M7 KEYS ONLY**  
**PRODUCTION MIGRATION = NOT AUTHORIZED**
