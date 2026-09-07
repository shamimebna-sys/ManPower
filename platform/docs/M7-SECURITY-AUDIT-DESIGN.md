# M7 — Security, audit, and concurrency design

**Freeze document.** Conceptual only. **Do not add permission keys or runtime grants.**

Parent: `docs/M7-IMPLEMENTATION-GATE.md`

---

## 1. Authorization (categories, not keys)

Do **not** add catalogue entries in this gate. Do **not** use numeric legacy role IDs (`101`, `104`, `109`, …). Use M2 stable role keys when grants are later designed.

Conceptual categories:

| Category | Intent | Typical scope |
|---|---|---|
| **finance read** | Read journals, requests, recon reports | Org / assigned partners |
| **wallet read** | Read derived wallet balances and history | Own agent/sub-agent or administered set |
| **payment request create** | Create P requests | Candidate in actor scope |
| **payment request approve** | P→A + journal | Finance approver; not self-serve cache debit |
| **payment request reject** | P→R + A18 zero journal | Same as approve |
| **journal read** | Read posted/reversed journals | As finance read |
| **reconciliation read** | Read gates A–J and quarantine | Finance admin / recon role |
| **finance administration** | Residual opening-balance **sign-off**, reversal **initiate**, quarantine resolve, **administrative FX rate entry** (DR-FX1) | Named finance admin; dual-control recommended at GO time |

No `*.delete` for posted journals (platform rule: no `*.delete` pattern for destructive finance).

Teacher flow: **no grants** until implementation-time mapping confirmation (A02-04 is requirement only).

Ajax finance routes without `authorize()` are a **known legacy defect**. Target must use explicit RBAC + resource scope. Not implemented here.

---

## 2. Concurrency / idempotency

Protect against: double approval, duplicate posting, concurrent wallet debit, retry after timeout, duplicate request submit, races.

Conceptual controls (implement later):

| Risk | Control |
|---|---|
| Double approve | Unique `(payment_request_id, entry_type=FEE_APPROVAL)`; unique `idempotency_key`; status check `P` inside lock |
| Duplicate posting | Same unique constraints; retries return existing `journal_id` |
| Concurrent debit | `SELECT … FOR UPDATE` (or equivalent) on `WalletAccount` **before** sufficiency check and insert |
| Retry after timeout | Client sends stable idempotency key; server is insert-or-fetch |
| Duplicate payment request | Historical duplicates **retained** in staging; new unique policy **requires implementation-time confirmation** |
| Partial commit | One DB transaction: journal + lines + request status + projection + audit |
| Isolation | Serializable or equivalent documented at GO |

Do **not** implement constraints in this task.

---

## 3. Audit

Every financial state transition has append-only audit (existing platform audit design). Minimum events:

| Event |
|---|
| `payment_request.created` |
| `payment_request.approved` |
| `payment_request.rejected` |
| `journal.posted` |
| `journal.reversed` |
| `historical_journal.imported` (reconstructed qualifying `payments`; DR-H1) |
| `opening_balance.imported` (staged residual only) |
| `opening_balance.approved` (A01-09 residual) |
| `fx_rate.entered` (administrative new-post `R`; DR-FX1) |
| `reconciliation.completed` |
| `quarantine.created` |
| `quarantine.resolved` |

Each record: **actor**, **timestamp**, **source/reference**, **entity type/id**, correlation/idempotency keys, reason where applicable.

Workflow events remain append-only (`workflow.events`) for request status; they do not replace journals.

---

## 4. Rollback vs audit

Reversal creates **new** audit (`journal.reversed`) plus the reversing journal. Audit rows are never deleted to “undo” finance.

---

## FINAL STATUS

**M7 IMPLEMENTATION GATE = COMPLETE**  
**DR-H1 = APPROVED (A)**  
**DR-FX1 = APPROVED (ADMINISTRATIVE RATE ENTRY)**  
**FINANCE RUNTIME GRANTS = NOT AUTHORIZED**  
**M7 IMPLEMENTATION GO = NOT ISSUED**
