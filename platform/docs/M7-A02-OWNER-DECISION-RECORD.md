# M7 A02 Owner Decision Record

**Recorded:** 2026-09-07  
**Superseded for closure status:** A02 is **CLOSED**. See `docs/M7-A02-CLOSURE-RECORD.md`.  
This file retains the audit trail, including the earlier placeholder `<code>` that was **not** a code.

**Does not issue M7 IMPLEMENTATION GO.** Closing A02 does not authorize M7 implementation.  
No application code, Prisma, migration, grants, CI, production data, or Laravel change.

Evidence: `docs/M7-A02-OWNER-DECISION-WORKSHEET.md`  
Request form: `docs/M7-A02-OWNER-DECISION-REQUEST.md`  
Combined A01/A02 recording: `docs/M7-A01-A02-OWNER-DECISIONS.md`  
Closure: `docs/M7-A02-CLOSURE-RECORD.md`

---

## A02 Status

**CLOSED** (see closure record)

**Prior open reason (historical):** A16 exact target code was unresolved while owner text `Code: <code>` was treated as a placeholder.

**Resolution:** Owner explicitly supplied **Panelty Fee = 100**. Placeholder `<code>` is not a code and is superseded.

---

## Decision table

| ID | Owner Decision | Status | Condition |
|----|----------------|--------|-----------|
| **A02-01** Fee debit currency | **EUR.** Legacy `currency_id=2` is accepted as the legacy fee-debit currency interpretation for the **target** decision. Do **not** rewrite historical transactions. Historical `payments.currency_id` remains authoritative for history. A01-10 opening balance continues to use **stored** payment currency. | **APPROVED** | History is not rewritten. Stored currency remains A01-10 input. Target fee-debit currency is EUR. |
| **A02-02** W1 FX | **APPROVE** W1 as already documented. No invented or altered formula. `EUR = BDT / R`; EUR identity `EUR = A`. | **APPROVED** | Depends on A02-01 EUR. Formula is TF-W1 / A02-W1. Not an implementation license. |
| **A02-03** Panelty Fee concept | **KEEP CONCEPT.** Distinct legacy bill-title. Dump **12** is a row count, not a code. | **APPROVED** | Distinct concept. |
| **A02-03** Panelty Fee exact code | **100.** Explicit owner decision. Supersedes earlier placeholder `<code>`. | **APPROVED** | Not row count 12. Not `bill_code=101`. |
| **A02-04** A17 Teacher payment | **RETAIN** teacher payment flow as a business requirement. | **APPROVED** | Does **not** authorize implementation. |
| **A02-05** A18 Zero-DR rejection | **INTENTIONAL.** Do **not** remove it unless a later owner decision changes it. | **APPROVED** | Preserve behavior. No silent redesign. |
| **A02-06** `bill_code=101` | **CONFIRM.** Preserve lineage. Do not map to another bill title. | **APPROVED WITH CONDITION** | Lineage only. Not Panelty Fee / 100. |

---

## A02-01 — Fee debit currency

**Owner decision: EUR**  
**Status: DECIDED / APPROVED**

Binding:

- Fee debit currency = **EUR**.
- Legacy `currency_id=2` is therefore accepted as the legacy fee-debit currency interpretation for the **target** decision.
- Do **NOT** rewrite historical transactions.
- Historical `payments.currency_id` remains authoritative for history.
- A01-10 opening balance continues to use stored payment currency.

This does not authorize posting, schema, or runtime grants.

---

## A02-02 — W1 FX formula / direction

**Owner decision: APPROVE**  
**Status: DECIDED / APPROVED**

A02-01 established EUR as the fee-debit currency. W1 is approved **exactly** as already documented. The formula is **not** invented or altered here.

### Documented W1 (verbatim from `docs/M7-A02-OWNER-DECISION-WORKSHEET.md` TF-W1)

Symbolic values: `A` = stored amount, `R` = stored `payments.exchange_rate`.

| | |
|---|---|
| Output / base | EUR |
| Direction | `EUR = BDT / R` when input is BDT; `EUR = A` when input is EUR (`R=1`) |
| Example | BDT `A=700000`, `R=140` → `5000 EUR`. EUR `A=3000`, `R=1` → `3000 EUR`. |
| Wallet | Ledger-derived EUR (A01-02: cache is not truth) |
| Ledger | Store `(amount, currency, fx_rate, base_amount)` append-only |

Companion wording already documented as **A02-W1** in `docs/M7-A01-A02-DECISION-PACKAGE.md`:

Target always stores `(amount, currency, fx_rate, base_amount)`. Wallet account currency is **EUR**. TAKA posts using per-row rate; EUR posts with `fx_rate=1` and `base_amount=amount`. Matches the **intent** of mechanism (C) for **new** postings. Does **not** claim cache already matches (C).

Historical FX remains per-row `payments.exchange_rate`. Do not rewrite history using `euro_to_bdt_rates`.

This does not authorize implementation.

---

## A02-03 / A16 — Panelty Fee

**Concept: APPROVED** (KEEP CONCEPT)  
**Exact numeric/target code: APPROVED = 100**

- `Panelty Fee` remains a distinct legacy bill-title concept.
- Dump **12** is a **row count**, not a code.
- Audit trail: an earlier owner field `Code: <code>` was recorded as a **placeholder** and was **not** a code. That left A02 open.
- Owner later supplied **Panelty Fee : 100**. **A16: Panelty Fee = 100.**
- Code 100 is not `bill_code=101`.

---

## A02-04 — A17 Teacher payment

**Owner decision: RETAIN**  
**Status: DECIDED / APPROVED**

Teacher payment flow is retained as a business requirement.

This does **NOT** authorize implementation yet. Target design still requires explicit finance implementation design and reconciliation before coding. FIN-BUG-05 lookup defect is **not** silently repaired by this recording.

---

## A02-05 — A18 Zero-DR rejection

**Owner decision: INTENTIONAL**  
**Status: DECIDED / APPROVED**

Zero-DR rejection behavior is considered intentional by the owner.

Do **NOT** remove it.  
Do **NOT** silently redesign it during this recording.

---

## A02-06 — bill_code=101

**Owner decision: CONFIRM**  
**Status: DECIDED / APPROVED WITH CONDITION**

- Preserve legacy `bill_code=101` lineage.
- Do not map it to another bill title.
- No invented business meaning.
- Target implementation must preserve traceability.

**Condition:** Preserve lineage only unless a future explicit decision changes the mapping.

---

## Closure

A02 is **CLOSED**. See `docs/M7-A02-CLOSURE-RECORD.md`.

**Closing A02 does not authorize M7 implementation.**

- A02-01 = APPROVED (EUR)
- A02-02 = APPROVED (W1)
- A02-03 concept = APPROVED
- A02-03 exact code = APPROVED (100)
- A02-04 = APPROVED (RETAIN)
- A02-05 = APPROVED (INTENTIONAL)
- A02-06 = APPROVED WITH CONDITION

---

## FINAL SAFETY STATUS

**M6 = CLOSED / VERIFIED**  
**M7 PRE-GATE = COMPLETE**  
**A01 = APPROVED WITH CONDITIONS**  
**A02 = CLOSED**

**A02-01 Fee currency = EUR**  
**A02-02 W1 = APPROVED**  
**A02-03 Panelty Fee concept = APPROVED; code = 100**  
**A02-04 A17 = RETAIN**  
**A02-05 A18 = INTENTIONAL**  
**A02-06 bill_code=101 = CONFIRMED WITH CONDITION**

**M7 IMPLEMENTATION GO = NOT ISSUED**  
**M7 IMPLEMENTATION = NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS = NOT AUTHORIZED**  
**PRODUCTION MIGRATION = NOT AUTHORIZED**
