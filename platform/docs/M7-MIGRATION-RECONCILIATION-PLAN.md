# M7 — Migration and reconciliation plan

**Freeze document.** No import executed. No production journals. No M7 IMPLEMENTATION GO.

Parent: `docs/M7-IMPLEMENTATION-GATE.md`  
A01-04/05/07/09/10 binding. **DR-H1 A** / **DR-FX1** binding (`docs/M7-DR-H1-FX1-OWNER-DECISION-RECORD.md`). M13 executes import **after** GO + reconciliation sign-off (and A01-09 only if a residual opening batch exists).

---

## 1. Offline pipeline

```
Extract
→ staging
→ profiling
→ quarantine
→ approved transformation
→ transactional load
→ legacy_key_map
→ FK validation
→ reconciliation
→ sign-off
```

Finance must **not** be loaded directly into production journals without reconciliation **and** sign-off.

Residual opening journals (if any) additionally require A01-09. Qualifying approved `payments` are reconstructed as journals (**DR-H1**), not opening posts.

---

## 2. Extract / staging

- Source: dump SHA already verified in PRE-GATE.
- Read amounts as **text**; store `NUMERIC(20,6)` / rates `NUMERIC(20,8)`.
- Live `payments` and `payment_requests` → staging LIVE.
- Six backup tables → ARCHIVE only (A01-04). **Never auto-UNION.**
- R6 overlap 1,268/1,275: live **wins**; 1,268 backup rows = duplicates; **7 UNCLASSIFIED** (A01-05). No backup posting.

Each staged row keeps: source table, source PK, source payload, source hash, migration run id.

---

## 3. Historical reconstruction and A01-10 control (DR-H1 A)

**DR-H1 APPROVED:** full historical journalization.

```
qualifying live payments (status=A)
→ A01 exclusions (backups, P/R, R6 duplicates)
→ quarantine unclassifiable rows
→ one reconstructed POSTED journal per remaining qualifying row
   (stored amount, currency, payments.exchange_rate, W1 base_amount)
→ legacy_key_map + migration metadata in the same transaction
→ A01-10 control totals (by wallet and currency) as recon — NOT as opening posts
```

Anti-double-count: `reconstructed_payment_ids ∩ opening_manifest_payment_ids = ∅`.

A01-10 remains the **calculation** used by Gate D. It does **not** authorize posting those same rows as `OPENING_BALANCE`.

Residual opening (amounts **not** represented by reconstructed qualifying payments), if any:

```
identify residual (not a qualifying payments.id)
→ STAGED OPENING_BALANCE_CANDIDATE
→ explicit approval (A01-09)
→ transactional OPENING_BALANCE journals
```

Create-form cache-only balances with no `payments` row are **not** invented as journals. Cache vs ledger differences → Gate J.

Each reconstructed row keeps: source table `payments`, source PK, source payload, source hash, migration run id, wallet, currency, source amount, source exchange rate.

Do not implement the import in this gate.

---

## 4. Quarantine classes

Preserve: `source_table`, `source PK`, `source payload`, `source hash`, `migration_run_id`, `quarantine_reason`.

| Reason | Examples (PRE-GATE / A01) |
|---|---|
| Duplicate payments | R6 backup-side matches |
| Missing wallet owner | Orphan `agent_id` / `sub_agent_id` |
| Invalid currency | Not 1 or 2 |
| Invalid amount | Unparseable / null money |
| Missing source references | Approved request without living `payment_id` (R2 was 0 on dump — re-check) |
| Inconsistent statuses | P/R handling per A01-01 (only A is live money) |
| Unexplained balance differences | vs cache after A01-10; quarantine, do not silent-adjust |
| Unsupported teacher relationships | Nonzero `teacher_id` if any appear; **do not invent** wallets |
| FIN-BUG-01 FKs | 318 `admission_payment_id` vs 3 Admission titles — stage as-is, not authority (A01-08) |
| Receipt duplicates | System B; no wallet post (A01-06) |

`LIVE + QUARANTINED + ARCHIVE + UNCLASSIFIED = SOURCE`.

Never silently repair, delete, fabricate, or reassign (M5 / A01).

---

## 5. Transactional load (after GO + recon; A01-09 only if residual opening exists)

Per batch, one transaction:

1. Insert target rows (requests, reconstructed journals, residual opening journals if in-scope for that batch).
2. Insert `legacy_key_map` (`source_system`, `source_table`, `source_id`, `target_type`, `target_id`, `migration_run_id`, `source_row_hash`, `migrated_at`).
3. FK validation.
4. Assert no qualifying `payments.id` is both a reconstructed journal source and an opening-manifest contributor.
5. Commit or roll back **entire** batch.

Historical `payments` status A (qualifying) become reconstructed journals (**DR-H1 A**). They are **not** opening-balance contributors.

Rejected requests: migrate workflow status `R`. A18 zero journals for **historical** rejects remain **implementation-time** (`DR-A18H`). **New** rejects after GO post A18 (A02-05).

---

## 6. Reconciliation gates (minimum)

No finance migration sign-off without these.

| Gate | Check |
|---|---|
| **A** | Payment row count: source LIVE vs staged vs loaded |
| **B** | Payment amount totals **by currency** (stored) |
| **C** | Approved (`status=A`) payment totals vs reconstructed journal source totals |
| **D** | Reconstructed journal wallet totals (by wallet, by currency) vs A01-10 formula; **and** reconstructed payment ids ∩ opening-manifest ids = ∅ |
| **E** | Payment-request totals (count, amount, by title including Panelty 12 rows) |
| **F** | Duplicate / excluded rows (R6 1,268; 7 unclassified accounted) |
| **G** | Journal debit/credit equality (every POSTED journal; batch trial balance) |
| **H** | Per-wallet ledger balance vs reconstructed historical journals + residual opening (if any) + any posted new journals |
| **I** | FX conversion totals (W1 `base_amount` EUR vs BDT/`R` identity; historical `R` = `payments.exchange_rate`) |
| **J** | Quarantined rows: count, reasons, `LIVE+QUARANTINED+ARCHIVE+UNCLASSIFIED=SOURCE` |

R1 cache-vs-computed **must not** be used as a pass criterion for authority (cache is not truth). Cache vs ledger differences go to **J**.

R2, R5 remain diagnostic. R3/R4 are M8/System B/C.

---

## 7. Rollback (non-destructive finance)

Finance rollback **never** means deleting posted financial history.

| Situation | Action |
|---|---|
| Migration of **staging** only | Delete/truncate staging + quarantine for that `migration_run_id` |
| Failed batch before commit | Transaction abort; no journals |
| Posted journals in error | **Reversal** journals; not DELETE |
| Quarantine rollback | Re-open quarantine; do not drop source payload |
| Deployment rollback | App binary/config rollback; **leave** posted journals; compensate with reversal if needed |
| Reconciliation failure | **No sign-off**; no production promotion |

---

## FINAL STATUS

**M7 IMPLEMENTATION GATE = COMPLETE**  
**DR-H1 = APPROVED (A)**  
**PRODUCTION MIGRATION = NOT AUTHORIZED**  
**M7 IMPLEMENTATION GO = NOT ISSUED**
