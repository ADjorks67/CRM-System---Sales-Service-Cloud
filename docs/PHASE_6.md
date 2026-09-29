# Phase 6 — Hardening and QA

**Sprint:** Week 16 (PROJECT_PLAN §4) · **Owners:** both developers  
**Priority:** Prove P0–P2 release candidate quality against SRS NFRs  
**Prerequisite:** Phase 5 complete ([PHASE_5.md](PHASE_5.md)) — feature set frozen for RC  
**Companion checklist:** [PHASE_6_CHECKLIST.md](PHASE_6_CHECKLIST.md)

Phase 6 is **not** a feature sprint for new FRs. It closes quality gaps, implements compliance surfaces called out in the SRS (GDPR), and produces a signed-off gate for Phase 7 deployment.

---

## 1. Scope map (SRS IDs)

| Area | SRS IDs | Nature |
|---|---|---|
| Coverage & FR matrix | PROJECT_PLAN §4 Phase 6; DoD §7 | Tests / process |
| Performance | **NFR-PERF-001..004**, NFR-SCAL-002 (indexing) | Seed, indexes, measure |
| Security | **NFR-SEC-001..004**, OWASP baseline | Tests + CI + review |
| Compliance / GDPR | **NFR-SEC-005** | **Product work** (export + erase) |
| Usability / a11y | **NFR-USE-002**, **NFR-USE-003** | Manual + **axe** automation |
| Scalability archive | **NFR-SCAL-002** retention/archive | Config + job (locked in for Phase 6) |
| Health | **NFR-REL-005** health check endpoint | **In Phase 6** (6.3); APM SaaS still Phase 7 |
| Reliability (defer) | NFR-REL-001..004, backups/CDN/APM product | **Phase 7** |

**Out of Phase 6**

| Item | Why |
|---|---|
| New P2/P3 product features | Scope freeze |
| Production Docker / staging / UAT | Phase 7 |
| Daily backups, APM SaaS, CDN | Phase 7 (NFR-REL / NFR-SCAL infra) |
| Real antivirus product | Keep Phase 5 `AttachmentScanner` unless you approve a package |
| Formal external penetration test | **Deferred** (team decision 2026-09-29) |

---

## 2. Sub-phases (recommended order)

```text
6.0  Scope freeze & gap inventory          (both, short)
  ↓
6.1  Test coverage & FR/NFR matrix         (both, parallel by module ownership)
  ↓
6.2  Performance & data scale              (both; Dev A seeds/indexes, Dev B report/dash timing)
  ↓
6.3  Security hardening                    (both; Dev A leads authz/API/audit)
  ↓
6.4  Accessibility & responsive QA         (Dev B leads; Dev A assists on forms they own)
  ↓
6.5  GDPR / compliance surfaces           (Dev A leads; Dev B reviews UI)
  ↓
6.6  Gate: bug bash, critical-bug bar, sign-off → Phase 7
```

Waves **6.1–6.4** can overlap after **6.0** if ownership files do not collide. **6.5** should land before the final gate so erase/export are covered by tests. **6.6** is sequential.

---

### 6.0 — Scope freeze & gap inventory

**Goal:** Agree what “RC” means and list holes before spending QA effort.

| Task | Owner | Output |
|---|---|---|
| Freeze v1.0 feature list (P0+P1+accepted P2) | both | Short note in this doc Status section |
| Inventory open bugs / known deferrals from Phase 4–5 docs | both | Issues labeled `phase-6` or checklist section |
| Build FR → test file matrix skeleton | both | [PHASE_6_CHECKLIST.md](PHASE_6_CHECKLIST.md) |
| Confirm no new Composer/npm packages without approval | both | Follow Phase 5 rule |

**Exit:** Matrix started; freeze recorded; no unapproved tool installs.

---

### 6.1 — Test coverage & requirement matrix

**Goal:** Feature tests for every in-scope FR/NFR; unit coverage ≥ **80%** (PROJECT_PLAN).

| Task | Owner | Notes |
|---|---|---|
| Complete FR → test mapping; fill gaps | Module owner (see §3) | Prefer feature tests; unit only for pure logic |
| Authorization matrix: 5 roles × critical actions | Dev A leads | Deny + allow cases; reuse `RbacTest` / policy patterns |
| Critical journeys regression | both | Lead convert, opp close, case resolve, MFA login, API token |
| Coverage report locally; raise to 80% | both | **pcov** locally + CI coverage job (`--min=80`) — locked §5 |
| Arch / smoke gates if useful | both | Optional Pest `arch()`; no new deps |

**Exit:** Checklist FR rows green; coverage ≥ 80% (once tooling approved) or documented interim metric; CI still green.

**Depends on:** 6.0 freeze (do not write tests for deferred P3).

---

### 6.2 — Performance & data scale

**Goal:** Prove NFR-PERF targets against large data; tune indexes (NFR-SCAL-002).

| Task | Owner | Notes |
|---|---|---|
| `LargeDatasetSeeder` / artisan command (100k+ rows across core tables) | Dev A | Use factories in chunks; never run in default `DatabaseSeeder` |
| Index audit on list/search/report/FK columns | Dev A | Migrations additive only; no rewrite of shipped migrations |
| Timed smoke: list, search, report (&lt;10k), dashboard (≤10 widgets) | Dev B | Record vs NFR-PERF-001 budgets |
| Query / N+1 review on hot paths | both | Eager load; avoid loading all rows |
| Concurrent-user exercise (k6) | both | Scripts under `scripts/load/` (dev-only; not a PHP dep) |
| Configurable archive / retention (NFR-SCAL-002) | Dev A | Soft-archive or move old closed records per config; document defaults |

**NFR-PERF-001 budgets (SRS)**

| Operation | Target |
|---|---|
| Initial page load | &lt; 3s |
| Form save | &lt; 2s |
| Simple search | &lt; 1s |
| Report (&lt; 10k rows) | &lt; 5s |
| Dashboard (≤ 10 widgets) | &lt; 5s |

Also: 100 concurrent users (NFR-PERF-002); DB optimization for &gt; 100k rows.

**Exit:** Seeder documented; indexes merged; perf notes attached to checklist; known hotspots ticketed or fixed.

**Depends on:** Stable factories (Phase 2–5); Docker Postgres.

---

### 6.3 — Security hardening

**Goal:** NFR-SEC-001..004 + OWASP-aligned checklist; no critical authz holes.

| Task | Owner | Notes |
|---|---|---|
| Policy / sharing regression (IDOR on show/update/delete/API) | Dev A | Every CRM object + API v1 |
| CSRF, mass-assignment, upload whitelist re-verify | Dev A | Attachments already Phase 5 |
| Session / lockout / MFA path smoke | Dev A | FR-AUTH + NFR-SEC-002 |
| `composer audit` in CI (fail on high) | Dev A | Native Composer — **confirm in §5 #6** |
| `GET /up` or `/health` (DB + app) | Dev A | NFR-REL-005; simple JSON/text — no APM SaaS |
| XSS / Blade `{{ }}` review on user-generated fields | Dev B | Especially reports, notes, assistant copy |
| Dependency / secret hygiene (`.env` not committed) | both | Already CI/process |

**Exit:** Authz matrix green; audit findings triaged; health endpoint tested; OWASP checklist signed in [PHASE_6_CHECKLIST.md](PHASE_6_CHECKLIST.md).

**Depends on:** 6.1 authz tests ideally in place first (can start in parallel).

---

### 6.4 — Accessibility, responsive, cross-browser

**Goal:** NFR-USE-002 / NFR-USE-003 (WCAG 2.1 AA intent).

| Task | Owner | Notes |
|---|---|---|
| Keyboard path: login → list → create → detail → destructive confirm | Dev B | All primary tabs |
| Focus, labels, skip link, contrast spot-check | Dev B | Design tokens already SRS §6 |
| Responsive at 320 / 768 / 1280 | Dev B | Touch targets ≥ 44px where interactive |
| Browser matrix: Chrome, Firefox, Safari, Edge | both | Manual smoke; record OS if Safari unavailable |
| Confirm modals on delete / archive | both | NFR-USE-004 |
| Automated axe scan (npm script) on key URLs | Dev B | Locked §5 — `@axe-core/cli` or equivalent; manual checklist still required |

**Exit:** Checklist a11y/responsive rows green or accepted exceptions listed; axe script documented.

**Depends on:** UI freeze from 6.0 (no polish churn mid-audit).

---

### 6.5 — GDPR / compliance surfaces [NFR-SEC-005]

**Goal:** Right to **portability** (export) and **erasure** (delete) for personal data; audit-trail review.

This is the only Phase 6 slice that ships **new product UI/API**. Keep it admin-scoped and narrow.

| Task | Owner | Notes |
|---|---|---|
| Personal data inventory (User, Contact, Lead email/phone, etc.) | Dev A | Document in checklist |
| Admin **export** subject data (JSON or ZIP of CSVs) | Dev A | FormRequest + Policy + transaction-safe read |
| Admin **erase / anonymize** subject data | Dev A | **Anonymize** PII when hard delete would break history; log action |
| Audit trail review of create/update ownership fields | both | Confirm `HasAuditFields` coverage; gap list |
| Feature tests for export + erase | Dev A | Role: System Administrator only |
| Retention / archive note | both | App archive in 6.2; DB backup retention still Phase 7 |

**Exit:** Admin can export and erase/anonymize a subject; tests green; inventory documented.

**Depends on:** Auth + Users admin (Phase 1); Contacts/Leads/etc. present.

**Locked policy:** Admin-only export; anonymize (not hard-delete) when related Opportunities/Cases/Activities must remain.

---

### 6.6 — Gate & handoff to Phase 7

**Goal:** Gate 5 from PROJECT_PLAN flowchart — all tests green, no critical bugs.

| Task | Owner | Notes |
|---|---|---|
| Full suite `php artisan test --compact` | both | On `develop` |
| Bug bash (2h) against seeded demo + large-data sample | both | File issues; P0/P1 only for gate |
| Critical / high defect bar: **zero open** | both | Medium+ may defer with tickets |
| Update this doc Status + SETUP Phase 6 exit | both | |
| Phase 7 kickoff prerequisites list | both | Staging, Docker prod, backups |

**Exit:** Sign-off recorded; ready for [Phase 7](../Master%20plan/PROJECT_PLAN.md) deployment work.

---

## 3. Ownership (avoid merge conflicts)

| Area | Primary | Secondary |
|---|---|---|
| Auth, users, policies, API, MFA, GDPR, import security | Dev A | Dev B review |
| Reports, dashboards, calendar, home, a11y UI | Dev B | Dev A review |
| Large seeder + indexes | Dev A | Dev B consumes for timing |
| CI workflow changes | one PR owner at a time | other reviews |
| Shared layout / `routes/web.php` | Additive only; small PRs | |

Branch naming: `feature/NFR-PERF-large-seed`, `feature/NFR-SEC-005-gdpr-export`, `chore/phase-6-coverage-gaps`, etc.

---

## 4. Dependencies

### Already satisfied (Phases 0–5)

| Dependency | Status | Used by |
|---|---|---|
| Auth, RBAC, 5 roles | Done | 6.1, 6.3 |
| Core CRM objects + factories | Done | 6.1, 6.2 |
| Tasks, events, conversion, builders | Done | Journey tests |
| Attachments, MFA, API v1 | Done | Security + API authz |
| Pest + PostgreSQL in CI | Done | All sub-phases |
| Design tokens + layout a11y skip link | Done | 6.4 |
| Mailpit / queues | Done | Regression only |

### Hard dependencies between sub-phases

```text
6.0 freeze
  → 6.1 coverage matrix (know in-scope IDs)
  → 6.2 large seed (factories stable; no schema thrash)
  → 6.3 security (authz tests from 6.1 preferred)
  → 6.4 a11y (UI freeze)
6.5 GDPR
  → needs Users admin + CRM PII models (met)
  → should finish before 6.6 gate
6.6
  → needs 6.1–6.5 exits (or explicit waivers)
```

### Soft / external

| Dependency | Impact if missing |
|---|---|
| Safari device/VM | Browser matrix incomplete — note OS limitation |
| Staging with HTTPS | NFR-SEC-001 TLS proven in Phase 7; local HTTP OK for app logic |
| k6 installed for load scripts | Document install in `scripts/load/README.md` |

---

## 5. Locked decisions (2026-09-29)

| # | Topic | Decision |
|---|---|---|
| 1 | Coverage | **Yes** — `pcov` locally + CI coverage job with `--min=80` |
| 2 | Load tests | **Yes** — **k6** scripts in `scripts/load/` (not a Composer package) |
| 3 | A11y automation | **Yes** — axe-based npm script **plus** manual WCAG checklist |
| 4 | GDPR erase | **Yes** — **anonymize** PII when hard delete would break related history |
| 5 | GDPR export | **Yes** — **admin-only** (no end-user self-service portal in v1) |
| 6 | `composer audit` in CI | **Yes** — fail on high/critical (native `composer audit`) |
| 7 | External pen-test | **No** — defer; rely on authz tests + OWASP checklist |
| 8 | Archive-old-data | **In Phase 6** (interpreted as do **not** defer) — configurable retention/archive under 6.2 |
| 9 | Health endpoint | **Pull into Phase 6** (6.3) — app/DB health only; APM still Phase 7 |

### What `#6 composer audit` means

Composer can scan PHP dependencies for known CVEs:

```bash
composer audit
```

**In Phase 6 we would:**

1. Run `composer audit` locally and triage findings (update or document accepted risk).
2. Add a CI step in `.github/workflows/ci.yml`, e.g. after `composer install`:
   - `composer audit --no-dev` (or with `--abandoned=ignore` if needed)
   - Fail the job when severity is **high** / **critical** (exact flags depend on Composer version).
3. No new package — this is built into Composer.

**You still need to say yes/no** to putting it in CI (it can fail builds when a new advisory appears until you bump a package).

**Locked 2026-09-29:** team accepted — add to CI.

---

## 6. Definition of Done (per sub-phase)

- Tasks in that sub-phase done; SRS IDs referenced in commits/PRs  
- Tests added/updated; `vendor/bin/pint --dirty --format agent` on PHP changes  
- No Vue / Inertia / React / TypeScript / MySQL  
- Checklist section updated  
- PR reviewed by the other developer  

---

## 7. Local verify (phase)

```bash
docker compose up -d
php artisan migrate:fresh --seed
# Optional (after 6.2): php artisan db:seed --class=LargeDatasetSeeder
composer run dev
php artisan test --compact
# Mailpit: http://localhost:8025
# Smoke: role matrix + search timing + admin GDPR export/erase + keyboard nav
```

---

## 8. Status

| Sub-phase | Status |
|---|---|
| Docs / kickoff | Done |
| 6.0 Scope freeze | Done — §5 locked (incl. composer audit CI) |
| 6.1 Coverage & matrix | In progress — authz matrix + a11y/GDPR smoke tests |
| 6.2 Performance (+ archive) | In progress — composite indexes + `PHASE_6_PERF_NOTES.md` |
| 6.3 Security (+ health) | Started — `/health`, `composer audit` in CI |
| 6.4 A11y / responsive (+ axe) | In progress — axe `/login` clean; AccessibilitySmokeTest; manual matrix open |
| 6.5 GDPR | Started — admin Privacy UI, export JSON, anonymize + `gdpr_audits` |
| 6.6 Gate | Not started |

**Feature freeze for RC:** Phase 5 Done assumed.

---

## 9. What was built (kickoff wave — 2026-09-29)

| Area | Location |
|---|---|
| Health JSON | `HealthController`, `routes/health.php` (`/health`; Laravel `/up` kept) |
| GDPR admin tools | `GdprController`, `routes/gdpr.php`, `resources/views/gdpr/index.blade.php`, Privacy nav link |
| Export / anonymize | `PersonalDataExportService`, `PersonalDataAnonymizeService`, `GdprAudit` |
| Archive job | `crm:archive-old-records`, scheduled daily 02:30, `config/crm.php` |
| Large seed | `database/seeders/LargeDatasetSeeder.php` (opt-in only) |
| Authz matrix tests | `tests/Feature/AuthorizationMatrixTest.php` |
| GDPR / archive / health tests | `GdprExportTest`, `GdprEraseTest`, `ArchiveOldRecordsTest`, `HealthEndpointTest` |
| CI | `composer audit`; coverage job with pcov; soft `--min=80` until baseline met |
| Load / a11y scripts | `scripts/load/*` (k6), `npm run a11y` (@axe-core/cli) |
| Perf indexes | migration `2026_09_29_152141_add_phase6_performance_indexes` |
| Perf notes | [PHASE_6_PERF_NOTES.md](PHASE_6_PERF_NOTES.md) |
| A11y smoke tests | `tests/Feature/AccessibilitySmokeTest.php` |

---

## 10. Related docs

| Doc | Role |
|---|---|
| [PHASE_6_PERF_NOTES.md](PHASE_6_PERF_NOTES.md) | Index + timing evidence |
| [PHASE_6_CHECKLIST.md](PHASE_6_CHECKLIST.md) | Executable matrix + exit gates |
| [SETUP_AND_PHASE_PREREQUISITES.md](SETUP_AND_PHASE_PREREQUISITES.md) | Phase 6 prerequisites |
| [PHASE_5.md](PHASE_5.md) | Prerequisite feature set |
| `Master plan/PROJECT_PLAN.md` §4 Phase 6 | Master scope |
| [SRS_DISTILLED.md](SRS_DISTILLED.md) | Working SRS index |
| `docs/CRM_Requirements_Specification.docx` §5 NFRs | Full acceptance criteria |
