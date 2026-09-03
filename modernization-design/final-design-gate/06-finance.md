# 06 — Finance Specification

`finance-operations.csv` is the operation-by-operation decision register. It records old
behavior, proposed behavior, parity intent, defects/risks, and required business decisions.

---

## MANDATORY FINANCE GATE

**No financial migration or posting logic may be implemented until approvals A01 and A02
are explicitly granted and recorded in the approval register.**

The following decisions are OPEN and block their respective features:

| Approval | Decision blocked |
|---|---|
| A01 | Authoritative live payment table; which backup tables (if any) contribute to opening balances; deduplication/authority rules |
| A02 | Base currency; rate source (manual entry vs automated feed); rounding rules; minor units; opening balance calculation method; historical FX formula to use |

Until A01 and A02 are resolved:

- All payment, payment_request, invoice, ticket_invoice, receipt, and backup data remains in
  a read-only staging import schema.
- No `agents.balance`, `sub_agents.balance`, or any other balance field is written in the
  new system from migration data.
- No ledger posting, journal entry, or balance calculation is activated.
- No backup table is merged, unioned, or treated as authoritative.
- The migration tooling emits reconciliation reports for human review but takes no
  write actions on the finance schema.
- Every financial row migrated carries `legacy_payment_id` and `legacy_source_table` in
  `legacy_key_map` for full traceability (Rule 2).

---

## Numeric types (approved)

| Domain | Target type | Notes |
|---|---|---|
| Money amounts | `NUMERIC(20,6)` | Handles sub-cent precision; display rounds at presentation layer |
| Exchange rates | `NUMERIC(20,8)` | 8 decimal places for EUR/BDT and similar conversions |
| VAT percentages | `NUMERIC(6,4)` | E.g. 19.0000% |
| Balance snapshots (if retained) | `NUMERIC(20,6)` | Source `FLOAT` values parsed as exact decimal text |
| Source FLOAT values | Parse as text first | Never apply binary float arithmetic to legacy amounts |

---

## Source precision risk

Every source financial table uses `FLOAT` or `float(12,0)`. Binary floating-point cannot
represent many decimal fractions exactly. During migration, every amount is:

1. Read as raw text from the SQL dump.
2. Parsed as a Python/Node `Decimal` (arbitrary precision).
3. Stored in the staging schema as `NUMERIC(20,6)`.
4. Compared to source `FLOAT` value after round-trip: any discrepancy > 0.01 is flagged
   for manual review.

No implicit rounding or truncation is permitted during extraction.

---

## Finance domain architecture (proposed — pending A01/A02)

Two parallel financial systems exist in the source and must remain separate in the target
until a business consolidation decision is made:

### System A — Agent/Sub-agent Wallet
- Source: `agents.balance`, `sub_agents.balance`, `payments` (CR/DR), `payment_requests`,
  `sub_agent_payment_requests`
- Semantics: agent deposits money (CR); candidate processing fees are debited (DR);
  balance must be non-negative before debit
- Target: immutable `ledger_entries` with `account_type=WALLET`, balanced journal per
  approved event; balance is a view/function over approved entries

### System B — Employer/Agency Billing
- Source: `invoices`, `invoice_lines`, `invoice_heads`, `invoice_candidates`,
  `invoice_money_receipts`
- Semantics: invoices issued to overseas employers (agenciers/companiers) for
  recruitment services; line items reference invoice_heads (VAT-bearing catalog)
- Target: `invoices` domain with versioned line snapshots, receipt allocations, and
  immutable print snapshots

### System C — Ticket Billing
- Source: `ticket_invoices`, `ticket_invoice_lines`, `ticket_invoice_money_receipts`,
  `ticket_companies`
- Semantics: air-ticket invoices; issuer always `ticket_company_id=1` (KNOWN: hardcoded
  in VoyagerTicketInvoiceController)
- Target: parallel ticket invoice module; same receipt/allocation model as System B;
  currency FK replaces varchar `EURO`/`TAKA` field

Systems A, B, and C share no financial flows in the legacy code. No integration between
agent wallet and employer billing was found in source. **Do not assume integration;
do not create integration without approval.**

---

## Confirmed known bugs (documented, NOT reproduced in new system — REQUIRES APPROVAL for correction)

| Bug ID | Location | Defect | Business decision needed |
|---|---|---|---|
| FIN-BUG-01 | `VoyagerAjaxController::candidatePaymentApproval():263-271` | `bill_title = 'Admission...'` uses assignment (`=`) not equality (`==`). First branch always executes. candidates.admission_payment_id may be set incorrectly for all approvals. | Confirm intended field linkage per bill_title; approve corrected comparison logic |
| FIN-BUG-02 | `CommonClass::manpowerStatus():520-527` and `mpVisaAndPaymentStatus():461-465` | `orWhereRaw('bill_title = bill_title')` is a tautology — matches all rows. Any approved payment_request for a candidate satisfies the manpower fee check. | Approve the correct filter: exact `bill_title='Manpower Fee'` or a bill_title_code enum |
| FIN-BUG-03 | `VoyagerPaymentController::store():110-119` | Admin direct CR/DR updates `balance ± amount` with no FX conversion. Wallet approval credits `balance += amount / exchange_rate`. Two different formulas for the same field. | Approve canonical balance update formula (always FX-normalize, or always use base currency amounts) |
| FIN-BUG-04 | `VoyagerAjaxController::candidatePaymentApproval():225-226` | Candidate fee debit hardcodes `currency_id = 2` on the DR payment row regardless of request currency. | Confirm currency ID 2 is always the fee currency; approve or correct |
| FIN-BUG-05 | `VoyagerPaymentController::store():103` | Teacher payment lookup uses `Agent::where('id', $agent_id)` instead of `Teacher::where('id', $teacher_id)`. Teacher payment path is broken. | Confirm whether teacher payments are in use; approve corrected lookup |
| FIN-BUG-06 | `VoyagerAjaxController::candidatePaymentApproval():208-209` | On rejection (status=R), a DR payment row is inserted with `amount=0`. Noise row with no financial effect pollutes the payments table. | Confirm whether zero-amount rejection rows were intentional; approve removal in new system |
| FIN-BUG-07 | `VoyagerTicketInvoiceController::update():171-173` | Ticket invoice update deletes the entire invoice (including lines) then calls `store()` to recreate. All historical IDs are destroyed. | Confirm whether external references to ticket invoice IDs exist; approve versioned update strategy |
| FIN-BUG-08 | `VoyagerReportController::agentReport():441-445` | Agent report metrics (balance, due_balance, selected_cand, visa_updated_cand, payment_due_cand) are hardcoded to literal `0` in SQL. | Approve correct metric queries for agent report |
| FIN-BUG-09 | `VoyagerTicketInvoice.currency` | Currency stored as varchar `'EURO'` / `'TAKA'` with no FK constraint. | Approve normalization to `currencies.id` FK |

---

## Reconciliation rules (required before A01/A02 sign-off)

The following queries must pass before any finance data is declared authoritative:

**R1 — Agent balance drift**
```sql
-- Compare cached agents.balance to sum of approved payments
SELECT a.id, a.balance AS cached,
  SUM(CASE WHEN p.type='CR' THEN p.amount / NULLIF(CASE WHEN p.currency_id=2 THEN 1
                                                         ELSE p.exchange_rate END, 0)
           WHEN p.type='DR' THEN -p.amount / NULLIF(CASE WHEN p.currency_id=2 THEN 1
                                                          ELSE p.exchange_rate END, 0)
  END) AS computed
FROM agents a
LEFT JOIN payments p ON p.agent_id=a.id AND p.status='A' AND p.sub_agent_id=0
GROUP BY a.id
HAVING ABS(COALESCE(cached,0) - COALESCE(computed,0)) > 0.01;
```
Expected result: 0 rows before any financial data is migrated.

**R2 — Orphan approved payment_requests (no matching payment)**
```sql
SELECT * FROM payment_requests
WHERE status='A' AND (payment_id IS NULL OR payment_id NOT IN (SELECT id FROM payments));
```

**R3 — Duplicate invoice_money_receipts**
```sql
SELECT invoice_id, receipt_no, amount, COUNT(*) c
FROM invoice_money_receipts
GROUP BY invoice_id, receipt_no, amount HAVING c > 1;
```

**R4 — Ticket invoice line totals vs header**
```sql
SELECT ti.id, ti.total, SUM(til.total_amount) AS line_sum,
       ABS(ti.total - COALESCE(SUM(til.total_amount),0)) AS delta
FROM ticket_invoices ti
LEFT JOIN ticket_invoice_lines til ON til.ticket_invoice_id = ti.id
GROUP BY ti.id HAVING ABS(ti.total - COALESCE(SUM(til.total_amount),0)) > 0.01;
```

**R5 — Payments with FX not applied (currency_id=1 but exchange_rate=1)**
```sql
SELECT * FROM payments WHERE currency_id=1 AND exchange_rate=1 AND status='A';
```

**R6 — Backup table deduplication (run once A01 approves inclusion)**
```sql
-- Cross-check live payments vs backup by (agent_id, amount, payment_date, type)
SELECT p.id, pb.id AS backup_id, 'POTENTIAL_DUPLICATE' AS flag
FROM payments p
JOIN payments_backup_01072026 pb
  ON p.agent_id=pb.agent_id AND p.amount=pb.amount
  AND DATE(p.payment_date)=DATE(pb.payment_date) AND p.type=pb.type;
```

---

## Opening balance strategy (BLOCKED PENDING A01/A02)

The backup tables contain rows labelled (by INFERRED convention) as "Agent Opening Balance"
or similar. These must not be automatically included without:

1. Confirmed identification of opening-balance rows (by bill_title or remarks — UNVERIFIED).
2. Verification that these rows are NOT duplicated in the live `payments` table.
3. Business sign-off that the backup-table amounts represent the authoritative opening
   position.

Until A01 is approved, opening balance rows are staged separately as
`entry_type=OPENING_BALANCE_CANDIDATE` and excluded from all balance computations.

---

## Bill titles (confirmed from dump)

| Bill title (source text) | Observed purpose | Candidate FK field (BUGGY linkage) |
|---|---|---|
| `Admission Group Approval` | Fee for admission to class group | `candidates.admission_payment_id` |
| `Final Group Approval` | Fee for final group placement | `candidates.final_group_payment_id` |
| `Medical Fee` | Candidate medical processing fee | `candidates.medical_fee_payment_id` |
| `Manpower Fee` | BMET/overseas processing fee | (no FK; detected via query — BUGGY) |
| `New Payment request` | Wallet top-up | N/A (CR, not a fee) |

**PROPOSED**: Replace free-text `bill_title` with a stable `bill_title_code` enum:
`ADMISSION_FEE`, `FINAL_GROUP_FEE`, `MEDICAL_FEE`, `MANPOWER_FEE`, `WALLET_TOPUP`.
Correction of linkage bugs (FIN-BUG-01, FIN-BUG-02) requires APPROVAL before implementation.
