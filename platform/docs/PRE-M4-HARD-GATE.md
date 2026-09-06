# PRE-M4 HARD GATE — decision lock + PostgreSQL 16

Date: 2026-09-04

This document locks prerequisite decisions for Design Gate M4 Recruitment.
It does **not** change `modernization-design/final-design-gate/11-milestones-risks-approvals.md`.

| Item | Status |
|---|---|
| A07 RBAC | **DECISION LOCKED** |
| A05 Agency / Company model | **DECISION LOCKED** |
| A20 `employer_candidates.status` | **DECISION LOCKED** |
| PostgreSQL 16 verification | **BLOCKED** — CI workflow is defined; no GitHub Actions run has proven `postgres:16` |
| Design Gate M4 Recruitment | **NO-GO** |

Design Gate M4 Recruitment was not started. No Agent, SubAgent, Agencier, Companier, EmployerCandidate, recruitment API, selection workflow, employer assignment, finance, or new permission/role work was created.

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

The integration job is reproducible from a clean checkout. It does not require Docker Desktop or a developer PostgreSQL install. Triggers: `push` / `pull_request` on `main` and `develop`, plus `workflow_dispatch`.

### Recorded results (this workspace)

| Check | Result |
|---|---|
| PostgreSQL version | **Unavailable** — no GitHub Actions run; local PostgreSQL 18 on 5432 was not used |
| Migration result | **Not run on PostgreSQL 16** — existing files only (see below) |
| DB integration result | **Not run** — suite is defined; skipped unless `RUN_DB_TESTS=true` |
| Sequence result | **Not run** on PostgreSQL 16 |
| FK result | **Not run** on PostgreSQL 16 |
| RESTRICT result | **Not run** on PostgreSQL 16 |
| Unique constraints | **Not run** on PostgreSQL 16 |
| Rollback | **Not run** on PostgreSQL 16 |
| Security integration | **Not run** on PostgreSQL 16 — live suite is `tests/db.security.integration.test.ts` |
| Lint | Local **Passed** (see section 6) |
| Typecheck | Local **Passed** (see section 6) |
| Build | Local **Passed** (see section 6) |

Do not treat unit/mocked coverage as database verification.

**PostgreSQL 16 HARD GATE = BLOCKED**

A PASS requires a green `postgres16-integration` job whose `SELECT version()` output identifies PostgreSQL 16 and whose `RUN_DB_TESTS=true` suite is green.

---

## 5. Candidate code sequence

Source: `nextval('candidate.candidate_code_seq')` → `9` + 6 digits (`9000001`, `9000002`, `9000003`, …).  
Not `COUNT+1`. Migrated codes are never reallocated. External format is unchanged.

Sequence starts at 1. `setval` is allowed only from actual `9######` rows in that database. No live PostgreSQL 16 rows exist here, so no `setval` was computed.

CI integration tests assert `nextval`, `/^9\d{6}$/` formatting, and concurrent uniqueness.

---

## 6. Quality gate (local UNIT + BUILD/STATIC — no PostgreSQL 16)

These results are **BUILD/STATIC** and **UNIT** only. They do not satisfy the hard gate.

| Check | Result |
|---|---|
| Lint | **Passed** |
| Typecheck | **Passed** |
| API unit tests | **79 passed**, 25 skipped (18 DB + 7 security integration) |
| Web tests | **8 passed** |
| Build | **Passed** |

### UNIT (mocked — not a DB proof)

401, 403 permission, 403 CSRF, child IDOR, 409 unique, 422 validation, cursor bounds, create+audit transaction, A07 helper deny/allow.

### INTEGRATION (defined, not executed here)

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

## 8. Remaining blockers for Design Gate M4

1. A real GitHub Actions `postgres16-integration` run on `postgres:16` with `SELECT version()` + `RUN_DB_TESTS=true` green. This workspace has no executed CI run (and typically no git remote) until the repo is pushed.
2. User↔partner/self ID bindings before scoped roles can resolve.
3. Explicit permission grants beyond `super_admin` (not invented here).
4. Company and teacher candidate scope still blocked.
5. Employer assignment waits for Recruitment implementation.

**GO / NO-GO for Design Gate M4 Recruitment: NO-GO.**
