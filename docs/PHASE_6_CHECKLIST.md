# Phase 6 — Executable checklist

**Parent plan:** [PHASE_6.md](PHASE_6.md)  
**Use:** Tick items as sub-phases complete. Prefer linking the Pest file that proves each FR.

---

## A. Scope freeze (6.0)

- [ ] v1.0 includes all P0 + P1 + Phase 5 P2 items (no new FRs without both-dev approval)
- [ ] Known deferrals listed (archive retention, CDN, external pen-test, real AV, …)
- [ ] Open bugs triaged into gate-blocking vs post-v1
- [ ] Permission answers recorded for PHASE_6.md §5 (coverage, load tool, GDPR erase/export, `composer audit`)

---

## B. Feature / FR → test matrix (6.1)

Fill **Test file** as gaps are closed. Mark N/A only if explicitly deferred.

### Auth & users

| ID | Topic | Test file | Done |
|---|---|---|---|
| FR-AUTH-001 | Login, lockout, timeout, remember-me | `tests/Feature/Auth/LoginTest.php` | [ ] |
| FR-AUTH-002 | Password reset / history | `tests/Feature/Auth/PasswordResetTest.php` | [ ] |
| FR-AUTH-003 | Roles / permissions | `tests/Feature/Auth/RbacTest.php` | [ ] |
| Users admin | CRUD / lock clear | `tests/Feature/UsersAdminTest.php` | [ ] |
| NFR-SEC-002 | Optional MFA | `tests/Feature/MfaTest.php` | [ ] |

### Core objects

| ID | Topic | Test file | Done |
|---|---|---|---|
| FR-ACCT-001..003 | Accounts | `tests/Feature/AccountsTest.php` | [ ] |
| FR-ACCT-004 | Hierarchy / roll-ups | `tests/Feature/AccountHierarchyTest.php` | [ ] |
| FR-CONT-001..003 | Contacts | `tests/Feature/ContactsTest.php` | [ ] |
| FR-LEAD-001..004,006,007 | Leads | `tests/Feature/LeadsTest.php` | [ ] |
| FR-LEAD-005 | Conversion | `tests/Feature/LeadConversionTest.php` | [ ] |
| FR-OPP-001..006 | Opportunities | `tests/Feature/OpportunitiesTest.php` | [ ] |
| FR-CASE-001..004 | Cases | `tests/Feature/CasesTest.php` | [ ] |
| Sharing / OWD | Record access | `tests/Feature/RecordSharingTest.php` | [ ] |

### Activities & productivity

| ID | Topic | Test file | Done |
|---|---|---|---|
| FR-TASK-001..003 | Tasks | `tests/Feature/TasksTest.php` | [ ] |
| Task mail / digests | Notifications | `tests/Feature/TaskMailNotificationsTest.php` | [ ] |
| FR-CAL-001..004 | Events / calendar | `tests/Feature/EventsCalendarTest.php` | [ ] |
| Import / export CSV | Data IO | `tests/Feature/DataImportExportTest.php` | [ ] |

### Search, home, reports, dashboards

| ID | Topic | Test file | Done |
|---|---|---|---|
| FR-SRCH-001/002 | Global search | `tests/Feature/SearchTest.php` | [ ] |
| FR-SRCH-003 | Advanced / saved | `tests/Feature/AdvancedSearchTest.php` | [ ] |
| FR-HOME-001..003,006 | Home charts / key deals | `tests/Feature/HomeDashboardTest.php` | [ ] |
| FR-HOME-004/005 | Tasks / events widgets | `tests/Feature/HomeEventsWidgetTest.php` (+ tasks coverage) | [ ] |
| FR-HOME-007 | Assistant | `tests/Feature/HomeAssistantTest.php` | [ ] |
| FR-RPT-002/004 | Pre-built reports | `tests/Feature/ReportsTest.php` | [ ] |
| FR-RPT-003/005 | Builder / export | `tests/Feature/ReportBuilderTest.php` | [ ] |
| FR-RPT-006 | Subscriptions | `tests/Feature/ReportSubscriptionTest.php` | [ ] |
| FR-DASH-001..004 | Dashboards | `tests/Feature/DashboardsTest.php` | [ ] |
| FR-DASH-003 auto-refresh | Timed refresh | `tests/Feature/DashboardAutoRefreshTest.php` | [ ] |

### Attachments, API, polish

| ID | Topic | Test file | Done |
|---|---|---|---|
| SRS §8.2 | Attachments | `tests/Feature/AttachmentsTest.php` | [ ] |
| SRS §8.3 | REST API v1 | `tests/Feature/Api/V1/*` | [ ] |
| NFR-USE-001 | Polish smoke | `tests/Feature/UiPolishSmokeTest.php` | [ ] |
| Phase gates | MVP / core objects | `Phase3MvpGateTest`, `CoreObjectsGateTest` | [ ] |

### Phase 6 new tests (add when built)

| ID | Topic | Test file | Done |
|---|---|---|---|
| NFR-SEC-005 | GDPR export | `tests/Feature/GdprExportTest.php` | [x] |
| NFR-SEC-005 | GDPR erase/anonymize | `tests/Feature/GdprEraseTest.php` | [x] |
| Authz matrix | Roles × objects | `tests/Feature/AuthorizationMatrixTest.php` | [x] |
| Coverage | ≥ 80% line/unit | CI coverage job (soft min until baseline) | [ ] |
| NFR-REL-005 | Health endpoint | `tests/Feature/HealthEndpointTest.php` | [x] |
| NFR-SCAL-002 archive | Archive command | `tests/Feature/ArchiveOldRecordsTest.php` | [x] |

---

## C. Performance (6.2) — NFR-PERF

| Check | Target | Evidence | Done |
|---|---|---|---|
| Large seed runnable | 100k+ rows (document table counts) | Seeder / command name | [ ] |
| Indexes on hot paths | Lists, FKs, search, archive | [x] `docs/PHASE_6_PERF_NOTES.md` + Phase6PerfIndexesTest |
| Page load | &lt; 3s | Note env + URL | [ ] |
| Form save | &lt; 2s | Note | [ ] |
| Simple search | &lt; 1s | Note | [ ] |
| Report &lt; 10k | &lt; 5s | Note | [ ] |
| Dashboard ≤ 10 widgets | &lt; 5s | Note | [ ] |
| 100 concurrent users | NFR-PERF-002 | k6 `scripts/load/` | [ ] |
| Archive / retention job | NFR-SCAL-002 | Config + artisan/schedule | [ ] |
| No unbounded list queries | max 200 / pagination | Code review | [ ] |

---

## D. Security (6.3) — NFR-SEC + OWASP-oriented

| Check | Done |
|---|---|
| HTTPS/TLS plan documented for staging/prod (prove in Phase 7) | [ ] |
| Passwords hashed (bcrypt/argon via Laravel) | [ ] |
| Session HTTP-only / SameSite / 2h idle | [ ] |
| Lockout after 5 failures | [ ] |
| Policies on all mutating web + API actions | [ ] |
| IDOR tests: cannot read/update others’ private records | [ ] |
| CSRF on web state changes | [ ] |
| XSS: Blade escaped for user content | [ ] |
| Upload type/size enforced | [ ] |
| `composer audit` run; highs triaged | [x] |
| `composer audit` in CI (if approved) | [x] |
| Health endpoint (`/up` or `/health`) returns OK when DB up | [x] |
| Secrets not in git | [ ] |

---

## E. Accessibility & responsive (6.4) — NFR-USE-002/003

| Check | Done |
|---|---|
| Skip link + main landmark on guest + app layouts | [x] AccessibilitySmokeTest |
| Form labels on login + account create | [x] AccessibilitySmokeTest |
| axe npm script run on key URLs; violations triaged | [x] `/login` = 0 violations (2026-09-29) |
| Keyboard: all primary flows without mouse | [ ] |
| Visible focus indicators | [ ] |
| Form labels / `aria-*` on icon-only controls | [ ] partial (form-field + smoke) |
| Contrast spot-check (4.5:1 body text) | [ ] |
| Text resize ~200% usable | [ ] |
| Layout usable at 320px width | [ ] |
| Destructive actions use confirm modal | [x] AccessibilitySmokeTest (account show) |
| Chrome smoke | [ ] |
| Firefox smoke | [ ] |
| Edge smoke | [ ] |
| Safari smoke or documented gap | [ ] |

---

## F. GDPR (6.5) — NFR-SEC-005

| Check | Done |
|---|---|
| PII inventory documented (tables/fields) | [ ] |
| Admin export of subject data | [ ] |
| Admin erase or anonymize with audit log | [ ] |
| Policy: System Administrator only | [ ] |
| Feature tests green | [ ] |
| Retention / backup note (full ops → Phase 7) | [ ] |

---

## G. Gate (6.6)

| Check | Done |
|---|---|
| `php artisan test --compact` green on `develop` | [ ] |
| Pint clean / CI green | [ ] |
| Zero open **critical** or **high** bugs for RC | [ ] |
| Medium bugs ticketed with owners | [ ] |
| PHASE_6.md Status updated | [ ] |
| SETUP Phase 6 exit marked | [ ] |
| Phase 7 prerequisites listed | [ ] |

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
