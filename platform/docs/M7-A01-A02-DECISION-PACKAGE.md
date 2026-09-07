# M7 A01 / A02 decision package — owner review

**Date:** 2026-09-07  
**Purpose:** Owner decisions required to resolve **A01** (authoritative finance source) and **A02** (balance / currency / FX model).  
**A01 is recorded APPROVED WITH CONDITIONS (2026-09-07)** in `docs/M7-A01-A02-OWNER-DECISIONS.md`. **A02 is not approved.** This package does not issue M7 IMPLEMENTATION GO. It does not authorize code, schema, grants, or production migration.

**No implementation.** No Prisma, migrations, permissions, CI, production data, or Laravel change.

---

## How to use this package

A01 and A02 are **separate hard gates**. Approve them independently, each with: named approver, date, chosen option ID(s), conditions, and a link to this file.

Evidence sources (do not expand beyond these):

- Design Gate Revision 2: `modernization-design/final-design-gate/06-finance.md`, `00-project-rules.md` (Rules 2, 4, 5), `10-api-migration-testing.md` (MIG-RULE-3, MIG-RULE-5), `11-milestones-risks-approvals.md`, `finance-operations.csv`
- M7 PRE-GATE: `docs/M7-PRE-GATE.md`, `docs/M7-PRODUCTION-PROFILE.md`, `docs/M7-FINANCE-RISK-REGISTER.md`
- Owner tick-sheet: `docs/M7-A01-A02-OWNER-CHECKLIST.md` (A01 recorded; A02 unsigned except W1 HOLD / fee-debit HOLD / Panelty Fee / bill_code=101 / A16–A18 unresolved)
- Owner recording: `docs/M7-A01-A02-OWNER-DECISIONS.md`
- Dump SHA (verified): `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`

**Recommended** means: consistent with already-approved Design Gate / M5–M6 policy and with PRE-GATE evidence. It is **not** an implementation license. Where evidence conflicts, the row stays **UNRESOLVED**.

A01 is signed **APPROVED WITH CONDITIONS**. A02 is **CLOSED**. Until a separate M7 IMPLEMENTATION GO is issued:

**M6: CLOSED / VERIFIED**  
**M7 PRE-GATE: COMPLETE**  
**A01: APPROVED WITH CONDITIONS**  
**A02: CLOSED**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS: NOT AUTHORIZED**

---

## Locked rules this package must not weaken

| Rule | Binding statement |
|---|---|
| Rule 4 | No authoritative finance write until A01 **and** A02 are explicitly granted |
| Rule 5 | Confirmed bugs are not silently reproduced or silently “fixed” |
| Rule 2 | Every migrated financial row has `legacy_key_map` in the same transaction as the business insert |
| M5/M6 quarantine | `LIVE + QUARANTINED = SOURCE`. Never silently repair, delete, fabricate, or reassign |
| Opening balance | Staged only after approval. Do not seed or migrate balances in M7 |
| Numeric types | Money `NUMERIC(20,6)`; rates `NUMERIC(20,8)`; parse FLOAT as text |
| Ledger principle | **Append-only double-entry** (proposed Design Gate; **not implemented**) |
| Currencies | Only dump-evidenced TAKA/Euro → ISO `BDT`/`EUR`. Do not add others |
| Subsystems | A / B / C remain isolated until a **new** consolidation approval |
| M8 | Ticket invoice mutation stays M8 |
| A16 | `bill_title_code` correction of FIN-BUG-01/02 is a **separate** gate. This package records titles; it does not close A16 |

---

## Finance subsystem boundaries (preserve)

| ID | Name | What it is | Moves System A wallet money? |
|---|---|---|---|
| **A** | Wallet / fees | `payments` CR/DR, `payment_requests`, `sub_agent_payment_requests`, cached `agents.balance` / `sub_agents.balance` | **Yes** — only this subsystem |
| **B** | Employer invoices | `invoices` (no money columns), `invoice_lines`, `invoice_heads`, `invoice_candidates`, `invoice_money_receipts` | **No** |
| **C** | Tickets | `ticket_invoices`, `ticket_invoice_lines`, `ticket_invoice_money_receipts`, `ticket_companies` | **No** |

| Object | Operational | Financial (wallet) | Mixed legacy |
|---|---|---|---|
| `payment_requests` create (status P) | Yes | No | Workflow only |
| `payment_requests` approve (status A) | Yes | Yes — inserts `payments` DR and decrements cache | **Mixed** |
| `payments` CR wallet deposit | Supporting docs on the row | Yes on approve | Mixed |
| `agents.balance` / `sub_agents.balance` | Display / gate for debit | Mutated cache, not a ledger | Mixed / defective (R1 fail) |
| `invoices` / lines / candidate links | Document | No | Operational + line money |
| `invoice_money_receipts` | Receipt document | No wallet posting | Mixed document (R3 duplicates) |
| `ticket_*` | Document | No wallet posting | Mixed; M8 |
| `agenciers.balance` / `companiers.balance` | Company-report prints `€` | All **zero**; no controller mutation | Column only — **do not invent transactions** |
| `currencies` / `euro_to_bdt_rates` | Reference | Rate table unused by approval code | Isolated |

Do not merge A with B or C because names contain invoice, payment, receipt, or balance.

---

# A01 — Authoritative finance source

A01 decides **which rows may become journals**. It does **not** choose FX (A02) and does **not** choose `bill_title_code` (A16).

### A01.1 Which legacy table(s) are authoritative for actual wallet money movement?

**Evidence:** Wallet credit and fee debit both write `payments`. Cache columns `agents.balance` / `sub_agents.balance` are mutated in the same operations but **fail R1** (30/40 agents). Backups have no model/controller. Invoices and ticket receipts do not debit/credit those caches.

**Owner 2026-09-07: APPROVED (A01-01 / A01-02 / A01-04).** Live **`payments`** rows with `status='A'` are the authoritative source for System A money-movement history. Cached `*.balance` is **not** authoritative. Backups are **not** live. Systems B and C are **not** wallet sources.

Backup-only rows are **not** automatically opening balances. The 7 non-overlapping backup rows remain **UNCLASSIFIED** (A01-05). Identification of backup labels as opening balance remains **UNVERIFIED**; they are not live authority.

### A01.2 Role of `payments`

Live System A **transaction table**: CR (wallet top-up, title `New Payment request`) and DR (candidate fee, title `{bill_title} for candidate …`). Mutable in Voyager BREAD (including `delete_payments` for sadmin). Also stores pending (`P` 4 rows) and rejected (`R` 54) wallet rows.

It is **not** a double-entry ledger. It is **not** an invoice. Target: migrate as staging history; post to append-only journals only after A01+A02+M7 GO.

### A01.3 Role of `payment_requests`

**Owner 2026-09-07: A01-03 APPROVED WITH CONDITION.** Mixed: create is workflow only (status `P`, no cache change). Approve is a **legacy posting trigger** (insert DR `payments`, set `payment_id`, decrement wallet). Dump: 702 rows; `A` 626 / `P` 66 / `R` 10; R2 **PASS** (every approved request has a living `payment_id`).

It is **not** the wallet ledger. Target must **not** blindly reproduce the legacy direct-balance mutation. Final target posting behavior remains subject to **A02** and **M7 implementation approval**. `sub_agent_payment_requests` (1 row) is the same pattern; Design Gate MERGE into `finance.payment_requests`.

### A01.4–A01.5 Backup tables — archival; never auto-union

All of the following are Design Gate **ARCHIVE**. Confirm they must **NEVER** be automatically unioned with live `payments` / `payment_requests`:

| Table | Rows |
|---|---:|
| `payments_backup_01072026` | 1,275 |
| `payments_backup_18062026` | 1,227 |
| `payments_backup_20260625_0214pm` | 1,233 |
| `payments_backup_210620261147am` | 58 |
| `payments_bk` | 1,208 |
| `payment_requests_backup_01072026` | 584 |

**Recommended:** ARCHIVE in `migration`/`archive` with lineage. Inclusion of any backup row as a live journal requires a **named A01 exception list**, not a UNION.

### A01.6 Duplicate / deduplication rule

**Detection key already specified in Design Gate R6** (do not invent another key):

`(agent_id, amount, DATE(payment_date), type)` between live `payments` and `payments_backup_01072026`.

**Recommended default (live wins):**

1. Load live `payments` into staging as `legacy_source_table='payments'`.
2. Load each backup into archive staging as `legacy_source_table=<backup name>`.
3. Flag R6 matches as `POTENTIAL_DUPLICATE`.
4. **Do not post** backup-side matches.
5. Backup rows that **do not** match live remain `UNCLASSIFIED` until the owner lists them as opening-balance exceptions (A01.11) or confirms discard-from-live (still keep archive copy).

**UNRESOLVED:** Whether a tighter key (include `id`, `invoice_no`, or `trx_no`) is required. R6 is the only signed detection SQL.

### A01.7 Duplicate receipt groups (R3)

**Evidence:** `invoice_money_receipts` — **14** duplicate groups, **19** extra rows, key `(invoice_id, receipt_no, amount)`. This is **System B**, not wallet.

**Owner 2026-09-07: A01-06 APPROVED.** Do not delete. Stage all 321 rows. Quarantine extras (`LIVE + QUARANTINED = SOURCE`). Do not post receipts into System A wallets. Extra/duplicate records are retained for quarantine/review, not silently deleted. M8 decides versioned receipt identity. **Do not repair in M7.**

**UNRESOLVED:** Which row in a duplicate group is the “real” receipt for print (controller today loads **first** receipt for an invoice id).

### A01.8 Authoritative relationships (System A vs B/C)

```
payment_request (workflow)
    --on approve--> payments DR (transaction record)
                    + agents/sub_agents.balance decrement (cache, not ledger)

payments CR P-->A (wallet approve)
    --> agents/sub_agents.balance increment (cache; FX formula conflicts — A02)

invoices / invoice_lines / invoice_money_receipts
    --> NO payments row, NO wallet mutation

ticket_invoices / ticket receipts
    --> NO payments row, NO wallet mutation
```

**Recommended target (not implemented):**

| Legacy | Target |
|---|---|
| `payment_requests` | Workflow aggregate; posts **one** journal when approved |
| `payments` status A | Staging source for that journal (A01 row set) |
| `agents.balance` / `sub_agents.balance` | Projection of posted journals **after** A02; never source of truth |
| Invoice / receipt / ticket | M8 documents; no System A posting without a **new** approval |

### A01.9 Traceability (already approved policy)

Every migrated financial record (including archive and quarantine) must carry, via existing `migration.legacy_key_map` (**Owner A01-07 APPROVED 2026-09-07** — do not create finance models now):

| Field | Value |
|---|---|
| `source_system` | `manpower_mysql` |
| `source_table` | exact dump table name (`payments`, `payments_backup_01072026`, …) |
| `source_id` | source PK as string |
| `target_type` | target entity type after insert |
| `target_id` | target PK after insert |
| `migration_run_id` | M13 run id |
| `source_row_hash` | deterministic hash of extracted text amounts/fields |
| `migrated_at` | migration timestamp |

Same transaction as the business/staging insert (Rule 2). Unique on `(source_system, source_table, source_id)`.

Design Gate also named `legacy_payment_id` + `legacy_source_table` on finance rows — that is **M13/M7 schema later**, not this package.

### A01.10 Quarantine of records that cannot be safely classified

Reuse **M5-approved** quarantine: do not repair, delete, fabricate, or reassign. Classify at M13:

| Class | Examples from PRE-GATE | Action |
|---|---|---|
| `LIVE` | `payments` 1,542; `payment_requests` 702 | Stage |
| `ARCHIVE` | All backup tables | Stage as archive; no live union |
| `QUARANTINE` | R6 overlaps (backup side); R3 extra receipts; `payment_requests.candidate_id` orphan **18**; `invoice_candidates` orphans 33/24; unclassified backup-only rows; FIN-BUG-01 `admission_payment_id` values (keep column in staging; do not treat as authority — MIG-RULE-5) | Quarantine row + reason code |
| `UNCLASSIFIED` | Backup rows not overlapping live and not signed as opening balance | Stay unclassified until A01 exception list |

`LIVE + QUARANTINED + ARCHIVE + UNCLASSIFIED = SOURCE`. Counts must reconcile.

### A01.11–A01.12 Opening balances — staged only after approval

**Confirm (already Design Gate / PRE-GATE):** Opening balance is **staged only after approval**. Do not migrate or seed `agents.balance` / `sub_agents.balance` into the live ledger in M7.

Legacy opening/current balances come from:

1. Agent/sub-agent **create form** writing `balance` with **no** `payments` row.
2. Subsequent wallet CR/DR and fee DR mutating the same FLOAT.
3. Possible backup rows labelled opening balance — identification **UNVERIFIED**.

**Recommended process after A01+A02 sign-off (M13, not now):**

1. Stage candidate opening journals as `entry_type=OPENING_BALANCE_CANDIDATE` (Design Gate name).
2. Exclude them from all live computations until a **named** finance approver posts them.
3. Do not use failed R1 as a silent backfill.

**Owner 2026-09-07:** A01-09 **APPROVED WITH CONDITION**; A01-10 **APPROVED**. Opening **amount** is computed from live approved `payments` (`status='A'`), after A01 dedup/exclusion, **by wallet and by currency**. Cache is not authority. Unexplained differences are quarantined. Staged opening balances must **not** become authoritative until the calculation output is reconciled and explicitly approved before production migration/cutover. FX **direction** for converting those per-currency totals is **HOLD** until fee-debit currency is approved (W1 HOLD).

### A01.13 Address 1,268 / 1,275 backup overlap — do not repair

R6 on this dump: **1,268** of **1,275** `payments_backup_01072026` rows match live `payments` on the R6 key.

**Owner 2026-09-07: A01-05 APPROVED.** This is proof that automatic UNION would **double-count** almost the entire backup. Treat the 1,268 as archive duplicates (live already has the money movement). The remaining **7** backup rows are **UNCLASSIFIED** — not automatically opening balances, not automatically discarded. No silent repair. No automatic financial posting from backup rows.

Do not delete backup tables. Do not merge them in code.

### A01.14 Address 318 `admission_payment_id` vs 3 Admission requests — do not repair

FIN-BUG-01: assignment `bill_title = 'Admission Group Approval'` always writes `candidates.admission_payment_id`.

| Fact | Count |
|---|---:|
| `payment_requests` with title `Admission Group Approval` | **3** |
| Candidates with `admission_payment_id` set | **318** |
| Of those, FK missing from `payments` | **18** |

**Owner 2026-09-07: A01-08 APPROVED.** These FKs are **buggy linkage**, not proof of 318 admission fees. MIG-RULE-5: migrate the column **as-is into staging**; do not copy it into a target “admission paid” fact until **A16**. Do not backfill, clear, or rewrite production/legacy data. Do not invent relationships. Quarantine where applicable during M13.

**Recommended target:** ignore `admission_payment_id` as authority; derive fee type from `payment_requests.bill_title` / future `bill_title_code` after A16.

### A01.15 Address zero Final / Medical payment FKs — do not repair

| Candidate column | Non-zero |
|---|---:|
| `final_group_payment_id` | **0** |
| `medical_fee_payment_id` | **0** |
| Requests titled `Final Group Approval` | **76** |
| Requests titled `Medical Fee` | **4** |

**Owner 2026-09-07: A01-08 APPROVED.** Dump shows Final/Medical fees **were requested** (and many approved — titles exist) but **never stored** on the intended candidate FKs because FIN-BUG-01 never reached those branches.

Do not fabricate FK values. Do not treat empty FKs as “fee not paid.” These FKs are **not** financial authority. No invented relationship.

---

# A02 — Balance / currency / FX model

A02 decides **how money is measured and posted**. It does **not** choose which backup rows are live (A01).

### A02.1–A02.2 Currencies

Dump `currencies` (2 rows only):

| Legacy `id` | `full_name` | Target ISO (recommended mapping) |
|---:|---|---|
| 1 | TAKA | **BDT** |
| 2 | Euro | **EUR** |

Ticket varchar `EURO` / `TAKA` is FIN-BUG-09 (M8 + this mapping). **Do not add other currencies.**

### A02.3–A02.4 Authoritative FX source — why 117.346 is not live behavior

| Candidate source | Evidence |
|---|---|
| `euro_to_bdt_rates.euro_rate = 117.346` (1 row, 2025-05-12) | **No** payment-approval controller read. Model unused in wallet/fee paths |
| POST `exchange_rate` on wallet approve | **Live path** — PHP `(float)` stored on `payments` and used as `amount/rate` |
| `payments.exchange_rate` column default `1` | 1,184 of 1,484 approved rows are `1` |
| Live non-1 rates | Cluster **140–148**, not 117.346 |

**Recommended:** Historical System A FX = **per-row `payments.exchange_rate` as stored**, parsed as decimal text. `euro_to_bdt_rates` is **not** the live formula and **must not** be applied retroactively to rewrite history unless the owner explicitly overrides A02 (that would be a new signed formula, not current behavior).

**Why 117.346 ≠ live:** the table is an unused/orphan rate snapshot. Operators typed ~145 (or left default 1) at approval time. Using 117.346 on historical rows would invent balances the production app never computed.

**UNRESOLVED:** Future **new** postings after cutover — manual rate entry vs a maintained rate table. Design Gate listed that as part of A02; PRE-GATE did not observe an automated feed.

### A02.5 The three observed balance mechanisms

| ID | Mechanism | Where | What it does |
|---|---|---|---|
| **(a)** | `amount / exchange_rate` | Ajax `paymentApproval` on status A | Credits agent/sub-agent cache. Ignores `currency_id` |
| **(b)** | `±amount` without FX | `VoyagerPaymentController::store` CR/DR | Adjusts cache by raw amount. Stores `currency_id`/`exchange_rate` but does not use them for the cache |
| **(c)** | `amount / IF(currency_id=2, 1, rate)` | `Agent::payments()` / `SubAgent::payments()` window SQL; Design Gate R1 | Treats **Euro (id=2) as already-base**; divides TAKA by rate. **Display**, not the write path |

Agent-ledger print uses a fourth variant (`amount/exchange_rate` without the id=2 short-circuit). FIN-BUG-03. R1 uses **(c)** vs cache and **fails 30/40 agents** — so even (c) is not equal to stored `agents.balance`.

Fee debit uses **(b)**-like raw `amount` against the same cache, while forcing `payments.currency_id=2` (FIN-BUG-04).

### A02.6 Which behavior is valid in the target? (owner must tick)

Do not silently reconcile (a)(b)(c).

| Option | Meaning |
|---|---|
| **A02-W1** | Target always stores `(amount, currency, fx_rate, base_amount)`. Wallet account currency is **EUR**. TAKA posts using per-row rate; EUR posts with `fx_rate=1` and `base_amount=amount`. Matches the **intent** of (c) for **new** postings. Does **not** claim cache already matches (c). |
| **A02-W2** | Always divide by `exchange_rate` like (a), even for `currency_id=2`. |
| **A02-W3** | Never convert; all amounts already “wallet units” like (b). |
| **A02-W4** | **UNRESOLVED** — owner writes a different formula |

**Recommended to put to the owner:** **A02-W1** was recommended in PRE-GATE. **Owner 2026-09-07: CHANGE / HOLD.** Do not lock FX direction until fee-debit currency is explicitly approved. W1 is **not** locked.

**Still UNRESOLVED even if W1 is chosen:** whether manpower DR amounts tagged `currency_id=2` with `exchange_rate=1` are really EUR or mis-tagged BDT (PRE-GATE conflict). That is a classification decision, not a license to rewrite dump amounts.

### A02.7 Target numeric representation (already approved types)

| Domain | Type | Rule |
|---|---|---|
| Amount | `NUMERIC(20,6)` | Extract FLOAT as text; no binary arithmetic |
| Rate | `NUMERIC(20,8)` | Same |
| Posted base amount | `NUMERIC(20,6)` | Computed only at **approved** posting boundary |
| Rounding | `ROUND_HALF_UP` at that boundary only (`finance-operations.csv`) | Display may round to 2; storage keeps 6 |
| VAT | `NUMERIC(6,4)` | System B/M8 |

### A02.8 Wallet balances: cache vs ledger

Design Gate System A target: **balance is a view/function over approved entries.**

**Recommended:** **Ledger-derived**, with an optional **controlled cached projection** updated **only** inside the same posting transaction as the journal (never by BREAD, never by agent create form, never by invoice/ticket). If cache and ledger disagree, **ledger wins**.

Do not make `agents.balance` the cutover source (R1 fail; opening form writes without rows).

### A02.9 Preserve: APPEND-ONLY DOUBLE-ENTRY LEDGER

No UPDATE/DELETE of `POSTED` journals. Correction = reversing journal linked to original (`finance-operations.csv`, `09-postgresql-prisma.md`). Prisma later; SQL procedure/trigger for balanced lines. **Not implemented now.**

### A02.10 What creates a journal posting (proposed; blocked until GO)

After A01+A02+M7 GO, a **POSTED** journal is created only by:

1. Wallet deposit **approval** (legacy WF-17, `payments` CR P→A).
2. Candidate fee request **approval** (WF-20, one journal per request).
3. Owner-approved **opening balance** post from staged `OPENING_BALANCE_CANDIDATE` (A01.11).
4. Owner-approved **reversal** of a posted journal.

Each journal has unique `idempotency_key` and `legacy_key_map`.

### A02.11 What MUST NOT directly mutate a balance

| Forbidden writer | Evidence |
|---|---|
| Agent/sub-agent create/edit BREAD `balance` field | Writes cache with no payment |
| Invoice issue / line edit / money receipt | No wallet path; System B |
| Ticket invoice store/update/receipt | System C / M8 |
| Live-status / manpower badge | Reads requests; must not post |
| Runtime SQL on `agents.balance` outside posting procedure | R1 drift |
| Backup UNION | A01.5 |
| Reject path | A02.13 |

### A02.12 Payment-request approval (target)

Serializable transaction:

1. Lock wallet account.
2. Assert request still `P` (or target equivalent) and idempotency key unused.
3. Insert **balanced** journal (wallet credit/debit per A02.6) — **once**.
4. Mark request posted with `journal_id`.
5. Update projection if used.
6. Write `legacy_key_map`.

Do not hardcode `currency_id=2`. Do not assign `bill_title`. Fee type from `bill_title_code` after **A16**. Until A16, do not implement the approve handler.

### A02.13 Rejection behavior

Legacy code inserts amount=0 DR (FIN-BUG-06); dump currently has **0** such rows; **10** requests are `R`. Design Gate WF-21 / A18 proposed: **no ledger posting** on reject.

**Recommended:** reject = workflow decision event only. No journal. No cache change. A18 still **OPEN** if the owner insists zero-DR was intentional — evidence says code exists, data does not.

### A02.14–A02.16 Duplicate approval / idempotency / never post twice

Legacy: no idempotency; debit **before** status check.

**Recommended (required for M7 GO, not implemented):**

- Unique idempotency key per approve/deposit command.
- Approved request **cannot** be posted twice. Second attempt returns the existing `journal_id`.
- Duplicate approved `(candidate_id, bill_title)` pairs in dump (**12**) stay in staging; A16 decides whether historical duplicates are two fees or data errors. Target **new** uniques are an A16 policy.

### A02.15 Concurrency / transaction requirements

One DB transaction per posting. Row lock on the wallet account. Balanced lines committed together or not at all. No Ajax-style partial writes. Details in `docs/M7-FINANCE-RISK-REGISTER.md` — do not implement here.

### A02.17 Legacy balances with no corresponding payment row

Agent/sub-agent create-form `balance`, and any cache remainder unexplained by A02 formula vs live `payments`.

**Recommended:** do **not** invent `payments` rows. Stage as `OPENING_BALANCE_CANDIDATE` (or quarantine `UNEXPLAINED_CACHE`) pending A01.11. Never write them to live journals until approved.

### A02.18–A02.19 Named balance columns

| Column | Dump | Target |
|---|---|---|
| `agents.balance` | 37/40 nonzero; R1 fail | Projection of System A ledger after GO; not M7 seed |
| `sub_agents.balance` | 4/12 nonzero | Same, sub-agent wallet accounts |
| `agenciers.balance` | **13 zeros** | **Do not invent transactions.** Column is unused as money movement. M8/report later |
| `companiers.balance` | **390 zeros** | **Do not invent transactions.** Company-report displays `€0` |
| `teachers.balance` | unused; FIN-BUG-05 | A17; not A02 |
| `candidates.balance` | column exists; no finance-controller mutation found | Do not treat as wallet |

---

# Bill titles (record only — A16 remains OPEN)

Observed `payment_requests.bill_title` on this dump (702 rows):

| Source text | Rows | In original Design Gate five? | Proposed `bill_title_code` (unsigned) |
|---|---:|---|---|
| `Manpower Fee` | 607 | Yes | `MANPOWER_FEE` (proposed in `06-finance.md`) |
| `Final Group Approval` | 76 | Yes | `FINAL_GROUP_FEE` (proposed) |
| `Panelty Fee` | **12** | **No** | **Owner 2026-09-07: preserve as distinct legacy bill-title concept.** Exact `bill_title_code` is **A16 UNRESOLVED**. |
| `Medical Fee` | 4 | Yes | `MEDICAL_FEE` (proposed) |
| `Admission Group Approval` | 3 | Yes | `ADMISSION_FEE` (proposed) |

Wallet CR title on `payments` (not a `payment_requests.bill_title`): `New Payment request` (~848). Design Gate listed it as `WALLET_TOPUP`. That is a **payment title**, not a request bill title.

Sub-agent table: one `Admission Group Approval`.

**No other `payment_requests.bill_title` values** were present in the profiled dump.

### `bill_code=101`

`VoyagerPaymentRequestController::store` and the sub-agent controller `merge(['bill_code' => 101])`.

Dump DDL for `payment_requests` / `sub_agent_payment_requests` has **no `bill_code` column**. All 702 parsed request rows have no persisted bill code (profile: `bill_code` NULL/absent).

**Owner 2026-09-07: APPROVE WITH CONDITION.** Preserve legacy value/lineage. Target business mapping requires explicit approval (A16). Do not hardcode unexplained `101` as runtime fee type.

---

# A01 / A02 decision matrix

Legend: **Recommended** = suggested tick for the owner. **UNRESOLVED** = owner must write the choice; this package will not pick.

| Decision ID | Question | Legacy evidence | Possible options | Recommended option | Reason | Risk | Owner approval required |
|---|---|---|---|---|---|---|---|
| A01-D1 | Authoritative wallet money-movement table | `payments` written on credit/debit; cache fails R1; backups unused in code | (1) live `payments` status A only (2) cache `agents.balance` (3) live ∪ backups (4) other | **(1)** | Only table that records CR/DR | If wrong, every opening journal is wrong | **A01-01 APPROVED 2026-09-07** |
| A01-D2 | Role of `payments` | 1,542 rows; CR/DR; P/A/R; mutable BREAD | Transaction source vs ledger vs ignore | **Staging transaction source**, not live ledger | Design Gate KEEP + immutable target ledger | Reproducing mutability | **A01-01 APPROVED 2026-09-07** |
| A01-D3 | Role of `payment_requests` | Create=P no cash; approve posts DR; R2 pass | Workflow only vs ledger vs mixed | **Workflow + legacy posting trigger; not the ledger; do not reproduce direct-balance mutation** | PRE-GATE mixed classification | Double-post if also summing `payments` blindly | **A01-03 APPROVED WITH CONDITION 2026-09-07** — posting subject to A02 and M7 GO |
| A01-D4 | Backup tables | Six archive tables; no controllers | Archive vs union vs opening-balance source | **All archival** | Design Gate ARCHIVE; Rule 4 | Double-count | **A01-04 APPROVED 2026-09-07** |
| A01-D5 | Auto-union backups with live? | R6 overlap 1,268/1,275 | Never vs always vs A01 exception list | **NEVER automatically** | Design Gate + R6 | 1,268 double posts | **A01-04 APPROVED 2026-09-07** |
| A01-D6 | Dedup rule | R6 key specified | R6 key, live wins / backup wins / manual | **R6 detect; live wins; backup archive; 7 UNCLASSIFIED; no backup posting** | Only specified SQL | Tighter key untested | **A01-05 APPROVED 2026-09-07** |
| A01-D7 | Duplicate receipts (R3) | 14 groups, 19 extras; System B | Delete extras / keep all + quarantine / pick first | **Keep all; quarantine extras; no wallet post** | M5 quarantine; M8 owns receipts | Print-first-receipt IDOR remains until M8 | **A01-06 APPROVED 2026-09-07** |
| A01-D8 | Request–payment–wallet–invoice–receipt | No code flow A↔B↔C | Isolate vs merge settlement | **Keep isolated** | Design Gate Systems A/B/C | Invented settlement | **A01 isolation confirmed with A01-06 (B stays B)** |
| A01-D9 | Traceability fields | Rule 2; M5 `legacy_key_map` | Listed fields vs weaker | **source_system, source_table, source_id, target_type, target_id, migration_run_id, source_row_hash, migrated_at** | Already approved M5/M3; owner confirmed for finance | Untraceable finance | **A01-07 APPROVED 2026-09-07** |
| A01-D10 | Unclassifiable rows | Orphans, R6 remainder 7, FIN-BUG-01 FKs | Quarantine vs drop vs fabricate | **Quarantine; never silent repair** | M5 policy | Lost audit | **A01-05 / A01-08 APPROVED 2026-09-07** |
| A01-D11 | Opening balance method | Form cache; unverified backup labels; R1 fail | Cache vs recompute vs manual schedule vs mix | **APPROVED:** live `payments` status A, after A01 dedup, by wallet and currency; quarantine unexplained; cache not authoritative | A01-10 recorded | FX HOLD still blocks posting those totals | **A01-10 APPROVED 2026-09-07** |
| A01-D12 | Opening staged only after approval? | Design Gate OPENING_BALANCE_CANDIDATE | Confirm / reject | **Confirm.** Output must be reconciled and explicitly approved before cutover | Already in 06-finance.md | Premature seed | **A01-09 APPROVED WITH CONDITION 2026-09-07** |
| A01-D13 | 1,268/1,275 overlap | R6 FAIL | Union / live-only / exception on 7 unmatched | **Live-only for those 1,268; 7 UNCLASSIFIED; no silent repair; no backup posting** | Overlap = duplicate | Counting 7 as opening without proof | **A01-05 APPROVED 2026-09-07** |
| A01-D14 | 318 admission FKs vs 3 titles | FIN-BUG-01 | Trust FK / trust titles / quarantine FK | **Do not trust FK; stage as-is; quarantine where applicable; no invented relationship** | MIG-RULE-5 | Fake admission-paid flags | **A01-08 APPROVED 2026-09-07**; A16 still required for correction |
| A01-D15 | Zero Final/Medical FKs vs 76+4 titles | FIN-BUG-01 never reached elseif | Backfill FKs / use request titles / ignore fees | **No backfill; do not repair; not financial authority** | Do not repair | Under-count fees if FKs trusted | **A01-08 APPROVED 2026-09-07** |
| A02-D1 | Legacy currencies | id 1 TAKA, id 2 Euro | Map to BDT/EUR / keep names / add more | **BDT and EUR only** | Dump has two; ISO required | Ticket `EURO` string still M8 | **Yes — A02** |
| A02-D2 | Add other currencies? | None in dump | No / yes list | **No** | User + PRE-GATE | Fake USD books | **Yes — A02** |
| A02-D3 | Authoritative FX source (history) | Per-row rate vs unused 117.346 vs POST body | Per-row `payments.exchange_rate` / euro_to_bdt_rates / other | **Per-row stored rate; do not apply 117.346 historically** | Table unused in code; live rates ≠ 117.346 | Revaluing history | **Yes — A02** |
| A02-D4 | Why 117.346 inconsistent | 1 unused row vs live 1 and 140–148 | Document unused / force as source | **Document as unused snapshot** | PRE-GATE | Treating it as ECB/BDT truth | **Yes — A02** |
| A02-D5 | Three mechanisms (a)(b)(c) | FIN-BUG-03; R1 fail | See A02-W1..W4 | **Record all three; do not claim cache equals any** | Conflict is the finding | Picking silently | **Yes — A02** |
| A02-D6 | Valid target posting rule | Design Gate wants one canonical formula | A02-W1 / W2 / W3 / W4 | **HOLD** — do not lock FX direction until fee-debit currency is explicitly approved; no hard-coded EUR | Owner 2026-09-07 CHANGE/HOLD | Implementing W1 anyway | **Yes — A02** |
| A02-D7 | NUMERIC(20,6)/(20,8) | Already approved types | Confirm / change | **Confirm** | 06-finance.md | Scale loss | **Yes — A02** |
| A02-D8 | Stored cache vs derived | R1 fail; Design Gate view-over-entries | Cache-as-truth / ledger-only / ledger+projection | **Ledger-derived; optional projection** | Approved principle | Cache drift recurrence | **Yes — A02** |
| A02-D9 | Append-only double-entry | Design Gate + 09-postgresql | Confirm / allow BREAD mutate | **Confirm** | finance-operations.csv | Silent history edits | **Yes — A02** |
| A02-D10 | What creates a journal | WF-16–21 | List in A02.10 | **Approve, opening (after A01.12), reversal only** | No invoice/ticket | Scope creep into M8 | **Yes — A02** |
| A02-D11 | Direct balance mutation forbidden | Create form, BREAD, invoices | Confirm list A02.11 | **Confirm** | PRE-GATE | R1 forever | **Yes — A02** |
| A02-D12 | Fee approval posting | Mixed request+DR | Workflow+one journal / payments-only / cache-only | **One journal per approval; request is not the ledger** | A01-D3 + A02.12 | Double debit | **Yes — A02** |
| A02-D13 | Rejection | Code zero-DR; dump 0 rows; 10 R requests | No post / keep zero-DR | **Recommend no post** (A18 still open if owner disagrees) | WF-21 proposed; dump has no zero-DR | A18 conflict | **Yes — A02 and A18** |
| A02-D14 | Idempotency | No legacy key; 12 duplicate pairs | Required unique key / allow doubles | **Required; cannot post twice** | PRE-GATE concurrency | Double pay | **Yes — A02** |
| A02-D15 | Transaction/lock | Ajax has no transaction | Serializable posting txn | **Required** | Risk register | Partial writes | **Yes — A02** |
| A02-D16 | Approved request posted twice? | Legacy race yes | Never / allow | **Never** | A02.14 | Double DR | **Yes — A02** |
| A02-D17 | Cache with no payment row | Agent create `balance` | Invent payments / stage opening / ignore | **Stage; do not invent payments** | A01.11 | Fake CR | **Yes — A01+A02** |
| A02-D18 | `agents`/`sub_agents` balance | Mutated cache; R1 fail | Authority vs projection | **Projection only after GO** | A02-D8 | Seeding cache | **Yes — A02** |
| A02-D19 | `agenciers`/`companiers` balance | All zero; no increment | Invent AR / leave unused | **Leave unused; invent nothing** | PRE-GATE | Fake agency ledger | **Yes — A02** |
| BILL-D1 | `Panelty Fee` (12) | Dump title | Include in enum / retire / map to existing | **Preserve as distinct legacy bill-title concept** | Owner 2026-09-07 APPROVE | Folding into another fee | **A16 still required for exact code** |
| BILL-D2 | `bill_code=101` | Merge in PHP; no column | Map 101→title / ignore | **Preserve lineage; mapping needs explicit approval** | Owner 2026-09-07 APPROVE WITH CONDITION | Hardcoding 101 as fee type | **A16** |
| M8-D1 | Ticket update/delete | FIN-BUG-07 | M8 A19 | **Out of M7** | Design Gate A19/M8 | Implementing tickets in M7 | **Not this package** |

---

## What this package does **not** close

| Still OPEN | Why |
|---|---|
| **A02** | W1 HOLD; fee-debit currency HOLD; no hard-coded EUR |
| **A16** | Exact definition required (Panelty Fee concept preserved; code not defined) |
| **A17** | Exact definition required |
| **A18** | Exact definition required |
| **A19 / M8** | Ticket versioning |
| Historical EUR vs BDT on fee DR tagged currency 2 | HOLD until fee-debit currency defined |
| Opening **posting** (FX on per-currency totals) | Method approved; output must still be reconciled; FX HOLD |
| **M7 IMPLEMENTATION GO** | A01 approval does not constitute GO |

A01 is **APPROVED WITH CONDITIONS**. That does not authorize implementation, production migration, or finance runtime grants.

---

## Owner signature

### A01 — Authoritative finance source

| Field | Value |
|---|---|
| Status | **APPROVED WITH CONDITIONS** (recorded 2026-09-07) |
| Approver name | Owner decision in chat (see `docs/M7-A01-A02-OWNER-DECISIONS.md`) |
| Date | 2026-09-07 |
| Option IDs accepted | A01-01 through A01-10 as recorded |
| Conditions / exceptions | (1) A02 remains open. (2) Opening-balance calculation output requires reconciliation. (3) No finance implementation authorized. (4) No production migration authorized. (5) Finance runtime grants unauthorized. (6) Final target posting subject to A02. (7) A01 is not M7 Implementation GO. Seven unmatched backup rows remain UNCLASSIFIED. |
| Explicit statement | A01 approved with the seven conditions above |

### A02 — Balance / currency / FX model

| Field | Value |
|---|---|
| Status | **OPEN — OWNER DECISION REQUIRED** |
| Approver name | |
| Date | |
| Formula option (W1 / W2 / W3 / W4 + text) | |
| Conditions | |
| Explicit statement | I approve / I do not approve A02 as written |

Until **A02** is signed **and** a separate **M7 IMPLEMENTATION GO** is issued:

- Do not implement M7.
- Do not create finance Prisma models.
- Do not issue M7 IMPLEMENTATION GO.
- Do not grant `finance.*` runtime permissions.
- Do not migrate or seed production balances.

A01 approval does **not** lift these restrictions.

---

## FINAL STATUS

**M6: CLOSED / VERIFIED**  
**M7 PRE-GATE: COMPLETE**  
**A01: APPROVED WITH CONDITIONS**  
**A02: CLOSED**  
**A16: Panelty Fee = 100**  
**A17: RETAIN (business requirement only)**  
**A18: INTENTIONAL**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS: NOT AUTHORIZED**  
**A18: UNRESOLVED**  
**M7 IMPLEMENTATION GO: NOT ISSUED**  
**M7 IMPLEMENTATION: NOT AUTHORIZED**  
**PRODUCTION MIGRATION: NOT AUTHORIZED**  
**FINANCE RUNTIME GRANTS: NOT AUTHORIZED**
