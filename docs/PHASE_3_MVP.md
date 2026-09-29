# Phase 3 — Sales, Service, Search & Basic Reports (MVP)

**SRS IDs:** FR-CASE-001..004, FR-OPP-001..006, FR-SRCH-001/002, FR-RPT-002/004, FR-HOME-001..003/006  
**Exit criteria:** Every P0 item in SRS §9.2 works — MVP demo.

## What was built

| Area | Location |
|---|---|
| Cases CRUD + workflow | `CrmCaseController`, `routes/cases.php`, `resources/views/cases/*` |
| Opportunities CRUD + stages | `OpportunityController`, `StageService`, `routes/opportunities.php`, `resources/views/opportunities/*` |
| Stage path UI | `resources/views/components/stage-path.blade.php` |
| Global search | `SearchController`, `GlobalSearchService`, `routes/search.php` |
| Home widgets | `HomeController`, `HomeDashboardService`, `resources/views/home.blade.php` |
| Pre-built reports | `app/Reports/*`, `ReportController`, `routes/reports.php` |
| Recent records / searches | `recent_records`, `recent_searches`, `RecentRecordService` |
| Schema | `cases`, `opportunities`, `opportunity_stage_histories`, `case_number_seq` |
| Demo data | `DemoCrmDataSeeder` (opportunities + cases) |

## Explicitly deferred

| Item | Phase |
|---|---|
| FR-LEAD-005 Convert Lead | Phase 4 |
| FR-HOME-004/005 Tasks/Events widgets | Phase 4 |
| FR-RPT-003 builder, FR-RPT-005 export | Phase 4 (done) |
| FR-RPT-006 subscriptions | Phase 5 — [PHASE_5_DEV_A.md](PHASE_5_DEV_A.md) |
| FR-SRCH-003 advanced/saved search | Phase 5 — [PHASE_5_DEV_A.md](PHASE_5_DEV_A.md) |
| Case Emails / Attachments / New Task | Tasks Phase 4; Attachments Phase 5 |
| Opportunity Products / Quotes | Later (empty shells) |
| FR-HOME-007 Assistant recommendations | Phase 5 — [PHASE_5_DEV_B.md](PHASE_5_DEV_B.md) |

## Phase 3 decisions

| Decision | Choice |
|---|---|
| Case model | `CrmCase` (table `cases`) to avoid PHP `case` keyword collisions |
| Case numbers | `CASE-{YYYY}-{#####}` via PostgreSQL sequence `case_number_seq` |
| Opportunity delete | Soft archive (`archived_at`); lists exclude archived by default |
| Closed cases | Read-only; **Reopen** sets status to `Working` and clears `closed_at` |
| Stage probability | Always overwritten from `opportunity_stage.meta_int` via `StageService` |
| Fiscal year | Calendar year (Jan–Dec) until company FY config exists |
| Recent records | Shared `recent_records` morph table (last 5 on Home) |
| Reports | Static query classes under `app/Reports/` for Phase 4 builder reuse |
| Sharing OWD | Private for `opportunity` / `case`; `scopeVisibleTo` on all lists |
| Lead conversion reports | Shipped; counts may be zero until Phase 4 |

## Local verify

```bash
docker compose up -d
php artisan migrate:fresh --seed
composer run dev
# Login: admin@crm.test / Password1!
# Smoke: Opportunities → stage path → Cases → Close/Reopen → Search → Home charts → Reports run
```

## Demo data (NFR-USE-005)

| Record | Owner |
|---|---|
| Acme Enterprise Renewal (Negotiation/Review, $120k) | Sales Manager |
| Globex Plant Expansion (Proposal, $45k) | Sales Rep |
| Acme Pilot Win (Closed Won) | Sales Manager |
| Cannot reset password (Working, High) | Service Rep |
| Billing question (Closed, Low) | Service Rep |

## Tests

- `tests/Feature/CasesTest.php`
- `tests/Feature/OpportunitiesTest.php`
- `tests/Feature/SearchTest.php`
- `tests/Feature/HomeDashboardTest.php`
- `tests/Feature/ReportsTest.php`
- `tests/Feature/Phase3MvpGateTest.php`

## Next

Phase 4 — Productivity and Analytics (P1).

- **Dev A:** [PHASE_4_DEV_A.md](PHASE_4_DEV_A.md) — tasks, email/queues, CSV import/export, TaskQueryService / EventQueryService.
- **Dev B:** [PHASE_4_DEV_B.md](PHASE_4_DEV_B.md) — calendar/events UI, lead conversion, report/dashboard builders, Home widgets.