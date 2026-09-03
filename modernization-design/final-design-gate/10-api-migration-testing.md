# 10 — API modules, migration architecture, testing, and migration rules

## Mandatory migration rules

The following rules are non-negotiable and apply to every migration job, rehearsal, and
staging run. They implement the mandatory project rules from `00-project-rules.md`.

### MIG-RULE-1 — Traceability is atomic
Every INSERT into a target business table **must** be wrapped in a transaction that also
INSERTs the corresponding `legacy_key_map` row(s). A migration job that inserts target rows
without lineage rows is a defect, not a warning. The migration fails and the transaction
rolls back.

```
-- Required in every business-table load transaction:
INSERT INTO migration.legacy_key_map
  (source_system, source_table, source_id, target_type, target_id, migrated_at, job_run_id)
VALUES
  ('manpower_mysql', '<source_table>', <source_pk>, '<target_entity>', <target_pk>, NOW(), $JOB_RUN_ID);
```

### MIG-RULE-2 — Candidates table: no column removal
The `candidates` migration load must include all 79 source columns. No column may be
omitted, renamed, or coerced during the initial load. Transformation rules apply only to
data types (FLOAT→NUMERIC, datetime→TIMESTAMPTZ) and encoding. Any normalization, removal,
or split of candidate columns is a future approved change that requires a separate migration
job and signed approval.

Column count evidence: CREATE TABLE `candidates` in dump (lines 5086–5166) defines exactly
79 columns (`id` through `replaced_by_candidate_id`). Confirmed independently in
`column-mapping.csv` (79 rows for source_table=candidates). The earlier figure of "55
columns" in narrative text was an error; the column-mapping.csv and DDL were always correct.

Row count evidence: 29 INSERT INTO `candidates` blocks (multi-line format), lines 5172–6879
of the authoritative dump (SHA-256:
D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8).
Total tuples counted per-block: 37+45+50+35+37+49+28+44+39+44+55+67+70+62+53+64+63+67+
61+69+76+79+78+72+74+74+76+80+31 = **1,679**. No duplicate IDs. ID range: 18–4634.
AUTO_INCREMENT=4635 (confirming ~2,955 deleted/gap IDs not in dump).

Verify before commit:
```sql
SELECT COUNT(*) FROM candidate.candidates;
-- Must equal 1679 (confirmed INSERT count from dump)
SELECT COUNT(DISTINCT legacy_source_id) FROM migration.legacy_key_map
  WHERE source_table = 'candidates';
-- Must equal 1679
SELECT COUNT(DISTINCT id) FROM candidate.candidates;
-- Must equal 1679 (no duplicates; verified against dump)
```

### MIG-RULE-3 — Finance staging only until A01 + A02 approved
All financial tables (`payments`, `payment_requests`, `sub_agent_payment_requests`,
`invoices`, `invoice_lines`, `invoice_money_receipts`, `ticket_invoices`,
`ticket_invoice_lines`, `ticket_invoice_money_receipts`, and all `*_backup_*` variants)
are loaded into the `migration` schema as read-only staging tables ONLY.

No data from these tables may be written to the `finance` schema until:
- A01 is APPROVED (authoritative source table, dedup rules confirmed)
- A02 is APPROVED (FX formula, rounding, opening balance method confirmed)

The migration job **must** fail if it attempts any write to the `finance` schema without
both approvals being recorded in the approval register.

### MIG-RULE-4 — Zero-row tables are migrated as empty but present
Tables classified as KEEP with 0 rows (e.g. `employer_candidates`, `attribute_list`,
`curricular_list`, `expertise_list`, `interest_list`, `skill_list`) must have their
corresponding target tables created and included in the reconciliation count. The
reconciliation report shows `source=0 / target=0 / delta=0` for these tables — not blank.

Tables classified as ARCHIVE with 0 rows (e.g. `professional_certificates`) are loaded
into the `migration` schema as read-only staging. They are not written to the target
business schema but they are not discarded.

### MIG-RULE-5 — No silent defect reproduction
Columns confirmed to carry buggy data (e.g. `candidates.admission_payment_id` set by
assignment bug FIN-BUG-01) are migrated AS-IS into the staging schema. The column value in
the target schema is left NULL or set to `LEGACY_BUGGY_VALUE_PRESERVED` (a nullable JSONB
column) until A01/A02/A16 are resolved. The original value is always available in the
staging schema for reconciliation.

### MIG-RULE-6 — File migration is non-destructive
Files must be copied, never moved. The source path is preserved in `legacy_key_map` as
`legacy_file_path`. After successful copy and checksum verification, the legacy path is
retained in audit for at least 3 years post-cutover.

### MIG-RULE-7 — Every rehearsal produces a signed reconciliation pack
A rehearsal is not complete without:
1. Row count per table (source vs target vs delta)
2. Lineage count per table (legacy_key_map rows vs target rows — must be 0 delta)
3. Finance staging row count vs source row count (read-only, must match exactly)
4. Document checksum delta (must be 0 for all successfully copied files)
5. A named human who reviews and signs off the pack before cutover is authorized

---

## Modules
Identity/RBAC, Candidates, Partners, Workflow, Training, Documents, Finance (staging only
until A01/A02), Invoicing (staging only until A01/A02), Travel/Flight, Medical/Compliance,
Notifications, Reporting, Reference Data, and Migration/Admin. APIs are versioned,
idempotent for commands, cursor-paginated, scoped by policy, and return legacy
ID/label compatibility only through explicit adapters. File content is never public by path.

## Migration architecture
Offline extract parser → immutable staging tables → profiling/quarantine → approved
transforms → target load (with simultaneous lineage map write) → legacy-key resolution →
deferred relationship validation → file copy/verification → reconciliation pack →
business sign-off. Each run has manifest hashes, code/config version, counts, rejects,
timings, and restart checkpoints.

Finance tables are STAGED ONLY until A01 and A02 approvals are on record.

Use dual-read/shadow comparison before cutover; dual-write only if operations can support
reconciliation. Backups are separate sources and excluded from live finance by default
until A01 approval.

## Acceptance criteria (migration complete)

- [ ] 90 source tables loaded or staged (zero missing)
- [ ] 1,245 source columns accounted for (zero omitted, zero renamed without mapping)
- [ ] 1,090,286 INSERT-tuple rows reconciled (zero unexplained delta; bounded exceptions are signed)
- [ ] 1,679 candidate rows migrated: source=1679, target=1679, lineage=1679 (verified against dump SHA-256 D6B2C811...)
- [ ] All 79 candidate columns present in target table (not 55 — narrative error corrected)
- [ ] legacy_key_map covers every business entity (0 untraceable rows)
- [ ] All 9 declared FK relationships validated post-load
- [ ] Zero orphan business records (all FKs resolve or exceptions signed)
- [ ] Finance data in staging: row count = source row count (exact)
- [ ] Finance schema: zero rows (pending A01/A02)
- [ ] All document files checksummed: copy success or quarantine (zero silently missing)
- [ ] Reconciliation pack signed by named business and engineering approvers
- [ ] All OPEN approval items either resolved or explicitly deferred with bounded risk

## Test gates
Schema parser tests; per-column transform tests; PK/FK/orphan/null/domain profiling; exact
row-count and hash reconciliation; workflow transition/property tests; role × permission ×
row-scope policy tests for all 13 roles/246 permissions; report golden datasets and totals;
document checksum/MIME/access/orphan tests; finance double-entry, idempotency, concurrency,
rounding and reversal tests (activated only after A01/A02); timezone boundary tests; API
contract/security/performance tests; rehearsal and rollback drills. Production cutover
requires zero unexplained count/sum deltas and signed exceptions.
