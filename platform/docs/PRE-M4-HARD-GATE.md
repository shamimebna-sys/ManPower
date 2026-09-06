# PRE-M4 HARD GATE — decision lock + PostgreSQL 16

Date: 2026-09-07

This document locks prerequisite decisions for Design Gate M4 Recruitment.
It does **not** change `modernization-design/final-design-gate/11-milestones-risks-approvals.md`.

| Item | Status |
|---|---|
| A07 RBAC | **DECISION LOCKED** |
| A05 Agency / Company model | **DECISION LOCKED** |
| A20 `employer_candidates.status` | **DECISION LOCKED** |
| PostgreSQL 16 verification | **PASS** — GitHub Actions `main` commit `d38eba1` |
| Design Gate M4 Recruitment | **GO** — implemented on the target platform. See `docs/M4-RECRUITMENT.md`. |

This document is the prerequisite lock. M4 Recruitment implementation is recorded in `docs/M4-RECRUITMENT.md`. Finance, Training/Exam, and production import remain out of scope.

---

## 1. A07 — DECISION LOCKED

Approved target role keys (do not invent others; do not use numeric IDs at runtime):

`super_admin`, `administrator`, `owner`, `agent`, `sub_agent`, `candidate`, `employer`, `company`, `agency`, `employee`, `teacher`

`normal_user` and `others` remain legacy evidence only. They are not created.

19 permission keys are unchanged. Runtime grants remain `super_admin` only (`pnpm db:ensure-permissions`). Report/document keys are not defined.

Reusable helper: `apps/api/src/auth/candidate-access.ts`. Existing candidate APIs call it. Partner profile IDs are not on `iam.users` yet; scoped roles without IDs resolve to no rows. That does not change current `super_admin` / `employee` (when permitted) behavior.

| Role key | Permission | Scope | Current runtime grant | Approved target policy | Unresolved item |
|---|---|---|---|---|---|
| `super_admin` | all 19 defined keys | `all` | granted + middleware bypass | global candidate access | none |
| `administrator` | none yet | `all` where a module key is later granted | none | global candidate access where permitted | which non-IAM keys to grant |
| `owner` | none yet | `all` where a module key is later granted | none | global candidate access where permitted | which keys to grant |
| `employee` | none yet | `all` only if that module key is granted | none | global candidate access only with an explicit key | which keys to grant |
| `agent` | none yet | `candidates.agent_id = authenticated_user.agent_id` | none | scoped; IDs attach in Recruitment | user↔agent binding |
| `sub_agent` | none yet | `candidates.sub_agent_id = authenticated_user.sub_agent_id` | none | scoped | user↔sub_agent binding |
| `agency` | none yet | `candidates.agencier_id = authenticated_user.agencier_id` | none | scoped | user↔agencier binding |
| `candidate` | none yet | `candidates.id = authenticated_user.candidate_id` | none | self only | user↔candidate UUID binding |
| `employer` | none; **no** `candidate.read` | none on candidate APIs | none | Recruitment/employer-candidate workflow only (not built) | assignment keys |
| `company` | none | **BLOCKED** | none | remains blocked until Recruitment evidence | companier row-scope |
| `teacher` | none | **BLOCKED** | none | remains blocked until evidence | training/exam access |

IAM keys stay `super_admin` only until a later IAM grant decision.

---

## 2. A05 — DECISION LOCKED

See `docs/A05-AGENCY-COMPANY-EVIDENCE.md`.

Live masters: Agent, SubAgent, Agencier, Companier.  
Snapshots: Agency, Company.  
`agenciers ≠ agencies`. `companiers ≠ companies`.  
No Organization / Tenant / generic Partner. Employer is a person profile.

---

## 3. A20 — DECISION LOCKED

See `docs/A20-EMPLOYER-CANDIDATES-STATUS.md`.

Target `status`: `A` active, `I` inactive/archived.  
Target `purpose`: `FAVORITE`, `RESERVE`, `SELECTED`.  
Active membership unique; archive not hard-delete; audit required.  
Column and table are **not** in the target schema yet.

---

## 4. PostgreSQL 16 CI hard gate

CI is the only accepted PostgreSQL 16 environment. It does not use PostgreSQL 17, PostgreSQL 18, local PostgreSQL 18, production MariaDB, or production data.

| Job | Class | What it proves |
|---|---|---|
| `static` | **BUILD/STATIC** | lint, typecheck, API build, web build |
| `unit` | **UNIT** | API + web tests with `RUN_DB_TESTS=false` (mocked Prisma; no live database) |
| `postgres16-integration` | **INTEGRATION** | `postgres:16` service, `SELECT version()` must contain `PostgreSQL 16.`, migrate deploy, `prisma generate`, `RUN_DB_TESTS=true`, then lint / typecheck / build |

Workflow files:

- Monorepo (this checkout): `.github/workflows/ci.yml` — `working-directory: platform`
- If `platform/` is later the git root: `platform/.github/workflows/ci.yml`

### Recorded CI result — commit `d38eba1` on `main`

| Check | Result |
|---|---|
| GitHub Actions overall | **SUCCESS** |
| BUILD/STATIC | **PASS** |
| UNIT | **PASS** |
| INTEGRATION — PostgreSQL 16 | **PASS** |
| PostgreSQL version | Proven in CI (`postgres:16`; `SELECT version()` hard-checked) |
| Migration result | **PASS** — all four existing target migrations applied on PostgreSQL 16 |
| API tests on PostgreSQL 16 | **104 passed** (pagination fixture included) |
| DB security integration | **7/7 passed** |
| Sequence / FK / RESTRICT / unique / rollback | Covered by the passing integration suite |
| Lint | **PASS** (BUILD/STATIC and integration jobs) |
| Typecheck | **PASS** (BUILD/STATIC and integration jobs) |
| Build | **PASS** (BUILD/STATIC and integration jobs) |

Applied migrations (unchanged names):

1. `20260903183000_m2_iam_audit`
2. `20260904004500_m3_candidate_core`
3. `20260904010000_m4_candidate_supporting_domains`
4. `20260904013000_m4_candidate_code_sequence`

**PostgreSQL 16 HARD GATE = PASS**

---

## 5. Candidate code sequence

Source: `nextval('candidate.candidate_code_seq')` → `9` + 6 digits (`9000001`, `9000002`, `9000003`, …).  
Not `COUNT+1`. Migrated codes are never reallocated. External format is unchanged.

Sequence starts at 1. `setval` is allowed only from actual `9######` rows in that database.

CI integration tests assert `nextval`, `/^9\d{6}$/` formatting, and concurrent uniqueness. Those checks passed on PostgreSQL 16 in commit `d38eba1`.

---

## 6. Quality gate

### UNIT (mocked — not a DB proof)

401, 403 permission, 403 CSRF, child IDOR, 409 unique, 422 validation, cursor bounds, create+audit transaction, A07 helper deny/allow.

CI UNIT job on `d38eba1`: **PASS**.

### INTEGRATION (PostgreSQL 16 — proven)

`tests/db.integration.test.ts` and `tests/db.security.integration.test.ts` against a real `postgres:16` database:

- UUID PK creation
- candidate child FK + `ON DELETE RESTRICT`
- unique email / mobile / passport / code
- cursor pagination
- A07 agent-scope filter
- child-record ownership / IDOR (404 Record)
- transaction rollback and audit rollback
- `nextval('candidate.candidate_code_seq')` and concurrent allocation
- Prisma queries and applied migration names
- live API 401 / 403 permission / 403 CSRF / agent empty scope / 409 / 422

CI INTEGRATION job on `d38eba1`: **PASS** — 104 API tests.

### BUILD/STATIC

CI BUILD/STATIC job on `d38eba1`: **PASS** (lint, typecheck, API build, web build).

---

## 7. Legacy safety

| Check | Result |
|---|---|
| Laravel `src/` | Not modified for this gate |
| Legacy routes | Not modified |
| Production dump | Intentionally excluded from Git (size + sensitivity). Recorded SHA-256 remains `D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8` |
| Production MariaDB | Not connected |
| Production data | Not imported |

The dump file `u410970153_eujobbd.sql` is not in the Git checkout. CI verifies the committed evidence file `docs/database-audit/production-dump.sha256` (recorded SHA-256 + source filename). That is **not** a re-hash of dump bytes. Actual dump-byte verification belongs in the controlled migration environment where the dump is available.

---

## 8. M4 Recruitment — prerequisite result

Pre-M4 hard-gate items are satisfied:

1. A07 = **DECISION LOCKED**
2. A05 = **DECISION LOCKED**
3. A20 = **DECISION LOCKED**
4. PostgreSQL 16 CI hard gate = **PASS** (`d38eba1`)

M4 implementation is recorded in `docs/M4-RECRUITMENT.md`. Teacher candidate access remains blocked pending M5 evidence.

**GO / NO-GO for Design Gate M4 Recruitment: GO.**

Implementation is recorded in `docs/M4-RECRUITMENT.md`. Do not treat this GO as delivery of finance, Training/Exam, or production import.
