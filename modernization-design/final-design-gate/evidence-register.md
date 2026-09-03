# Evidence register

| Evidence | Result | Classification |
|---|---:|---|
| CREATE TABLE definitions | 90 | CONFIRMED |
| Source columns | 1245 | CONFIRMED |
| INSERT tuple rows (not AUTO_INCREMENT) | 1,090,286 | CONFIRMED |
| Declared FK constraints | 9 | CONFIRMED |
| Logical FK candidates | 151 | INFERRED |
| Voyager/BREAD data types | 47 | CONFIRMED |
| Roles / permissions / grants | 13 / 246 / 714 | CONFIRMED |
| DB reports / print outputs | 17 / 6 | CONFIRMED inventory; classifications mixed |

Canonical evidence is `src/`; dependencies under `src/vendor` were excluded from application-reference indexing. The SQL dump was read offline; no DB connection was used. `evidence-metrics.json` records the dump SHA-256 for provenance but no database row hashes, secrets, tokens, or row-level personal data are emitted.

## Prior-finding discrepancies

The supplied prior audit reported 1,082,959 parsed rows and eight declared FKs. The current dump independently yields **1,090,286 INSERT tuples** (a **+7,327** difference) and **9 explicit FK constraints**. A second anchored count found 1,090,333 tuple-like physical lines; the 47-line difference from the stateful count consists of tuple-like lines outside recognized INSERT blocks and is excluded. The ninth confirmed FK is `fk_candidates_replaced_by` on `candidates.replaced_by_candidate_id`. The current-file evidence is authoritative for this gate; the prior numbers are retained here as reconciliation evidence rather than silently overwritten.

Evidence labels: CONFIRMED = directly present in authorized source/dump; INFERRED = strong structural/code inference; PROPOSED = target design; UNVERIFIED = evidence absent or business confirmation required.
