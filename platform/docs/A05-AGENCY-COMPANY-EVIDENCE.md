# A05 — Agency / Company model (read-only evidence)

**Status: DECISION LOCKED** (2026-09-04)

Design Gate A05: *Agency/company master vs candidate snapshot matching; when and how to reconcile.*

Partner models, APIs, Organization/Tenant, and Design Gate M4 Recruitment were **not** implemented.

**Do not merge:** `agencies` ≠ `agenciers`. `companies` ≠ `companiers`. `employers` ≠ `companiers`.

Source: dump `docs/database-audit/u410970153_eujobbd.sql`, `table-mapping.csv`, Laravel `src/app` (read-only).

---

## Entity inventory

| Table | Rows | PK | Indexes | Columns (material) | User ownership | Classification |
|---|---:|---|---|---|---|---|
| `agenciers` | 13 | `id` | PK | code, name, email, mobile, license, VAT, address, **balance**, logo, status, country_id, owner_*, bank_*, signature | `users.agencier_id`; `candidates.agencier_id` | **A — live master** |
| `agencies` | 265 | `id` | PK | name, address, **candidate_id** | Child of a candidate | **C — snapshot** |
| `companiers` | 390 | `id` | PK | code, name, email, mobile, license, VAT, address, **balance**, logo, status, country_id, owner_* | `users.companier_id`; `candidates.companier_id` | **A — live master** |
| `companies` | 263 | `id` | PK | name, address, **candidate_id** | Child of a candidate | **C — snapshot** |
| `agents` | 40 | `id` | PK | Person/org recruiting profile + **balance** + status | `users.agent_id`; `candidates.agent_id` | **A — live master** |
| `sub_agents` | 12 | `id` | PK | Same shape as agent + required `agent_id` + balance | `users.sub_agent_id`; `candidates.sub_agent_id` | **A — live master** (child of agent) |
| `employers` | 1 | `id` | PK | Person profile (same columns as employees) | `users.employer_id` | **A — live person master** (not a company) |
| `employees` | 3 | `id` | PK | Internal staff person profile | `users.employee_id` | **A — live person master** |
| `teachers` | 2 | `id` | PK | Teaching staff person profile | `users.teacher_id` | **A — live person master** |
| `users` | 2154 | `id` | unique email; `role_id` FK | `role_id` + eight profile FKs | Login identity | Identity, not an organization |

`employer_candidates` (0 rows) is an assignment join, not a partner master. See A20.

---

## Why agencies ≠ agenciers and companies ≠ companiers

| | Master (`agenciers` / `companiers`) | Snapshot (`agencies` / `companies`) |
|---|---|---|
| Created | Sep 2025 BREAD (`data_types` 50 / 52) | May 2025 BREAD (`data_types` 30 / 31) |
| Shape | License, VAT, bank (agencier), balance, status, code | name + address + candidate_id only |
| Cardinality | 13 / 390 distinct principals | 265 / 263 rows; names repeat per candidate |
| Menu | Management → “Agencys” (`voyager.agenciers.index`), “Companies” (`voyager.companiers.index`) | **No main-menu item.** Candidate browse links to `voyager.agencies.index` are commented out |
| Controller | `VoyagerAgencierController` / `VoyagerCompanierController` | `VoyagerAgencyController` / `VoyagerCompanyController` both `where('candidate_id', request)` |
| Candidate FK | `candidates.agencier_id` / `companier_id` (opaque ids on master) | `agencies.candidate_id` / `companies.candidate_id` |
| Finance / invoice | `invoices.agenciers_id`, `invoices.companier_id`, ticket invoices, licenses | None |
| Reports | `VoyagerReportController::selectedCandidateReport`, `flightReport` | No active report catalog hit for `agencies`; companies only appear in replacement reassignment |

Example snapshot duplication: many `agencies` rows are named `DIKALO AGENCY` / `NICOSIA` with different `candidate_id`. `agenciers.id=2` is the single live `DIKALO AGENCY` principal (`code=2002`, license, IBAN).

`AuthManager` writes `Company.sub_domain`, `email`, `package_id`, `expire_at`. Those columns **do not exist** on dump `companies`. That path is leftover SaaS code and is not evidence that `companies` is a tenant master.

---

## Relationships and usage

**Candidate**

- `belongsTo` Agent, SubAgent, Agencier, Companier
- `hasMany` Agency, Company (snapshots)
- Row scope for roles 101 / 108 / 109 filters `agent_id` / `agencier_id` / `sub_agent_id` (`Candidate::boot`)
- Replacement flow copies `companier_id` and reassigns `companies.candidate_id` (`VoyagerCandidatesController` ~619)

**Users**

- One login row may point at one profile FK (`agent_id`, `sub_agent_id`, `teacher_id`, `employee_id`, `candidate_id`, `employer_id`, `agencier_id`, `companier_id`)
- Role 107 profile is `companier_id`, not `companies.id`

**Payments**

- `payments` infers `agent_id`, `sub_agent_id`, `employer_id`, `employee_id`, `teacher_id` — not `agencier_id` / `companier_id`
- Agencier/companier money appears on invoice / ticket invoice / `balance` columns (finance later; A01/A02)

**BREAD / menu**

- Separate BREAD + Voyager permissions for each of the six partner tables
- Snapshot BREADs exist; live masters are the Management menu entries

**Companier scope quirk**

- `Companier::boot` restricts role 108 (agency) to `companier_id` values already used on that agency’s candidates. That is agency-scoped visibility of company masters, not proof that `companies` snapshots are masters.

---

## Classification answers

| Concept | A live master | B user-owned | C snapshot | D duplicate name | E different lifecycle |
|---|---|---|---|---|---|
| `agenciers` | Yes | Login via `users.agencier_id` | No | Name collides with `agencies` | Licensed overseas agency principal |
| `agencies` | No | Owned by candidate | Yes | Name collides with `agenciers` | Per-candidate name/address history |
| `companiers` | Yes | Login via `users.companier_id` | No | Name collides with `companies` | Licensed overseas employer-company principal |
| `companies` | No | Owned by candidate | Yes | Name collides with `companiers` | Per-candidate name/address history |
| `agents` | Yes | Login via `users.agent_id` | No | Distinct from agencier | Recruiting partner + wallet |
| `sub_agents` | Yes | Login via `users.sub_agent_id`; `agent_id` required | No | Distinct | Agent-owned sub-recruiter + wallet |
| `employers` | Yes (person) | Login via `users.employer_id` | No | Distinct from companier | Rapid-interview shortlist actor |
| `employees` / `teachers` | Yes (staff persons) | Login FKs | No | Distinct | Internal operations / training |

---

## Decision lock (approved — not implemented)

1. **Live masters:** Agent, SubAgent, Agencier, Companier. Do not fold them into Organization, Tenant, Generic Partner, or a generic Company/Agency abstraction.
2. **Candidate snapshots:** `agencies` and `companies` stay candidate-owned name/address history. `agenciers ≠ agencies`. `companiers ≠ companies`. Do not merge.
3. **Employer** remains a separate person/profile concept for Rapid Interview lists. It is not a companier.
4. Employee and Teacher remain staff person masters. Out of Recruitment except as login roles.
5. Target FKs `candidates.agent_id`, `sub_agent_id`, `agencier_id`, `companier_id` stay opaque until Design Gate M4 implements those masters.
6. `AuthManager` multi-tenant `Company` fields are not part of the live model.
7. Name-matching reconcile (snapshot → master) is a later approved job, not an automatic unique-name join.
