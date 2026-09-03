# 09 — PostgreSQL and Prisma proposal

This is design pseudocode, not an application schema.

## PostgreSQL baseline
- Schemas: `iam`, `candidate`, `workflow`, `partners`, `finance`, `documents`, `communications`, `reporting`, `migration`, `audit`.
- `timestamptz` in UTC for instants; `date` for civil dates; source timezone is an approval item. Never reinterpret timestamps before approval.
- UUID business identifiers; exact legacy lineage through `migration.legacy_key_map`.
- Deferred FK creation: load, profile orphans, resolve approvals, then add `NOT VALID`, validate, and make mandatory only where CONFIRMED.
- Audit: append-only actor/action/entity/before-hash/after-hash/correlation/reason at application and privileged DB boundaries.
- Workflow: append-only `workflow.events`; unique `(aggregate_id, sequence)` and idempotency key.
- Files: private metadata/object table plus polymorphic links and checksum index.
- RBAC: stable role/permission keys, grants, scoped assignments; unique normalized keys.
- Finance: journals/entries with balanced-posting procedure, immutable POSTED state, reversal link, `numeric(20,6)` amounts and `numeric(20,8)` rates.
- Index every validated FK; partial indexes for active/open queues; composite indexes follow confirmed filters; avoid speculative indexes.

## Constraint examples
```sql
CHECK (amount >= 0);
CHECK (status IN ('PENDING','APPROVED','REJECTED','VOID')) -- only after status approval
UNIQUE (source_system, source_table, source_id);
UNIQUE (journal_id, line_no);
CHECK ((status = 'POSTED') = (posted_at IS NOT NULL));
```
Status checks above are PROPOSED, not mandatory until value profiling and business approval.

## Prisma-shaped pseudocode
```text
model Candidate { id UUID; legacyKeys LegacyKey[]; workflowEvents WorkflowEvent[]; documents DocumentLink[] }
model LegacyKey { sourceSystem String; sourceTable String; sourceId String; targetType String; targetId UUID; @@unique([sourceSystem,sourceTable,sourceId]) }
model WorkflowEvent { aggregateId UUID; sequence Int; eventKey String; occurredAt Instant; actorId UUID?; payload Json }
model Role { key String @unique; permissions RolePermission[]; assignments RoleAssignment[] }
model Permission { key String @unique; module String; action String; resource String }
model FileObject { id UUID; objectKey String @unique; sha256 Bytes; size BigInt; mime String; classification String }
model Journal { id UUID; status JournalStatus; entries LedgerEntry[]; reversalOf UUID?; legacyKeys LegacyKey[] }
model LedgerEntry { journalId UUID; lineNo Int; accountId UUID; currency String; debit Decimal(20,6); credit Decimal(20,6) }
```

Prisma cannot itself guarantee balanced multi-row journals or immutable history; use restricted SQL procedures/triggers plus transaction tests. Complete field proposals remain in `column-mapping.csv`.
