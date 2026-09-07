# M7 — Target finance design

**Freeze document.** No Prisma. No code. No M7 IMPLEMENTATION GO.

Parent: `docs/M7-IMPLEMENTATION-GATE.md`

---

## 1. Scope

M7 implements **System A** only (agent / sub-agent wallet and candidate fee requests).

| System | Legacy | M7 writes? |
|---|---|---|
| **A** | `payments`, `payment_requests`, `sub_agent_payment_requests`, cached `agents.balance` / `sub_agents.balance` | **Yes**, after M7 IMPLEMENTATION GO |
| **B** | `invoices`, lines, heads, `invoice_candidates`, `invoice_money_receipts` | **No** — M8 |
| **C** | `ticket_invoices`, ticket lines, ticket receipts | **No** — M8 |

Do not invent settlement between A and B/C. Do not invent partner (`agenciers` / `companiers`) ledgers (dump balances are zero; A01: do not invent transactions).

---

## 2. Architecture principles (locked)

- Append-only **double-entry** journals.
- Journal **header** + journal **lines**.
- Explicit debit / credit.
- Posted entries are **immutable**. Correction = **reversal + replacement**.
- Money `NUMERIC(20,6)`; rates `NUMERIC(20,8)`; ISO `BDT` / `EUR`.
- Historical transactions retain historical currency and rate (A02-01 / A02-02).
- Cached wallet columns are **not** authority (A01-02).
- All finance writes are **one database transaction**.
- Every migrated row has `migration.legacy_key_map` in the same transaction (A01-07, Rule 2).

---

## 3. Conceptual entities (not Prisma)

These names are **design freeze**. They are not schema files.

| Entity | Role |
|---|---|
| **Currency** | ISO master: `BDT`, `EUR` only. Legacy map 1→BDT, 2→EUR. |
| **BillType** | Stable fee identity. **Panelty Fee = 100** is owner-approved. Other titles remain legacy strings until separately coded. |
| **Wallet** | Ownership envelope: one owner (agent or sub-agent; teacher slot reserved — see teacher). |
| **WalletAccount** | Per wallet **and currency**. Balance is derived from posted lines. |
| **JournalEntry** | Posted header: status POSTED or REVERSED pointer; source type/id; idempotency key; actors; timestamps. |
| **JournalLine** | Debit or credit; account; currency; amount; FX metadata; `base_amount` (EUR). |
| **PaymentRequest** | Workflow aggregate (not the ledger). Status P / A / R. Posts **once** on approve (and A18 zero journal on reject). |
| **FxRateObservation** | Per-posting stored rate. Historical: `payments.exchange_rate`. New posts: authorized administrative rate-entry (**DR-FX1**). Not `euro_to_bdt_rates`. |
| **OpeningBalanceBatch** | Staged A01-10 **control totals** and any **residual** opening not represented by reconstructed payments. Qualifying `payments` are **not** opening contributors (**DR-H1 A**). Residual not live until A01-09. |
| **ReconciliationRun** | Gates A–J results; sign-off record. |
| **QuarantineRecord** | Unclassifiable / unexplained rows; `LIVE + QUARANTINED + ARCHIVE + UNCLASSIFIED = SOURCE`. |
| **LegacyKeyMap** | Existing M5 `migration.legacy_key_map` — reuse; do not duplicate. |

Do **not** add wallet types beyond evidenced owners: **agent**, **sub-agent**. Teacher is a **retained business requirement** without dump wallets (`payments.teacher_id` ≠ 0 is 0). See wallet design.

---

## 4. Posting events (System A)

After GO, a **POSTED** journal is created only by:

| Event | Legacy analogue | Journal |
|---|---|---|
| Wallet deposit **approval** | `payments` CR P→A (`paymentApproval`) | Balanced; wallet liability CREDIT in EUR via W1 |
| Candidate fee **approval** | `candidatePaymentApproval` status A | Balanced; wallet DEBIT in **EUR** (A02-01) |
| Fee **rejection** | status R + amount=0 DR (A18 **INTENTIONAL**) | Balanced **zero-amount** journal (preserve behavior; do not omit) |
| **Historical reconstruction** | Qualifying live `payments` `status=A` (**DR-H1 A**) | One POSTED journal per qualifying row; stored amount/currency/`R`; W1 `base_amount`; `legacy_key_map`. **Not** also an opening post. |
| **Opening balance** post | Residual only (amounts **not** represented by reconstructed qualifying payments) | Only after reconciliation **and** explicit approval (A01-09). Empty residual → no opening journals. |
| **Reversal** | none in live path (BREAD mutate is forbidden in target) | Linked reversing journal |

Forbidden writers: agent create-form `balance`, Voyager BREAD mutate of posted journals, invoice/ticket, backup UNION, live-status reads.

---

## 5. Implementation phases (defined, not started)

Do not begin these phases without **M7 IMPLEMENTATION GO**.

| Phase | Name | Intent |
|---|---|---|
| **M7.1** | Finance foundation | Journal/line conceptual schema, invariants, transaction helper — still no production grants |
| **M7.2** | Currency / FX | ISO master, W1 posting function, historical `payments.exchange_rate`, administrative `R` for new posts (DR-FX1) |
| **M7.3** | Wallets | Agent/sub-agent accounts; derived balance; no cache authority |
| **M7.4** | Payment requests | P/A/R workflow, idempotency, audit |
| **M7.5** | Fee posting | EUR debit; bill types; Panelty 100; A18 zero-DR |
| **M7.6** | Teacher payment flow | Design + mapping confirmation; no invented wallets |
| **M7.7** | Opening / control totals | A01-10 recon control; residual opening only, not live until signed; **no** opening post of reconstructed payment ids |
| **M7.8** | Reconciliation | Gates A–J; anti-double-count; no sign-off without pass/exception list |
| **M7.9** | Finance authorization | Permission keys only after GO; this gate names **categories** only |
| **M7.10** | Historical journal load | Reconstruct one journal per qualifying `payments` row + `legacy_key_map`; M13 executes import |

---

## 6. Out of scope (M8+)

- Employer invoices, invoice lines, invoice heads, invoice candidates, money receipts (A01-06 receipts stay System B).
- Ticket invoices, ticket lines, ticket receipts, ticket companies (FIN-BUG-07 / A19).
- Ticket varchar currency (FIN-BUG-09).
- Agent-report hardcoded zeros (FIN-BUG-08) as a report slice (M10), not a wallet write.
- Payment gateways.
- Runtime `finance.*` grants until a **separate grant GO**.
- Production cutover (M15 / A15).

---

## 7. Unresolved implementation-time items

These do **not** reopen A01/A02. They must be confirmed during design review / GO, not invented here:

| Item | Why open |
|---|---|
| Full `bill_title_code` enum except Panelty **100** | Only Panelty numeric code is owner-approved. Other titles exist as dump strings. |
| FIN-BUG-01 / FIN-BUG-02 correction | Design Gate A16 named linkage correction; closing A02 did **not** authorize implementing those fixes. |
| Teacher wallet owner mapping | RETAIN requirement; **0** dump `teacher_id` payments; lookup is broken (FIN-BUG-05). **Requires implementation-time mapping/profile confirmation.** |
| Exact chart-of-accounts codes for FEE_INCOME vs CLEARING | Conceptual accounts are frozen; numeric GL codes are not in the dump. **Requires implementation-time chart confirmation.** |
| Reserved (held) wallet balance | Not evidenced in legacy. Not in M7 unless a later decision. |
| Sub-agent request MERGE into one `PaymentRequest` aggregate | Design Gate MERGE; 1 dump row. Confirm identity of that row at mapping time. |
| Residual opening-balance **output** sign-off | A01-09: any residual (non-reconstructed) staged until reconciled and explicitly approved. Qualifying payments are journals (**DR-H1**), not this batch. |
| Administrative rate-entry **mechanism shape** | DR-FX1 selects source type. Exact screen/field/store is implementation-time. No keys/grants here. |

---

## FINAL STATUS

**M7 IMPLEMENTATION GATE = COMPLETE**  
**M7 DESIGN REVIEW = READY FOR IMPLEMENTATION GO**  
**DR-H1 = APPROVED (A)**  
**DR-FX1 = APPROVED (ADMINISTRATIVE RATE ENTRY)**  
**M7 IMPLEMENTATION GO = NOT ISSUED**  
**M7 IMPLEMENTATION = NOT AUTHORIZED**
