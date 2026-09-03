# 01 — Complete source-to-target mapping

The normative details are in `table-mapping.csv` (one row per source table), `column-mapping.csv` (one row per source column), `source-references.csv`, and `relationships.csv`. Row counts are physical INSERT tuples, never AUTO_INCREMENT metadata.

- Source tables: **90**
- Source columns: **1245**
- Parsed INSERT rows: **1,090,286**
- Declared foreign keys: **9**
- Logical relationships: **151**

## Decision roll-up (updated — revision 2)
- KEEP: 70  (+1 from removing SPLIT of candidates)
- MERGE: 1
- SPLIT: 0  (removed — no physical split of candidates permitted)
- ARCHIVE: 8  (+1 professional_certificates KEEP→ARCHIVE with evidence)
- IGNORE: 12

> **Candidates table**: Previously marked SPLIT for optional address/profile normalization.
> Per approved correction, `candidates` is KEEP. All legacy columns and values are preserved
> as-is in migration. Future normalization is a separate approved change that must not occur
> during initial migration. The candidates table row count is 1,679 INSERT tuples (confirmed
> from dump — see candidate count verification note below); all 79 source columns map to
> the target `candidate.candidates` table. The column-mapping.csv confirms 79 columns;
> the earlier reference to "55 columns" in narrative text was an error and is corrected here.

## Candidate count verification (independently verified from dump)

| Item | Value | Method |
|---|---|---|
| Dump SHA-256 | `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8` | `Get-FileHash -Algorithm SHA256` |
| INSERT statements | 29 | `Select-String` on exact `^INSERT INTO \`candidates\`` |
| Row tuples (total) | **1,679** | Per-block line count on multi-line INSERT format (each row on its own line) |
| Duplicate IDs | **0** | All 1,679 IDs extracted and checked with `Group-Object` |
| ID range | 18 – 4,634 | Min/max of extracted IDs |
| AUTO_INCREMENT | 4,635 | `ALTER TABLE \`candidates\` MODIFY \`id\` ... AUTO_INCREMENT=4635` (line 1098079) |
| ID gaps / deleted rows | ~2,955 estimated | AUTO_INCREMENT(4635) − 1 − max_contiguous_id; gaps do not represent missing data |
| Candidate columns | **79** | `CREATE TABLE \`candidates\`` (lines 5086–5166): 79 column definitions; independently confirmed by 79 rows in `column-mapping.csv` for `source_table=candidates` |
| Total source columns (all tables) | 1,245 | `column-mapping.csv` row count (1,246 − 1 header) — consistent with 79 candidates columns |

**The row count 1,679 is confirmed as the authoritative figure from the production SQL dump.**
The earlier narrative reference to "55 columns" was an error; the column-mapping.csv was always correct at 79.

## Review protocol
1. Filter `table-mapping.csv` by decision/risk/module. 2. Review every column in `column-mapping.csv`. 3. Resolve every UNVERIFIED item before making a target constraint mandatory. 4. Attach signed business approval to the register in artifact 11.

## Special collisions
- `agenciers`/`companiers` are master counterparties with account/contact fields; `agencies`/`companies` are candidate-owned snapshots with `candidate_id`. KEEP separately; optional later matching must preserve snapshot text and lineage.
- `trainings` is profile training; `manpower_trainings` is candidate process evidence. KEEP in separate modules.
- `police_clearances` keeps the correct plural target name and owns `photo_file_path`. Separately, `arcs.acr_file_path` is the legacy ARC typo; it maps to `arc_file_id` while retaining exact source-column lineage.
- Payment backup tables are ARCHIVE only. They must not be unioned into live history absent an approved, row-level authority/deduplication decision (Approval A01).
- Voyager/BREAD tables (`data_types`, `data_rows`, `menus`, `menu_items`, `categories`,
  `pages`, `posts`, `migrations`, `translations`) are configuration/framework infrastructure
  owned entirely by the TCG Voyager third-party package. They are IGNORE as runtime target
  because they will be replaced by the new system's IAM/content/navigation design. Their
  content (permission names, menu labels) is referenced during migration mapping only.
- `failed_jobs` and `password_resets` are pure Laravel framework tables with no business
  model, no application code references, and 0 rows. IGNORE is correct.

## Zero-row table classification evidence

Per mandatory Rule 3 (00-project-rules.md): zero rows ≠ unused. Every zero-row table is
verified against model, controller, Voyager BREAD, and business evidence:

| Table | Rows | Model | Controller | BREAD | Verdict | Evidence |
|---|---|---|---|---|---|---|
| `employer_candidates` | 0 | `EmployerCandidate` — CONFIRMED | `VoyagerAjaxController::employerCandidateAddRemove()` — CONFIRMED | Not registered in data_types | **KEEP** | Active AJAX toggle route confirmed; schema drift (missing `status` column in DDL) documented in WF-05 |
| `attribute_list` | 0 | `AttributeList` — CONFIRMED | `PageController`, `VoyagerMyPanelController` — CONFIRMED | BREAD data_type exists | **KEEP** | Active profile child; currently empty may mean data lives in `candidates.attribute` JSON |
| `curricular_list` | 0 | `CurricularList` — CONFIRMED | Same profile controllers — CONFIRMED | BREAD exists | **KEEP** | Same as above |
| `expertise_list` | 0 | `ExpertiseList` — CONFIRMED | Same — CONFIRMED | BREAD exists | **KEEP** | Same |
| `interest_list` | 0 | `InterestList` — CONFIRMED | Same — CONFIRMED | BREAD exists | **KEEP** | Same |
| `skill_list` | 0 | `SkillList` — CONFIRMED | Same — CONFIRMED | BREAD exists | **KEEP** | Same |
| `professional_certificates` | 0 | No model — CONFIRMED | No controller — CONFIRMED | No BREAD | **ARCHIVE** | Fully defined DDL with user_id/country_id/file fields; no application code references; planned but abandoned feature; preserve as read-only staging table; do not discard |
| `employer_candidates` | 0 | (repeated above) | | | KEEP | |
| `failed_jobs` | 0 | No app model — framework only | No app controller | No BREAD | **IGNORE** | Laravel queue infrastructure; replaced by BullMQ in new system |
| `password_resets` | 0 | No app model — framework only | No app controller | No BREAD | **IGNORE** | Laravel auth infrastructure; replaced by new token/session design |
