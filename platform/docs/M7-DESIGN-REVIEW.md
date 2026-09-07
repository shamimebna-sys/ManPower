# M7 Finance — Design Review

**Document type:** FINAL DESIGN REVIEW  
**Date:** 2026-09-07  
**Does not issue M7 IMPLEMENTATION GO.**  
No application code, Prisma, migration, API, UI, permissions, grants, CI, production data, or Laravel change.

Reviewed freeze package:

- `docs/M7-IMPLEMENTATION-GATE.md`
- `docs/M7-TARGET-FINANCE-DESIGN.md`
- `docs/M7-ACCOUNTING-MODEL.md`
- `docs/M7-WALLET-PAYMENT-DESIGN.md`
- `docs/M7-FX-CURRENCY-DESIGN.md`
- `docs/M7-MIGRATION-RECONCILIATION-PLAN.md`
- `docs/M7-SECURITY-AUDIT-DESIGN.md`

Owner closures of review blockers: `docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`

Locked inputs (not reopened): A01 APPROVED WITH CONDITIONS; A02 CLOSED; `docs/M7-A01-A02-OWNER-DECISIONS.md`; `docs/M7-A02-CLOSURE-RECORD.md`; `docs/M7-PRE-GATE.md`; Design Gate `06-finance.md` / `finance-operations.csv` / Rule 2, 4, 5.

---

## 1. Review scope

Determine whether the frozen design is complete enough to **authorize** implementation.

This review:

- Does **not** implement finance.
- Records owner choices for DR-H1 and DR-FX1; it does not invent further policy.
- Does **not** invent teacher wallets, GL numbers, or reserved balances.
- Does **not** rewrite historical FKs, amounts, currencies, or rates.
- Does **not** pull M8 objects into M7.
- Does **not** issue M7 IMPLEMENTATION GO.

**M7 IMPLEMENTATION GO remains NOT ISSUED.**  
**M7 IMPLEMENTATION remains NOT AUTHORIZED.**  
**FINANCE RUNTIME GRANTS remain NOT AUTHORIZED.**  
**PRODUCTION MIGRATION remains NOT AUTHORIZED.**

---

## 2. Design strengths

- System A vs B/C isolation is explicit. M8 is not in M7 write scope.
- A01/A02 applied without alteration: fee EUR; W1 `EUR = BDT / R` / `EUR = A`; Panelty **100**; A18 zero-DR for new rejects; `bill_code=101` lineage-only; cache is not authority; history is not rewritten; `euro_to_bdt_rates` is not authority.
- **DR-H1 A** reconstructs qualifying approved `payments` as journals with `legacy_key_map`; the same row is not also an opening post.
- **DR-FX1** supplies new-post `R` by authorized administrative rate-entry; historical `R` remains `payments.exchange_rate`.
- Double-entry, reversal-not-delete, NUMERIC types, `ROUND_HALF_UP` only at posting.
- Payment-request P/A/R, transactional approve, idempotency, insufficient-funds reject.
- A01-10 remains the recon **control**; residual opening only (A01-09) if anything is not a reconstructed payment.
- Reconciliation gates A–J include anti-double-count (D) and reconstructed vs formula (C/D/H).
- Conceptual RBAC only; no keys; no grants.
- Teacher RETAIN without invented wallets. Reserved balance not introduced.

---

## 3. Implementation blockers

**None remaining that require a further owner decision.**

Previous blockers, now closed:

| ID | Resolution |
|---|---|
| **DR-H1** | **APPROVED — A.** Full historical journalization. Binding: `docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`. |
| **DR-FX1** | **APPROVED — administrative rate-entry** for new-post `R`. Same record. |

A01-09 residual-opening sign-off (if a residual batch exists) and a separate **M7 IMPLEMENTATION GO** document still block **coding / cutover**. They are not open design choices.

The freeze’s former silent defaults (opening-totals path; “manual per-row unless later”) are **superseded** by these owner ticks.

---

## 4. Implementation-time decisions

May be confirmed during implementation **after** GO, without inventing business meaning.

| ID | Item | Class |
|---|---|---|
| **DR-T1** | Teacher owner mapping / profile confirmation; FIN-BUG-05 lookup correction **if** a teacher path is actually built | **IMPLEMENTATION-TIME MAPPING / PROFILE CONFIRMATION** |
| **DR-G1** | Numeric/statutory GL codes for FEE_INCOME, DEPOSIT_CLEARING, OPENING_EQUITY | **IMPLEMENTATION-TIME DECISION** |
| **DR-B1** | Numeric codes for bill titles other than Panelty **100** | **IMPLEMENTATION-TIME DECISION** |
| **DR-F1** | NEW posting must not reproduce FIN-BUG-01 assignment; use title/code **equality** | **IMPLEMENTATION-TIME DECISION** |
| **DR-F2** | Target WF-09 / manpower check: exact `Manpower Fee` title (and code if later approved) | **IMPLEMENTATION-TIME DECISION** |
| **DR-S1** | Sub-agent request MERGE into one `PaymentRequest` aggregate (1 dump row) | **IMPLEMENTATION-TIME DECISION** |
| **DR-O1** | Reconstruction **manifest schema** (per qualifying payment: id, hash, wallet, currency, amount, rate) plus residual-opening manifest if any | **IMPLEMENTATION-TIME DECISION** |
| **DR-Q1** | Quarantine reason-code catalogue and UI | **IMPLEMENTATION-TIME DECISION** |
| **DR-P1** | Optional wallet projection table vs view-only | **IMPLEMENTATION-TIME DECISION** |
| **DR-U1** | New uniqueness of `(candidate, bill_type)` | **IMPLEMENTATION-TIME DECISION** |
| **DR-K1** | Exact permission **key strings** and grant matrix | **IMPLEMENTATION-TIME DECISION** (categories frozen; keys require a later grant GO) |
| **DR-I1** | Isolation level (serializable or documented equivalent) at posting | **IMPLEMENTATION-TIME DECISION** |
| **DR-FX1M** | Exact administrative rate-entry **mechanism shape** (screen/field/store) | **IMPLEMENTATION-TIME DECISION** (source **type** is frozen as administrative entry) |
| **DR-A18H** | Whether **historical** rejected requests get A18 zero journals vs workflow-only + lineage | **IMPLEMENTATION-TIME DECISION** (not qualifying approved `payments`; new rejects still post A18) |

---

## 5. Out-of-scope items

| ID | Item | Class |
|---|---|---|
| **DR-R1** | Reserved / held wallet balance | **OUT OF SCOPE** |
| **DR-M8** | Employer invoices, receipts, tickets, ticket currency, FIN-BUG-07/09, A↔B/C settlement | **OUT OF SCOPE** — M8 |
| **DR-P2** | Partner `agenciers`/`companiers` ledgers | **OUT OF SCOPE** |
| **DR-HFIX** | Repairing historical `admission_payment_id` / empty Final/Medical FKs | **OUT OF SCOPE** (A01-08) |
| **DR-E1** | Using `euro_to_bdt_rates` as any rate authority | **OUT OF SCOPE** |
| **DR-G2** | Payment gateways | **OUT OF SCOPE** |
| **DR-C1** | Candidate `balance` column as a wallet | **OUT OF SCOPE** |
| **DR-B08** | FIN-BUG-08 agent-report hardcoded zeros | **OUT OF SCOPE** — M10 |
| **DR-M9** | Cheque file storage | **OUT OF SCOPE** — M9 |
| **DR-CF** | Create-form cache-only balances with no `payments` row | **OUT OF SCOPE** as invented journals; quarantine vs cache |

Approach **B** (opening-totals posting of the same qualifying payments) is **rejected** by DR-H1. It is not available as an implementation-time pick.

---

## 6. Historical journalization decision status

**Status: APPROVED — A (DR-H1)**

Owner 2026-09-07:

> Every qualifying approved legacy payments row shall be reconstructed into the target accounting journal.  
> The same legacy payment shall NOT also be included in an opening-balance posting.  
> Historical payment lineage must be preserved through `legacy_key_map` and migration metadata.

### What “qualifying” means (A01, not reopened)

Live `payments`, `status='A'`, after A01 exclusions (no backup UNION; R6 live-wins; P/R excluded from money).

### What A means in the ledger

| | |
|---|---|
| Advantages (accepted) | Native journal history; row-for-row recon C/G/H/I; lineage on each journal |
| Disadvantages (accepted, not silently “fixed”) | Legacy FX/cache defects remain in stored amount/rate/currency; large journal volume |
| Reconciliation | Gate D = reconstructed wallet totals vs A01-10 formula **and** empty intersection with opening-manifest ids |
| Audit | `journal.posted` / `historical_journal.imported` + `legacy_key_map` per `payments.id` |
| Migration | One journal per qualifying id; unique idempotency; same-transaction lineage |
| Historical fidelity | High for row existence; still not equal to cache (R1 fail → Gate J) |

### Approach B

**Not selected.** Do not post A01-10 sums of those same rows as `OPENING_BALANCE`.

A01-10 remains the **calculation used to check** reconstruction. Residual opening (non-payment amounts only), if any, still needs A01-09.

Historical `payment_requests` status `R` are **not** qualifying approved payments. **DR-A18H** stays implementation-time.

---

## 7. Opening balance readiness

Pipeline after DR-H1:

```
legacy approved total (payments status=A)
→ exclusions (backups never unioned; R6 live-wins; P/R excluded)
→ quarantine
→ reconstruct one journal per remaining qualifying row  ← money in the ledger
→ A01-10 control totals by wallet and currency          ← recon, not a second post
→ residual opening only if amount is NOT a reconstructed payment
→ A01-09 if residual exists
→ Gate D anti-double-count
```

**Required lineage on each reconstructed journal / manifest row**

| Field | Required |
|---|---|
| Source payment ID (`payments.id`) | Yes |
| Source table | Yes (`payments`) |
| Source hash | Yes (A01-07) |
| Migration run | Yes |
| Wallet (agent vs sub-agent + owner id) | Yes |
| Currency (stored; ISO label beside it) | Yes |
| Source amount | Yes |
| Source exchange rate | Yes (`payments.exchange_rate`) |

Manifest **schema** drawing remains DR-O1 (implementation-time).

Create-form cache-only balances: still not invented journals. R5 rows migrate with **stored** `R`. R12 remains diagnostic.

**Opening-balance readiness:** RECON CONTROL **YES**. RESIDUAL POSTING only if a residual set exists + A01-09. QUALIFYING PAYMENTS = JOURNALS, NOT OPENING.

---

## 8. FX readiness

| Layer | Status |
|---|---|
| Historical rate | **READY** — `payments.exchange_rate` |
| `euro_to_bdt_rates` | **NON-AUTHORITATIVE** (history and new posts) |
| W1 | **READY** — `EUR = BDT / R`; `EUR = A` |
| Fee debit currency | **READY** — EUR (`R=1`) |
| **NEW** transaction `R` supplier | **READY (policy)** — authorized administrative rate-entry (**DR-FX1**) |
| Mechanism shape | **IMPLEMENTATION-TIME** (DR-FX1M) |

**FX readiness:** HISTORICAL + W1 + NEW-RATE **SOURCE TYPE** **YES**. Grants/UI **NOT AUTHORIZED**.

---

## 9. Teacher payment readiness

Unchanged. Owner RETAIN (A02-04). Dump `teacher_id != 0` = **0**. No invented teacher wallet, balance, account, or relationship.

**Class: IMPLEMENTATION-TIME MAPPING / PROFILE CONFIRMATION** (DR-T1).

**Teacher readiness:** DESIGN **SAFE**. RUNTIME PATH **NOT READY**.

---

## 10. GL account readiness

Conceptual types frozen. No invented statutory codes.

**Class: IMPLEMENTATION-TIME DECISION** (DR-G1).

**GL readiness:** CONCEPTUAL **YES**. STATUTORY NUMBERS **N/A**.

---

## 11. Reconciliation readiness

Gates A–J are sufficient for System A under **DR-H1 A**.

| Gate | Role |
|---|---|
| A | LIVE vs staged vs loaded **count** |
| B | Amount totals **by stored currency** |
| C | `status=A` totals vs reconstructed journal sources |
| D | Reconstructed wallet totals vs A01-10; **ids ∩ opening-manifest = ∅** |
| E | Payment-request totals (Panelty 12 rows) |
| F | Exclusions (R6 1,268; 7 unclassified) |
| G | Journal debit/credit equality |
| H | Ledger = reconstructed historical + residual opening (if any) + new journals |
| I | W1 `base_amount` vs stored `R` |
| J | `LIVE + QUARANTINED + ARCHIVE + UNCLASSIFIED = SOURCE` |

R1 is not authority. R5 listed, not rewritten.

**Reconciliation readiness:** DESIGN **YES**.

---

## 12. Security readiness

Categories frozen, including finance administration covering residual opening sign-off, reversal, quarantine, and administrative FX rate entry.

**No keys or grants in this review.**

**Security readiness:** CATEGORIES **YES**. GRANTS **NOT AUTHORIZED**.

---

## 13. FIN-BUG-01 / FIN-BUG-02 (and related)

Do **not** fix historical data silently.

| Bug | Historical | New M7 behavior |
|---|---|---|
| **FIN-BUG-01** | Stage FKs as-is (A01-08) | Equality on title/code (DR-F1) |
| **FIN-BUG-02** | Do not rewrite requests | Exact `Manpower Fee` (DR-F2) |
| **FIN-BUG-03 / 04** | Stored amount/rate/currency kept on reconstructed journals | New posts: W1 + EUR fees; admin `R` (DR-FX1) |
| **FIN-BUG-05** | No dump rows | DR-T1 mapping |
| **FIN-BUG-06** | Historical R journals = DR-A18H | New rejects post A18 |
| **FIN-BUG-07 / 09** | M8 | M8 |
| **FIN-BUG-08** | M10 | M10 |

---

## 14. Implementation boundary (frozen)

**M7 — System A only**

- Agent wallet
- Sub-agent wallet
- Payment requests
- Fee posting
- EUR fees
- Panelty Fee code **100**
- W1 FX
- Administrative new-post `R` (DR-FX1)
- Full historical journalization of qualifying `payments` (DR-H1 A)
- A01-10 recon control; residual opening only
- Reconciliation
- Finance audit

**M8 — not M7**

- Employer invoices
- Receipts
- Ticket invoices
- Ticket receipts
- Ticket currency cleanup
- FIN-BUG-07 / FIN-BUG-09
- A↔B/C settlement

No M8 functionality may enter M7.

---

## 15. Final recommendation

Owner decisions DR-H1 and DR-FX1 close the only design-review blockers. The freeze is complete enough to **authorize** implementation **via a separate M7 IMPLEMENTATION GO document**.

This review does **not** itself issue that GO.

Implementation-time items (GL numbers, permission key strings, teacher mapping, rate-entry screen shape, historical reject journals, reconstruction manifest schema) may be confirmed after GO without new owner policy — except that grants still require a **separate grant GO**.

---

## M7 DESIGN REVIEW RESULT

**READY FOR IMPLEMENTATION GO**

Closed by owner:

1. **DR-H1** — **A — FULL HISTORICAL JOURNALIZATION**
2. **DR-FX1** — **ADMINISTRATIVE FX RATE ENTRY**

This review does **not** issue M7 IMPLEMENTATION GO.

---

## FINAL STATUS

**M6 = CLOSED / VERIFIED**  
**M7 PRE-GATE = COMPLETE**  
**A01 = APPROVED WITH CONDITIONS**  
**A02 = CLOSED**  
**DR-H1 = APPROVED (A)**  
**DR-FX1 = APPROVED (ADMINISTRATIVE RATE ENTRY)**  
**M7 DESIGN = FROZEN**  
**M7 DESIGN REVIEW = READY FOR IMPLEMENTATION GO**

**M7 IMPLEMENTATION GO = NOT ISSUED**  
**M7 IMPLEMENTATION = NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS = NOT AUTHORIZED**  
**PRODUCTION MIGRATION = NOT AUTHORIZED**
