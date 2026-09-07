# ManPower Platform — M1–M5 complete; M6 Overseas Processing next

Modern rewrite of the ManPower overseas employment management system.

**Stack:** Next.js 15 · Node.js 20 · TypeScript (strict) · PostgreSQL 16 · Prisma 6 · Express 5

---

## Architecture

```
platform/
├── apps/
│   ├── api/          Node.js REST API (Express + Prisma + Zod)
│   └── web/          Next.js web application (App Router)
├── packages/
│   └── shared/       Shared types, Zod schemas, and constants
├── docker-compose.yml   PostgreSQL (local development)
├── .env.example         Required environment variables
└── README.md
```

**Key principles:**
- **Modular monolith** — single API process; PostgreSQL schemas are module boundaries, not separate services
- **API-first** — the same REST API will serve the web app and future Android/iOS apps
- **Type-safe end-to-end** — strict TypeScript, Zod validation at every boundary
- **No legacy interference** — the legacy Laravel application in `src/` is never modified

### Database strategy

| Database | Role | Location |
|---|---|---|
| **PostgreSQL** (new) | **Target** — all new data lives here | `platform/` / Docker |
| MariaDB/MySQL (legacy) | Source — read-only reference during migration | `src/` — **NEVER modify** |

---

## Prerequisites

| Tool | Minimum version | Install |
|---|---|---|
| Node.js | 20.x LTS | [nodejs.org](https://nodejs.org/) |
| pnpm | 10.x | `npm install -g pnpm` |
| Git | any | [git-scm.com](https://git-scm.com/) |
| Docker Desktop | optional local only | [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop/) — **not required for CI** |

**PostgreSQL 16 is the hard-gate database.** CI uses the GitHub Actions service image `postgres:16` (not 17, not 18). Do not point the platform at local PostgreSQL 18, production MariaDB, or production data.

Local `pnpm db:up` uses `postgres:16-alpine` when Docker is available. PostgreSQL 16 is proven only by the CI job `postgres16-integration`, not by a developer workstation.

Latest verified M4 Recruitment CI: commit **`4160aae`** (BUILD/STATIC PASS, UNIT PASS, INTEGRATION PostgreSQL 16 PASS). The earlier PRE-M4 prerequisite proof is commit `d38eba1`.

> **Windows users:** Docker Desktop requires WSL 2 if you want a local PostgreSQL 16 container. It is not required to run UNIT or BUILD/STATIC checks.

---

## Quick Start

```bash
# 1. Enter the platform workspace
cd platform

# 2. Install all dependencies
pnpm install

# 3. Copy environment template
cp .env.example .env
# Edit .env and set real values if needed (defaults work for local dev)

# 4. Start PostgreSQL (requires Docker Desktop)
pnpm db:up

# 5. Generate Prisma client
pnpm db:generate

# 6. Run database migrations (creates the schema)
pnpm db:migrate

# 7. Start development servers (web + API in parallel)
pnpm dev
```

After step 7:
- **Web:** http://localhost:3000
- **API:** http://localhost:4000
- **API health:** http://localhost:4000/api/health
- **DB health:** http://localhost:4000/api/health/db

---

## Development Commands

Run all commands from the `platform/` directory.

### Install & setup

```bash
pnpm install                    # Install all workspace dependencies
pnpm db:up                      # Start PostgreSQL container
pnpm db:down                    # Stop PostgreSQL container
pnpm db:logs                    # View PostgreSQL logs
```

### Development

```bash
pnpm dev                        # Start web + API in parallel (watch mode)
pnpm --filter api dev           # Start API only
pnpm --filter web dev           # Start web only
```

### Database (Prisma)

```bash
pnpm db:generate                # Generate Prisma client (required after schema changes)
pnpm db:migrate                 # Create and apply a new migration (dev)
pnpm db:bootstrap-admin         # One-time secure initial administrator
pnpm db:ensure-permissions      # Upsert permission catalogue onto super_admin
pnpm db:push                    # Push schema changes without migration file (prototyping only)
pnpm db:studio                  # Open Prisma Studio (visual DB browser)
```

### Quality checks

```bash
pnpm typecheck                  # TypeScript strict check (all packages)
pnpm lint                       # ESLint (all packages)
pnpm format                     # Prettier format (all files)
pnpm format:check               # Check formatting without writing
```

### Testing

```bash
pnpm test                       # Run all tests (unit — no DB needed)
pnpm --filter api test          # API tests only
pnpm --filter web test          # Web tests only
pnpm --filter api test:watch    # API tests in watch mode
```

### Build

```bash
pnpm build                      # Build all packages
pnpm --filter api build         # Build API only → dist/
pnpm --filter web build         # Build web only → .next/
```

---

## Environment Variables

Copy `.env.example` to `.env` and configure:

| Variable | Required | Default | Description |
|---|---|---|---|
| `NODE_ENV` | yes | `development` | Runtime environment |
| `PORT` | no | `4000` | API server port |
| `DATABASE_URL` | **yes** | — | PostgreSQL connection string |
| `CORS_ORIGINS` | no | `http://localhost:3000` | Comma-separated allowed origins |
| `LOG_LEVEL` | no | `info` | Pino log level |
| `NEXT_PUBLIC_API_URL` | yes (web) | `http://localhost:4000` | API URL visible to browser |
| `SESSION_TTL_HOURS` | no | `24` | Opaque session lifetime |
| `BCRYPT_ROUNDS` | no | `12` | Password hashing cost |
| `BOOTSTRAP_ADMIN_*` | bootstrap only | — | One-time administrator credentials |

> **Security:** Never commit `.env` to version control. Real credentials must never appear in `.env.example`, CI logs, or documentation.

---

## API Endpoints (M3)

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/health` | Liveness probe — always 200 when process is running |
| `GET` | `/api/health/db` | Readiness probe — 200 when DB connected, 503 when not |
| `POST` | `/api/v1/auth/login` | Email/username login |
| `POST` | `/api/v1/auth/logout` | Revoke current session (auth + CSRF) |
| `GET` | `/api/v1/auth/me` | Current authenticated user |
| `POST` | `/api/v1/auth/change-password` | Change password and revoke all sessions |
| `GET` | `/api/v1/iam/users` | List users without credential fields |
| `POST` | `/api/v1/iam/users` | Create a local user securely |
| `POST` | `/api/v1/iam/users/:userId/roles` | Assign role; permission protected |
| `POST` | `/api/v1/iam/roles/:roleId/permissions` | Grant permission; permission protected |
| `PATCH` | `/api/v1/iam/users/:userId/status` | Activate/suspend/deactivate account |

| `GET` | `/api/v1/candidates` | Paginated candidate list/search |
| `GET` | `/api/v1/candidates/:id` | Candidate detail |
| `POST` | `/api/v1/candidates` | Create candidate |
| `PATCH` | `/api/v1/candidates/:id` | Update candidate (not status) |
| `PATCH` | `/api/v1/candidates/:id/status` | Change A/P/I status (no hard delete) |
| `GET/POST` | `/api/v1/candidates/:candidateId/educations` | Education list/create |
| `PATCH` | `/api/v1/candidates/:candidateId/educations/:id` | Education update |
| `GET/POST` | `/api/v1/candidates/:candidateId/experiences` | Experience list/create |
| `PATCH` | `/api/v1/candidates/:candidateId/experiences/:id` | Experience update |
| `GET/POST` | `/api/v1/candidates/:candidateId/skills` | Skill certificate list/create |
| `PATCH` | `/api/v1/candidates/:candidateId/skills/:id` | Skill update |
| `GET/POST` | `/api/v1/candidates/:candidateId/skill-list` | Skill-tag list/create |
| `PATCH` | `/api/v1/candidates/:candidateId/skill-list/:id` | Skill-tag update |
| `GET/POST` | `/api/v1/candidates/:candidateId/languages` | Language list/create |
| `PATCH` | `/api/v1/candidates/:candidateId/languages/:id` | Language update |
| `GET/POST` | `/api/v1/candidates/:candidateId/trainings` | Profile training list/create |
| `PATCH` | `/api/v1/candidates/:candidateId/trainings/:id` | Profile training update |

See `docs/M2-IAM-ARCHITECTURE.md`, `docs/M3-CANDIDATE-DOMAIN.md`,
`docs/M4-CANDIDATE-SUPPORTING-DOMAINS.md`, `docs/M4-LEGACY-FIELD-MAPPING.md`,
`docs/M4-RECRUITMENT.md`, `docs/M4-PERMISSIONS.md`, and the PRE-M4 hard-gate evidence:

| Gate | Status |
|---|---|
| A07 RBAC | **DECISION LOCKED** — `docs/PRE-M4-HARD-GATE.md` |
| A05 Agency / Company | **DECISION LOCKED** — `docs/A05-AGENCY-COMPANY-EVIDENCE.md` |
| A20 employer_candidates.status | **DECISION LOCKED** — `docs/A20-EMPLOYER-CANDIDATES-STATUS.md` |
| PostgreSQL 16 HARD GATE (PRE-M4) | **PASS** — CI `main` commit `d38eba1` |
| **M4 Recruitment** | **COMPLETE** — CI commit `4160aae` — `docs/M4-RECRUITMENT.md` |
| PostgreSQL 16 HARD GATE (M4) | **PASS** — CI commit `4160aae` (BUILD/STATIC, UNIT, INTEGRATION) |
| **M5 Training / Exam** | **IMPLEMENTED** — `docs/M5-TRAINING.md`, `docs/M5-PERMISSIONS.md` |

M4 APIs: `/api/v1/partners/:type`, `/api/v1/employer-candidates`, `/api/v1/iam/users/:userId/bindings`.  
M5 APIs: `/api/v1/teachers`, `/api/v1/class-groups`, `/api/v1/class-schedules`, `/api/v1/exams`, `/api/v1/exam-results`, `/api/v1/manpower-trainings`. Teacher candidate access remains blocked. Agency/Company snapshots, finance, and production import are not implemented.

Later business modules remain excluded until their milestone approvals.

---

## Testing

Tests are written with [Vitest](https://vitest.dev/) and [supertest](https://github.com/ladjs/supertest) (API) / [@testing-library/react](https://testing-library.com/docs/react-testing-library/intro/) (web).

Keep these classes separate:

| Class | How it runs | What it may claim |
|---|---|---|
| **UNIT** | `pnpm test` with `RUN_DB_TESTS` unset/false | Mocked Prisma only — not a PostgreSQL 16 proof |
| **INTEGRATION** | CI job `postgres16-integration` with `RUN_DB_TESTS=true` on `postgres:16` | Real database + live API security checks |
| **BUILD/STATIC** | CI job `static` (also repeated at the end of integration) | lint, typecheck, build |

```
API UNIT:
  tests/health.test.ts       Health endpoint (GET /api/health, GET /api/health/db)
  tests/config.test.ts       Environment validation
  tests/errorHandler.test.ts Error handling middleware
  tests/auth.test.ts         Login/logout/me/password change
  tests/rbac.test.ts         Multi-role permission evaluation and 401/403
  tests/iam.test.ts          Audited role/permission assignment
  tests/audit.test.ts        Audit metadata sanitization
  tests/candidates.test.ts   Candidate CRUD, search, RBAC, audit (mocked)
  tests/profile.test.ts      Education/experience/skills/languages/training (mocked)
  tests/training.test.ts     Teacher/exam/result/manpower API, IDOR, publish (mocked)
  tests/training-permissions.test.ts Exact M5 matrix
  tests/training-access.test.ts Teacher self-scope helpers
  tests/training-workflow.test.ts PASS/FAIL, five marks, no formula

API INTEGRATION (CI / RUN_DB_TESTS=true only):
  tests/db.integration.test.ts          PostgreSQL 16 schema, FK, sequence, unique, rollback
  tests/db.security.integration.test.ts Live 401 / 403 / CSRF / scope / IDOR / 409 / 422
  tests/db.training.integration.test.ts M5 PG16 constraints, IDOR, workflow, audit

Web UNIT:
  tests/page.test.tsx        Page render + config validation
  tests/auth-ui.test.tsx     Login and protected shell
  tests/candidates-ui.test.tsx Candidate create form
  tests/training-ui.test.tsx Permission-gated training navigation and pages
```

CI workflow (monorepo root): `.github/workflows/ci.yml`. Duplicate for a `platform/`-only git root: `platform/.github/workflows/ci.yml`. Neither job uses local Docker Desktop or PostgreSQL 18.

---

## Production Deployment

### PM2 (traditional server)

```bash
# Build
pnpm build

# Start API with PM2
cd apps/api
pm2 start dist/index.js --name manpower-api

# Start web with PM2
cd apps/web
pm2 start "pnpm start" --name manpower-web
```

### Docker (containerised)

> Docker images will be added in a future milestone.
> The `docker-compose.yml` currently manages the PostgreSQL service only.

---

## Legacy Application

The legacy Laravel application lives in `src/` (one level above `platform/`).

> ⛔ **NEVER** modify, delete, or rename any file in `src/`, the legacy `routes/`, `config/`, `database/`, or `storage/`.

The production SQL dump (`docs/database-audit/u410970153_eujobbd.sql`) is intentionally excluded from Git because of its size (~223 MB) and sensitivity. Its SHA-256 is preserved as migration evidence in `docs/database-audit/production-dump.sha256`:

`D6B2C811CC456DD545AB3CF2578E6C45F713F89D262426F5C18FB5E5D660F2E8`

CI verifies that recorded evidence. It does not re-hash dump bytes. Actual dump-byte verification must be performed in the controlled migration environment where the dump is available.

The legacy application remains the system of record during development. Data migration will occur in M13 following the approved migration design in `modernization-design/`.

---

## Module Roadmap

Numbering matches `modernization-design/final-design-gate/11-milestones-risks-approvals.md`.
Do not renumber later milestones.

| Milestone | Module | Status |
|---|---|---|
| M0 | Audit + baseline (Design Gate) | ✅ Approved |
| M1 | Foundation | ✅ Complete |
| M2 | Auth / RBAC / Audit | ✅ Complete |
| M3 | Candidate (master + supporting profile domains) | ✅ Complete |
| M4 | Recruitment / Partners | ✅ Complete — CI `4160aae` |
| M5 | Training / Exam | ✅ Implemented — `docs/M5-TRAINING.md` |
| M6 | Overseas Processing | ⏳ |
| M7 | Finance *(blocked: A01+A02)* | ⏳ |
| M8 | Invoice / Receipt / Ticket | ⏳ |
| M9 | Documents | ⏳ |
| M10 | Reports | ⏳ |
| M11 | Notifications | ⏳ |
| M12 | Dashboard / Admin | ⏳ |
| M13 | Migration | ⏳ |
| M14 | Testing / UAT | ⏳ |
| M15 | Cutover | ⏳ |

Conversation work labeled “M4 Candidate Supporting Domains” completed Design Gate **M3**
profile children. Design Gate **M4 Recruitment** is **COMPLETE**. Design Gate **M5 Training/Exam**
is **IMPLEMENTED**. M13 remains responsible for production migration and orphan quarantine.
