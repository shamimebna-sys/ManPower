# 00 — Mandatory Project Rules

These rules govern every design, implementation, migration, and operational decision in this
project. They are non-negotiable and override local engineering convenience. Any exception
requires a signed approval recorded in the open approval register (artifact 11).

---

## RULE 1 — Legacy Parity

**No business functionality may be removed, simplified, or silently changed because it is old,
poorly designed, technically inconvenient, or apparently unused.**

Any removal, merge, split, behavior change, or deprecation requires:

1. Explicit written evidence that the feature is confirmed unused (code reference count AND
   production data count AND stakeholder confirmation).
2. A named business decision in the approval register (A01–A15 or a new entry).
3. A documented rationale and effective date.
4. A migration note so historical records remain consistent with the old behavior until
   cutover is signed off.

This rule applies to ALL of the following without exception:

- Candidate data, fields, and status values
- Workflows, lifecycle stages, and live-status labels
- Roles and permissions (including roles 105/107 that currently have zero grants)
- Reports, filters, columns, totals, export formats, and PDF layouts
- Invoices, invoice lines, money receipts, ticket invoices, ticket receipts
- Payments, payment requests, approval flows, bill titles
- Documents and file types
- Notifications and announcement targeting
- Class groups, exam results, and group promotion rules
- Replacement and inactivation behavior
- PDF/print outputs (all 6 confirmed print actions)
- Search and filter behavior on every BREAD and custom route
- All confirmed business modules

**Evidence label on any deprecation decision: CONFIRMED UNUSED (evidence) + APPROVED.**
Anything not carrying that label remains active.

---

## RULE 2 — Legacy Traceability

**Every migrated business record must remain traceable to its exact legacy source table and
source ID through the `legacy_key_map` mechanism for the lifetime of the system.**

Requirements:

- Every INSERT into a target business table produced by migration creates a corresponding
  `legacy_key_map(source_system, source_table, source_id, target_type, target_id, ...)` row.
- No migration job may commit target rows without simultaneously committing their lineage map
  rows in the same database transaction.
- No migration may create a business record without a traceable source — if a record is
  created de novo (e.g. a synthetic opening-balance entry), its `source_type` must be
  `SYNTHETIC` with a documented justification and approval reference.
- MERGE/SPLIT decisions produce multiple `legacy_key_map` rows (all source IDs → one target,
  or one source ID → multiple targets). All lineage paths are preserved.
- The lineage map must be queryable in the production system even after legacy decommission.

---

## RULE 3 — Zero-Row ≠ Unused

**A table with zero rows is not automatically unused, obsolete, or safe to discard.**

Before classifying any zero-row table as ARCHIVE or IGNORE, verify all of:

- Dedicated Laravel/Eloquent model exists?
- Eloquent relationship references in other models?
- Controller/service/helper references?
- Voyager BREAD data_type registration?
- Public or admin route that inserts/queries the table?
- Business meaning documented by stakeholder?
- Historical data potentially deleted (AUTO_INCREMENT gap)?

Classification must be KEEP (business table, even if currently empty), ARCHIVE (evidenced as
superseded or staging), or IGNORE (framework infrastructure only — e.g. `failed_jobs`,
`password_resets`, `migrations`). IGNORE is reserved for tables that are entirely owned by a
third-party framework and have no business data or business-code references.

---

## RULE 4 — Finance Migration Gate

**No financial record becomes authoritative in the new system until approvals A01 and A02
are explicitly granted.**

Until those approvals exist:

- All payment, payment request, invoice, ticket invoice, receipt, and ledger data remains in
  a read-only staging area within the migration schema.
- No balance calculation, ledger posting, or reconciliation report becomes a production
  output.
- No backup payment table (payments_backup_*, payments_bk, payment_requests_backup_*) is
  unioned or merged into live financial history.
- No FX rate is applied to historical records using an unapproved formula.
- No rounding rule is applied to historical amounts without documented approval.
- The migration tooling must emit a reconciliation report for human review before any
  financial posting is activated.

---

## RULE 5 — No Silent Defect Reproduction

**Confirmed source defects must not be silently reproduced in the new system.**

For each confirmed defect (e.g. `bill_title =` assignment bug, tautological manpower query,
zeroed agent-report metrics, broken CSV export):

1. The defect is documented in the finance spec or report catalog with label KNOWN BUG.
2. The corrected behavior is proposed in the PROPOSED NEW column.
3. The correction is not applied until a business decision is recorded (REQUIRES APPROVAL or
   APPROVED) confirming the old behavior was not intentional.
4. Until approval, the migration preserves source data as-is so reconciliation remains valid.

---

## RULE 6 — Modular Monolith

**The target system is a modular monolith. No microservices are to be introduced.**

PostgreSQL schemas (`iam`, `candidate`, `workflow`, `partners`, `finance`, `documents`,
`communications`, `reporting`, `migration`, `audit`) are organizational boundaries within a
single database and single application process. They must not become separate services,
separate databases, or separate deployment units without an explicit architectural decision
and sign-off by the steering committee.

---

## RULE 7 — No Hard-Coded Numeric Role IDs at Runtime

**The new application code must never reference legacy numeric role IDs (1, 2, 100–110) as
runtime logic.**

These IDs are preserved only in:
- The `legacy_key_map` table for compatibility lookups.
- The RBAC compatibility mapping (readable by migration tooling only).
- Documentation artifacts.

All runtime authorization must use stable string permission keys as defined in
`05-rbac.md` and the permission design.

---

## RULE 8 — No Production Data Modification

**The legacy Laravel application and the legacy MariaDB/MySQL production database must not be
modified in any way during development, testing, migration rehearsal, or cutover preparation.**

Read-only access to a sanitized copy of the production database is the only permitted form
of interaction. Production credentials must never appear in any artifact, config file, test,
or source control entry. If a staging clone is needed, it must be derived from an anonymized
export and kept in a separate environment with restricted access.

---

*These rules are in force from the date of initial design-gate approval and supersede any
conflicting local engineering decision.*
