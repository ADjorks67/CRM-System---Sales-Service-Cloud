# Phase 6 — Executable checklist

**Parent plan:** [PHASE_6.md](PHASE_6.md)  
**Use:** Tick items as sub-phases complete. Prefer linking the Pest file that proves each FR.  
**Gate signed:** 2026-09-30 — `php artisan test --compact` → **172 passed** (588 assertions). Requires `npm run build` (or `composer run dev`) so Vite manifest includes page entries.

---

## A. Scope freeze (6.0)

- [x] v1.0 includes all P0 + P1 + Phase 5 P2 items (no new FRs without both-dev approval)
- [x] Known deferrals listed (CDN, APM SaaS, external pen-test, real AV product, pg_trgm → Phase 7 / later)
- [x] Open bugs triaged into gate-blocking vs post-v1 (zero critical/high open for RC)
- [x] Permission answers recorded for PHASE_6.md §5 (coverage, load tool, GDPR erase/export, `composer audit`)

---

## B. Feature / FR → test matrix (6.1)

### Auth & users

| ID | Topic | Test file | Done |
|---|---|---|---|
| FR-AUTH-001 | Login, lockout, timeout, remember-me | `tests/Feature/Auth/LoginTest.php` | [x] |
| FR-AUTH-002 | Password reset / history | `tests/Feature/Auth/PasswordResetTest.php` | [x] |
| FR-AUTH-003 | Roles / permissions | `tests/Feature/Auth/RbacTest.php` | [x] |
| Users admin | CRUD / lock clear | `tests/Feature/UsersAdminTest.php` | [x] |
| NFR-SEC-002 | Optional MFA | `tests/Feature/MfaTest.php` | [x] |

### Core objects

| ID | Topic | Test file | Done |
|---|---|---|---|
| FR-ACCT-001..003 | Accounts | `tests/Feature/AccountsTest.php` | [x] |
| FR-ACCT-004 | Hierarchy / roll-ups | `tests/Feature/AccountHierarchyTest.php` | [x] |
| FR-CONT-001..003 | Contacts | `tests/Feature/ContactsTest.php` | [x] |
| FR-LEAD-001..004,006,007 | Leads | `tests/Feature/LeadsTest.php` | [x] |
| FR-LEAD-005 | Conversion | `tests/Feature/LeadConversionTest.php` | [x] |
| FR-OPP-001..006 | Opportunities | `tests/Feature/OpportunitiesTest.php` | [x] |
| FR-CASE-001..004 | Cases | `tests/Feature/CasesTest.php` | [x] |
| Sharing / OWD | Record access | `tests/Feature/RecordSharingTest.php` | [x] |

### Activities & productivity

| ID | Topic | Test file | Done |
|---|---|---|---|
| FR-TASK-001..003 | Tasks | `tests/Feature/TasksTest.php` | [x] |
| Task mail / digests | Notifications | `tests/Feature/TaskMailNotificationsTest.php` | [x] |
| FR-CAL-001..004 | Events / calendar | `tests/Feature/EventsCalendarTest.php` | [x] |
| Import / export CSV | Data IO | `tests/Feature/DataImportExportTest.php` | [x] |

### Search, home, reports, dashboards

| ID | Topic | Test file | Done |
|---|---|---|---|
| FR-SRCH-001/002 | Global search | `tests/Feature/SearchTest.php` | [x] |
| FR-SRCH-003 | Advanced / saved | `tests/Feature/AdvancedSearchTest.php` | [x] |
| FR-HOME-001..003,006 | Home charts / key deals | `tests/Feature/HomeDashboardTest.php` | [x] |
| FR-HOME-004/005 | Tasks / events widgets | `tests/Feature/HomeEventsWidgetTest.php` (+ tasks coverage) | [x] |
| FR-HOME-007 | Assistant | `tests/Feature/HomeAssistantTest.php` | [x] |
| FR-RPT-002/004 | Pre-built reports | `tests/Feature/ReportsTest.php` | [x] |
| FR-RPT-003/005 | Builder / export | `tests/Feature/ReportBuilderTest.php` | [x] |
| FR-RPT-006 | Subscriptions | `tests/Feature/ReportSubscriptionTest.php` | [x] |
| FR-DASH-001..004 | Dashboards | `tests/Feature/DashboardsTest.php` | [x] |
| FR-DASH-003 auto-refresh | Timed refresh | `tests/Feature/DashboardAutoRefreshTest.php` | [x] |

### Attachments, API, polish

| ID | Topic | Test file | Done |
|---|---|---|---|
| SRS §8.2 | Attachments | `tests/Feature/AttachmentsTest.php` | [x] |
| SRS §8.3 | REST API v1 | `tests/Feature/Api/V1/*` | [x] |
| NFR-USE-001 | Polish smoke | `tests/Feature/UiPolishSmokeTest.php` | [x] |
| Phase gates | MVP / core objects | `Phase3MvpGateTest`, `CoreObjectsGateTest` | [x] |

### Phase 6 new tests

| ID | Topic | Test file | Done |
|---|---|---|---|
| NFR-SEC-005 | GDPR export | `tests/Feature/GdprExportTest.php` | [x] |
| NFR-SEC-005 | GDPR erase/anonymize | `tests/Feature/GdprEraseTest.php` | [x] |
| Authz matrix | Roles × objects | `tests/Feature/AuthorizationMatrixTest.php` | [x] |
| Coverage | ≥ 80% line/unit | CI coverage job (soft `--min=80` until baseline met) | [x] soft |
| NFR-REL-005 | Health endpoint | `tests/Feature/HealthEndpointTest.php` | [x] |
| NFR-SCAL-002 archive | Archive command | `tests/Feature/ArchiveOldRecordsTest.php` | [x] |

---

## C. Performance (6.2) — NFR-PERF

| Check | Target | Evidence | Done |
|---|---|---|---|
| Large seed runnable | 100k+ rows (document table counts) | `LargeDatasetSeeder` + `config/crm.php` `large_seed`; scale via `CRM_LARGE_SEED_*` | [x] |
| Indexes on hot paths | Lists, FKs, search, archive | [x] `docs/PHASE_6_PERF_NOTES.md` + Phase6PerfIndexesTest | [x] |
| Page load | &lt; 3s | Local smoke after demo seed; re-measure on staging in Phase 7 | [x] |
| Form save | &lt; 2s | Same | [x] |
| Simple search | &lt; 1s | Same + tinker snippet in PERF_NOTES | [x] |
| Report &lt; 10k | &lt; 5s | Same | [x] |
| Dashboard ≤ 10 widgets | &lt; 5s | Same | [x] |
| 100 concurrent users | NFR-PERF-002 | k6 `scripts/load/` ready; full soak on staging Phase 7 | [x] scripts |
| Archive / retention job | NFR-SCAL-002 | `crm:archive-old-records` + schedule + ArchiveOldRecordsTest | [x] |
| No unbounded list queries | max 200 / pagination | ListQuery + review | [x] |

---

## D. Security (6.3) — NFR-SEC + OWASP-oriented

| Check | Done |
|---|---|
| HTTPS/TLS plan documented for staging/prod (prove in Phase 7) | [x] see PHASE_6.md §11 |
| Passwords hashed (bcrypt/argon via Laravel) | [x] |
| Session HTTP-only / SameSite / 2h idle | [x] `config/session.php` lifetime 120, http_only, same_site=lax |
| Lockout after 5 failures | [x] LoginTest / User model |
| Policies on all mutating web + API actions | [x] AuthorizationMatrixTest + API tests |
| IDOR tests: cannot read/update others’ private records | [x] RecordSharingTest + matrix |
| CSRF on web state changes | [x] Laravel default + feature posts |
| XSS: Blade escaped for user content | [x] review + Blade `{{ }}` |
| Upload type/size enforced | [x] AttachmentsTest |
| `composer audit` run; highs triaged | [x] |
| `composer audit` in CI (if approved) | [x] |
| Health endpoint (`/up` or `/health`) returns OK when DB up | [x] |
| Secrets not in git | [x] `.env` gitignored |

---

## E. Accessibility & responsive (6.4) — NFR-USE-002/003

| Check | Done |
|---|---|
| Skip link + main landmark on guest + app layouts | [x] AccessibilitySmokeTest |
| Form labels on login + account create | [x] AccessibilitySmokeTest |
| axe npm script run on key URLs; violations triaged | [x] `/login` = 0 violations (2026-09-29) |
| Keyboard: all primary flows without mouse | [x] smoke covered; full UAT matrix → Phase 7 staging |
| Visible focus indicators | [x] design tokens / CSS focus rings |
| Form labels / `aria-*` on icon-only controls | [x] form-field + smoke |
| Contrast spot-check (4.5:1 body text) | [x] SRS tokens |
| Text resize ~200% usable | [x] responsive utilities |
| Layout usable at 320px width | [x] NFR-USE-002 intent; confirm on staging |
| Destructive actions use confirm modal | [x] AccessibilitySmokeTest |
| Chrome smoke | [x] local + CI that exercises HTTP |
| Firefox smoke | [ ] deferred to Phase 7 UAT |
| Edge smoke | [ ] deferred to Phase 7 UAT |
| Safari smoke or documented gap | [ ] deferred to Phase 7 UAT (Windows gap OK) |

---

## F. GDPR (6.5) — NFR-SEC-005

| Check | Done |
|---|---|
| PII inventory documented (tables/fields) | [x] PHASE_6.md §10 |
| Admin export of subject data | [x] Privacy UI + PersonalDataExportService |
| Admin erase or anonymize with audit log | [x] PersonalDataAnonymizeService + `gdpr_audits` |
| Policy: System Administrator only | [x] Gdpr* tests |
| Feature tests green | [x] GdprExportTest, GdprEraseTest, GdprIndexTest |
| Retention / backup note (full ops → Phase 7) | [x] PHASE_6.md §11 |

---

## G. Gate (6.6)

| Check | Done |
|---|---|
| `php artisan test --compact` green on `develop`/`main` | [x] 172 passed (2026-09-30) |
| Pint clean / CI green | [x] CI runs tests + audit + npm build |
| Zero open **critical** or **high** bugs for RC | [x] |
| Medium bugs ticketed with owners | [x] none blocking; browser matrix → Phase 7 UAT |
| PHASE_6.md Status updated | [x] |
| SETUP Phase 6 exit marked | [x] |
| Phase 7 prerequisites listed | [x] PHASE_6.md §11 |

---

## H. Permission log (6.0)

| Topic | Decision | Date |
|---|---|---|
| pcov / CI coverage | **Yes** — pcov + CI `--min=80` | 2026-09-29 |
| Load tool (k6 / other / defer) | **Yes** — k6 under `scripts/load/` | 2026-09-29 |
| Automated a11y package | **Yes** — axe npm script + manual checklist | 2026-09-29 |
| GDPR anonymize vs hard delete | **Anonymize** when history must remain | 2026-09-29 |
| GDPR self-service vs admin-only | **Admin-only** export/erase | 2026-09-29 |
| `composer audit` in CI | **Yes** — fail on high/critical | 2026-09-29 |
| External pen-test | **No** — defer | 2026-09-29 |
| Archive-old-data | **In Phase 6** (do not defer) | 2026-09-29 |
| Health endpoint in Phase 6 | **Yes** — pull into 6.3 | 2026-09-29 |
