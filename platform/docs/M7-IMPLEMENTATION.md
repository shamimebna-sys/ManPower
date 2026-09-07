# M7 implementation — System A

**Status:** implementation record (not production migration).  
**GO:** `docs/M7-IMPLEMENTATION-GO.md`  
**Does not authorize M13 / production import / A01-09 cutover sign-off.**

---

## Implementation-time decisions (bounded by freeze)

| ID | Choice | Notes |
|---|---|---|
| **DR-T1** | No teacher wallets created. Nonzero `teacher_id` on reconstruction → quarantine `UNSUPPORTED_TEACHER_RELATIONSHIP`. | Mapping confirmation not yet available. |
| **DR-G1** | Chart identities are type keys: `WALLET_LIABILITY`, `DEPOSIT_CLEARING`, `FEE_INCOME`, `OPENING_EQUITY`, `REVERSAL_CLEARING`. No invented statutory numbers. | Reversible: add numeric codes later without rewriting journals. |
| **DR-B1** | Only Panelty Fee posts with code `100`. Other titles stored as exact dump strings. | |
| **DR-F1** | New approve does **not** write `admission_payment_id` / Final / Medical FKs. Relationship is the payment request. Title/code compared with **equality**, never assignment. | Historical FKs untouched. |
| **DR-F2** | Live-status step 9 requires `billTitle === 'Manpower Fee'` **and** status `A`. No tautology. | |
| **DR-O1** | Reconstruction manifest table `finance.reconstruction_manifests`. Residual opening manifest `finance.opening_manifests`. Required columns: payment id, table, hash, run, wallet, currency, amount, rate. | |
| **DR-Q1** | Reason codes in `finance/quarantine.ts` (`QUARANTINE_REASONS`). UI lists reason + payload. | |
| **DR-P1** | Optional `projected_available_eur` on wallet, updated in the posting transaction. Sufficiency and displayed available balance are **ledger-derived**. Projection never authority. | |
| **DR-U1** | Partial unique index on pending **new** requests `(candidate_id, bill_title)` where `source_legacy_id IS NULL` and status `P`. Historical duplicates retained. | |
| **DR-K1** | Keys listed in `permission-catalogue.ts` `M7_PERMISSION_KEYS`. No `*.delete`. | |
| **DR-I1** | Posting transactions use PostgreSQL **Serializable**. | |
| **DR-A18H** | Historical rejected requests: workflow status `R` only; **no** reconstructed zero journals. New rejects post `FEE_REJECTION_ZERO`. | |
| **DR-FX1M** | `finance.fx_rate_entries`: `rate`, `effective_at`, `currency_from`/`currency_to`, `entered_by`, audit. New BDT posts require an effective administrative rate (or explicit rate id). Posted journals copy `R` onto the line. | |
| **DR-S1** | `sub_agent_payment_requests` maps into the same `PaymentRequest` aggregate (`source_table` in lineage). | |

---

## M8

Not implemented. No invoice/ticket models, APIs, UI, permissions, or migrations.

---

## Production

Reconstruction APIs/scripts operate on **caller-supplied staged rows**. They do not connect to the legacy production dump or write production cutover journals.

Historical `payment_requests` / `sub_agent_payment_requests` are imported as workflow rows only (`source_legacy_id` + `source_table`). Status `R` is stored without A18 zero journals (**DR-A18H**). Money remains the reconstructed `payments` journals (**DR-H1**). Residual opening manifests may be staged; residual opening journals remain subject to A01-09 and are not production-posted here.

Payment-request list/get is wallet-scoped for agent/sub_agent. Staff (owner/administrator/employee) is global.
