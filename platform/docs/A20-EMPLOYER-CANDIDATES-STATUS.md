# A20 — `employer_candidates.status` (read-only evidence)

**Status: DECISION LOCKED** (2026-09-04)

Design Gate A20: *employer_candidates.status column addition and purpose enum values*.

The field was **not** added to the target schema. Employer-assignment tables and APIs were not created.

---

## Dump DDL (authoritative schema)

```sql
CREATE TABLE `employer_candidates` (
  `id` int(10) UNSIGNED NOT NULL,
  `employer_id` int(11) DEFAULT NULL,
  `candidate_id` int(11) DEFAULT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);
```

Indexes: primary key on `id` only. No unique `(employer_id, candidate_id, purpose)`. No foreign keys. AUTO_INCREMENT exists.

**There is no `status` column in the dump.**

INSERT data: **none** (CREATE TABLE is followed immediately by `euro_to_bdt_rates`). `table-mapping.csv` count = 0.

No BREAD `data_types` row. No menu item. No Laravel migration adding `status` under `src/database/migrations`.

---

## Application code (drift)

`VoyagerAjaxController::employerCandidateAddRemove` (POST, validated `employer_id`, `candidate_id`, `purpose`):

1. Lookup: `where candidate_id + purpose(strtoupper) + employer_id + status='A'`
2. If found: set `status = 'I'`, then **`$entityExist->delete()`**
3. If not found: `insert` employer_id, candidate_id, `purpose` uppercase, created_at — **does not write status**

`PageController::candidates` filters Rapid Interview listings:

- `whereHas('employerCandidates', purpose + employer_id + status='A')`

`CommonClass::checkEmployerCandidates` used by the public UI **does not filter status** — any remaining row matches.

Model `EmployerCandidate` has no `$fillable`, no casts, no status constant.

UI purposes (US spelling) from `candidates.blade.php`, `candidate-details.blade.php`, `layout.blade.php`:

- `FAVORITE`
- `RESERVE`
- `SELECTED`

AJAX route: `web.ajax.employer-candidate-add-remove` / admin twin. Toggle is add-or-delete, not a workflow state machine.

`selected_candidates` is a **different** table (banner/slider). Do not treat it as this join.

Reports: no active report catalog query on `employer_candidates`.

---

## Determination

| Question | Answer |
|---|---|
| Genuinely required? | **Intended by application code**, missing from dump DDL |
| Inferred from other fields? | **No.** `purpose` is the list bucket; `status` is attempted active/inactive |
| Historical/obsolete? | Partially. Remove path deletes the row after setting `I`, so persisted inactive rows would not remain |
| Code/DDL drift? | **Yes.** Queries and one write reference `status`; dump has no column and 0 rows |

Design Gate risk register already records this drift and says the new schema must add a status enum. That is an approval, not an implementation order.

---

## Decision lock (approved — not implemented)

| Item | Locked target rule |
|---|---|
| Meaning | Active vs inactive membership of an employer purpose list |
| `status` values | `A` = active; `I` = inactive/archived |
| Default | `A` |
| `purpose` values | `FAVORITE`, `RESERVE`, `SELECTED` |
| Legacy spelling | `FAVOURITE` (if seen) maps to `FAVORITE` at migration. Do not change the dump. |
| Uniqueness | Active membership is unique for the employer + candidate + purpose relationship |
| Transitions | A → I archives. Inactive rows stay historically traceable. No hard delete. |
| Audit | Status and membership changes must be audited |
| When to add the column | Only when Design Gate M4 implements employer assignment |

`purpose` is the membership bucket. `status` is lifecycle. They are orthogonal.

Zero dump rows: no data-migration risk when the column is added later.
