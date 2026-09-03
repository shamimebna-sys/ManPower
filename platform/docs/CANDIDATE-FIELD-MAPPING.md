# Candidate field mapping (legacy `candidates` → target `candidate.candidates`)

Source evidence: dump SHA-256 `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`,
79 columns, 1,679 rows. No column is discarded.

| Legacy column | Target column | Transform | Notes |
|---|---|---|---|
| id | legacy_key_map.source_id | string of source PK | New PK is UUID. Source ID is not reused as the target PK. |
| code | code | exact text | Unique when present |
| name | name | exact text | Required on create |
| email | email | lowercased | Unique when present |
| secondary_email | secondary_email | exact text | |
| mobile | mobile | exact text | Unique when present |
| secondary_mobile | secondary_mobile | exact text | |
| emergency_mobile | emergency_mobile | exact text | |
| bid | bid | exact text | |
| bid_file_path | bid_file_ref | preserve JSON/path | Private reference only; document module later |
| nid | nid | exact text | Not uniquely constrained in confirmed create rules |
| nid_file_path | nid_file_ref | preserve path | Private reference |
| passport_no | passport_no | exact text | Unique when present |
| passport_issue_date | passport_issue_date | DATE | Invalid/zero dates rejected at migration |
| passport_expire_date | passport_expire_date | DATE | |
| passport_file_path | passport_file_ref | preserve JSON/path | Private reference |
| dob | dob | DATE | |
| full_photo_file_path | full_photo_file_ref | preserve JSON/path | Private reference |
| half_photo_file_path | half_photo_file_ref | preserve JSON/path | Private reference |
| father_name | father_name | exact text | |
| mother_name | mother_name | exact text | |
| nationality | nationality | exact text | |
| gender | gender | exact text; no enum yet | Values not fully profiled |
| blood_group | blood_group | exact text | |
| present_address_* | present_address_* | exact / bigint IDs | Geography IDs have no FK until reference data exists |
| permanent_address_* | permanent_address_* | exact / bigint IDs | Same |
| basic_info_career | basic_info_career | exact text | |
| basic_info_special | basic_info_special | exact text | |
| other_skills | other_skills | exact text | |
| balance | balance | NUMERIC(20,6) | Not posted in M3 |
| cv_file_path | cv_file_ref | preserve JSON/path | Private reference |
| status | status | exact A/P/I or LEGACY_UNKNOWN later | No hard delete |
| created_at / updated_at | created_at / updated_at | TIMESTAMPTZ UTC | Timezone interpretation still A08 |
| agent_id | agent_id | bigint | Opaque; partners module later |
| class_group_id | class_group_id | bigint | Opaque; training module later |
| remarks | remarks | exact text | |
| replacement_remarks | replacement_remarks | exact text | |
| admission_payment_id | admission_payment_id | bigint | Finance evidence only; A01/A02 gated |
| final_group_payment_id | final_group_payment_id | bigint | Same |
| medical_fee_payment_id | medical_fee_payment_id | bigint | Same |
| abroad_ex / local_ex | abroad_ex / local_ex | integer | |
| drive/facebook/youtube/linkedin/twitter/instagram_link | matching columns | exact text | linkedin was varbinary; store as text |
| position | position | exact text | |
| skill_certificate_file_path | skill_certificate_file_ref | preserve JSON/path | Private reference |
| height / weight | height / weight | exact text | Legacy default `'0'` |
| marital_status | marital_status | exact text | |
| expertise / skills / extra_curricular / interest / attribute | matching columns | exact text/JSON | Child-list tables remain a later domain |
| companier_id / agencier_id | matching columns | bigint | Opaque |
| stamp_file_path | stamp_file_ref | preserve JSON/path | Private reference |
| position_id | position_id | bigint | Opaque |
| company_status | company_status | exact text | Default ACTIVE |
| sub_agent_id | sub_agent_id | bigint | Opaque |
| country_id | country_id | bigint | Opaque |
| replaced_by_candidate_id | replaced_by_legacy_id | bigint | Stored as source ID until candidate UUIDs exist |

## Intentionally not normalized in M3

No column is omitted. Normalization of addresses, profile JSON, or payment FKs is a future
approved change. Education/experience/language/skill **child tables** are separate source
tables and are not created as runtime APIs in M3.
