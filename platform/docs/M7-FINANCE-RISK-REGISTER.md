# M7 finance risk register

**Date:** 2026-09-07  
**Status:** documentation only. **No defect is fixed here.**  
**Sources:** Design Gate `06-finance.md` (original nine bugs, six queries, three subsystems); Laravel `src/` (read-only); dump SHA `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8` profiled in `docs/M7-PRODUCTION-PROFILE.md`.

A01 and A02 remain **separate** and **NOT YET APPROVED**.

---

## Three isolated finance subsystems (reconfirmed)

| ID | Subsystem | Source | Shared money flow with others? |
|---|---|---|---|
| **A** | Agent/sub-agent wallet + candidate fee requests | `payments` CR/DR, `payment_requests`, `sub_agent_payment_requests`, `agents.balance`, `sub_agents.balance` | **None found** |
| **B** | Employer/agency invoices | `invoices`, `invoice_lines`, `invoice_heads`, `invoice_candidates`, `invoice_money_receipts` | **None found** |
| **C** | Ticket invoices | `ticket_invoices`, `ticket_invoice_lines`, `ticket_invoice_money_receipts`, `ticket_companies` | **None found** |

Do not merge wallets with AR invoices without a new named approval.

---

## Nine known finance bugs (recovered)

Each row is a **target-system decision**, not a license to implement.

### FIN-BUG-01 — `bill_title` assignment, not comparison

| | |
|---|---|
| **Source** | `src/app/Http/Controllers/Admin/VoyagerAjaxController.php` `candidatePaymentApproval()` lines 263–271 |
| **Tables / fields** | `payment_requests.bill_title`; `candidates.admission_payment_id`, `final_group_payment_id`, `medical_fee_payment_id`; `payments.id` |
| **Legacy behavior** | After inserting a DR payment and updating the request, the code intends to attach the payment id to a candidate FK based on bill title. |
| **Defect** | `if ($paymentRequest->bill_title = 'Admission Group Approval')` **assigns** and is always truthy. `elseif` Final/Medical never run. In-memory `bill_title` is overwritten to Admission (request row already saved with original title). |
| **Dump impact** | `admission_payment_id` set on **318** candidates; `final_group_payment_id` **0**; `medical_fee_payment_id` **0**. Only **3** requests have title `Admission Group Approval`. Manpower/Final approvals polluted the admission FK. |
| **Financial impact** | Wrong candidate-stage linkage. Does not by itself change the debit amount, but corrupts “which fee was paid” for operations and any future report that trusts the FK. |
| **Target decision** | A16: map `bill_title_code` → linkage field (or stop storing FKs and use posted journals). Corrected comparison **must not** ship without approval (Rule 5). |
| **Depends on** | **A16** primarily; **A01** if historical `admission_payment_id` is treated as authoritative; not A02 |

### FIN-BUG-02 — tautological Manpower fee query

| | |
|---|---|
| **Source** | `src/app/Helpers/CommonClass.php` `manpowerStatus():520-527` and `mpVisaAndPaymentStatus():461-465` |
| **Tables / fields** | `payment_requests.bill_title`, `status`, `candidate_id` |
| **Legacy behavior** | Live-status step 9 / manpower badge should mean Manpower Fee collected. |
| **Defect** | `where('bill_title','like','%Manpower%')->orWhereRaw('bill_title = bill_title')` matches **every** row. Combined with `status='A'`, **any** approved request satisfies Manpower Status. |
| **Dump impact** | 626 approved requests; 539 have Manpower in the title. The tautology makes the other 87 approved non-Manpower requests also satisfy step 9. |
| **Financial impact** | Operational false-complete on manpower; does not move money by itself. Can hide unpaid Manpower Fee if another fee was approved. |
| **Target decision** | A16: `bill_title_code = MANPOWER_FEE` (or exact title) only. WF-09. |
| **Depends on** | **A16**; WF-09; not a substitute for A01/A02 |

### FIN-BUG-03 — two (actually more) FX formulas for the same cache

| | |
|---|---|
| **Source** | `VoyagerPaymentController::store():110-119` vs `VoyagerAjaxController::paymentApproval():117-122`; also `Agent.php` window SQL; agent-ledger blade |
| **Tables / fields** | `agents.balance` / `sub_agents.balance`; `payments.amount`, `currency_id`, `exchange_rate` |
| **Legacy behavior** | Admin CR/DR adjusts cache by **raw amount**. Wallet approval credits **amount/exchange_rate**. Display window skips FX when `currency_id=2`. |
| **Defect** | Same FLOAT cache, incompatible updates. R1 (display formula vs cache) **fails 30/40 agents**. |
| **Financial impact** | Material. Dump deltas range from ~1 (float noise) to >1e6. Wallet statements cannot be trusted. |
| **Target decision** | **A02** canonical formula. **A01** which rows enter the recomputation. |
| **Depends on** | **A02** (formula); **A01** (row set) |

### FIN-BUG-04 — fee DR `currency_id` hardcoded to 2

| | |
|---|---|
| **Source** | `VoyagerAjaxController::candidatePaymentApproval():225-226` |
| **Tables / fields** | `payments.currency_id`; `payment_requests.amount` (no currency column in dump) |
| **Legacy behavior** | Every candidate-fee DR is stored as Euro id=2, typically with `exchange_rate` default 1. |
| **Defect** | Ignores request currency (column absent anyway). Mixes TAKA-scale integers into the Euro-tagged stream. |
| **Dump impact** | 1,205 / 1,542 payments are `currency_id=2`. Fee titles sit in that population. |
| **Financial impact** | If wallet cache is “Euro” and manpower fee 6200 is tagged Euro, a 6200 EUR debit is booked; if operators meant 6200 BDT, the cache is wrong by ~100×. **Unresolved which was intended.** |
| **Target decision** | A02 + A16: fee currency per `bill_title_code`. |
| **Depends on** | **A02**; **A16** |

### FIN-BUG-05 — teacher payment looks up `Agent` with `$agent_id`

| | |
|---|---|
| **Source** | `VoyagerPaymentController::store():102-103` (`Teacher::where('id', $agent_id)`). Same pattern for candidate: `Candidate::where('id', $agent_id)` at 104–105. |
| **Tables / fields** | `teachers.balance`; `payments.teacher_id` |
| **Legacy behavior** | Intended to credit/debit teacher (or candidate) balance and insert `payments`. |
| **Defect** | Uses `$agent_id` after that branch already requires teacher_id/candidate_id and empty agent_id — lookup is always `id=null/empty`. Path broken. |
| **Dump impact** | `payments.teacher_id` nonzero **0**; `teachers.balance` all zero/null. |
| **Financial impact** | Teacher wallet unused in production data; if someone uses the UI, cache update targets the wrong entity or none. |
| **Target decision** | **A17**: in use or deprecated? |
| **Depends on** | **A17**; not A01 unless teacher backups appear (they do not) |

### FIN-BUG-06 — rejection inserts amount=0 DR

| | |
|---|---|
| **Source** | `candidatePaymentApproval():208-213` (`$amountNew = status==A ? amount : 0` then always `new Payment` with that amount, `status` = posted status) |
| **Tables / fields** | `payments.amount`, `type`, `status`; `payment_requests.status` |
| **Legacy behavior** | Reject still inserts a `payments` row. |
| **Defect** | Zero-amount DR pollutes the table; not a real posting. |
| **Dump impact** | **0** current DR rows with `amount=0`. **10** `payment_requests` status `R`. Historical path may differ. Code defect remains. |
| **Financial impact** | Low on current dump; high if reports sum DR without filtering amount/status. |
| **Target decision** | **A18**: reject = workflow event only. |
| **Depends on** | **A18**; posting shape is A01-adjacent |

### FIN-BUG-07 — ticket invoice update deletes then recreates

| | |
|---|---|
| **Source** | `VoyagerTicketInvoiceController::update():171-173` (`store()` then `TicketInvoice::where('id',$id)->delete()` and delete lines) |
| **Tables / fields** | `ticket_invoices.id`; `ticket_invoice_lines`; receipts pointing at old id |
| **Legacy behavior** | Edit destroys historical IDs. |
| **Defect** | Receipts (`ticket_invoice_id`) and external references dangle or attach to the new id only if store copied them (it does not migrate receipts). |
| **Dump impact** | PK gaps (`ticket_invoices` ids 7–142, 111 rows). Consistent with delete/recreate. |
| **Financial impact** | Broken receipt linkage; duplicate invoices possible under race. |
| **Target decision** | **A19** (Design Gate: **M8**). Versioned update; no delete. |
| **Depends on** | **A19 / M8**, not M7 wallet writes |

### FIN-BUG-08 — agent report metrics hardcoded to 0

| | |
|---|---|
| **Source** | `VoyagerReportController::agentReport():441-445` |
| **Tables / fields** | Report SQL aliases `selected_cand`, `visa_updated_cand`, `payment_due_cand`, `balance`, `due_balance` |
| **Legacy behavior** | Agent report prints those columns as literal `0`. |
| **Defect** | Finance/ops metrics are fake. |
| **Dump impact** | N/A (query, not stored). |
| **Financial impact** | Misstated agent balances in the named report; operators may ignore real `agents.balance`. |
| **Target decision** | A10 (reports) + A01/A02 if the replacement uses ledger totals. |
| **Depends on** | M10 / A10; **not** an A01 approval by itself |

### FIN-BUG-09 — ticket currency varchar `EURO`/`TAKA`

| | |
|---|---|
| **Source** | `ticket_invoices.currency` DDL `varchar(255)`; dump values `EURO` 101, `TAKA` 10 |
| **Tables / fields** | `ticket_invoices.currency` vs `currencies.id` |
| **Legacy behavior** | No FK; strings not equal to `currencies.full_name` (`TAKA`/`Euro`) or ISO. |
| **Defect** | Cannot join reliably; third encoding beside numeric ids. |
| **Financial impact** | Ticket totals cannot be converted with the wallet FX table without a mapping decision. |
| **Target decision** | A02 mapping + M8 normalize to ISO FK. |
| **Depends on** | **A02** (codes); **M8** (ticket schema) |

---

## Six reconciliation queries (recovered)

SQL text is normative in `modernization-design/final-design-gate/06-finance.md`. Results on this dump SHA: `docs/M7-PRODUCTION-PROFILE.md` §8.

### R1 — Agent balance drift

| | |
|---|---|
| **Purpose** | Cached `agents.balance` must match recomputed approved payments |
| **Sources** | `agents`, `payments` (`type`, `amount`, `currency_id`, `exchange_rate`, `status`, `sub_agent_id=0`) |
| **Expected invariant** | `ABS(cached - computed) ≤ 0.01` for every agent (0 failing rows) |
| **Failure** | Formula mismatch, opening balances without rows, backup/live split, `exchange_rate` 0/NULL, FLOAT |
| **This dump** | **30 failing agents** |

A01 chooses the row set; A02 chooses the formula. Both required before R1 can be a cutover gate rather than a diagnosis.

### R2 — Orphan approved payment_requests

| | |
|---|---|
| **Purpose** | Every approved fee request has a living `payments` row |
| **Sources** | `payment_requests.payment_id`, `payments.id` |
| **Expected invariant** | 0 rows `status='A' AND (payment_id IS NULL OR payment_id NOT IN payments)` |
| **Failure** | Partial write after debit; deleted payments |
| **This dump** | **PASS (0)** |

### R3 — Duplicate invoice money receipts

| | |
|---|---|
| **Purpose** | Receipts unique per invoice/number/amount |
| **Sources** | `invoice_money_receipts` |
| **Expected invariant** | no `GROUP BY invoice_id, receipt_no, amount HAVING COUNT(*)>1` |
| **Failure** | Double-click store; no uniqueness constraint |
| **This dump** | **FAIL — 14 groups, 19 extra rows** |

### R4 — Ticket line totals vs header

| | |
|---|---|
| **Purpose** | `ticket_invoices.total` equals sum of `ticket_invoice_lines.total_amount` |
| **Sources** | System C |
| **Expected invariant** | delta ≤ 0.01 |
| **Failure** | Edit/delete races (FIN-BUG-07); manual header edit |
| **This dump** | **PASS (0)** |

### R5 — FX not applied (TAKA with rate 1)

| | |
|---|---|
| **Purpose** | Approved TAKA (`currency_id=1`) must not sit at `exchange_rate=1` if 1 means “identity EUR” |
| **Sources** | `payments` |
| **Expected invariant** | 0 such approved rows |
| **Failure** | Operator entered 1; default column default 1 |
| **This dump** | **FAIL — 38 rows** |

### R6 — Backup vs live potential duplicates

| | |
|---|---|
| **Purpose** | Do not union backup into live without A01 |
| **Sources** | `payments` ⋈ `payments_backup_01072026` on `(agent_id, amount, DATE(payment_date), type)` |
| **Expected invariant** | 0 overlaps **or** an approved exception list |
| **Failure** | Copy-overlap; opening balances duplicated |
| **This dump** | **FAIL — 1,268 / 1,275 backup rows overlap** |

R6 is **run once A01 approves inclusion**. Until then, overlap is evidence **not** to union.

### Extra checks supported by evidence (not in the original six)

| ID | Purpose | This dump |
|---|---|---|
| R7 | Invoice header total vs lines | **N/A** — invoices have no header total |
| R8 | Duplicate approved `(candidate_id, bill_title)` | **12** pairs |
| R9 | `admission_payment_id` count vs Admission titles | 318 vs 3 (FIN-BUG-01) |
| R10 | `euro_to_bdt_rates.euro_rate` vs mode of `payments.exchange_rate` | 117.346 vs 1 and ~145 |
| R11 | Ticket receipts unique analog of R3 | not fully aggregated this pass — **UNVERIFIED** |
| R12 | Sub-agent cache vs sub-agent payments | not run — **UNVERIFIED** |

---

## Concurrency / transaction safety

| Operation | Lost update | Double pay | Duplicate invoice | Double balance | Partial write | Approval inconsistency |
|---|---|---|---|---|---|---|
| `paymentApproval` POST | Yes — no lock; `empty(balance)` assignment | Retry after status=A blocked (“already processed”) **unless** first update failed after credit | n/a | Yes if status write fails after increment (no wrapping transaction) | Yes | Status A without credit or credit without consistent display formula |
| `candidatePaymentApproval` POST | Yes | **Yes** — debit happens **before** `status==P` check | n/a | **Yes** | **Yes** — no `DB::beginTransaction` | P→A with DR inserted even when later candidate FK branch would fail (first branch always succeeds) |
| `walletStore` | Low (insert only) | Duplicate CR requests allowed (no idempotency) | n/a | No (no cache write) | Transaction present | Multiple pending CR |
| `VoyagerPaymentController::store` | Yes (no lock) | Duplicate admin CR/DR | n/a | Same-transaction increment+insert (better) | Rollback on payment save fail | FX skipped (FIN-BUG-03) |
| Invoice store/update | n/a | n/a | Line delete+insert | No wallet | Line wipe then insert | Totals only on lines |
| Ticket update | n/a | n/a | **Yes** (store then delete old) | No | New row without deleting old if delete fails | FIN-BUG-07 |
| Agent create `balance` | n/a | n/a | n/a | Opening cache with no journal | n/a | R1 drift |

Target: one serializable posting transaction per approval; unique idempotency key; no cache-as-source-of-truth.

---

## Security (audit only — do not implement)

| Risk | Evidence | Notes |
|---|---|---|
| **Unauthorized approval** | Ajax payment + candidate-payment approval: **no `authorize()`** | Any authenticated panel user who can guess/find an id |
| **Direct endpoint access** | `Route::any` GET+POST on those Ajax routes | CSRF depends on Voyager middleware group; policy still missing |
| **IDOR / cross-agent** | `Payment` global scope is path-dependent (`my/wallet` vs `payments`) and uses numeric `role_id` | Easy to miss a path; `find($id)` in approval does not re-check ownership |
| **Cross-sub-agent** | `role_id==109` loads `SubAgentPaymentRequest::find($id)` with **no** `sub_agent_id = profile` check on POST | |
| **Cross-candidate** | Fee approval does not verify the candidate belongs to the acting agent beyond the request row | Request `agent_id` copied at create time |
| **Cross-agency / company** | `Invoice` scope uses `agenciers_id = profile_id` for **all** non-role-1 users | Company role can be mis-scoped as agency; `company` has no invoice grants but print routes may still be reachable |
| **Amount tampering** | Wallet create: client `amount`. Admin store: client `amount`. Approval debit uses **stored** request amount (better) | Wallet approve uses client `exchange_rate` — **rate tampering** |
| **Currency tampering** | Wallet create: client `currency_id`. Fee DR: hardcoded 2 | A02 |
| **Duplicate posting / replay** | No idempotency key; double click on approve | |
| **Report exposure** | `reports/generate/agent-ledger`, `agent-report`, `money-receipt-report`, invoice PDF by id | FIN-BUG-08 zeros; printInvoice has no extra policy beyond Voyager |
| **Receipt print IDOR** | `InvoiceMoneyReceiptController::printInvoiceMoneyReceipt` loads **first** receipt for `invoice_id` from the URL, not receipt id | Wrong document / enumeration |
| **Cheque files** | `cheque_file_path` on `payments`; M9 not started | Path disclosure risk later |

Cross-candidate / cross-agent **data** in dump: 18 payment_requests with missing candidates (quarantine, do not repair).

---

## RBAC (no grants)

Do **not** reuse numeric role ids 101/106/108/109 at runtime.

Conceptual mapping only:

| Legacy actor | A07 role | Finance capability observed | Target (proposed, **not granted**) |
|---|---|---|---|
| sadmin | `super_admin` | Full BREAD including `delete_payments` | Bypass + `finance.*.read/manage`; **no delete** |
| owner (106) | `owner` | add/browse payments, payment_requests, invoices, tickets | Global finance ops after GO |
| Agent (101) | `agent` | browse payments; wallet deposit; list own payment requests; approve **sub-agent** wallet if `role_id==101` branch | Scoped `finance.wallet.deposit`, `finance.payment_request.read` |
| Sub Agent (109) | `sub_agent` | browse payments scoped; wallet; own sub-agent requests; **same Ajax approve** as staff | Scoped deposit; **not** global fee approve unless A07 says so |
| Agency (108) | `agency` | invoices + money receipts BREAD | M8 document keys |
| Employee (104) | `employee` | **no** payment_* grants in CSV | WF-19 conflict |
| Teacher / candidate / employer / company | those roles | no wallet grants | none |

Platform catalogue today: **no** `finance.*` keys. Keep it that way until A01/A02 and a separate grant GO.

---

## FINAL STATUS

**M6: CLOSED / VERIFIED**  
**M7 PRE-GATE: COMPLETE**  
**A01: APPROVED WITH CONDITIONS**  
**A02: CLOSED**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**
