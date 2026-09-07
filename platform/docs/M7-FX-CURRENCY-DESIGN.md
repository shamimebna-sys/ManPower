# M7 — FX and currency design

**Freeze document.** No Prisma. No code. No M7 IMPLEMENTATION GO.

Parent: `docs/M7-IMPLEMENTATION-GATE.md`

---

## 1. Currency master

Legacy dump `currencies` (two rows only):

| Legacy id | `full_name` | Target ISO |
|---:|---|---|
| 1 | TAKA | **BDT** |
| 2 | Euro | **EUR** |

Do not add USD or other codes.

Conceptual mapping for **new** posts and for **display of mapped ISO**: `1 → BDT`, `2 → EUR`.

**Historical records** keep original stored `payments.currency_id` / amount / `exchange_rate` through `legacy_key_map`. Do **not** change historical amounts. Do **not** rewrite historical currency ids in the source sense: staging copies them as stored; ISO is a **target label** beside lineage, not a license to alter figures.

Ticket varchar `EURO`/`TAKA` is **M8** (FIN-BUG-09).

---

## 2. Owner-approved W1 (verbatim)

**A02-02 APPROVED.** Do not alter.

```
EUR = BDT / R
EUR identity:
EUR = A
```

| | |
|---|---|
| Rate direction | BDT is divided by stored rate `R` to obtain EUR. EUR amounts are already EUR. |
| Source currency | Line currency: BDT or EUR (from stored legacy id or new ISO). |
| Target / base | **EUR** (wallet control and `base_amount`). |
| Stored rate | Historical `R` = per-row `payments.exchange_rate`. New-post `R` = authorized administrative rate-entry (**DR-FX1**). `euro_to_bdt_rates` is not authority. |
| Conversion | If input is BDT: `EUR = BDT / R`. If input is EUR: `EUR = A` with `R = 1`. |
| Symbolic | `A` = stored amount; `R` = stored rate. Example: BDT `A=700000`, `R=140` → `5000 EUR`. EUR `A=3000`, `R=1` → `3000 EUR`. |
| Fee debit | **EUR** (A02-01): identity `EUR = A`. |

Companion freeze (A02-W1 / TF-W1): store `(amount, currency, fx_rate, base_amount)`. Wallet account control currency is **EUR**. TAKA posts using per-row rate; EUR posts with `fx_rate=1` and `base_amount=amount`. This matches the **intent** of display mechanism (C) for **new** posts. It does **not** claim `agents.balance` already matches (C). R1 failed 30/40 agents; cache is not truth.

---

## 3. Historical FX

**Authoritative historical rate = per-row `payments.exchange_rate`.**

`euro_to_bdt_rates.euro_rate = 117.346` is **not** historical runtime authority (unused in approval paths; live rates cluster at 1 and ~140–148).

Do **not**:

- rewrite history with 117.346
- create a new historical rate source
- change stored amounts

A01-10 **control totals** use **stored** payment currency, then W1 may be applied **in staging reports** to produce EUR views **without** mutating source rows. Those qualifying rows become reconstructed journals (**DR-H1 A**), not `OPENING_BALANCE` posts. Residual opening journals (if any) post only after A01-09 sign-off.

---

## 4. Rounding policy

| Step | Policy |
|---|---|
| Extract | FLOAT as text → Decimal → `NUMERIC(20,6)` / `NUMERIC(20,8)` |
| Posting | `ROUND_HALF_UP` only at posting boundary for `base_amount` |
| Display | May round to 2; storage keeps 6 / 8 |
| Discrepancy | Round-trip vs source FLOAT > 0.01 → quarantine / review (Design Gate) |

---

## 5. New vs historical posts

| | Historical (migrated qualifying `payments` status A) | New (after GO) |
|---|---|---|
| Amount | As stored | As entered |
| Currency | As stored (map label 1/2 → BDT/EUR) | ISO BDT or EUR; **fees EUR** |
| Rate | As stored (`payments.exchange_rate`) | Administrative rate-entry supplies `R` (**DR-FX1**); fees `R=1` |
| W1 | Used to compute `base_amount` at load, not to overwrite `amount` | Used at posting |
| Journal | One reconstructed journal per qualifying row; **not** also opening | Live deposit/fee/reject journals |

`euro_to_bdt_rates` remains **non-authoritative**.

---

## FINAL STATUS

**M7 IMPLEMENTATION GATE = COMPLETE**  
**DR-H1 = APPROVED (A)**  
**DR-FX1 = APPROVED (ADMINISTRATIVE RATE ENTRY)**  
**M7 IMPLEMENTATION GO = NOT ISSUED**
