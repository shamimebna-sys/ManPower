# ManPower modernization — final design gate (revision 2)

This review pack is generated solely from the read-only canonical `src/` tree and offline
SQL dump. Machine-readable CSV files are normative for complete matrices; Markdown files
explain decisions and gates.

**Revision 2** applies the mandatory corrections approved by the project sponsor:
- `candidates` SPLIT → KEEP (no physical split)
- `professional_certificates` KEEP → ARCHIVE (with evidence)
- `employer_candidates` schema drift documented (missing `status` DDL column)
- Workflow transitions expanded to 10 fields per transition (21 transitions total)
- Finance gate hardened: A01 and A02 are HARD GATES on all financial writes
- 8 mandatory project rules added (`00-project-rules.md`)
- 7 migration rules added (`10-api-migration-testing.md`)
- 20 open approval items (5 new: A16–A20)

## Index

### 🔴 Mandatory project rules (read first)
- [00-project-rules.md](00-project-rules.md) — **NEW** 8 non-negotiable rules; must be read before any implementation work

### Table mapping
- [01-table-mapping.md](01-table-mapping.md) — Decision roll-up (KEEP=70, ARCHIVE=8, IGNORE=12, MERGE=1, SPLIT=0)
- [table-mapping.csv](table-mapping.csv) — Normative per-table decisions
- [column-mapping.csv](column-mapping.csv) — Per-column transform rules
- [source-references.csv](source-references.csv) — Model/controller/helper/report refs

### Relationships
- [02-relationships.md](02-relationships.md) — 9 declared FKs, 151 logical relationships
- [relationships.csv](relationships.csv)

### Legacy ID mapping
- [03-legacy-id.md](03-legacy-id.md) — `legacy_key_map` design; traceability rule

### Workflow
- [04-workflow.md](04-workflow.md) — Evidence basis, gaps, and compatibility rules
- [workflow-transitions.csv](workflow-transitions.csv) — **EXPANDED** 21 transitions × 10 fields each

### RBAC
- [05-rbac.md](05-rbac.md)
- [rbac-permissions.csv](rbac-permissions.csv)
- [rbac-role-grants.csv](rbac-role-grants.csv)
- [rbac-hardcoded-role-ids.csv](rbac-hardcoded-role-ids.csv)

### Finance (HARD GATE: blocked until A01 + A02 approved)
- [06-finance.md](06-finance.md) — **EXPANDED** mandatory gate, 9 known bugs, reconciliation queries
- [finance-operations.csv](finance-operations.csv)

### Reports
- [07-reports.md](07-reports.md)
- [report-catalog.csv](report-catalog.csv)

### Documents
- [08-documents.md](08-documents.md)
- [document-fields.csv](document-fields.csv)

### Schema design
- [09-postgresql-prisma.md](09-postgresql-prisma.md)

### Migration architecture, rules, and acceptance criteria
- [10-api-migration-testing.md](10-api-migration-testing.md) — **EXPANDED** 7 migration rules + acceptance criteria checklist

### Milestones, risks, approvals
- [11-milestones-risks-approvals.md](11-milestones-risks-approvals.md) — **EXPANDED** 8 project rules summary, 20 open approvals, A01/A02 hard gate

### Metrics
- [evidence-metrics.json](evidence-metrics.json) — Updated decision counts
- [evidence-register.md](evidence-register.md)

## Gate posture (revision 2)

The design is evidence-complete at source inventory level. PROPOSED target rules are
intentionally non-mandatory where evidence is UNVERIFIED.

**Hard gates before M1:**
- A01 and A02 must be resolved before any finance schema writes
- 8 mandatory project rules must be acknowledged by all team members before coding begins

**Open approvals:** 20 items total (see `11-milestones-risks-approvals.md`). Items A01 and
A02 are HARD GATES. Items A03–A20 block their respective modules but not project start.

No raw row-level personal data, secrets, credential hashes, or tokens are included in
any artifact in this pack.
