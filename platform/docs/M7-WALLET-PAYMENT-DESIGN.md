# M7 — Wallet and payment-request design

**Freeze document.** No Prisma. No code. No M7 IMPLEMENTATION GO.

Parent: `docs/M7-IMPLEMENTATION-GATE.md`  
Accounting: `docs/M7-ACCOUNTING-MODEL.md`

---

## 1. Wallet model

Legacy `agents.balance` and `sub_agents.balance` are **not** target authority (A01-02). They must not be copied as live ledger balances.

### Ownership (evidenced)

| Owner type | Evidence | Target |
|---|---|---|
| **Agent** | `payments.agent_id`; `payment_requests.agent_id` | One wallet per agent |
| **Sub-agent** | `payments.sub_agent_id`; `sub_agent_payment_requests` (1 row) | One wallet per sub-agent |
| **Teacher** | `payments.teacher_id` ≠ 0 is **0**; lookup broken (FIN-BUG-05) | **RETAIN** as business requirement. **Do not invent** teacher wallets from dump. **Requires implementation-time mapping/profile confirmation.** |
| Agencier / Companier | All zero; no controller mutation | **Not M7 wallets** |

Do not invent additional wallet types.

### WalletAccount

One account per `(wallet, currency)` for **historical** stored currencies (BDT and EUR as mapped from legacy 1/2). Control / available balance for **new** fee debits is **EUR** (A02-01) via W1.

### Balance calculation (ledger-derived)

Let posted lines on `WALLET_LIABILITY` for that account be `L`.

Liability normal credit:

**current_balance(currency) = SUM(CREDIT amounts) − SUM(DEBIT amounts)** in that currency.

**available_balance (EUR control)** = W1 conversion of posted wallet lines to EUR `base_amount` credits minus debits.

- **Transaction history** = posted journal lines for that wallet, newest last for display. After DR-H1, migrated qualifying `payments` appear here as reconstructed journals.
- **Opening balance** = posted **residual** `OPENING_BALANCE` journals after A01-09 only (amounts **not** represented by reconstructed qualifying payments). Qualifying `payments` are **not** opening posts (**DR-H1**). Until residual approval, residual is **staged only**.
- **Reserved balance**: **not evidenced** in legacy. Not in M7 unless a later owner decision. Insufficient-funds check uses **available**, not a hold ledger.
- **Negative balance policy**: legacy fee approve refuses when cache `<` request amount. Target: **reject posting** if `available_balance_EUR < fee_base_EUR` (strictly insufficient). Do not post a partial fee. Do not silently overdraw.

Optional **projection** of available EUR may be stored for performance **only** inside the same posting transaction as the journal. If projection ≠ ledger, **ledger wins**. Projection is never A01-10 input and is never an opening-balance source.

---

## 2. Payment-request workflow

Verified legacy statuses on `payment_requests`: **P** (pending), **A** (approved), **R** (rejected). Default create = P. No other dump statuses.

```
CREATE → PENDING (P) → APPROVED (A)
                      → REJECTED (R)
```

`payment_requests` is **workflow + posting trigger**, not the ledger (A01-03). Do not reproduce direct cache mutation as authority.

### Actors (conceptual; no numeric role IDs)

| Action | Conceptual actor | Scope |
|---|---|---|
| Create | Agent (own candidates) or employee acting for an agent, per existing WF-19 evidence | Candidate must exist and be in actor scope |
| Create (sub-agent table) | Sub-agent path (1 dump row); MERGE into same aggregate | Sub-agent scope |
| Approve | Finance approver (legacy Ajax finance path) | Request in scope; wallet lock |
| Reject | Same approver class | Request still P |

Exact permission **keys** are not added in this gate (`docs/M7-SECURITY-AUDIT-DESIGN.md`).

### Create (P)

- Persist request: candidate, owner wallet, amount, **bill title**, Panelty **code 100** when title is `Panelty Fee`.
- Store `bill_code=101` as **lineage metadata only** (A02-06). Do not use 101 as bill type.
- **No journal.** No wallet change.
- Audit: `payment_request.created`.
- Idempotency: do not invent uniqueness beyond dump (12 duplicate `(candidate_id, bill_title)` pairs exist). **New** uniqueness policy for `(candidate, bill_type)` is **not** owner-approved except as implementation-time confirmation. Historical duplicates stay in staging.

### Approve (P→A)

Single serializable transaction:

1. Lock wallet account.
2. Assert status still `P` and idempotency key unused.
3. Assert sufficient **available EUR**.
4. Insert **balanced** fee journal **once** (wallet DEBIT / FEE_INCOME CREDIT in EUR).
5. Set request `A` and `journal_id`.
6. Write `legacy_key_map` if this is a migrated/replayed row; for live ops, audit + journal source ids.
7. Update projection if used.

**Must not:** mutate cache as authority; post a second journal; commit journal without status or status without journal.

### Reject (P→R)

Single serializable transaction:

1. Lock request.
2. Assert still `P`.
3. Set `R`.
4. Post **FEE_REJECTION_ZERO** journal (A18). Amount 0 / 0.
5. Audit: `payment_request.rejected`.

Do not decrement available balance (zero). Do not skip the zero journal.

### Duplicate approval protection

- Unique `idempotency_key` per request-approve command.
- Unique `(payment_request_id)` on posted `FEE_APPROVAL` journals.
- Second approve returns existing `journal_id` (no new lines).

---

## 3. Fee debit

**Currency = EUR** (A02-01). New fee posts are EUR; `base_amount = amount` with `R=1` when already EUR.

### Panelty Fee

| | |
|---|---|
| Bill title | `Panelty Fee` (legacy spelling) |
| Bill code (target) | **100** |
| Debit | WALLET_LIABILITY (agent or sub-agent) |
| Credit | FEE_INCOME (bill type 100) |
| Candidate | `payment_request.candidate_id` — relationship is the **request**, not buggy `admission_payment_id` (A01-08) |
| FX | Fee is EUR; identity `EUR = A` |
| Audit | `payment_request.approved` + `journal.posted` |
| Idempotency | `fee-approve:{payment_request_id}` |

Dump **12** is a **row count**, not a code.

### Other dump titles (no invented numeric codes)

`Manpower Fee`, `Final Group Approval`, `Medical Fee`, `Admission Group Approval` remain **legacy title strings** until a later explicit code decision. Posting still uses EUR and the same accounts, distinguished by stored title / future code. **Do not** assign them 100 or 101.

FIN-BUG-01/02: do not silently “fix” candidate FKs or WF-09 tautology in this freeze. Linkage correction remains an implementation-time item, not authorized by this document.

---

## 4. Wallet deposit (CR)

Legacy: create CR `status=P` with form `currency_id`; approve stores `exchange_rate` and used `amount/rate` on cache.

Target approve:

- Journal: DEBIT DEPOSIT_CLEARING / CREDIT WALLET_LIABILITY.
- If source is BDT: `EUR = BDT / R` with stored `R`.
- If source is EUR: `EUR = A`, `R=1`.
- **New** posts: `R` from authorized administrative rate-entry (**DR-FX1**). Fees EUR use `R=1`.
- Historical migrated deposits keep stored currency and `payments.exchange_rate` as `R` (not rewritten). One reconstructed journal per qualifying `payments` row; that row is **not** also an opening post (**DR-H1**).

---

## 5. Teacher payment flow

**A02-04 RETAIN** — business requirement only. Not implementation authorization.

Verified:

- FIN-BUG-05 uses `$agent_id` on the teacher branch.
- `payments.teacher_id` nonzero = **0**.
- `TeacherPaymentAction` display hook is empty.

Target boundary:

- Keep a **Teacher** owner type in the design vocabulary.
- Do **not** create teacher wallets from the dump.
- Do **not** invent payment rows or balances.
- **Requires implementation-time mapping/profile confirmation** before any teacher journal, UI, or grant.

FIN-BUG-05 correction (use `teacher_id`) is part of that mapping, not a silent fix in this gate.

---

## 6. bill_code=101

**A02-06 CONFIRMED WITH CONDITION.**

- Preserve **lineage** that Laravel `merge(['bill_code' => 101])` ran on create.
- Persist as `legacy_bill_code=101` (or equivalent metadata) on the request / `legacy_key_map` payload.
- **Do not** assign business meaning.
- **Do not** map to `Panelty Fee`.
- **Do not** collapse into code **100**.
- **Do not** use 101 as `BillType` or posting key.

---

## FINAL STATUS

**M7 IMPLEMENTATION GATE = COMPLETE**  
**DR-H1 = APPROVED (A)**  
**DR-FX1 = APPROVED (ADMINISTRATIVE RATE ENTRY)**  
**M7 IMPLEMENTATION GO = NOT ISSUED**
