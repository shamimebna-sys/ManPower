# M7 production dump profile — Finance tables

**Profiling date:** 2026-09-07  
**Dump filename:** `u410970153_eujobbd.sql`  
**Dump path (local, not in Git):** `docs/database-audit/u410970153_eujobbd.sql`  
**SHA-256 (verified on bytes):** `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`  
**SHA-256 match:** **PASS** (matches `docs/database-audit/production-dump.sha256`)  
**Method:** read-only INSERT-tuple parse. No MariaDB import. Dump file not modified. Quote-aware statement termination; comment-prefixed `INSERT` statements included.

This profile does **not** authorize M7 implementation, A01, A02, runtime finance grants, or M13 import.

Companion: `docs/M7-PRE-GATE.md`, `docs/M7-FINANCE-RISK-REGISTER.md`.  
Authoritative Design Gate: `modernization-design/final-design-gate/06-finance.md`.

Row counts below match `table-mapping.csv` (Design Gate Revision 2) and M6’s `payment_requests` adjacency count (702).

No person names, bank account numbers, passport numbers, or receipt payee strings are reproduced here.

---

## 1. Exact source tables and row counts

| Table | INSERT statements | Rows | PK unique | PK min–max | PK duplicate rows | Design Gate disposition |
|---|---:|---:|---:|---|---:|---|
| `payments` | 10 | **1,542** | 1,542 | 2–1,601 | 0 | KEEP → `finance` (A01 live-table decision **OPEN**) |
| `payment_requests` | 2 | **702** | 702 | 3–704 | 0 | KEEP → `finance` |
| `sub_agent_payment_requests` | 1 | **1** | 1 | 1–1 | 0 | MERGE → `finance.payment_requests` |
| `payments_backup_01072026` | 8 | **1,275** | 1,275 | 1–1,316 | 0 | ARCHIVE — never union without A01 |
| `payments_backup_18062026` | 7 | **1,227** | 1,227 | 7–1,233 | 0 | ARCHIVE |
| `payments_backup_20260625_0214pm` | 7 | **1,233** | 1,233 | 7–1,267 | 0 | ARCHIVE |
| `payments_backup_210620261147am` | 1 | **58** | 58 | 13–1,257 | 0 | ARCHIVE |
| `payments_bk` | 7 | **1,208** | 1,208 | 7–1,214 | 0 | ARCHIVE |
| `payment_requests_backup_01072026` | 2 | **584** | 584 | 3–586 | 0 | ARCHIVE |
| `invoices` | 2 | **487** | 487 | 20–507 | 0 | KEEP → `finance` (M8 posting, not M7 writes) |
| `invoice_lines` | 13 | **7,792** | 7,792 | 385–10,080 | 0 | KEEP |
| `invoice_candidates` | 1 | **930** | 930 | 1–939 | 0 | KEEP |
| `invoice_money_receipts` | 2 | **321** | 321 | 14–334 | 0 | KEEP |
| `invoice_heads` | 1 | **16** | 16 | 1–16 | 0 | KEEP (VAT catalog) |
| `ticket_invoices` | 2 | **111** | 111 | 7–142 | 0 | KEEP (M8) |
| `ticket_invoice_lines` | 3 | **1,130** | 1,130 | 11–1,390 | 0 | KEEP |
| `ticket_invoice_money_receipts` | 1 | **45** | 45 | 1–45 | 0 | KEEP |
| `ticket_companies` | 1 | **1** | 1 | 1–1 | 0 | KEEP → `operations` (issuer master, not a ledger) |
| `currencies` | 1 | **2** | 2 | 1–2 | 0 | KEEP → `operations.currencies` (not the wallet ledger) |
| `euro_to_bdt_rates` | 1 | **1** | 1 | 1–1 | 0 | KEEP → `finance.euro_to_bdt_rates` (unused by payment approval code) |

Parent / balance-holder tables (not finance subsystems by themselves):

| Table | Rows | `balance` column | Non-zero balances | Notes |
|---|---:|---|---:|---|
| `agents` | **40** | `float DEFAULT 0` | 37 | System A cached wallet |
| `sub_agents` | **12** | `float DEFAULT 0` | 4 | System A cached wallet |
| `teachers` | **2** | `float DEFAULT 0` | 0 | FIN-BUG-05 path; no live teacher payments |
| `candidates` | **1,679** | `float DEFAULT 0` | not aggregated this pass | Also `admission_payment_id`, `final_group_payment_id`, `medical_fee_payment_id` |
| `agenciers` | **13** | `float DEFAULT 0` | **0** | Column exists; no increment/decrement in finance controllers |
| `companiers` | **390** | `float DEFAULT 0` | **0** | Displayed on company-report as `€`; all stored zeros |
| `employees` | 3 | **no** | — | `payments.employee_id` unused (0 non-zero) |
| `employers` | 1 | **no** | — | `payments.employer_id` unused (0 non-zero) |

`payments.id` starts at **2** (id 1 absent), same pattern as M6 `licenses.id`.

---

## 2. DDL money types (legacy)

These types **violate** the approved target (`NUMERIC(20,6)` money, `NUMERIC(20,8)` rates) if copied as-is.

| Column | Legacy type | Target rule |
|---|---|---|
| `payments.amount` | `float(12,0)` | integer-scale FLOAT; cannot store sub-unit cents on this table |
| `payments.exchange_rate` | `float DEFAULT 1` | binary float; default 1 |
| `payment_requests.amount` | `float DEFAULT 0` | binary float |
| `sub_agent_payment_requests.amount` | `float DEFAULT 0` | binary float |
| `agents.balance` / `sub_agents.balance` / `teachers.balance` / `candidates.balance` / `agenciers.balance` / `companiers.balance` | `float DEFAULT 0` | mutable cached FLOAT |
| `invoice_lines.unit_price`, `sub_total_amount`, `vat_rate`, `vat_amount`, `total_amount` | `float` | fractional values exist (receipts include `.2` amounts) |
| `invoice_money_receipts.amount` | `float DEFAULT 0` | fractional values exist |
| `ticket_invoices.subtotal`, `tax_rate`, `tax_amount`, `total` | `float DEFAULT 0` | |
| `ticket_invoice_lines.unit_price`, `total_amount` | `float DEFAULT 0` | |
| `euro_to_bdt_rates.euro_rate` | `float DEFAULT 1` | observed `117.346` |

No declared MariaDB FKs on these finance edges (Design Gate: 9 declared FKs on the whole database; none of these).

---

## 3. Currencies actually present

**Only two `currencies` rows.** Do not invent additional currencies.

| `id` | `full_name` | `short_name` | `symbol` | `position` | ISO-4217? |
|---:|---|---|---|---|---|
| 1 | TAKA | Tk | ৳ | A (after amount) | **No** — ISO code is `BDT` |
| 2 | Euro | Euro | € | B (before amount) | **No** — ISO code is `EUR` |

`ticket_invoices.currency` is **varchar**, not an FK:

| Value | Rows |
|---|---:|
| `EURO` | 101 |
| `TAKA` | 10 |

That is FIN-BUG-09. It is **not** the same encoding as `currencies.id` (numeric 1/2) and **not** ISO.

`euro_to_bdt_rates`: one row, `conversion_date=2025-05-12`, `euro_rate=117.346`. No controller writes this table during wallet approval. Live approved `payments.exchange_rate` values cluster at **1** (1,184 of 1,484 status `A`) and **140–148** (BDT-per-EUR style), **not** 117.346.

**Conflict (unresolved, A02):** unused `euro_to_bdt_rates.euro_rate=117.346` vs wallet approval rates ~145 vs identity rate `1` on most Euro (`currency_id=2`) rows.

---

## 4. System A — `payments` / `payment_requests`

### 4.1 `payments`

| Field | Distribution |
|---|---|
| `status` | `A` 1,484 / `R` 54 / `P` 4 |
| `type` | `CR` 856 / `DR` 686 |
| `currency_id` | `2` (Euro) 1,205 / `1` (TAKA) 337 |
| `payment_method` | `SELF` 630, `CASH` 568, `ONLINE` 273, NULL 49, `SELF-SYSTEM` 18, `CHEQUE` 4 |
| Approved `exchange_rate` (top) | `1` 1,184; `145` 105; `144` 55; `146.5` 31; `147` 28; `146` 24; `148` 12; NULL 12; `0` 4 |
| Status `A` × currency | currency 1: 326; currency 2: 1,158 |
| `teacher_id` ≠ 0 | **0** |
| `employee_id` ≠ 0 | **0** |
| `employer_id` ≠ 0 | **0** |
| DR rows with `amount=0` | **0** (FIN-BUG-06 is confirmed in **code**; not present as rows today) |

Title prefixes (counts only; no identity strings):

- `New Payment request` (wallet CR, WF-16): **848**
- Remaining DR titles are generated as `{bill_title} for candidate …`

`payments.request_id` exists in DDL (INFERRED link to `payment_requests`). `payment_requests.payment_id` is the link used on approval.

### 4.2 `payment_requests`

DDL columns: `id`, `bill_title`, `agent_id`, `group_id`, `candidate_id`, `amount`, `status`, `payment_id`, `remarks`, timestamps.

**No `currency_id` column. No `bill_code` column.** Controller `merge(['bill_code' => 101])` and notification `currency_id` have nowhere to persist. All 702 parsed rows have those keys absent.

| Field | Distribution |
|---|---|
| `status` | `A` **626** / `P` **66** / `R` **10** |
| `bill_title` | `Manpower Fee` **607**; `Final Group Approval` **76**; `Panelty Fee` **12**; `Medical Fee` **4**; `Admission Group Approval` **3** |
| Manpower in title | 607; approved among those: **539** (matches M6 adjacency) |
| `candidate_id` missing from living `candidates` | **18** (same as M6) |
| `agent_id` orphan | 0 |
| Approved rows with `payment_id` resolving to `payments.id` | **626 / 626** (R2 = 0) |
| Distinct approved `(candidate_id, bill_title)` pairs with count > 1 | **12** |

**`Panelty Fee` (legacy spelling) is a sixth bill title.** Design Gate’s five-title list did not include it. Do not drop it. Do not invent a code until A16.

### 4.3 `sub_agent_payment_requests`

1 row: `status=A`, `bill_title=Admission Group Approval`, `amount=100`, `payment_id` set. Separate table, same approval controller branch (`role_id==109`).

---

## 5. Candidate payment FK fields (FIN-BUG-01 evidence)

| Candidate column | Non-null/non-zero | Points at missing `payments.id` |
|---|---:|---:|
| `admission_payment_id` | **318** | 18 |
| `final_group_payment_id` | **0** | 0 |
| `medical_fee_payment_id` | **0** | 0 |

Only **3** `payment_requests` have `bill_title='Admission Group Approval'`, but **318** candidates carry `admission_payment_id`. `final_group` and `medical` FKs are unused in data. This matches the assignment-not-comparison bug (first branch always writes `admission_payment_id`).

There is **no** candidate FK for Manpower Fee (detected only by querying `payment_requests`).

`candidates.balance` exists (`float`). No `increment('balance')` on `Candidate` was found in finance controllers. Treat as unused cached field until proven otherwise. Do not migrate as a ledger balance.

---

## 6. System B — employer/agency invoices

`invoices` has **no amount/total/currency column**. Money lives on `invoice_lines` and `invoice_money_receipts`. Header is an operational document (parties, bank snapshot, `service`, `status`).

| `invoices.status` | Rows |
|---|---:|
| `A` | 474 |
| `I` | 13 |

`invoice_candidates`: 930 rows; 33 `invoice_id` values not in living `invoices`; 24 `candidate_id` values not in living `candidates`. Do not silently drop.

`invoice_heads` catalog (16), VAT:

- Heads 1–14: `vat_rate=0`
- Head 15 `Other Services Fee (VAT 19%)`: `19`
- Head 16 `Office Administration Fee/Our Services Fee (VAT 19%)`: `19`

Line vs header total: **not applicable** on `invoices` (no header total). Extra check of `ticket_invoices.total` vs sum(`ticket_invoice_lines.total_amount`): **0** mismatches > 0.01 (R4 pass).

---

## 7. System C — ticket invoices

| Field | Distribution |
|---|---|
| `ticket_company_id` | **1** on all 111 rows (hardcoded in `VoyagerTicketInvoiceController::store`) |
| `currency` | `EURO` 101 / `TAKA` 10 |
| `bill_to` | `AGENCIER` 83 / `OTHER` 17 / `COMPANIER` 11 |
| `status` | `A` 106 / `I` 5 |

Code `store()` also handles `bill_to=='AGENT'`. Dump has **`OTHER`**, not `AGENT`. Unresolved: what `OTHER` means vs the three code branches.

`ticket_invoice_money_receipts`: 45 rows. Same shape as invoice receipts plus `remarks`.

---

## 8. Reconciliation queries run against this dump

Exact SQL is in Design Gate `06-finance.md` (R1–R6). Results on this SHA (read-only parse; R1 uses Decimal text amounts):

| ID | Invariant | Result on this dump |
|---|---|---|
| **R1** | Cached `agents.balance` vs sum of approved payments (FX rule: skip divide when `currency_id=2`) | **FAIL — 30 of 40 agents** drift > 0.01 |
| **R2** | Approved `payment_requests` must have a living `payment_id` | **PASS — 0 rows** |
| **R3** | No duplicate `(invoice_id, receipt_no, amount)` in `invoice_money_receipts` | **FAIL — 14 groups, 19 extra rows** |
| **R4** | `ticket_invoices.total` = sum of line `total_amount` | **PASS — 0 rows** |
| **R5** | No approved `currency_id=1` with `exchange_rate=1` | **FAIL — 38 rows** |
| **R6** | Live `payments` vs `payments_backup_01072026` key overlap `(agent_id, amount, DATE(payment_date), type)` | **FAIL — 1,268 overlapping backup rows** of 1,275 |

R1 sample magnitudes (no identity): cached vs computed deltas include ~1 (float noise) and thousands to >1,000,000 (formula / opening-balance / backup conflict). **Do not treat cached `agents.balance` as authoritative.** This is an A01+A02 input, not a migration write.

---

## 9. What does **not** participate in the same money movement

Do not fold these into System A because of a name match:

| Object | Why separate |
|---|---|
| `currencies` | Reference table; 2 rows; Design Gate target `operations.currencies` |
| `euro_to_bdt_rates` | 1 row; no payment-approval read in Laravel |
| `ticket_companies` | Issuer snapshot source; 1 row; hardcoded id=1 |
| `invoice_heads` | Line catalog / VAT rates; not a journal |
| `agenciers.balance` / `companiers.balance` | All zeros; no wallet increment in invoice controllers |
| `candidates.balance` | Column present; no finance-controller mutation found |
| Voyager leftover print blades (`sales`, `purchase`, `stock`, `customer-ledger`, `supplier-ledger`) | **No corresponding tables** in `table-mapping.csv` |

---

## 10. Backup tables are not live history

Five payment copies plus one payment-request backup. Live `payments` is 1,542 rows; largest backup is 1,275; `payments_bk` is 1,208; one backup is only 58 rows. **Chronology is not authority.** R6 shows almost complete key overlap between live and `payments_backup_01072026`. Union without A01 would double-count.

Until A01: ARCHIVE / staging only. No opening-balance seed. No production migration.

---

## FINAL STATUS (this file)

**M6: CLOSED / VERIFIED**  
**M7 PRE-GATE: COMPLETE**  
**A01: APPROVED WITH CONDITIONS**  
**A02: CLOSED**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**
