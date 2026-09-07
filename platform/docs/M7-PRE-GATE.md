# M7 PRE-GATE — Finance / Manpower Fee & Payment Processing

**Investigation date:** 2026-09-07  
**Prerequisites:** M6 CLOSED / VERIFIED. PG16 CI gate passed (run [34114736036](https://github.com/shamimebna-sys/ManPower/actions/runs/34114736036), SHA `8aacb885e873b2ef06dfa07d72a39a3c6fa5b7e7`).  
**This document:** evidence and open questions only. **No schema, migration, API, UI, permission, CI, Prisma, runtime grant, production-data, or Laravel change.**

M7 remains **Finance / Manpower Fee & Payment Processing**. Locked M1–M6 decisions are not reopened. A01 and A02 are **separate** hard gates. They are **not** approved by this document.

Authoritative pack: `modernization-design/final-design-gate/` especially `00-project-rules.md` (Rule 4, Rule 5), `06-finance.md`, `finance-operations.csv`, `11-milestones-risks-approvals.md`, `workflow-transitions.csv` (WF-09, WF-16–WF-21), `09-postgresql-prisma.md`.

Companion dump profile: `docs/M7-PRODUCTION-PROFILE.md`.  
Companion defects/security: `docs/M7-FINANCE-RISK-REGISTER.md`.  
Owner A01/A02 decision package (unsigned): `docs/M7-A01-A02-DECISION-PACKAGE.md`.  
Owner tick-sheet: `docs/M7-A01-A02-OWNER-CHECKLIST.md`.  
Partial owner recording: `docs/M7-A01-A02-OWNER-DECISIONS.md`.

---

## Executive status

| Item | Status |
|---|---|
| M6 overseas processing | **CLOSED / VERIFIED** |
| M7 PRE-GATE | **COMPLETE** (analysis only; A01/A02 still OPEN) |
| A01 (authoritative payment table / backups / dedup) | **NOT YET APPROVED** — HARD GATE |
| A02 (currency / FX / rounding / opening balance) | **NOT YET APPROVED** — HARD GATE |
| A16 (`bill_title_code` / FIN-BUG-01 / FIN-BUG-02) | **OPEN** — required for correct fee linkage; not a substitute for A01/A02 |
| A17 (teacher payments / FIN-BUG-05) | **OPEN** |
| A18 (zero-DR on reject / FIN-BUG-06) | **OPEN** |
| A19 (ticket invoice delete-then-store / FIN-BUG-07) | **OPEN** — Design Gate blocks **M8**, not M7 writes |
| M7 implementation | **NOT AUTHORIZED** |
| M7 IMPLEMENTATION GO | **NOT ISSUED** |
| Runtime finance grants | **NOT APPROVED** |
| Production import / balance seed (M13) | **NOT AUTHORIZED** |
| M8 Invoice/Receipt/Ticket implementation | **OUT OF SCOPE** (profiled only) |
| Payment gateway integration | **OUT OF SCOPE** |

**Exact reason M7 implementation remains blocked:** Rule 4 forbids any authoritative finance write until **A01 and A02** are explicitly granted with named approver, date, and written decision. This PRE-GATE does not grant them. Cached balances fail R1 (30/40 agents). Backup overlap fails R6 (1,268 rows). FX source and rounding are contradictory in legacy code vs dump. Starting payment/ledger code now would either reproduce confirmed defects (Rule 5) or invent a money model the owner has not signed.

---

## Locked constraints carried forward

- Rule 4: no finance writes until A01 **and** A02.
- Rule 5: do not silently reproduce or silently “fix” FIN-BUG-01…09.
- Rule 7: no numeric legacy role IDs at runtime. A07 closed set: `super_admin`, `administrator`, `owner`, `agent`, `sub_agent`, `candidate`, `employer`, `company`, `agency`, `employee`, `teacher`.
- No `*.delete` permission keys.
- Teacher: **NO** `candidate.read`.
- Live-status step 9 (“Manpower Status”) is a **payment_request** query, not `manpower_trainings` (M5 lock).
- Opening balance is staged only after approval. Do not migrate or seed balances.
- Preserve `bill_title_code` as the approved replacement for free-text `bill_title`. Do not hardcode unexplained numeric IDs (`bill_code=101` is unexplained and **does not exist** as a dump column).
- Money: `NUMERIC(20,6)`. Rates: `NUMERIC(20,8)`. Currency: ISO codes. Do not create currencies the dump does not support (only TAKA/Euro evidence).
- Target ledger principle (approved, **not implemented**): **append-only / double-entry**.
- Systems A, B, and C share **no** financial flows in legacy code. Do not integrate them without a new approval.

---

## 1. Production table profile

See `docs/M7-PRODUCTION-PROFILE.md` for DDL, distributions, and SHA.

Dump SHA `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8` **PASS**.

Three isolated finance subsystems (Design Gate `06-finance.md`, reconfirmed):

| ID | Name | Live tables | Cached balances | Legacy posting? |
|---|---|---|---|---|
| **A** | Agent / sub-agent wallet + candidate fees | `payments`, `payment_requests`, `sub_agent_payment_requests` | `agents.balance`, `sub_agents.balance` | Yes — increment/decrement + payment rows |
| **B** | Employer / agency billing | `invoices`, `invoice_lines`, `invoice_heads`, `invoice_candidates`, `invoice_money_receipts` | `agenciers.balance` / `companiers.balance` exist but **all zero**; not mutated by invoice controllers | Documents + receipts; **no** double-entry |
| **C** | Ticket billing | `ticket_invoices`, `ticket_invoice_lines`, `ticket_invoice_money_receipts`, `ticket_companies` | none | Documents + receipts; issuer hardcoded `ticket_company_id=1` |

Archive copies (not live): five `payments_backup_*` / `payments_bk` plus `payment_requests_backup_01072026`.

---

## 2. Exact row counts (this dump)

| Table | Rows |
|---|---:|
| `payments` | 1,542 |
| `payment_requests` | 702 |
| `sub_agent_payment_requests` | 1 |
| `payments_backup_01072026` | 1,275 |
| `payments_backup_18062026` | 1,227 |
| `payments_backup_20260625_0214pm` | 1,233 |
| `payments_backup_210620261147am` | 58 |
| `payments_bk` | 1,208 |
| `payment_requests_backup_01072026` | 584 |
| `invoices` | 487 |
| `invoice_lines` | 7,792 |
| `invoice_candidates` | 930 |
| `invoice_money_receipts` | 321 |
| `invoice_heads` | 16 |
| `ticket_invoices` | 111 |
| `ticket_invoice_lines` | 1,130 |
| `ticket_invoice_money_receipts` | 45 |
| `ticket_companies` | 1 |
| `currencies` | 2 |
| `euro_to_bdt_rates` | 1 |
| `agents` | 40 |
| `sub_agents` | 12 |
| `teachers` | 2 |
| `candidates` | 1,679 |
| `agenciers` | 13 |
| `companiers` | 390 |

---

## 3. Finance relationships (logical; none declared as MariaDB FK)

```
agents.balance  <--+-- payments.agent_id (CR credit / DR debit)
sub_agents.balance <-+-- payments.sub_agent_id
payment_requests.payment_id --> payments.id
payment_requests.candidate_id --> candidates.id
payment_requests.agent_id --> agents.id
payment_requests.group_id --> class_groups.id
sub_agent_payment_requests.payment_id --> payments.id
candidates.admission_payment_id --> payments.id   (BUGGY linkage)
candidates.final_group_payment_id --> payments.id (unused in dump)
candidates.medical_fee_payment_id --> payments.id (unused in dump)
payments.currency_id --> currencies.id
invoices.agenciers_id --> agenciers.id
invoices.companier_id --> companiers.id
invoice_lines.invoice_id --> invoices.id
invoice_lines.invoice_head_id --> invoice_heads.id
invoice_candidates (invoice_id, candidate_id)
invoice_money_receipts.invoice_id --> invoices.id
ticket_invoices.ticket_company_id --> ticket_companies.id  (always 1)
ticket_invoice_lines.ticket_invoice_id --> ticket_invoices.id
ticket_invoice_money_receipts.ticket_invoice_id --> ticket_invoices.id
```

**No code path** posts System A `payments` when an invoice or ticket receipt is issued. Receipts do not debit `agents.balance`. Invoices do not credit `agenciers.balance`.

---

## 4. A01 status — NOT YET APPROVED

A01 is the **authoritative live payment table**, backup inclusion/exclusion, and deduplication authority. It is **not** A02.

### Exact legacy behavior

- Live writes go to `payments` (Voyager BREAD `VoyagerPaymentController::store`, My Panel `walletStore`, Ajax `paymentApproval` / `candidatePaymentApproval`).
- Backup tables have **no** model, **no** controller, **no** report catalog reference. They are dated copies (`01072026`, `18062026`, `20260625_0214pm`, `210620261147am`) plus `payments_bk`.
- `payment_requests` is the candidate-fee **workflow** table; approval inserts a `payments` DR and stores `payment_id`.
- Agent/sub-agent **cached** `balance` is mutated in place. Display SQL on `Agent::payments()` **also** computes a running window from `payments` (different formula than cache writes). Two “balances” already exist in one screen.

### Source tables / fields / relationships

Must be decided by A01:

- Authoritative live: `payments` (1,542) — **default Design Gate stance**, not signed.
- Whether any backup row is an opening balance **not** present in live.
- Dedup key: Design Gate R6 uses `(agent_id, amount, DATE(payment_date), type)` — 1,268 of 1,275 `payments_backup_01072026` rows match live on that key.

### Monetary calculations / ledger / invoices / receipts

A01 does **not** choose FX (that is A02). A01 **does** choose which **rows** may ever become journal lines. Invoices/receipts/ticket invoices are **not** in `payments`. Including them in “opening wallet balance” would invent a flow that legacy code does not perform.

### Rollback / duplicate / transactions / concurrency / audit

- No reversal after approval (WF-17/WF-20 CONFIRMED).
- Duplicate approved `(candidate_id, bill_title)` pairs: **12**.
- Ajax approval has **no** `DB::transaction` and **no** `authorize()`.
- Audit: `payments.user_id`, `created_at`/`updated_at`, remarks. Mutable BREAD edit/delete exists for `sadmin` (`delete_payments`). Not append-only.

### Reconciliation requirement before A01 sign-off

R1 (fails), R2 (passes), R6 (fails). Owner must say whether live cache, live `payments` recomputation, or a backup is the cutover source. **This PRE-GATE does not pick one.**

### A01 decision still required (do not implement)

1. Live table = `payments` only, or live + named backup rows.
2. Dedup algorithm and which side wins on R6 overlap.
3. Opening-balance row identification (bill_title / remarks / backup-only) — currently **UNVERIFIED**.
4. Whether Voyager-mutated `payments` history (edit/delete) makes live rows non-authoritative.

---

## 5. A02 status — NOT YET APPROVED

A02 is **currency minor units, FX source, rounding, opening-balance calculation method, historical FX formula**. It is **not** A01.

### Exact legacy behavior (conflicting formulas — do not silently reconcile)

| Path | Formula | Currency handling |
|---|---|---|
| Wallet deposit create (`walletStore`) | Insert `payments` CR `status=P`; **no** balance change | Request `currency_id`; no rate yet |
| Wallet approve (`paymentApproval`) | `balance += amount / exchange_rate` **always** (POST `exchange_rate` as PHP `(float)`) | Rate entered at approval; **not** read from `euro_to_bdt_rates` |
| Admin `VoyagerPaymentController::store` | `balance ± amount` **with no FX** | Stores `currency_id` + `exchange_rate` on the row but does not use them for the cache |
| Fee approve (`candidatePaymentApproval`) | `balance -= amount` (0 on reject); DR row `currency_id` **hardcoded 2** | Request has **no** currency column in dump |
| `Agent::payments()` display window | `amount / IF(currency_id=2, 1, NULLIF(exchange_rate,0))` for status `A` | Treats **Euro id=2 as already-base** |
| Agent-ledger print blade | `balance += amount/exchange_rate` for CR (no currency_id=2 special case) | Third formula |
| `currencySymbol` | Lookup `currencies`; default fallback **BDT** / ৳ | id 1 = TAKA, id 2 = Euro |
| `euro_to_bdt_rates` | Unused in these paths | Single FLOAT 117.346 |

FIN-BUG-03 is this split. R1 uses the `Agent::payments()` rule and **fails** for 30/40 agents — so even the “display” formula does not match `agents.balance`.

### Currencies actually present

- Dump: TAKA (`currencies.id=1`) and Euro (`id=2`) only.
- Ticket varchar: `EURO` / `TAKA`.
- Target: ISO `BDT` / `EUR`. Mapping requires A02. **Do not add USD or others.**

### Opening balance

- `VoyagerAgentController::store` writes `agents.balance` from the create form **without** a `payments` row.
- Same for `VoyagerSubAgentController::store`.
- Wallet approval credits the same column.
- Design Gate: opening balance staged as `OPENING_BALANCE_CANDIDATE` until A01; **excluded from computation** until approved. **Do not seed.**

### Rounding / minor units

- `payments.amount` is `float(12,0)` — integer scale.
- Invoice/receipt amounts are `float` with observed tenths (e.g. `.2`).
- Display uses `ROUND(..., 2)` in the window SQL and `number_format(..., 2)` in some blades; `currencySymbol` **does not** `number_format` (dead code after live `return`).
- Target: `ROUND_HALF_UP` only at an approved posting boundary (`finance-operations.csv`). **Not signed as A02.**

### A02 decision still required (do not implement)

1. Base currency for wallet (`EUR` vs `BDT` vs dual-currency accounts).
2. Whether `currency_id=2` means “already base” (display SQL) or “still divide by rate” (approval).
3. Historical FX: store per-row `exchange_rate` as-is vs revalue using `euro_to_bdt_rates` vs ignore the unused table.
4. Rounding mode and scale for integer `float(12,0)` vs fractional invoices.
5. How to compute opening balances once A01 names the rows (cached column vs recomputed ledger).

---

## 6–8. Nine bugs, six queries, three subsystems

Recovered in `docs/M7-FINANCE-RISK-REGISTER.md`. IDs FIN-BUG-01…09, R1–R6, Systems A/B/C. None are fixed here.

Additional evidenced defects **not** in the original nine (do not silently add them to the nine):

| Extra | Evidence | Decision |
|---|---|---|
| `Panelty Fee` bill title (12 requests) | Dump `bill_title` | A16 must include or explicitly retire |
| `VoyagerPaymentController` teacher **and** candidate lookups use `$agent_id` | `store():102-105` | Related to FIN-BUG-05 |
| Invoice header has no total/currency | DDL | M8 document model |
| `bill_to=OTHER` on tickets (17) vs code AGENCIER/COMPANIER/AGENT | Dump vs `VoyagerTicketInvoiceController` | Unresolved |
| Ajax finance routes have no `authorize()` | `admin.php` + `VoyagerAjaxController` | Security; not a silent fix |

---

## 9. Currency / rate findings

Approved target remains: money `NUMERIC(20,6)`, rates `NUMERIC(20,8)`, ISO codes.

Legacy dependence / violation:

- Binary FLOAT everywhere.
- `payments.amount` integer-scale `float(12,0)`.
- Non-ISO names `TAKA` / `Euro` / varchar `EURO`/`TAKA`.
- `currency_id` default **1** (TAKA) on `payments`, while fee DR forces **2**.
- Live rates ~145 vs table 117.346 vs identity 1.
- R5: 38 approved TAKA rows with `exchange_rate=1` (1:1 TAKA=base — almost certainly wrong if base is EUR).

---

## 10. Balance mutation findings

| Mutation | Has immutable financial record? |
|---|---|
| Agent/sub-agent **create** form `balance` | **No** corresponding `payments` row required |
| Wallet approve credit `amount/exchange_rate` | `payments` row already exists (status P→A); cache updated separately |
| Fee approve debit `amount` | Inserts DR `payments` **after** decrement; not one atomic journal |
| Fee reject `amount=0` DR (code) | Noise row; cache not decremented (`amountNew=0`) |
| Admin CR/DR `± amount` (no FX) | Inserts `payments` in a transaction (this path only) |
| Agent BREAD **update** | `insertUpdateData` on edit rows; if `balance` is an edit field, cache changes **without** a new payment (Voyager form includes `balance`) |
| Invoice / receipt / ticket issue | **Does not** mutate wallet or agencier/companier cache |
| `agenciers.balance` / `companiers.balance` | All zeros; company-report still prints `€ {balance}` |
| `teachers.balance` | Unused in dump; FIN-BUG-05 would miss the teacher row |
| `candidates.balance` | No finance-controller write found |

**Any target recommendation that treats `agents.balance` as source of truth is incompatible with R1 and with create-form opening balances.** Append-only journals must be the source; cache, if any, is a projection.

---

## 11. Payment-request findings

**Mixed legacy subsystem — evidence, not invention:**

| Aspect | Evidence | Classification |
|---|---|---|
| Create | Voyager BREAD `VoyagerPaymentRequestController::store`; copies `agent_id`/`group_id` from candidate; status default `P`; **no** balance change | **Approval workflow** (request only) |
| Grants | `add_payment_requests` granted to **sadmin + owner** only in `rbac-role-grants.csv` | Conflicts with WF-19 “Agent or Employee creates” |
| Agent UI | `GET /panel/my/payment-request` **lists** requests; does not insert | Workflow inbox |
| Approve/reject | Ajax `candidatePaymentApproval`; **posts a DR `payments` row** and mutates wallet | **Financial posting** |
| Duplicate | No uniqueness on `(candidate_id, bill_title, status)`; 12 duplicate approved pairs | Mixed defect |
| Currency | No column; DR forced to `currency_id=2` | Posting invented at approve time |
| Documents | None on `payment_requests`; wallet CR uses `cheque_file_path` on `payments` | Split |
| Audit | remarks + timestamps; status overwrite P→A/R; no append-only event | Weak |
| Manpower live-status | `CommonClass::manpowerStatus()` reads **approved payment_requests**, not `payments` | Workflow flag, tautological (FIN-BUG-02) |

**Conclusion:** `payment_request` is **both** an approval workflow **and**, on the approve path, a financial posting trigger. It is not a ledger. The ledger-like object is `payments`, which is itself mutable.

---

## 12. Invoice / receipt findings

| Object | Role in legacy | Target reading |
|---|---|---|
| `invoices` | Operational document (parties, bank copy, `service`, status A/I). **No money columns.** | Document, not a journal |
| `invoice_lines` | Monetary lines + VAT from `invoice_heads` | Document lines; should snapshot into issued version (M8) |
| `invoice_candidates` | M2M association | Operational association |
| `invoice_money_receipts` | Receipt document; print by `invoice_id` (controller loads **first** receipt for that invoice, not by receipt id) | Mixed document; **not** a wallet posting |
| `ticket_invoices` | Operational + stored `subtotal`/`tax`/`total` + varchar currency | Document + header totals |
| `ticket_invoice_lines` | Passenger/candidate lines | Document lines |
| `ticket_invoice_money_receipts` | Ticket receipts | Document |

These are **mixed operational/financial documents**, not accounting journals. They must not be posted into System A wallets without a new approval. M8 owns versioned invoices; M7 must not implement them.

---

## 13. Concurrency findings

See risk register. Target **must** use transactional financial posting with idempotency keys. **Not implemented here.**

Highest-severity legacy races:

1. `candidatePaymentApproval`: decrement **before** `status=='P'` check; no transaction; no row lock.
2. `paymentApproval`: status updated **before** credit; failure after update leaves approved payment without credit (or the reverse on retry → double credit). `empty($agent->balance)` treats `0` as empty and **assigns** instead of increment (lost prior zero vs null).
3. Ticket `update()`: `store()` then `delete()` old id — ID destruction (FIN-BUG-07) plus duplicate insert window.
4. Invoice `update`: `delete` all lines then insert — lost lines on partial failure.

---

## 14. RBAC findings (conceptual only — **no grants approved**)

Legacy Voyager (numeric IDs are compatibility evidence only):

| Legacy permission | Roles in `rbac-role-grants.csv` | Conceptual A07 map |
|---|---|---|
| `browse_payments` | sadmin, Agent, owner, Sub Agent | `agent` / `sub_agent` scoped read; `owner` / `super_admin` global |
| `add_payments` | sadmin, owner | staff posting; **not** agent (agents use My Panel wallet, not this BREAD add) |
| `delete_payments` | sadmin | **Forbidden** in target (`*.delete`) |
| `browse/add_payment_requests` | sadmin, owner | Conflicts with WF-19 Agent/Employee create |
| `browse/add_invoices` + money receipts | sadmin, owner, Agency (108) | `agency` document access; `company` **not** granted |
| `browse_ticket_invoices` | sadmin, owner | staff only |

Ajax `/panel/ajax/payment-approval/{id}` and `/candidate-payment-approval/{id}`: **no `authorize()`**. Global scopes on `Payment` (`role_id` 101/109 vs everyone else `sub_agent_id=0`) are **not** a substitute.

`Invoice` global scope: if `role_id != 1`, filter `agenciers_id = profile_id`. Company users (`role_id=107`) would be compared as agenciers. Agency can CRUD invoices.

**Do not approve runtime finance grants.** A01/A02 must precede M7 grant approval. Platform `FOUNDATION_PERMISSIONS` currently has **zero** `finance.*` keys (correct).

---

## 15. Security findings

Full list: `docs/M7-FINANCE-RISK-REGISTER.md`. Do not implement fixes.

Headline: unauthenticated-to-policy Ajax approval, IDOR on print-by-id, amount/currency trusted from client or hardcoded, duplicate posting, report exposure (agent-report metrics hardcoded 0 — FIN-BUG-08).

---

## 16. Proposed target finance architecture (**proposal only**)

Keep Systems A/B/C **separate** until an owner consolidation approval exists. Share primitives, not balances.

### 16.1 Schema (PostgreSQL `finance`, not implemented)

- `finance.currencies` — ISO-4217 `BDT`, `EUR` only unless A02 adds more. Map legacy ids 1/2 and varchar `TAKA`/`EURO`.
- `finance.fx_rates` — `NUMERIC(20,8)`, dated, source documented. **Do not auto-load** `euro_to_bdt_rates` as authority until A02.
- `finance.accounts` — wallet accounts per agent/sub-agent (and later invoice control accounts). No mutable `balance` column as source of truth.
- `finance.journals` — header: `status` (`DRAFT`/`POSTED`/`VOID` via reversal), `idempotency_key` unique, `posted_at`, `reversal_of`, `legacy_key_map`.
- `finance.journal_lines` — `journal_id`, `line_no`, `account_id`, `currency`, `debit NUMERIC(20,6)`, `credit NUMERIC(20,6)`. Unique `(journal_id, line_no)`. CHECK balanced per currency.
- `finance.payment_requests` — workflow aggregate: `bill_title_code`, amount, currency, candidate, agent/sub-agent, status, documents, **no** direct balance write.
- `finance.issued_invoices` / lines / receipts — **M8**; staging only in M13 until A01/A02; posting into AR accounts only after M8 + A01/A02.
- `finance.ticket_*` — M8 sub-ledger, same posting primitives.
- Archive backups remain `migration`/`archive` copies with lineage; never union automatically.

Immutable events: every POSTED journal is append-only. Corrections = reversing journal, not UPDATE/DELETE.

### 16.2 Payment-request workflow (target)

1. Create request (idempotent per candidate + `bill_title_code` policy — **A16 decides uniqueness**).
2. Approve: **one** serializable transaction: lock account, insert balanced journal (wallet DR / fee income CR or equivalent), set request `POSTED` with `journal_id`, write `legacy_key_map`.
3. Reject: decision event only; **no** journal (A18: do not insert zero-amount noise).
4. Manpower live-status (WF-09): `bill_title_code = MANPOWER_FEE` AND posted — **A16**; do not keep `orWhereRaw('bill_title = bill_title')`.

### 16.3 Posting rules (blocked on A02)

- Store `amount`, `currency`, `fx_rate`, `base_amount` all as NUMERIC.
- Round only at posting boundary (`ROUND_HALF_UP` as currently proposed).
- Wallet credit and fee debit **must use the same FX rule** once A02 names it.

### 16.4 Idempotency and transactions

- Unique `(actor or client, idempotency_key)` on approve/deposit.
- `SELECT … FOR UPDATE` on the wallet account row (or equivalent advisory lock).
- Entire post in one DB transaction. Prisma cannot enforce balanced journals; use a SQL procedure/trigger as in `09-postgresql-prisma.md`.

### 16.5 Reconciliation (continuous)

R1–R6 plus extras in the risk register, as reports, **not** as silent writers.

### 16.6 What M7 should implement vs defer (when a future GO exists)

| In M7 (after A01+A02+GO) | Not M7 |
|---|---|
| Wallet deposit workflow + posting | M8 invoice/ticket versioning |
| Payment-request workflow + posting | M13 production import |
| Append-only ledger + accounts | Payment gateways |
| Currency/FX tables per A02 | Balance seed |
| Reconciliation jobs (read) | Runtime grants (separate approval) |

---

## 17. Unresolved decisions

| ID | Conflict / gap | Blocks |
|---|---|---|
| A01 | Live vs five backups; R6 overlap 1,268 | All M7 writes |
| A02 | Three+ FX formulas; unused 117.346 rate; ISO mapping; opening-balance method | All FX/balance logic |
| A16 | Five Design Gate titles vs six dump titles (`Panelty Fee`); FIN-BUG-01/02 | WF-09, fee FK linkage. **2026-09-07:** Panelty Fee preserved as distinct concept; **exact A16 definition still required** |
| A17 | Teacher payments unused (0 rows) but code exists and is broken | Teacher wallet |
| A18 | Zero-DR on reject in code; **0** such rows in dump | Reject posting |
| A19 | Ticket delete-then-recreate | M8 |
| WF-19 vs grants | Agent/Employee create vs Voyager add only sadmin/owner | Payment-request create policy |
| `bill_to=OTHER` | 17 ticket invoices | M8 |
| Invoice vs wallet | No shared flow; do not invent settlement | Architecture |
| `candidates.balance` | Column exists; no mutation found | Ignore vs include |
| R1 failure | 30/40 agents; which figure is “true” | Opening balance |
| `currency_id=2` + `exchange_rate=1` on Manpower DR | Amounts look like BDT scale tagged as Euro | A02 |

Do not silently pick a side.

---

## 18. Proposed M7 gates (none issued)

| Gate | Meaning | Depends on |
|---|---|---|
| **M7-PG** | This PRE-GATE (evidence only) | M6 closed |
| **A01** | Authoritative table + backup/dedup written decision | Owner + finance |
| **A02** | FX / ISO / rounding / opening-balance method | Owner + finance; **separate** from A01 |
| **A16** | `bill_title_code` enum including `Panelty Fee` disposition; FIN-BUG-01/02 correction | Can be drafted in parallel; **required before** correct WF-09 |
| **A17 / A18** | Teacher path; reject noise rows | M7 fee/wallet |
| **M7-R** | R1–R6 (and extras) signed with accepted exceptions | A01/A02 method applied on staging |
| **M7 IMPLEMENTATION GO** | Explicit issue document, like M6 | A01 **and** A02 **and** M7-R |
| **M7-G-RBAC** | Runtime `finance.*` keys + grants | After GO; still no `*.delete` |
| **M8 GO** | Invoices/tickets posting | Separate milestone |
| **M13** | Production load of finance | Dual A01/A02 + rehearsal packs |

Until **M7 IMPLEMENTATION GO** is explicitly issued, engineers must not add Prisma finance models, migrations, handlers, or grants.

---

## 19. Exact reason implementation remains blocked

1. **A01 is OPEN.** Choosing live `payments` vs backups would change every opening journal. R6 overlap makes a silent union a double-count.
2. **A02 is OPEN.** Credit uses `amount/rate`; debit does not; admin store uses neither; display SQL uses a third rule. Implementing any one of these is a business decision, not a coding preference.
3. **Rule 4** forbids authoritative finance records until both approvals exist.
4. **Rule 5** forbids shipping FIN-BUG-01/02/03/04 as “parity” without A16/A02.
5. **No M7 IMPLEMENTATION GO** has been issued.
6. Runtime finance grants are **not** approved; A01/A02 must precede that approval.
7. Production balance migration is **not** authorized (opening balance staged after approval only).

---

## FINAL STATUS

**M6: CLOSED / VERIFIED**  
**M7 PRE-GATE: COMPLETE**  
**A01: APPROVED WITH CONDITIONS**  
**A02: CLOSED**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**
