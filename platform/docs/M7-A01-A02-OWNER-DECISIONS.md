# M7 A01 / A02 — recorded owner decisions

**Recorded:** 2026-09-07  
**Scope:** A01-01 through A01-10 (A01 APPROVED WITH CONDITIONS). A02-01 through A02-06 complete. **A02 = CLOSED.** A16 Panelty Fee code = **100**.

This recording **does** close A01 as **APPROVED WITH CONDITIONS**.  
This recording **does** close A02 (see `docs/M7-A02-CLOSURE-RECORD.md`).  
This recording does **not** issue M7 IMPLEMENTATION GO.  
No code, Prisma, migration, grants, CI, production data, or Laravel change.

Tick-sheet: `docs/M7-A01-A02-OWNER-CHECKLIST.md`  
Evidence package: `docs/M7-A01-A02-DECISION-PACKAGE.md`  
A02 evidence worksheet: `docs/M7-A02-OWNER-DECISION-WORKSHEET.md`  
A02 owner decision request: `docs/M7-A02-OWNER-DECISION-REQUEST.md`  
A02 owner decision record: `docs/M7-A02-OWNER-DECISION-RECORD.md` (superseded for closure status)  
A02 closure record: `docs/M7-A02-CLOSURE-RECORD.md`  
Implementation gate (design freeze, **not** GO): `docs/M7-IMPLEMENTATION-GATE.md`

---

## A01 recorded decisions

| Item | Owner mark | Binding text |
|---|---|---|
| **A01-01** | **APPROVED** | `payments` is the authoritative legacy source for live wallet money movement. Only `payments.status = 'A'` counts as an active legacy wallet transaction. |
| **A01-02** | **APPROVED** | `agents.balance` and `sub_agents.balance` are **not** authoritative. They are legacy cached/mutable values. The target must not treat them as the source of financial truth. |
| **A01-03** | **APPROVED WITH CONDITION** | `payment_requests` is both (1) approval workflow and (2) legacy posting trigger upon approval. It is **not** itself the authoritative ledger. Target implementation must **not** blindly reproduce the legacy direct-balance mutation behavior. **Condition:** final target posting behavior remains subject to A02 and M7 implementation approval. |
| **A01-04** | **APPROVED** | All six identified payment backup tables are **ARCHIVAL ONLY**. They must **never** be automatically UNIONed with live payment data. |
| **A01-05** | **APPROVED** | For the documented **1,268 / 1,275** backup overlap: live records **WIN**; 1,268 overlapping backup rows are duplicates; remaining **7** backup rows remain **UNCLASSIFIED**. No silent repair. No automatic financial posting from backup rows. |
| **A01-06** | **APPROVED** | The **14** duplicate receipt groups identified in R3 remain within System B. They do **not** create wallet postings. Extra/duplicate records are retained for quarantine/review, not silently deleted. |
| **A01-07** | **APPROVED** | Every migrated financial record must preserve `source_system`, `source_table`, `source_id`, `target_type`, `target_id`, `migration_run_id`, `source_row_hash`, `migrated_at` through `migration.legacy_key_map`. No financial record may lose its legacy lineage. |
| **A01-08** | **APPROVED** | Do **not** repair or reinterpret as financial authority: 318 `admission_payment_id` references; only 3 Admission requests; zero Final/Medical payment FKs. Inconsistencies remain documented and, where applicable, quarantined during M13. No invented relationship. |
| **A01-09** | **APPROVED WITH CONDITION** | Opening balance must be staged and must **not** become authoritative until explicitly approved. A01-10 is the calculation principle. **Condition:** the final opening-balance calculation output must be reconciled and explicitly approved before production migration/cutover. |
| **A01-10** | **APPROVED** | Opening balance: calculate from approved live `payments` (`status='A'`), after approved A01 deduplication/exclusion rules, **separately by wallet and currency**. Unexplained differences are **quarantined**. Cached balances are **not** authoritative. |

### A01 final status

**A01 = APPROVED WITH CONDITIONS**

Conditions:

1. At A01 recording, A02 was still open. **A02 is now CLOSED.** That historical condition does not keep A02 open. A01 conditions 2–7 still apply. Posting still requires a separate **M7 IMPLEMENTATION GO**.
2. Opening-balance calculation output requires reconciliation.
3. No finance implementation is authorized by this document.
4. No production migration is authorized.
5. Finance runtime grants remain unauthorized.
6. Final target posting behavior remains subject to A02.
7. A01 approval does not constitute M7 Implementation GO.

---

## A02 recorded decisions (2026-09-07; A02 CLOSED)

Authoritative closure: `docs/M7-A02-CLOSURE-RECORD.md`.

| Item | Owner mark | Binding text |
|---|---|---|
| **A02-01 Fee debit currency** | **APPROVED** | **EUR.** Legacy `currency_id=2` is accepted as the legacy fee-debit currency interpretation for the **target** decision. Do **not** rewrite historical transactions. Historical `payments.currency_id` remains authoritative for history. A01-10 continues to use stored payment currency. |
| **A02-02 W1** | **APPROVED** | Documented TF-W1: `EUR = BDT / R` when input is BDT; EUR identity `EUR = A`. Store `(amount, currency, fx_rate, base_amount)`. Cache is not truth. Formula is not invented or altered. Not an implementation license. |
| **A02-03 `Panelty Fee` concept** | **APPROVED** | KEEP CONCEPT. Distinct legacy bill-title. Dump count **12** is a **row count**, not a code. |
| **A02-03 exact target code** | **APPROVED** | **A16: Panelty Fee = 100.** Explicit owner value. Supersedes earlier placeholder `<code>` (that placeholder was not a code). Do not confuse 100 with lineage `bill_code=101`. |
| **A02-04 A17** | **APPROVED** | **RETAIN** teacher payment flow as a business requirement. Does **not** authorize implementation. Design and reconciliation still required before coding. |
| **A02-05 A18** | **APPROVED** | Zero-DR rejection is **INTENTIONAL**. Do **not** remove it unless a later owner decision changes it. Do **not** silently redesign it. |
| **A02-06 `bill_code=101`** | **APPROVED WITH CONDITION** | **CONFIRM.** Preserve lineage only. Do not map to another bill title (including Panelty Fee / 100). No invented business meaning. Traceability required. **Condition:** lineage only unless a future explicit decision changes the mapping. |

**A02 = CLOSED.** Closing A02 does not authorize M7 implementation.

Audit trail: an earlier recording treated owner text `Code: <code>` as a placeholder and left A16 exact code UNRESOLVED. The owner has now supplied **100**. The placeholder is historical evidence only.

---

## Locked A02 meaning (not implementation)

1. Target fee-debit currency is **EUR**. Legacy id `2` is the accepted **target interpretation** of fee-debit currency. Historical `payments.currency_id` is **not** rewritten.
2. W1 is the approved FX rule for **new** postings: EUR base; `EUR = BDT / R`; EUR identity `EUR = A`. Historical rates remain per-row `payments.exchange_rate`. Do not apply `euro_to_bdt_rates` (117.346) to history.
3. `Panelty Fee` is a distinct concept. **A16: Panelty Fee = 100.** Dump **12** is a row count. Earlier `<code>` was a placeholder, not a code.
4. Teacher payment flow is retained as a requirement only. No finance coding is authorized.
5. Zero-DR on reject is intentional and must not be removed or silently redesigned unless a later owner decision changes it.
6. `bill_code=101` is lineage-only. It is not Panelty Fee and is not code 100.

Do not implement. Do not issue M7 IMPLEMENTATION GO. Closing A02 does not authorize M7 implementation.

---

## Locked A01 meaning (authoritative source)

1. Live `payments` with `status = 'A'` is the only authoritative legacy wallet money-movement source.
2. Cached `agents.balance` / `sub_agents.balance` are not financial truth.
3. `payment_requests` is workflow + legacy posting trigger, not the ledger. Do not reproduce direct cache mutation as target policy. Posting shape waits on A02 + M7 GO.
4. All six payment backup tables are archive only. Never auto-UNION with live.
5. R6 overlap 1,268/1,275: live wins; 1,268 backup rows are duplicates; 7 remain UNCLASSIFIED. No silent repair; no backup posting.
6. R3 receipt duplicates stay in System B; no wallet postings; retain extras for quarantine/review.
7. Finance migration lineage is mandatory via `migration.legacy_key_map` (including `target_type`, `target_id`, `migrated_at`).
8. Broken admission/Final/Medical FKs are not authority and are not repaired.
9. Opening balances are staged only. A01-10 is the calculation method. Output must be reconciled and explicitly approved before cutover.
10. Unexplained differences versus cache (or versus expected control totals) are quarantined.

Do not seed or migrate production balances. Do not implement.

---

## What W1 / fee-debit now mean (supersedes HOLD)

Previous HOLD on W1 and fee-debit currency is **superseded** by A02-01 / A02-02 above.

- Fee debit currency is **EUR** (target decision).
- Legacy `currency_id=2` is accepted as that target interpretation.
- Historical rows are **not** rewritten; A01-10 still sums **stored** `payments.currency_id`.
- W1 is **APPROVED** as documented TF-W1 / A02-W1 (EUR base; `EUR = BDT / R` or `EUR = A`).
- `payment_requests` still has **no** persisted `currency_id` in the dump. Target fee-debit currency is the owner EUR decision, not a recovered request-table field.

**A02 is CLOSED.** See `docs/M7-A02-CLOSURE-RECORD.md`. Closing A02 does not authorize M7 implementation.

---

## What Panelty Fee / bill_code=101 mean

- **`Panelty Fee`:** distinct legacy bill-title concept. Dump **12** is a row count, not a code. **A16: Panelty Fee = 100** (explicit owner decision). Earlier recording of `Code: <code>` was a placeholder and is superseded; `<code>` was never a code.
- **`bill_code=101`:** CONFIRMED WITH CONDITION. Preserve lineage only. Do **not** map `101` to Panelty Fee or to code 100. No invented business meaning.

---

## A16 / A17 / A18

| ID | Status | Binding |
|---|---|---|
| **A16** | **APPROVED** for Panelty Fee code | **Panelty Fee = 100.** Concept retained. Dump 12 is a row count. Placeholder `<code>` was historical only. FIN-BUG-01 / FIN-BUG-02 correction remains an implementation-design item; closing A02 does **not** authorize those fixes or M7 implementation. |
| **A17** | **APPROVED — RETAIN** | Teacher payment flow retained as a business requirement. Implementation is **not** authorized. |
| **A18** | **APPROVED — INTENTIONAL** | Zero-DR rejection row is intentional. Do not remove unless a later owner decision changes it. Do not silently redesign. |

---

## Status after this recording

| Gate | Status |
|---|---|
| A01-01 through A01-10 | Recorded as above |
| A01 (hard gate) | **APPROVED WITH CONDITIONS** |
| A02-01 Fee debit currency | **APPROVED** (EUR) |
| A02-02 W1 | **APPROVED** |
| A02-03 Panelty concept | **APPROVED** |
| A02-03 exact code | **APPROVED** (100) |
| A02-04 A17 | **APPROVED** (RETAIN) |
| A02-05 A18 | **APPROVED** (INTENTIONAL) |
| A02-06 `bill_code=101` | **APPROVED WITH CONDITION** |
| A02 (hard gate) | **CLOSED** |
| M7 IMPLEMENTATION GATE | **COMPLETE** |
| M7 DESIGN REVIEW | **READY FOR IMPLEMENTATION GO** (`docs/M7-DESIGN-REVIEW.md`) |
| DR-H1 | **APPROVED** — A, full historical journalization (`docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`) |
| DR-FX1 | **APPROVED** — administrative FX rate entry (same record) |
| M7 IMPLEMENTATION GO | **NOT ISSUED** |

---

## FINAL STATUS

**M6: CLOSED / VERIFIED**  
**M7 PRE-GATE: COMPLETE**  
**A01: APPROVED WITH CONDITIONS**  
**A02: CLOSED**  
**M7 IMPLEMENTATION GATE = COMPLETE**  
**M7 DESIGN REVIEW = READY FOR IMPLEMENTATION GO** (`docs/M7-DESIGN-REVIEW.md`)  
**DR-H1 = APPROVED (A — FULL HISTORICAL JOURNALIZATION)** (`docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`)  
**DR-FX1 = APPROVED (ADMINISTRATIVE FX RATE ENTRY)**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**A02-01 Fee currency = EUR**  
**A02-02 W1 = APPROVED**  
**A02-03 Panelty Fee concept = APPROVED; code = 100**  
**A02-04 A17 = RETAIN**  
**A02-05 A18 = INTENTIONAL**  
**A02-06 bill_code=101 = CONFIRMED WITH CONDITION**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS: NOT AUTHORIZED**
