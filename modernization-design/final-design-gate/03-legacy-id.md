# 03 — Legacy ID and lineage strategy

Every migrated business row receives a target UUID/bigint according to module convention and an immutable mapping:

```sql
legacy_key_map(
  source_system text NOT NULL,
  source_table text NOT NULL,
  source_id text NOT NULL,
  target_type text NOT NULL,
  target_id uuid NOT NULL,
  migration_run_id uuid NOT NULL,
  source_row_hash bytea NOT NULL,
  migrated_at timestamptz NOT NULL,
  PRIMARY KEY(source_system, source_table, source_id),
  UNIQUE(target_type, target_id, source_system, source_table, source_id)
)
```

Use `source_table + source_id` even where IDs overlap between live and backup tables. Child resolution joins the mapping, never assumes identical numeric IDs. Records without a declared PK use a deterministic synthetic source ID from table name + canonical row hash + duplicate ordinal; this is PROPOSED and must be approved before load. MERGE/SPLIT records may have multiple mappings through `legacy_record_component`; all business targets retain at least one exact source lineage. Hashes are stored in the migration environment, not these documents. Re-runs are idempotent by source triplet and compare source hashes before any update.
