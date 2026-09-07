# M7 — Accounting model

**Freeze document.** No Prisma. No code. No M7 IMPLEMENTATION GO.

Parent: `docs/M7-IMPLEMENTATION-GATE.md`

---

## 1. Invariants

For every **POSTED** journal:

**SUM(debits) = SUM(credits)**

in the journal’s **posting base (EUR)** and, for each line-currency group, in that currency’s stored amounts where dual-currency lines exist.

- A journal with no lines is forbidden.
- A single-line journal is forbidden.
- Zero-amount journals are **allowed only** for A18 rejection preservation (both sides 0).
- Posted journals are **immutable**: no UPDATE of monetary fields, accounts, rates, or status except a status flag that records “reversed by journal J”.
- No DELETE of posted journals.
- Correction = **reversal journal** linked to the original + optional **replacement** journal.

Rounding: `ROUND_HALF_UP` **only** at the approved posting boundary (`finance-operations.csv`). Storage `NUMERIC(20,6)` / rates `NUMERIC(20,8)`. Display may round to 2; storage keeps 6. Legacy FLOAT is parsed as **text** first.

---

## 2. Conceptual account types

Do not invent extra wallet products. Counterpart accounts exist so every wallet movement is balanced.

| Account type | Normal balance | Currency | Use |
|---|---|---|---|
| **WALLET_LIABILITY** | Credit | Per `WalletAccount` (BDT and/or EUR; **derived EUR** via W1 for control) | Agent or sub-agent wallet. Deposit **credits** the wallet; fee **debits** the wallet. |
| **DEPOSIT_CLEARING** | Debit | Matches source deposit currency; EUR `base_amount` stored | Counterpart to wallet top-up. |
| **FEE_INCOME** | Credit | **EUR** for new fee posts (A02-01) | Counterpart to candidate fee debit. Sub-identify by bill type (Panelty = 100). |
| **OPENING_EQUITY** | Credit / debit as needed | Per residual opening currency; EUR base | Counterpart to **residual** opening-balance wallet lines only (amounts not represented by reconstructed qualifying payments). Staged until A01-09. Qualifying `payments` use deposit/fee journals, not this account (**DR-H1 A**). |
| **REVERSAL_CLEARING** | — | Same as reversed journal | Used only if a replacement needs a parking line; prefer exact reverse of original lines. |

Teacher accounts: **not created from dump evidence**. If implementation-time mapping confirms a teacher owner, the same WALLET_LIABILITY pattern applies. Until then, no teacher wallet rows.

Partner / employer AR: **out of M7** (M8). Do not invent.

---

## 3. Journal header (conceptual fields)

| Field | Rule |
|---|---|
| `id` | Stable PK |
| `status` | `POSTED` or `REVERSED` (header still exists; lines untouched) |
| `entry_type` | `WALLET_DEPOSIT` / `FEE_APPROVAL` / `FEE_REJECTION_ZERO` / `OPENING_BALANCE` / `REVERSAL` |
| `source_type` | e.g. `payment_request`, `payments`, `opening_batch` (residual only) |
| `source_id` | Target or staged id |
| `reference` | Human invoice/request reference |
| `posted_at` | Posting timestamp |
| `created_by` | Actor |
| `approved_by` | Approver where applicable |
| `idempotency_key` | Unique; retries return the same journal |
| `reverses_journal_id` | Set on reversal journals |
| `reversed_by_journal_id` | Set on original when reversed |
| `correlation_id` | Audit correlation |

---

## 4. Journal line (conceptual fields)

| Field | Rule |
|---|---|
| `journal_id` | Header FK |
| `line_no` | Stable order |
| `account_type` + owner/account id | WALLET_LIABILITY / FEE_INCOME / … |
| `side` | `DEBIT` or `CREDIT` |
| `currency` | ISO `BDT` or `EUR` |
| `amount` | `NUMERIC(20,6)` in **line currency** |
| `fx_rate` | `NUMERIC(20,8)` stored `R` (historical: `payments.exchange_rate`; new posts: administrative rate-entry, **DR-FX1**) |
| `base_amount` | EUR amount after W1; `NUMERIC(20,6)` |
| `fx_direction` | Documented as W1: `EUR = BDT / R` or identity `EUR = A` |
| `source_type` / `source_id` | Line-level lineage when needed |
| `bill_type_code` | On fee lines: **100** for Panelty Fee; other titles as approved later |

Invariant per journal: sum of debit `base_amount` = sum of credit `base_amount`.

---

## 5. Reversal

| Rule | Meaning |
|---|---|
| Trigger | Authorized finance correction; never BREAD edit |
| Method | New journal `entry_type=REVERSAL` with swapped sides and identical amounts/rates |
| Link | `reverses_journal_id` → original; original `reversed_by_journal_id` → reversal |
| Original lines | Unchanged |
| Replacement | Optional second POSTED journal with new idempotency key |
| Audit | `journal.reversed` with actor, reason, correlation |

Migration rollback of **unposted staging** may delete staging rows. Migration rollback of **posted** journals is reversal only (see migration plan).

---

## 6. A18 zero-DR rejection

Owner: **INTENTIONAL**. Do **not** omit.

Target: when a payment request is rejected, post a **FEE_REJECTION_ZERO** journal:

- Two lines, amount **0**, `base_amount` **0**
- Wallet DEBIT 0 / FEE_INCOME CREDIT 0 (or equivalent balanced zero pair)
- `source_type=payment_request`, request status `R`
- Unique idempotency key so reject cannot double-post

This preserves the legacy “insert amount=0 DR row” **behavior** without mutating a cache. It satisfies SUM(debit)=SUM(credit).

Do not treat Design Gate CSV “no ledger posting on reject” as overriding A02-05.

---

## 7. Prohibitions

- UPDATE/DELETE posted journal lines or monetary header fields.
- Recalculate historical `amount` / `fx_rate` / `currency` using `euro_to_bdt_rates` or any new table.
- Post the same qualifying `payments` row as both a reconstructed journal **and** an `OPENING_BALANCE` line (**DR-H1**).
- Use `agents.balance` / `sub_agents.balance` as a journal source or posting target of truth.
- Post from backup tables (A01-04 / A01-05).
- Partial commit (journal without lines, or cache without journal).

---

## FINAL STATUS

**M7 IMPLEMENTATION GATE = COMPLETE**  
**DR-H1 = APPROVED (A)**  
**DR-FX1 = APPROVED (ADMINISTRATIVE RATE ENTRY)**  
**M7 IMPLEMENTATION GO = NOT ISSUED**
