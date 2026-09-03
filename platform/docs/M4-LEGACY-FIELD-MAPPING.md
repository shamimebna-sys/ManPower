# M4 Legacy Field Mapping

Source dump SHA-256: `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`.

## Finalized owner mapping (release gate)

Profile child tables (`educations`, `experiences`, `skills`, `skill_list`,
`language_list`, `trainings`) store `user_id` in the source. That is the login
user, not the candidate PK.

```
legacy users.id
  = profile_child.user_id
  → users.candidate_id          (Candidate::user() is hasOne User.candidate_id)
  → migration.legacy_key_map    (source_system=manpower_mysql, source_table=candidates, source_id=users.candidate_id)
  → candidate.candidates.id     (target UUID)
```

`source_user_id` on every target child row keeps the original `users.id` for
traceability even if `users.candidate_id` is later corrected. Rows whose user
has a null `candidate_id` must be quarantined at migration time; they are not
silently attached to another candidate.

`manpower_trainings.candidate_id` already points at `candidates.id` and is out
of this domain.

No production migration is executed in M4.

## educations → candidate.educations

| Legacy | Target | Transform |
|---|---|---|
| id | legacy_key_map.source_id | string of source PK |
| user_id | candidate_id + source_user_id | remap via users.candidate_id; keep source id |
| exam_name | exam_name | exact |
| institute_name | institute_name | exact |
| subject_group_major | subject_group_major | exact |
| education_label | education_label | exact (source spelling preserved) |
| start_date / end_date | start_date / end_date | DATE; invalid/zero dates rejected at migration |
| duration_year | duration_year | integer |
| result_type | result_type | exact |
| result | result | exact |
| achievements | achievements | exact |
| certificate_file_path | certificate_file_ref | private path/JSON; no public URL |
| status | status | exact A/I; unknown kept as text |
| created_at / updated_at | created_at / updated_at | TIMESTAMPTZ UTC (A08 still open) |
| created_by / updated_by | created_by_legacy_id / updated_by_legacy_id | opaque bigint; no user FK |
| board_name | board_name | exact |
| scale | scale | exact |
| passing_year | passing_year | exact text |

## experiences → candidate.experiences

| Legacy | Target | Transform |
|---|---|---|
| id | legacy_key_map | |
| user_id | candidate_id + source_user_id | same owner remap |
| company_name | company_name | exact |
| company_address | company_address | exact |
| country_id | country_ref | **text**, not a country FK (source is varchar) |
| designation | designation | exact |
| department | department | exact |
| start_date / end_date | start_date / end_date | DATE; end may be null |
| responsibilities / expertise / descriptions | matching text | exact |
| achievements | achievements | exact |
| status / timestamps / created_by / updated_by | matching | same as education |

No salary column exists. No currently-working flag exists. Neither was invented.

## skills → candidate.skills

| Legacy | Target | Transform |
|---|---|---|
| title / institute_name / details | matching | exact |
| result_score / exam_score | result_score / exam_score | float → NUMERIC(10,2) |
| certificate_file_path | certificate_file_ref | private ref |
| remaining columns | matching | as education |

Not a tag list. Not joined to `skill_list`.

## skill_list → candidate.skill_list

| Legacy | Target | Transform |
|---|---|---|
| id | legacy_key_map | |
| user_id | candidate_id + source_user_id | owner remap |
| skill_name | skill_name | exact |
| created_at / updated_at | timestamps | TIMESTAMPTZ |
| — | status | **target-only** lifecycle A/I (source had no status) |

Dump has 0 rows. Table is still KEEP.

## language_list → candidate.language_list

| Legacy | Target | Transform |
|---|---|---|
| language_name | language_name | exact |
| language_status | language_status | exact free text; **no enum** |
| user_id | candidate_id + source_user_id | owner remap |
| — | status | target-only lifecycle A/I |

No speaking/reading/writing/listening fields exist in source.

## trainings → candidate.trainings

| Legacy | Target | Transform |
|---|---|---|
| title / institute_name / topics | matching | exact |
| start_date / end_date | dates | DATE |
| duration / duration_type | matching | integer / varchar(10) |
| country_id | country_ref | text, not FK |
| descriptions / achievements / address | matching | exact |
| certificate_file_path | certificate_file_ref | private ref |

## manpower_trainings — not implemented in M4

| Legacy field | Treatment |
|---|---|
| all columns | Deferred to the manpower/workflow milestone. Do not merge with `trainings`. |

Future destination: a workflow table, not `candidate.trainings`.
