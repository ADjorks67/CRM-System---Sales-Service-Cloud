# CRM System — Setup Achievements & Phase Prerequisites

**Document purpose:** Record what has already been set up (Phase 0), and what must be in place before each later phase — including *why* each prerequisite exists.  
**Aligned with:** `Master plan/PROJECT_PLAN.md` and the locked stack (Laravel + Blade + PostgreSQL).  
**Last updated:** 2026-09-28

---

## 1. Locked stack (do not change)

| Layer | Choice | Why |
|---|---|---|
| Backend | Laravel (PHP 8.3+) | SRS / project standard |
| Frontend | Blade + HTML + CSS + vanilla JS | No Vue, Inertia, React, Livewire, or TypeScript |
| Database | **PostgreSQL only** | Replaces older MySQL drafts; rules forbid MySQL |
| Tests | Pest + PostgreSQL (not SQLite) | Behaviour must match production DB |
| Tooling | Vite, Tailwind CSS v4, Pint, Laravel Boost | Asset build, style, agent guidelines |

---

## 2. What has been achieved (Phase 0 — local/repo setup)

This section documents work already completed so both developers can clone, run, and extend the app.

### 2.1 Runtime environment

| Item | Status | Notes |
|---|---|---|
| Laravel application skeleton | Done | Laravel 13.x in repo |
| PHP / Composer / Node | Done | Verified via Laravel Herd (PHP 8.5) |
| Composer `vendor/` | Done | Pest, Pint, Boost installed |
| npm dependencies | Done | Vite + Tailwind v4 |
| Frontend production build | Done | `npm run build` succeeds |
| Application key | Done | Present in local `.env` |

### 2.2 PostgreSQL via Docker Compose

Herd Pro “services” are not available on this machine’s Herd plan, and a local Windows PostgreSQL instance on port **5432** required an unknown password. The team-standard local DB is therefore **Docker Compose**.

| Service | Container | Host ports | Why |
|---|---|---|---|
| PostgreSQL 18 | `crm-postgres` | **5433 → 5432** | App + test database; 5433 avoids clash with Windows Postgres on 5432 |
| Mailpit | `crm-mailpit` | **1025** (SMTP), **8025** (UI) | Capture local email without a real mail server |

**Credentials (local Docker only — see `.env.example`):**

- Database: `crm_database` (app), `crm_testing` (tests, created by `docker/postgres/init.sql`)
- User / password: `postgres` / `secret`

**Why this setup is required:** every later phase persists CRM data (users, accounts, leads, cases, etc.). Using PostgreSQL from day one avoids SQLite-only behaviour and MySQL-specific SQL. Mailpit is required once Phase 1+ sends password-reset and notification emails.

**Key files:**

- `compose.yaml` — Postgres + Mailpit
- `docker/postgres/init.sql` — creates `crm_testing`
- `scripts/apply-docker-env.php` — aligns `.env` with Docker Postgres/Mailpit
- `.env.example` — documented defaults for new clones

**Daily start:**

```bash
docker compose up -d
php artisan migrate
composer run dev
# Mailpit UI: http://localhost:8025
```

### 2.3 Application configuration

| Change | Why |
|---|---|
| `DB_CONNECTION=pgsql` in `.env` / `.env.example` | Lock stack to PostgreSQL |
| `phpunit.xml` → PostgreSQL `crm_testing` on port 5433 | Tests match production DB (NFR / CRM data rules) |
| `SESSION_LIFETIME=120` | SRS auth timeout (2 hours) |
| Mail → SMTP `127.0.0.1:1025` | Ready for Mailpit |
| Default app name `CRM System` | Branding in layout / config |
| Removed SQLite bootstrap from `composer.json` | Prevent accidental SQLite creation |

Migrations already applied on local `crm_database`: default Laravel `users`, `sessions`, `cache`, `jobs` tables — foundation for Phase 1 auth and queues.

### 2.4 UI shell and design tokens (SRS §6)

| Artifact | Purpose |
|---|---|
| `resources/css/app.css` | Design tokens: primary `#032d60`, secondary `#0176d3`, page `#f3f3f3`, card/text/status colours, typography scale |
| `resources/views/layouts/app.blade.php` | Header, global search (UI only), 10 primary tabs, flash area, footer, a11y skip link |
| `resources/views/home.blade.php` | First page using the shell |
| `routes/web.php` → named route `home` | Stable entry URL for navigation |

**Why:** Phase 1+ modules must render inside one consistent layout. Tokens prevent ad-hoc colours and keep UI aligned with the SRS. Nav tabs are placeholders until each module’s routes exist.

### 2.5 Quality gates and collaboration templates

| Artifact | Why |
|---|---|
| `.github/workflows/ci.yml` | On PR/push: Composer, npm build, Pint, Pest against a PostgreSQL service |
| `.github/PULL_REQUEST_TEMPLATE.md` | Forces SRS IDs, stack check, test plan |
| `.github/ISSUE_TEMPLATE/srs-requirement.md` | One GitHub issue per FR/NFR |

**Verification already run locally:** Pest (2 tests) passed; Pint clean.

### 2.6 Cursor / agent rules (PostgreSQL)

Master plan and `.cursor/rule/` rules lock PostgreSQL and forbid MySQL (including `crm-postgresql-data.mdc`, `crm-core.mdc`, `USER_RULES.txt`).  
**Why:** stops agents and developers from regenerating MySQL-oriented schema or suggesting Vue/Inertia.

### 2.7 Chart.js + FullCalendar (locked)

| Package | Role |
|---|---|
| `chart.js` | Funnel (horizontal bar), donut, bar charts for Home / Reports |
| `@fullcalendar/core` + daygrid, timegrid, list, interaction | Calendar day/week/month/list + drag-and-drop |

| Registration | Path |
|---|---|
| Shared helpers | `resources/js/lib/charts.js`, `resources/js/lib/calendar.js` |
| Vite entries | `resources/js/charts.js`, `resources/js/calendar.js` (listed in `vite.config.js`) |
| Blade | `<x-chart>`, `<x-calendar>` (auto-push the matching Vite entry) |
| CSRF / fetch | `resources/js/bootstrap.js` via `resources/js/app.js` |

**Why:** Phase 3 home widgets and Phase 4 calendar need one agreed stack; central registration prevents CDN copies or competing libraries.

### 2.8 Phase 0 items still outstanding (governance)

Tracked in [PHASE_0_GOVERNANCE_CHECKLIST.md](PHASE_0_GOVERNANCE_CHECKLIST.md). Summary:

| Item | Why it still matters |
|---|---|
| GitHub branch protection on `main` | Parallel work without broken `main` (no separate `develop` branch) |
| Project board + one issue per SRS ID (P0/P1/P2 labels) | Traceability and sprint planning |
| Paste `USER_RULES.txt` into Cursor Settings (each developer) | Consistent agent behaviour |
| Confirm CI green on GitHub after first push | Proves clones can rely on Actions |

**Completed governance (this pass):** Chart.js + FullCalendar agreed, installed (`package.json`), registered in Vite
(`resources/js/charts.js`, `resources/js/calendar.js`, `resources/js/lib/*`), and exposed as `<x-chart>` / `<x-calendar>`.

**Phase 0 exit criteria (from plan):** both developers can clone, run (`docker compose up -d`, migrate, serve), and open a PR that passes CI.

### 2.9 Phase 1 Foundation (implemented)

See [PHASE_1_FOUNDATION.md](PHASE_1_FOUNDATION.md) and [SRS_DISTILLED.md](SRS_DISTILLED.md).

| Item | Status |
|---|---|
| FR-AUTH-001 login / lockout / session / remember-me | Done |
| FR-AUTH-002 password reset + history (last 5) | Done |
| FR-AUTH-003 roles / permissions / sharing schema | Done |
| Users admin (System Administrator) | Done |
| Blade CRM components + list helpers | Done |
| HasAuditFields + picklist + demo user seeders | Done |
| Empty Accounts module smoke | Done |

**Daily start (updated):**

```bash
docker compose up -d
php artisan migrate --seed
composer run dev
# Login: admin@crm.test / Password1!
# Mailpit: http://localhost:8025
```

---

## 3. Prerequisites by phase

Each phase assumes **all previous phases’ exit criteria** are met. Below: what must exist *before* coding that phase, and why.

### Phase 0 — Setup and governance

**Needed before starting Phase 0**

| Prerequisite | Why |
|---|---|
| SRS v1.0 signed off | Source of truth for FR/NFR IDs and priorities |
| Git + GitHub access | Shared repo, PRs, issues |
| PHP 8.3+, Composer, Node/npm | Run Laravel and Vite |
| Docker Desktop (or equivalent Postgres) | Local PostgreSQL without Herd Pro |
| Agreement on locked stack | Avoid MySQL / SPA drift |

**Produced by Phase 0 (target):** runnable app, Postgres, CI, rules, layout shell, issue/PR process.

---

### Phase 1 — Foundation (Auth, RBAC, layout components)

**Needed before Phase 1**

| Prerequisite | Why |
|---|---|
| Phase 0 exit met (clone/run/CI) | Shared baseline; no “works on my machine” blockers |
| PostgreSQL + migrations running | Users, sessions, password resets need real tables |
| Layout shell + design tokens | Dev B extends layout; Dev A auth pages use same chrome |
| CI pipeline | Every auth/RBAC PR must stay green |
| Module ownership agreed (Dev A / Dev B) | Avoid merge wars on `routes/web.php` / layout |
| Role list from SRS (FR-AUTH-003) | Seeders and policies need a fixed permission model |

**Why Phase 1 comes first:** Accounts, Leads, Cases, etc. all require authenticated users and policies. Building CRM objects without login/RBAC forces expensive rewrites.

**Exit:** login by role, layout usable, empty module can render inside the shell.

**Status (2026-09-28):** Phase 1 exit criteria are met in-repo. See [PHASE_1_FOUNDATION.md](PHASE_1_FOUNDATION.md). Remaining Phase 0 GitHub governance is tracked separately in [PHASE_0_GOVERNANCE_CHECKLIST.md](PHASE_0_GOVERNANCE_CHECKLIST.md).

---

### Phase 2 — Core objects P0 (Accounts, Contacts, Leads)

**Needed before Phase 2**

| Prerequisite | Why |
|---|---|
| Auth + policies working | List/create/detail must enforce record-level access |
| `HasAuditFields` + picklist seeders | Ownership, created_by/updated_by, lead statuses/sources |
| Blade list/form components | Shared UI for CRUD without reinventing tables/modals |
| **Accounts migration merged first (day 1)** | Contacts require Account; Leads convert into Account later |
| Per-module route files pattern | Reduce conflicts between Dev A and Dev B |

**Why:** Contacts and lead conversion depend on Accounts. Leads are the top of the sales funnel; without them Phase 3 opportunities and conversion cannot proceed.

**Exit:** Accounts, Contacts, Leads usable with RBAC and tests.

**Status:** Phase 2 exit criteria are met in-repo. See [PHASE_2_CORE_OBJECTS.md](PHASE_2_CORE_OBJECTS.md).

---

### Phase 3 — Sales, service, search, basic reports (MVP)

**Needed before Phase 3**

| Prerequisite | Why |
|---|---|
| Accounts, Contacts, Leads stable | Opportunities/Cases relate to them; reports query them |
| Chart.js + FullCalendar installed and registered | Home funnel / donut widgets (FR-HOME-*); see `<x-chart>` |
| Global search UI slot in header | Wire FR-SRCH-001/002 into existing search box |
| Stage/probability rules from SRS | Opportunity stage path and history |
| Case numbering rules from SRS | Auto case numbers must be unique and consistent |

**Why:** This phase delivers the P0 MVP. Search and home analytics only make sense once core objects hold real data.

**Exit:** all P0 items in SRS 9.2 work — **MVP demo**.

**Status:** Phase 3 exit criteria are met in-repo. See [PHASE_3_MVP.md](PHASE_3_MVP.md).

---

### Phase 4 — Productivity and analytics (P1)

**Needed before Phase 4**

| Prerequisite | Why |
|---|---|
| MVP objects + opportunities complete | Lead conversion and tasks/events attach to them |
| Queue connection + scheduler runnable | Email digests, reminders, subscriptions |
| Mailpit (or mail driver) verified | Safe testing of assignment/owner-change emails |
| FullCalendar installed and registered | Calendar day/week/month + drag-and-drop; see `<x-calendar>` |
| Polymorphic relation morph map planned | Tasks/Events `related_to` without storing raw class names |
| Pre-built report queries reusable | Report/dashboard builders should share query layer |
| `DB::transaction()` pattern established | Lead conversion must be atomic |

**Why:** Conversion, calendar, and email are multi-step and failure-sensitive. Queues/Mailpit and transactions prevent partial writes and blocked HTTP requests.

**Exit:** all P1 items; critical journeys covered by tests.

**Status:** Phase 4 Dev A exit met — Tasks, email/queues, Event schema (no calendar UI), CSV import/export. See [PHASE_4_DEV_A.md](PHASE_4_DEV_A.md). Dev B track (calendar, conversion, builders, HOME widgets) remains separate.

---

### Phase 5 — Enhancements (P2)

**Needed before Phase 5**

| Prerequisite | Why |
|---|---|
| P0 + P1 features stable | P2 builds on search, reports, dashboards, accounts |
| File storage outside `public/` + type whitelist rules | Attachments (25MB, preview, scan) |
| Report subscription + queue jobs working | FR-RPT-006 depends on mail/queues |
| Account parent self-reference already in schema | Hierarchy / roll-ups (FR-ACCT-004) |
| OpenAPI approach agreed (if REST API in scope) | Consistent public API docs (SRS 8.3) |

**Why:** Attachments, MFA, and API are cross-cutting; introducing them earlier delays MVP. Hierarchy needs existing account graph.

---

### Phase 6 — Hardening and QA

**Needed before Phase 6**

| Prerequisite | Why |
|---|---|
| Feature set frozen for release candidate | QA against a known scope |
| Factories/seeders for large datasets | Performance tests (100k+ rows, NFR-PERF) |
| Authorization test matrix by role | Prove policies were not skipped |
| Accessibility checklist (WCAG 2.1 AA) | NFR-USE requirements |
| Dependency scanning in CI or release checklist | Security baseline |

**Why:** Hardening without complete features wastes effort; load/security tests need realistic data and routes.

---

### Phase 7 — Deployment and handover

**Needed before Phase 7**

| Prerequisite | Why |
|---|---|
| Phase 6 gates green | Do not ship known critical defects |
| Staging environment + secrets management | UAT without touching production |
| Backup strategy for PostgreSQL | 30-day retention requirement |
| Production Dockerfile / host plan | Repeatable deploys and rollback |
| Runbooks (deploy, rollback, health) | Handover to operators |

**Why:** Deployment assumes a tested build; ops docs and backups are mandatory for production CRM data (GDPR / continuity).

---

## 4. Dependency chain (summary)

```text
Phase 0  env, Postgres, CI, layout, rules
    ↓
Phase 1  auth + RBAC + shared Blade components
    ↓
Phase 2  Accounts → Contacts + Leads
    ↓
Phase 3  Cases + Opportunities + search + home reports  = MVP
    ↓
Phase 4  tasks, calendar, conversion, email, builders
    ↓
Phase 5  P2 polish (attachments, MFA, API, hierarchy, …)
    ↓
Phase 6  QA / performance / security / a11y
    ↓
Phase 7  staging → production → handover
```

Skipping a layer (for example building Opportunities before Accounts, or email before queues) creates rework and merge risk.

---

## 5. Quick reference — important paths

| Path | Role |
|---|---|
| `Master plan/PROJECT_PLAN.md` | Full phase plan and ownership |
| `Master plan/crm-*.mdc` | Domain / Laravel / Postgres / frontend rules |
| `compose.yaml` | Local Postgres + Mailpit |
| `.env.example` | Documented environment defaults |
| `.github/workflows/ci.yml` | Automated quality gate |
| `resources/views/layouts/app.blade.php` | App chrome for all modules |
| `resources/css/app.css` | SRS design tokens |
| `phpunit.xml` | PostgreSQL test database settings |
| `scripts/apply-docker-env.php` | Point `.env` at Docker services |

---

## 6. Related decisions log

| Decision | Rationale |
|---|---|
| PostgreSQL instead of MySQL | Project/rules lock; portable Eloquent; no InnoDB-specific assumptions |
| Docker Postgres on host port **5433** | Coexist with existing Windows Postgres on 5432 |
| App DB `crm_database` / user `postgres` | Aligns with project `.env.example` and local tooling |
| Mailpit instead of log-only mail long term | Visible email testing for auth and notifications |
| **Locked UI libs:** Chart.js + FullCalendar (npm + Vite entries + Blade components) | No alternate chart/calendar stacks; Phase 3/4 reuse the same registration |
| CI uses real PostgreSQL service | Same constraint as local Pest config — no SQLite shortcut |
| No `develop` branch | Feature PRs target `main` |

---

*End of document. Update this file when Phase 0 governance items close or when a phase exit is formally signed off.*
