# 11 — Milestones, risks, and approval register

## M0–M15

### M0 Audit+baseline
- Outputs: frozen source/dump hashes, 90-table/1,245-column map, row/FK baseline, evidence and approval registers.
- Tests: parser repeatability; table/column completeness; no-PII artifact scan.
- Acceptance gate: current evidence totals reconcile and every discrepancy/UNVERIFIED item has an owner.

### M1 Foundation
- Outputs: PostgreSQL environments/schemas, deployment pipeline, observability, secrets and backup standards.
- Tests: migration rollback, restore drill, health checks, timezone/config tests.
- Acceptance gate: platform/security owners approve recoverability and environment parity.

### M2 Auth/RBAC/Audit
- Outputs: 13 stable role keys, 246 permission mappings, 714 grants, row scopes, append-only audit design.
- Tests: role × permission × route × row-scope matrix; document/report denial tests; audit tamper tests.
- Acceptance gate: all numeric-role compatibility mappings and exceptions are approved.

### M3 Candidate
- Outputs: candidate aggregate, profile/reference data, exact legacy lineage, status compatibility.
- Tests: 1:1 reconciliation, uniqueness/orphan checks, sensitive-field access and lifecycle tests.
- Acceptance gate: candidate owners approve fields, requiredness, duplicates and status semantics.

### M4 Recruitment
- Outputs: agent/sub-agent, agencier/companier masters, candidate agency/company snapshots, selection/employer flows.
- Tests: ownership/scope, matching without destructive merge, recruitment transition and report parity.
- Acceptance gate: master-versus-snapshot decisions and actor responsibilities are signed.

### M5 Training/Exam
- Outputs: separate profile training and manpower training modules, classes/schedules/exams/results.
- Tests: schedule ordering/conflicts, result publication, Teacher Schedule and Exam Result Sheet parity.
- Acceptance gate: training/exam owners approve domains, grading and publication rules.

### M6 Overseas Processing
- Outputs: medical, police clearance, ARC, visa, labour contract, license and flight workflows/documents.
- Tests: prerequisite transitions, expiry/date rules, live-status compatibility, document access.
- Acceptance gate: operations approves every transition, naming rule and exception path.

### M7 Finance
- Outputs: immutable balanced ledger, payment-request workflow, decimals/rates, reconciliation and reversal design.
- Tests: double-entry properties, idempotency/concurrency, rounding, opening balances, backup exclusion.
- Acceptance gate: Finance signs authoritative sources, balances, rounding and correction policy.

### M8 Invoice/Receipt/Ticket
- Outputs: versioned invoices/lines, receipts, allocations, ticket sub-ledger and print contracts.
- Tests: totals/tax/allocation, issue/void/reversal, numbering, all four print-output golden tests.
- Acceptance gate: Finance/Operations sign monetary and document parity.

### M9 Documents
- Outputs: private object metadata, checksums/MIME/size, Voyager JSON extraction, access/retention/orphan rules.
- Tests: byte checksum/size parity, MIME sniffing, malware workflow, authorization and orphan quarantine.
- Acceptance gate: Security/Legal approves classifications, retention and unresolved missing/orphan treatment.

### M10 Reports
- Outputs: 17 ACTIVE DB reports, 6 ACTIVE print outputs, legacy archive catalog and replacement contracts.
- Tests: golden rows/columns/totals/order/filters; PDF/Excel/CSV; authorization; empty/large datasets.
- Acceptance gate: each report has an owner and signed ACTIVE/LEGACY disposition with accepted defects fixed.

### M11 Notifications
- Outputs: notification/audience model, retention/partitioning, delivery preferences and retry/idempotency.
- Tests: volume/performance, duplicate delivery, retry/dead-letter, privacy and retention.
- Acceptance gate: Product/Legal approves retention and delivery semantics for dominant-volume tables.

### M12 Dashboard/Admin
- Outputs: scoped dashboards, admin/reference management, Voyager/BREAD replacement decisions.
- Tests: widget role visibility, admin CRUD policy, accessibility, performance and audit coverage.
- Acceptance gate: Product/Security approves every administrative capability and archived Voyager feature.

### M13 Migration
- Outputs: repeatable staging/transforms/loaders, lineage map, quarantine, files, manifests and reconciliations.
- Tests: two full rehearsals, restart/idempotency, 90-table/1,245-column/1,090,286-row accounting, FK/orphan checks.
- Acceptance gate: zero unexplained deltas; every signed exception is bounded and reversible.

### M14 Testing/UAT
- Outputs: integrated test evidence, UAT scripts/results, security/performance review and cutover rehearsal.
- Tests: end-to-end workflows, RBAC, finance, reports, documents, failure/rollback and operational support.
- Acceptance gate: business, security, finance and engineering sign production readiness.

### M15 Cutover
- Outputs: approved write freeze, final delta load/reconciliation, traffic switch, rollback point and hypercare.
- Tests: smoke/reconciliation/monitoring, backup verification, rollback trigger and support runbook.
- Acceptance gate: steering committee signs final metrics; legacy remains read-only until decommission approval.

## Mandatory project rules (as of design-gate approval)

See `00-project-rules.md` for the full, normative text of all 8 rules. Summary:

| Rule | Name | Impact |
|---|---|---|
| Rule 1 | Legacy Parity | No removal or simplification without approval and evidence |
| Rule 2 | Legacy Traceability | Every migrated record has a `legacy_key_map` entry in the same transaction |
| Rule 3 | Zero-Row ≠ Unused | Zero rows does not permit IGNORE; evidence required for every decision |
| Rule 4 | Finance Migration Gate | No finance writes until A01 and A02 are approved |
| Rule 5 | No Silent Defect Reproduction | Confirmed bugs documented and corrections require approval |
| Rule 6 | Modular Monolith | No microservices; PostgreSQL schemas are module boundaries only |
| Rule 7 | No Hard-Coded Role IDs at Runtime | Stable string permission keys; numeric IDs in legacy_key_map only |
| Rule 8 | No Production Data Modification | Read-only access to anonymized copy; no prod credentials in artifacts |

## Principal risks and mitigations

| Risk | Mitigation |
|---|---|
| Nine declared FKs vs 151 logical relationships | Profile logical set; defer target constraints until data is loaded and validated |
| Float money / mutable balances | NUMERIC(20,6); immutable ledger; signed reconciliation before A01/A02 sign-off |
| Backup payment tables overlap | ARCHIVE separately; never union without A01 authority/dedup decision |
| Dominant notification volume (475k+) | Partition/archive strategy; retention approval required before M11 |
| Hard-coded role IDs in legacy code | Stable string keys in new system; compatibility shim; exhaustive policy tests |
| File JSON/path drift in document fields | Tolerant parser; quarantine; checksum; orphan inventory before M9 |
| Unknown timezone/status semantics | Compatibility adapter; no mandatory assumptions; confirm via A08 |
| Sensitive dump/source handling | Generated artifacts contain metadata only; no row-level PII, secrets, or tokens |
| employer_candidates schema drift | DDL has no `status` column; code queries `status='A'`; new schema must add `status` enum; legacy 0 rows means no data migration risk |
| liveStatus() step-5 gap | Selection (step 5) is SKIPPED in legacy runtime; new workflow engine must add it; backfill rule requires A03 |
| finance bill_title assignment bugs | 3 confirmed assignment-not-comparison bugs; new system must use bill_title_code enum; correction requires A01/A02/A03 |
| professional_certificates abandoned | DDL exists, 0 rows, no model; reclassified ARCHIVE; preserve in staging; no business data lost |
| Candidates table split temptation | CONFIRMED: no physical split during migration; all 79 columns preserved (79 confirmed from CREATE TABLE DDL in dump); any normalization is a future approved change |

## Explicit business approval register

| ID | Decision requiring approval | Blocks | Status |
|---|---|---|---|
| **A01** | **Authoritative live payment table; backup table exclusion or inclusion rules; deduplication authority** | **M7 Finance (all write paths); WF-17, WF-20, WF-21** | **OPEN — HARD GATE** |
| **A02** | **Currency minor units; FX rate source; rounding rules; opening balance calculation method; historical FX formula** | **M7 Finance (all FX/balance logic); WF-17, WF-20** | **OPEN — HARD GATE** |
| A03 | Candidate transition preconditions, actors, prerequisites, step-5 selection backfill, employer_candidates status enum | M3 Candidate, M6 Overseas, WF-05, WF-11 | OPEN |
| A04 | Exact live-status compatibility labels/codes and future label retirement schedule | M3, M10 Reports, all workflow adapters | OPEN |
| A05 | Agency/company master vs candidate snapshot matching; when and how to reconcile | M4 Recruitment | OPEN |
| A06 | Profile training vs manpower training semantics; shared vs separate training history | M5 Training | OPEN |
| A07 | Role stable-key names, row scopes, report/document permission grants | M2 Auth/RBAC | OPEN |
| A08 | Legacy application timezone and DST interpretation for stored timestamps | M13 Migration, M3 | OPEN |
| A09 | File retention, classification, orphan handling, malware quarantine policy | M9 Documents | OPEN |
| A10 | ACTIVE/LEGACY disposition and named owner for every report and print action | M10 Reports | OPEN |
| A11 | Data-quality exceptions: which nullable source fields may become required in target | M13 Migration | OPEN |
| A12 | Notification retention, partition, and archive horizon | M11 Notifications | OPEN |
| A13 | Voyager pages/posts/settings/menu content: rebuild or archive | M12 Dashboard/Admin | OPEN |
| A14 | Police-clearance/ARC naming and external API/contract compatibility | M6 Overseas Processing | OPEN |
| A15 | Cutover strategy, write freeze duration, rollback trigger conditions, legacy decommission date | M15 Cutover | OPEN |
| A16 | bill_title_code enum values and correction of FIN-BUG-01, FIN-BUG-02 linkage logic | M7 Finance, WF-09, WF-20 | OPEN |
| A17 | Teacher payment flow (FIN-BUG-05): in-use or deprecated? | M7 Finance | OPEN |
| A18 | Zero-DR noise row on fee rejection (FIN-BUG-06): confirm not intentional, approve removal | M7 Finance | OPEN |
| A19 | Ticket invoice versioned-update strategy (FIN-BUG-07): approve no-delete approach | M8 Invoice | OPEN |
| A20 | employer_candidates.status column addition and purpose enum values (FAVOURITE/RESERVE/SELECTED) | M4 Recruitment, WF-05 | OPEN |

> **A01 and A02 are HARD GATES.** No financial record becomes authoritative in the new
> system and no balance, posting, or ledger write occurs until both are explicitly resolved
> with a named approver, date, and written decision recorded in this register.

Approval means: named approver, approval date, decision text, conditions, and link to evidence artifact. OPEN items block only their affected modules. Evidence-preserving staging is always permitted regardless of approval status.
