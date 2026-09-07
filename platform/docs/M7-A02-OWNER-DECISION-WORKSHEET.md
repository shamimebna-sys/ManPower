# M7 A02 — owner decision worksheet (evidence only)

**Date:** 2026-09-07  
**Document type:** EVIDENCE AND OWNER DECISION WORKSHEET  
**Does not approve A02.** Does not issue M7 IMPLEMENTATION GO.

**Prerequisite:** M6 CLOSED / VERIFIED. M7 PRE-GATE COMPLETE. A01 APPROVED WITH CONDITIONS.

No application code, Prisma, migration, grants, CI, production data, or Laravel change.

Related:

- `docs/M7-A01-A02-OWNER-DECISIONS.md`
- `docs/M7-A01-A02-OWNER-CHECKLIST.md`
- `docs/M7-A01-A02-DECISION-PACKAGE.md`
- `docs/M7-PRE-GATE.md`
- `docs/M7-PRODUCTION-PROFILE.md`
- `docs/M7-FINANCE-RISK-REGISTER.md`
- Design Gate: `modernization-design/final-design-gate/06-finance.md`, `11-milestones-risks-approvals.md`, `finance-operations.csv`

Dump SHA (verified in PRE-GATE): `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`

---

## How to read this worksheet

Every item stays in one of: **APPROVE / REJECT / CHANGE / HOLD / UNRESOLVED**, unless an existing owner recording already binds it.

This worksheet **recommends** and **presents options**. It does **not** lock A02.

Owner recordings already binding (do not reopen here):

| Item | Binding status |
|---|---|
| Fee debit currency | **HOLD** — must be defined by approved fee/request currency; **no hard-coded EUR** |
| W1 | **HOLD** — do not lock FX direction until fee-debit currency is approved |
| `Panelty Fee` concept | **APPROVE** — distinct legacy bill-title concept |
| Exact `bill_title_code` for Panelty / A16 enum | **UNRESOLVED** |
| `bill_code=101` | **APPROVE WITH CONDITION** — preserve lineage; mapping needs later explicit approval |
| A17 | **UNRESOLVED** |
| A18 | **UNRESOLVED** |

---

# 1. Fee debit currency

**Owner choice: HOLD** (unchanged). This section is evidence only.

## 1.1 Where the fee amount originates

| Step | Source | Evidence |
|---|---|---|
| Request create | Operator enters `payment_requests.amount` (Voyager BREAD) | DDL: `amount float DEFAULT 0`. Controller does not overwrite amount. |
| Request approve | `$paymentRequest->amount` copied onto the DR `payments` row | `VoyagerAjaxController::candidatePaymentApproval():218` |
| Wallet cache | `$agent->decrement('balance', $amountNew)` where `$amountNew` is the request amount if status `A`, else `0` | same method `:208-209` — **no FX** |

Fee amount therefore originates on the **request**, not on `currencies` and not on `euro_to_bdt_rates`.

## 1.2 Where currency originates

| Object | Has currency? | Evidence |
|---|---|---|
| `payment_requests` | **No** | Dump DDL columns: `id, bill_title, agent_id, group_id, candidate_id, amount, status, payment_id, remarks, created_at, updated_at`. **No `currency_id`.** Model `PaymentRequest` has no fillable currency field. |
| `sub_agent_payment_requests` | Same pattern (1 dump row) | Same approval branch when `role_id==109`. |
| `payments` (fee DR) | **Yes, written by code** | `$entityPayment->currency_id = 2;` at `candidatePaymentApproval():226` |
| `payments` (wallet CR) | **Yes, from the create form** | `VoyagerMyPanelController` wallet store: `$request->get('currency_id')` |
| `payments.exchange_rate` on fee DR | **Not set in the approve method** | Column default `float DEFAULT 1`. Observed fee DRs: `currency_id=2`, `exchange_rate=1` |

Legacy currency IDs (dump `currencies`, two rows only):

| `id` | `full_name` | `short_name` | `symbol` |
|---:|---|---|---|
| 1 | TAKA | Tk | ৳ |
| 2 | Euro | Euro | € |

**Do not treat dump id=2 as target ISO EUR automatically.** Mapping TAKA→BDT and Euro→EUR is an A02 item still unsigned. Hard-coded `2` is a legacy numeric id.

## 1.3 Does legacy approval hard-code currency?

**Yes, on the DR payment row only.**

```
VoyagerAjaxController::candidatePaymentApproval():226
$entityPayment->currency_id = 2;
```

This is FIN-BUG-04 (`06-finance.md`). It does **not** read a request currency. The request has none.

Design Gate business question (unsigned): “Confirm currency ID 2 is always the fee currency; approve or correct.”

Owner 2026-09-07 already forbids reproducing that hard-code as policy: **no hard-coded EUR.**

## 1.4 Do different fee types use different currencies?

**No evidence of per-title currency in source or dump.**

All candidate-fee approvals use the same method and the same `currency_id = 2` assignment. Observed `bill_title` values all go through that path:

| `bill_title` | Request rows | Typical request amounts (live table) |
|---|---:|---|
| `Manpower Fee` | 607 | commonly ~6000–6300 (example DR `payments.id=2` amount `6200`) |
| `Final Group Approval` | 76 | 1600–6300 |
| `Panelty Fee` | 12 | 250, 300, 500, 1500, 2000 |
| `Medical Fee` | 4 | (same approval path) |
| `Admission Group Approval` | 3 | 100 and 6200 observed |

No dump column distinguishes fee-type currency. Scale differences are amounts, not currencies.

## 1.5 Candidate fee approval vs wallet top-up

| | Wallet top-up | Candidate fee |
|---|---|---|
| Create | `payments` CR, `status=P`, `currency_id` from form | `payment_requests` `status=P`, **no currency** |
| Approve | `paymentApproval`: store POST `exchange_rate`; `balance += amount / exchange_rate` | `candidatePaymentApproval`: `balance -= amount` (raw); DR `currency_id=2`; rate left at default 1 |
| Reject | No cache change; status `R` on existing CR row | Still inserts a DR `payments` row with `amount=0` (FIN-BUG-06); cache decrement 0 |
| Authoritative money row after A01 | live `payments` `status=A` | live `payments` `status=A` (A01-01); request is not the ledger (A01-03) |

These are **different** currency/FX write paths. Do not assume they share a formula.

## 1.6 Notification / display conflict (not authority)

Fee create/approve notifications call:

`CommonClass::currencySymbol(true, $paymentRequest->currency_id, $paymentRequest->amount)`

`$paymentRequest->currency_id` is empty (no column). `currencySymbol` then hits the null-id branch and returns **`৳` or `BDT`**, **dropping the amount**. Dump notifications therefore look like:

`Payment request of ৳ (Panelty Fee) for candidate …`

That is **display fallback**, not a stored request currency, and **not** A01 financial authority.

## 1.7 Conflict / risk

| Conflict | Why it matters |
|---|---|
| Request has no currency; DR is hardcoded `2`; notifications fall back to ৳ | “Approved fee/request currency” **cannot be read from the request table** |
| Cache debit is raw amount; wallet credit is `amount/rate` | Same FLOAT cache, incompatible units (FIN-BUG-03) |
| Display SQL treats `currency_id=2` as already-base | Fee DRs tagged `2` + rate `1` are shown as full amount in “Euro-base” units |
| Amounts such as 6200 could be EUR cash-wallet units **or** BDT mis-tagged as Euro | PRE-GATE listed this as unresolved; **neither reading is proven** |
| Owner: no hard-coded EUR | Implementing “always EUR because id=2” **contradicts** the HOLD |

If 6200 tagged Euro is posted as EUR while operators meant BDT, error is ~100× at ~140–148. If 6200 is EUR and the target posts BDT, error is the same size the other way. **Do not silently pick.**

## 1.8 Possible target options (unsigned)

| ID | Option | What it would mean |
|---|---|---|
| **FC-1** | Fee amount is EUR | Treat hardcoded `payments.currency_id=2` as intended Euro. Contradicts “no hard-coded EUR” unless the owner **explicitly** approves Euro as the fee currency (not as a copy of the hard-code). |
| **FC-2** | Fee amount is BDT | Treat request amounts as TAKA despite DR tag 2. Would **not** rewrite historical `payments.currency_id` (A01: no silent repair). Opening-by-currency using stored id=2 would then not match business currency (see contradictions). |
| **FC-3** | Fee amount is wallet-cache units (legacy (B): no FX) | Matches the cache write `balance -= amount`. Does not name ISO currency. Insufficient for a double-entry ledger that requires a currency per line. |
| **FC-4** | New explicit request currency field | Target may store currency on the request. **Source of the value is still missing in the dump.** Owner must say how to fill historical rows. |
| **FC-5** | Per `bill_title_code` after A16 | No dump evidence that titles used different currencies. Would be a **new** policy, not a recovery of legacy. |

## 1.9 Recommended option

**HOLD.**

Do not choose EUR or BDT in this worksheet. Owner already required: fee debit currency **must be defined by approved fee/request currency**, and that currency **is not present** on `payment_requests`.

W1 stays HOLD until this item leaves HOLD.

---

# 2. W1 FX formula / direction

**Owner choice: HOLD** (unchanged).

## 2.1 Verified legacy mechanisms

None of these is “correct” merely because it is used more often. R1 uses (C) vs cache and **fails 30/40 agents**.

### Mechanism A — `amount / exchange_rate` (always divide)

| | |
|---|---|
| Where | Wallet approve `VoyagerAjaxController::paymentApproval():117-122`. Agent-ledger print `agent-ledger.blade.php:47-49`. |
| Input | `payments.amount`; POST or stored `exchange_rate` (PHP `(float)` on approve). **Ignores `currency_id`.** |
| Cache / print | `balance += amount/rate` (CR) or `-=` (print DR) |
| Rate source | Approver-entered rate on wallet approve. **Not** `euro_to_bdt_rates`. |
| Example | `amount=700000`, `rate=140` → cache credit `5000`. `amount=6200`, `rate=1` → `6200`. |

### Mechanism B — `±amount` without FX

| | |
|---|---|
| Where | Admin `VoyagerPaymentController::store():110-121`. Fee approve cache debit `candidatePaymentApproval():208-209`. |
| Input | Raw `amount`. Stores `currency_id` / `exchange_rate` on admin store but **does not use them for cache**. Fee path does not even write `exchange_rate`. |
| Example | Fee `amount=6200` → cache `-= 6200` regardless of tag 2. |

### Mechanism C — `amount / IF(currency_id=2, 1, rate)`

| | |
|---|---|
| Where | `Agent::payments()` and `SubAgent::payments()` window SQL (display running balance). Design Gate R1 uses this. |
| Input | `payments.amount`, `currency_id`, `exchange_rate` for `status='A'` |
| Meaning | Legacy id **2** is treated as **already base** (divide by 1). Other ids divide by `exchange_rate`. |
| Not a write path | Does not credit/debit `agents.balance`. |

A fourth display variant exists in the payment edit blade: if `currency_id != 2`, show `amount/rate` as Euro; if `currency_id==2`, show raw amount. Same “id 2 = already Euro” idea as (C), UI only.

## 2.2 Candidate target formulas

Symbolic values: `A` = stored amount, `R` = stored `payments.exchange_rate`, `C` = stored `payments.currency_id` (legacy 1 or 2). Wallet account currency is **not locked**.

### TF-W1 — EUR-base, TAKA converts, EUR identity (Design Gate / PRE-GATE recommended **before** owner HOLD)

| | |
|---|---|
| Input currency | Per-row stored currency (after ISO map: 1→BDT, 2→EUR) **or** owner-approved fee currency — **not decided** |
| Output / base | EUR |
| Direction | `EUR = BDT / R` when input is BDT; `EUR = A` when input is EUR (`R=1`) |
| Example | BDT `A=700000`, `R=140` → `5000 EUR`. EUR `A=3000`, `R=1` → `3000 EUR`. |
| Wallet | Ledger-derived EUR (A01-02: cache is not truth) |
| Ledger | Store `(amount, currency, fx_rate, base_amount)` append-only |
| Historical compatibility | Matches **intent** of (C) for **new** posts. Does **not** match cache (R1 fail). Conflicts with fee HOLD (hard-coded 2). |
| Risk | Treating mis-tagged fee DRs as EUR. Owner forbade locking this until fee-debit currency is approved. |

### TF-W2 — always divide by `R` (mechanism A)

| | |
|---|---|
| Direction | `base = A / R` even when `C=2` |
| Example | Fee DR `A=6200`, `R=1` → `6200`. Wallet BDT `700000/140` → `5000`. |
| Historical | Matches wallet **approve write**. Diverges from (C) when `C=2` and `R≠1`. |
| Risk | If some Euro rows have `R` as BDT-per-EUR and others use `R=1`, mixed meaning of `R`. |

### TF-W3 — never convert (mechanism B)

| | |
|---|---|
| Direction | `base = A` in unnamed wallet units |
| Example | `6200` stays `6200` |
| Historical | Matches fee cache debit and admin store |
| Risk | Cannot satisfy dual-currency ledger or A01-10 “by currency” without naming the unit. |

### TF-W4 — owner-written formula

Reserved. Do not invent one here.

## 2.3 Recommended target formula

**HOLD — W1 is not locked.**

After fee-debit currency is explicitly approved, re-present TF-W1 vs TF-W2/W3/W4 against that currency. Frequency of (A) vs (B) vs (C) is **not** a vote.

---

# 3. Historical FX

## 3.1 Authoritative historical rate

**Historical System A FX = per-row `payments.exchange_rate` as stored** (parse FLOAT as decimal text; target type `NUMERIC(20,8)` already in Design Gate — not implemented here).

A01-01: only `payments.status='A'` is live wallet movement. Do not rewrite those rates.

Wallet approve **writes** the POST body into `payments.exchange_rate`. Fee approve **does not write** rate; dump fee DRs show default `1`.

## 3.2 `euro_to_bdt_rates = 117.346` is not historical runtime authority

| Fact | Evidence |
|---|---|
| Table | `euro_to_bdt_rates` — 1 row: `conversion_date=2025-05-12`, `euro_rate=117.346` |
| Code | Model `App\Models\EuroToBdtRate` exists. **No controller / helper / approval path reads it** (search of `src/` usages = the model file only). |
| Live rates | Approved `payments.exchange_rate`: `1` on 1,184 of 1,484 status `A`; next cluster **140–148**; **not** 117.346 |
| R5 | 38 approved `currency_id=1` rows with `exchange_rate=1` (Design Gate query) |

Using 117.346 on history would invent balances the production app never computed.

**Do not create a new FX rate source in this worksheet.** Future cutover rate policy remains an unsigned A02 item.

---

# 4. Panelty Fee / A16

## 4.1 Exact stored title

**`Panelty Fee`** (legacy spelling; not “Penalty”).

Voyager `data_rows` dropdown (data types 47 and 68 — payment_requests and sub-agent requests):

`Admission Group Approval`, `Final Group Approval`, `Medical Fee`, `Manpower Fee`, `Panelty Fee`, `Others (Customize)`.

Dump `payment_requests.bill_title` has **zero** `Others (Customize)` rows. Five titles + Panelty = 702.

## 4.2 Exact legacy ID / code

**None persisted.**

- No `bill_title_code` column.
- No `bill_code` column (see §5).
- Voyager option key is the **same string** as the label: `"Panelty Fee":"Panelty Fee"`.
- **`12` is a row count, not a code.** Live `payment_requests` with this title: **12**.

Do **not** invent `PANELTY_FEE` / `PENALTY_FEE` / `12` as a target code. Design Gate proposed codes (`ADMISSION_FEE`, …) **do not include Panelty** and are **unsigned**.

## 4.3 Where it appears / which operation

| Location | What |
|---|---|
| `payment_requests.bill_title` | 12 rows: 10 `A`, 2 `P`; amounts 250–2000; remarks often `AIR TICKET` / `CANCELLATION FEE` |
| `payments.title` | `{bill_title} for candidate …` DR rows, `currency_id=2`, `exchange_rate=1`, `payment_method=SELF` (same as other fees) |
| Notifications | Text contains `(Panelty Fee)` and symbol ৳ (null request currency fallback) |
| Candidate FKs | No Panelty-specific FK. FIN-BUG-01 still assigns `admission_payment_id` on approve |

It is a **candidate fee request**, same workflow as Manpower/Final/Medical/Admission. Linked via `payment_requests.payment_id` → `payments.id` when approved. Not a wallet top-up. Not System B/C.

## 4.4 A16 definition (from approved Design Gate — do not invent)

From `11-milestones-risks-approvals.md`:

> **A16** — `bill_title_code` enum values and correction of FIN-BUG-01, FIN-BUG-02 linkage logic  
> Blocks: M7 Finance, WF-09, WF-20  
> Status: OPEN

From `06-finance.md`: proposed enum `ADMISSION_FEE`, `FINAL_GROUP_FEE`, `MEDICAL_FEE`, `MANPOWER_FEE`, `WALLET_TOPUP` — **PROPOSED, not approved**. Panelty not in that list.

Owner 2026-09-07: preserve Panelty as a **distinct legacy bill-title concept**. Exact target code still A16.

## 4.5 A16 status

**UNRESOLVED.**

Source evidence establishes the **legacy string** and the **Design Gate question**. It does **not** establish the target `bill_title_code` value, uniqueness policy, or FIN-BUG-01/02 correction. Those remain owner decisions.

---

# 5. `bill_code=101`

**Owner choice: APPROVE WITH CONDITION** (unchanged) — preserve lineage; target business mapping requires explicit approval.

| Question | Evidence |
|---|---|
| Source table | Intended for `payment_requests` / `sub_agent_payment_requests` at **create** |
| Source field | PHP only: `$request->merge(['bill_code' => 101]);` in `VoyagerPaymentRequestController::store():81` and `VoyagerSubAgentPaymentRequestController::store():83` |
| Persisted? | **No.** Dump DDL has no `bill_code`. Grep of dump SQL: **no `bill_code` string at all**. Voyager `insertUpdateData` cannot store a field with no column. |
| Production rows | All 702 parsed requests: key absent / not stored |
| Related bill title | Merge runs for **every** create, independent of `bill_title`. Not specific to Panelty, Manpower, or any title. |
| Real business concept? | **Unproven.** Constant `101` has no dump dictionary, no `currencies`/`bill` table, and is not role_id 101 (Agent) in this assignment. |

Do **not** map 101 to a bill title. Do not treat 101 as Panelty. Do not hardcode 101 as runtime fee type.

**No new owner choice is required by this evidence.** Keep APPROVE WITH CONDITION.

---

# 6. A17

## 6.1 Authoritative definition (Design Gate — not invented)

From `11-milestones-risks-approvals.md`:

> **A17** — Teacher payment flow (FIN-BUG-05): in-use or deprecated?  
> Blocks: M7 Finance  
> Status: OPEN

FIN-BUG-05 (`06-finance.md`): `VoyagerPaymentController::store():103` — teacher branch does `Teacher::where('id', $agent_id)` instead of `$teacher_id`. Same pattern for candidate at `:104-105`.

## 6.2 Additional evidence (does not close A17)

| Fact | Evidence |
|---|---|
| `payments.teacher_id ≠ 0` | **0** rows (production profile) |
| `teachers.balance` | Unused / zero (2 teacher rows) |
| `TeacherPaymentAction` | Voyager button route `payments.create?teacher_id=…`; `shouldActionDisplayOnDataType()` is empty (not enabled on a slug) |

## 6.3 Status

**UNRESOLVED.**

The **question** is defined in the Design Gate. The **answer** (in-use vs deprecated; whether to repair the lookup) is not owner-approved. This worksheet does not pick.

---

# 7. A18

## 7.1 Authoritative definition (Design Gate — not invented)

From `11-milestones-risks-approvals.md`:

> **A18** — Zero-DR noise row on fee rejection (FIN-BUG-06): confirm not intentional, approve removal  
> Blocks: M7 Finance  
> Status: OPEN

FIN-BUG-06: `candidatePaymentApproval():208-209` — on status `R`, `$amountNew = 0` then still `new Payment()` DR with that amount and `status=R`.

`finance-operations.csv`: proposed reject = “Append decision event; no ledger posting” — **PROPOSED**, not A18 approval.

## 7.2 Additional evidence (does not close A18)

| Fact | Evidence |
|---|---|
| DR `payments.amount=0` in dump | **0** rows |
| `payment_requests.status=R` | **10** rows |

Code exists; current dump has no zero-DR artifacts. Historical dumps were not re-scanned here.

A02-08 (“rejection creates no posting”) is related and **unsigned**. It does not close A18.

## 7.3 Status

**UNRESOLVED.**

The **question** is defined. The **answer** (intentional vs remove) is not owner-approved. Recommended *direction* in PRE-GATE was “no journal on reject”; that is **not** an owner APPROVE.

---

# 8. Decision matrix

| ID | Decision | Legacy evidence | Recommended position | Owner choice | Reason | Dependencies |
|---|---|---|---|---|---|---|
| **A02-FEE-CURRENCY** | Currency of candidate-fee debit (DR) | Amount from `payment_requests.amount`. Request has **no** `currency_id`. Approve copies amount; cache `-=` raw amount; `payments.currency_id` **hardcoded 2**; `exchange_rate` default 1. Notifications fall back to ৳. All fee titles share this path. Wallet top-up uses form `currency_id` + `amount/rate`. | **HOLD.** Present FC-1..FC-5; do not select EUR or BDT. | **HOLD** (2026-09-07) | Owner: defined by approved fee/request currency; no hard-coded EUR. That currency is **not in the request table**. | A02; A16 if per-title; A01-03 posting; A01-10 opening-by-currency |
| **W1** | Canonical FX direction / formula | (A) `A/R` wallet approve + ledger print. (B) `±A` admin store + fee cache. (C) `A/IF(C=2,1,R)` display + R1. R1 FAIL 30/40. | **HOLD.** After fee currency is decided, re-evaluate TF-W1..W4. Do not lock W1 now. | **HOLD** (2026-09-07) | Owner: do not lock FX direction until fee-debit currency is approved. Frequency ≠ correctness. | A02-FEE-CURRENCY; A01-02 cache not truth; historical per-row rate |
| **A16** | `bill_title_code` enum + FIN-BUG-01/02 correction | Design Gate A16 text. Dump titles include **`Panelty Fee`** (12 rows, string only, no numeric code). Proposed five-code enum unsigned and omits Panelty. | Keep Panelty as distinct **concept**. **Do not invent a code.** Enum + linkage remain owner-written. | Concept **APPROVE**; exact code **UNRESOLVED** | Evidence gives the string and the Design Gate question, not a target enum value. | WF-09, WF-20, FIN-BUG-01/02; not a substitute for A02 |
| **A17** | Teacher payment path in-use or deprecated? | Design Gate A17 + FIN-BUG-05. `teacher_id` nonzero **0**. Lookup uses `$agent_id`. Action display disabled. | Do not implement teacher wallet. Do not “fix” the lookup without owner mark. | **UNRESOLVED** | Question is defined; in-use vs deprecated is not decided. | M7 teacher slice only |
| **A18** | Zero-DR on reject: intentional or remove? | Design Gate A18 + FIN-BUG-06. Code inserts amount=0 DR. Dump: **0** such rows; **10** rejected requests. CSV proposes no ledger post. | Recommend no journal on reject **as a candidate**, not as approval. | **UNRESOLVED** | Question is defined; owner has not confirmed removal. | A02-08 if signed later; A01-03 posting shape |
| **BILL-101** | Treatment of `bill_code=101` | PHP `merge(['bill_code'=>101])` on every request create. **No dump column, no dump occurrences.** Independent of `bill_title`. | Preserve lineage only. Do not map to a title. Do not ship as runtime fee type. | **APPROVE WITH CONDITION** (2026-09-07) | Evidence confirms non-persistence; no business mapping found. | A16 if mapping is ever proposed |

---

# 9. Contradiction check

Checked against A01-01–A01-10, A01 APPROVED WITH CONDITIONS, M7 PRE-GATE, and Design Gate finance rules.

| ID | Topic | Result |
|---|---|---|
| CONTRA-00 | Payments `status=A` as wallet authority (A01-01); cache not truth (A01-02) | **No contradiction.** Worksheet does not promote cache or backups as authority. |
| CONTRA-01 | Owner: fee currency = **approved fee/request currency**. Dump: request has **no currency**. Hardcoded `payments.currency_id=2` is **not** a request field. | **Documented gap, not resolved.** This is why A02-FEE-CURRENCY stays HOLD. Do not invent a request currency. |
| CONTRA-02 | A01-10 opening totals **by currency** using live `payments`. Fee DRs are stored as legacy id **2**. If owner later says fees are BDT, stored currency ≠ business currency. | **Documented tension, not silently repaired.** Do not rewrite historical `currency_id`. Unexplained differences stay quarantined (A01-10 / A01-08 style). Reclassification would need a **new** explicit approval. |
| CONTRA-03 | Historical FX = `payments.exchange_rate`; table 117.346 unused | **No contradiction.** Worksheet forbids applying `euro_to_bdt_rates` to history. No new rate source created. |
| CONTRA-04 | Target append-only double-entry (Design Gate proposed) vs legacy cache mutation | **No contradiction if A01-03 is kept:** do not blindly reproduce direct-balance mutation. Posting shape still waits on A02 + M7 GO. |
| CONTRA-05 | Opening staged until output reconciled (A01-09/10) vs picking an FX formula now | **No contradiction.** W1 HOLD blocks converting per-currency opening totals into a single base. |
| CONTRA-06 | Partner `agenciers`/`companiers` balances all zero | **No contradiction.** Worksheet invents no partner transactions. |
| CONTRA-07 | No silent legacy repair (M5/A01-05/08) vs “correct” fee currency or teacher lookup | **No contradiction.** FIN-BUG-04/05/06 stay documented. A16/A17/A18 remain UNRESOLVED rather than auto-fixed. |
| CONTRA-08 | PRE-GATE note “Manpower DR amounts look like BDT scale tagged as Euro” vs cash-wallet EUR-scale reading of 250–6300 | **Unresolved interpretation conflict.** Neither is proven. Do not pick. |
| CONTRA-09 | Design Gate proposed `bill_title_code` list omits `Panelty Fee`; owner preserved the concept | **Not a contradiction** if A16 stays UNRESOLVED. Would be a contradiction only if the five-code list were treated as approved. It is not. |
| CONTRA-10 | A01-03: request is not the ledger vs owner “approved fee/request currency” | **Tension.** Currency policy may live on the request in the **target**, but historical authority for money remains `payments` (A01-01). Worksheet does not move ledger authority onto requests. |

No contradiction was “fixed” by choosing EUR, BDT, W1, or an invented Panelty code.

---

## FINAL STATUS

**M6: CLOSED / VERIFIED**  
**M7 PRE-GATE: COMPLETE**  
**A01: APPROVED WITH CONDITIONS**  
**A02: CLOSED**

**W1: APPROVED**  
**Fee debit currency: EUR**  
**A16: Panelty Fee = 100**  
**A17: RETAIN (business requirement only)**  
**A18: INTENTIONAL**  
**bill_code=101: APPROVED WITH CONDITION**

**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS: NOT AUTHORIZED**
