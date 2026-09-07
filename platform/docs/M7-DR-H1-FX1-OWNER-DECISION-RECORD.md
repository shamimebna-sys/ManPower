# M7 — DR-H1 / DR-FX1 owner decision record

**Recorded:** 2026-09-07  
**Document type:** OWNER DECISION RECORD  
**Does not issue M7 IMPLEMENTATION GO.**  
No application code, Prisma, migration, API, UI, permissions, grants, CI, production data, or Laravel change.

Closes the two items left **OWNER DECISION REQUIRED** by `docs/M7-DESIGN-REVIEW.md`.

Does **not** reopen A01 or A02.

---

## Binding decisions

| ID | Owner mark | Binding text |
|---|---|---|
| **DR-H1** | **APPROVED** | **A — FULL HISTORICAL JOURNALIZATION.** Every qualifying approved legacy `payments` row shall be reconstructed into the target accounting journal. The same legacy payment shall **not** also be included in an opening-balance posting. Historical payment lineage must be preserved through `legacy_key_map` and migration metadata. |
| **DR-FX1** | **APPROVED** | **ADMINISTRATIVE FX RATE ENTRY.** New-post FX rate `R` shall be supplied through an authorized administrative rate-entry mechanism. Historical payments shall use `payments.exchange_rate`. `euro_to_bdt_rates` shall remain **non-authoritative**. |

---

## DR-H1 — A (full historical journalization)

### Qualifying set (unchanged A01)

A row is qualifying when **all** of:

- live `payments` (not backup UNION; A01-04)
- `status = 'A'` (A01-01)
- after A01 deduplication/exclusion (R6 live-wins; 1,268 backup overlaps excluded; 7 unclassified remain unclassified; P/R excluded from money)

### Reconstruction

Each qualifying row becomes **one** POSTED journal (deposit or fee according to legacy `type` / request linkage), using:

- stored `amount`
- stored `currency_id` (ISO label beside lineage; not rewritten)
- stored `exchange_rate` as `R`
- W1 `base_amount` computed at load (`EUR = BDT / R` or `EUR = A`)
- unique idempotency per `payments.id`
- `legacy_key_map` in the same transaction (A01-07): `source_system`, `source_table`, `source_id`, `target_type`, `target_id`, `migration_run_id`, `source_row_hash`, `migrated_at`

Do **not** rewrite historical amounts, currencies, or rates. FIN-BUG-03/04 remain encoded in stored values; they are not silently corrected.

### Anti-double-count (mandatory)

A01-10 remains the **calculation / reconciliation control** for the same qualifying set (by wallet and currency).

A01-10 sums of those rows **must not** be posted as `OPENING_BALANCE` journals.

```
qualifying payments.id
→ reconstructed journal
→ NOT on any opening-balance manifest or OPENING_BALANCE journal
```

`reconstructed_payment_ids ∩ opening_manifest_payment_ids = ∅`

### Residual opening (A01-09 still binds)

`OPENING_BALANCE` journals exist only for amounts **not** represented by reconstructed qualifying payments (if any such residual is later identified and staged).

- Create-form cache-only `agents.balance` / `sub_agents.balance` with no `payments` row: **do not invent** journals (existing out-of-scope). Cache deltas → quarantine vs cache.
- Residual opening, if any, stays staged until reconciled and explicitly approved (A01-09).
- If the residual set is empty, no `OPENING_BALANCE` journals are posted.

### What DR-H1 does not decide

- Historical **rejected** `payment_requests` (`status=R`) are **not** qualifying approved `payments` rows. Whether those receive A18 zero journals remains **IMPLEMENTATION-TIME** (`DR-A18H`). New rejects after GO still post A18 (A02-05).
- Teacher wallets are not invented (A02-04 / DR-T1).
- M8 objects are not included.

---

## DR-FX1 — administrative rate entry

| Layer | Authority |
|---|---|
| Historical reconstructed journals | Per-row `payments.exchange_rate` |
| New posts (after GO) | Authorized **administrative rate-entry** supplies `R`; W1 applies: `EUR = BDT / R`; EUR identity `EUR = A` (`R=1` for EUR fees) |
| `euro_to_bdt_rates` | **Non-authoritative** for history and for new posts |

Exact screen, field, or dated-admin-store shape is **implementation-time**. This decision selects the **source type** (authorized administrative entry). It does **not**:

- select an external/automated feed
- load `euro_to_bdt_rates` as `R`
- invent a new historical rate source
- authorize permission keys or runtime grants

New BDT deposits store the administratively entered `R` on the journal line. New EUR fees use identity `R=1`.

---

## Effect on design review

DR-H1 and DR-FX1 are **closed**.  
They are no longer OWNER DECISION REQUIRED.

This record does **not** issue M7 IMPLEMENTATION GO.

---

## FINAL STATUS

**M6 = CLOSED / VERIFIED**  
**M7 PRE-GATE = COMPLETE**  
**A01 = APPROVED WITH CONDITIONS**  
**A02 = CLOSED**  
**DR-H1 = APPROVED (A — FULL HISTORICAL JOURNALIZATION)**  
**DR-FX1 = APPROVED (ADMINISTRATIVE FX RATE ENTRY)**  
**M7 DESIGN REVIEW = READY FOR IMPLEMENTATION GO** (`docs/M7-DESIGN-REVIEW.md`)

**M7 IMPLEMENTATION GO = NOT ISSUED**  
**M7 IMPLEMENTATION = NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS = NOT AUTHORIZED**  
**PRODUCTION MIGRATION = NOT AUTHORIZED**
