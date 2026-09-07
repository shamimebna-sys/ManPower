# M6 implementation — Overseas Processing

**Status:** Implemented under issued **M6 IMPLEMENTATION GO** (2026-09-07).  
**Baseline:** M1–M5. PostgreSQL 16.  
**M7 / M13 / A01–A02:** still blocked. This milestone does not execute them.

---

## Domain mapping

| Domain | Schema.table | Source |
|---|---|---|
| Medical | `candidate.medicals` | `medicals` |
| Police Clearance | `workflow.police_clearances` | `police_clearances` |
| ARC | `candidate.arcs` | `arcs` (`acr_file_path` → `arc_file_id`) |
| Labour Contract | `candidate.labour_contracts` | `labour_contracts` |
| Visa | `candidate.visa_immigrations` | `visa_immigrations` (no `country_id`) |
| Flight | `candidate.flight_schedules` | `flight_schedules` (`airlinece_name` spelling preserved) |
| License | `operations.licenses` + `operations.license_positions` | companier masters |
| Live-status lookup | `operations.live_status` | 10 dump labels |

Police Clearance ≠ ARC. Neither is renamed. Do not create competing duplicate models.

Migration: `20260907160000_m6_overseas_processing`.

---

## API endpoints

| Method | Path |
|---|---|
| GET/POST | `/api/v1/overseas/medicals` |
| GET/PATCH | `/api/v1/overseas/medicals/:id` |
| GET/POST | `/api/v1/overseas/police-clearances` |
| GET/PATCH | `/api/v1/overseas/police-clearances/:id` |
| GET/POST | `/api/v1/overseas/arcs` |
| GET/PATCH | `/api/v1/overseas/arcs/:id` |
| GET/POST | `/api/v1/overseas/labour-contracts` |
| GET/PATCH | `/api/v1/overseas/labour-contracts/:id` |
| GET/POST | `/api/v1/overseas/visas` |
| GET/PATCH | `/api/v1/overseas/visas/:id` |
| GET/POST | `/api/v1/overseas/flights` |
| GET/PATCH | `/api/v1/overseas/flights/:id` |
| GET/POST | `/api/v1/licenses` |
| GET/PATCH | `/api/v1/licenses/:id` |
| GET | `/api/v1/live-status` |
| GET | `/api/v1/live-status/:id` (candidate badge) |

No DELETE. Archive / rollback is in-place PATCH.

`activeOnly=true` is an **explicit** list filter (`status = 'A'`). List does not apply an implicit status filter. Flight rows have no status column, so that filter is not applied to flights.

---

## Permission catalogue (approved 14 keys)

These are the only M6 keys. No `*.delete`. No live-status catalogue key.

- `overseas.medical.read` / `overseas.medical.manage`
- `overseas.police_clearance.read` / `overseas.police_clearance.manage`
- `overseas.arc.read` / `overseas.arc.manage`
- `overseas.labour_contract.read` / `overseas.labour_contract.manage`
- `overseas.visa.read` / `overseas.visa.manage`
- `overseas.flight.read` / `overseas.flight.manage`
- `operations.license.read` / `operations.license.manage`

Live-status HTTP requires **at least one** `overseas.*.read` key (`requireAnyPermission`). It is not served from M4 `candidate.read` alone.

---

## Runtime grants

Three layers are distinct:

| Layer | What it is | Where |
|---|---|---|
| Approved catalogue | The 14 keys above | `FOUNDATION_PERMISSIONS` / `M6_PERMISSION_KEYS` |
| Approved runtime-grant design | G3/G7 matrix from Owner Review + Implementation GO | `M6_ROLE_GRANTS` |
| Actually applied grants | Same matrix, upserted by `ensure-permissions` | `ensure-permissions.ts` loops M4+M5+M6. Super_admin receives **all catalogue keys** (including the 14 M6 keys) in the super_admin grant loop. |

| Role | Applied M6 grants |
|---|---|
| super_admin | all 14 (via full catalogue grant, not `M6_ROLE_GRANTS`) |
| owner | all 14, global |
| administrator | all 14, global |
| employee | all 14, global |
| agent | 12 overseas keys, candidate-scoped; **no license** |
| sub_agent | 12 overseas keys, candidate-scoped; **no license** |
| agency | 12 overseas keys, candidate-scoped; **no license** |
| company | none |
| candidate | none |
| employer | none |
| teacher | none (still no `candidate.read`) |

**G7 exception:** agent / sub_agent / agency labour-contract access is an **intentional TARGET-SYSTEM authorization decision. It is not legacy parity.** Do not remove or weaken it because Voyager behaviour differed.

---

## Candidate-scope rules

Candidate-scoped M6 reads/writes use existing `candidate-access.ts`:

- Staff (`super_admin`, `owner`, `administrator`, `employee`): all candidates.
- `agent` / `sub_agent` / `agency`: bound partner legacy id vs `candidates.agent_id` / `sub_agent_id` / `agencier_id`.
- Unbound scoped users: empty list / 404 (not 403) on out-of-scope rows, matching M5.
- Missing permission: 403.
- Teacher cannot browse candidates and has no M6 keys.
- Company has M4 `candidate.read` (companier-scoped) but **no M6 grant**, so overseas and live-status HTTP are 403.

---

## License companier scope

Licenses are companier masters, not candidate documents.

- Create requires a real `companierId` (UUID). No synthetic companier.
- Staff with `operations.license.*` may list/filter/read/update across companiers (`?companierId=` is an explicit filter).
- Non-staff license access, if ever granted, is limited to `users.companier_id` (existing binding UUID). Company currently has **no** license grant, so this path is 403.
- Orphan `license_positions.license_id` NULL (Q-LP-LIC, 17 source rows) and `licenses.companier_id` NULL (Q-LIC-COMP, 1 source row) stay nullable. M6 does not repair, delete, merge, or reassign them.
- Position replace-all is part of `overseas.license.updated`. No delete events. No DELETE HTTP.

---

## `latest()`

Deterministic latest / list order:

```
ORDER BY created_at DESC, source_legacy_id DESC NULLS LAST
```

- No implicit status filter.
- Duplicate rows per candidate are allowed.
- No silent deduplication.
- No hard delete.
- Unique `source_legacy_id` is a migration/traceability key, not a latest() uniqueness rule.

---

## Workflow (A03)

M6 does **not** invent a prerequisite / state-machine engine.

- Labour: no create-time prerequisite; no gate against previous steps.
- Police: no labour prerequisite.
- Medical: independent; off the live-status waterfall.
- Visa: no police / medical / `country_id` requirement.
- ARC: independent; does not advance the badge.
- Flight: no payment prerequisite; no M7 coupling.
- Rollback: in-place PATCH only.

---

## Live-status compatibility

Read-model badge matching legacy `liveStatus()` compatibility behaviour.

Exact dump labels (do not correct spelling):

1. Registration  
2. Profile Update/CV  
3. Group Name  
4. Interview  
5. Selection  
6. Labour Contact  
7. Police Clearance & Medical  
8. VISA/Work Permite  
9. Manpower Status  
10. Flight  

Waterfall evaluated by M6:

flight → visa → police (row existence only) → labour → Rapid class group → any class group → `cvFileRef` → Registration.

- Step 5 Selection is skipped (never returned as the derived step).
- Medical is off the ladder.
- ARC is off the ladder.
- Step 9 is **not evaluated** (M7 / payment). The lookup row exists; the badge never reads payment tables.
- Badge never writes `candidates.status`.
- `overseas.status.changed` fires only when the **derived** step name/number changes after a document write.

---

## Audit events

Exactly:

- `overseas.medical.created` / `overseas.medical.updated`
- `overseas.police_clearance.created` / `overseas.police_clearance.updated`
- `overseas.arc.created` / `overseas.arc.updated`
- `overseas.labour_contract.created` / `overseas.labour_contract.updated`
- `overseas.visa.created` / `overseas.visa.updated`
- `overseas.flight.created` / `overseas.flight.updated`
- `overseas.license.created` / `overseas.license.updated`
- `overseas.status.changed`

Catalogue keys stay `operations.license.*`. Audit names use `overseas.license.*` as approved.

No `*_VOIDED`, no hard-delete events, no automatic `candidates.status` events. Payloads omit file bytes.

---

## Files

UUID `*_file_id` private references only. Public URLs, filesystem paths, and document bytes are rejected or omitted. Actual storage migration is M9 / out of M6. ARC lineage: `acr_file_path` → `arc_file_id`.

---

## Exclusions

- **Finance:** M6 handlers perform zero finance writes (no payments, invoices, wallet, ledger, manpower fee). A01/A02 remain hard blocks.
- **M7:** out of scope. Step 9 is not evaluated. No `payment_requests` access.
- **M13:** no production dump import, no `legacy_key_map` load, no orphan repair. Target tables remain compatible with `migration.legacy_key_map`.
- **selected_candidates / Step 5 implementation / government APIs / public file storage / hard delete:** out of scope.

---

## Known legacy anomalies / orphans (preserved)

| Code | Observation | M6 handling |
|---|---|---|
| Q-LP-LIC | 17 `license_positions` → missing license 1 | `license_id` nullable; no repair |
| Q-LIC-COMP | 1 license with NULL `companier_id` | `companier_id` nullable; no synthetic companier |
| Q-*-CAND | orphan candidate FKs on overseas tables | `candidate_id` nullable; staff may see; scoped users get 404 |

---

## UI

- `/app/overseas` — document list/create + badge read
- `/app/overseas/[resource]/[id]` — detail / in-place update
- `/app/licenses` — companier license list/create
- `/app/licenses/[id]` — license detail / in-place update
- Candidate detail shows the compatibility badge when the actor has an overseas read key
