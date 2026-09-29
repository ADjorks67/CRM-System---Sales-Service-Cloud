# Phase 4 — Dev B: Calendar, Conversion, Reports & Dashboards (P1)

**Owner:** Dev B — “Sales, Activity and Analytics”  
**Sprint window:** Weeks 10–13 (PROJECT_PLAN §4)  
**Stack:** Laravel + Blade + HTML/CSS/vanilla JS + PostgreSQL only  
**Sibling (do not implement):** Dev A owns Tasks, email/queues/scheduler, import/export, and task query APIs for Home.

---

## 1. Scope (Dev B only)

| SRS ID | Feature | Priority |
|---|---|---|
| **FR-CAL-001** | Calendar views: Day / Week / Month / Table (list); Today / prev/next; mini-month jump; New Event; click-slot create | P1 |
| **FR-CAL-002** | Create Event (subject, start/end required; assigned to; related-to morph; Name/contact; all-day; location; show-as; private; description) | P1 |
| **FR-CAL-003** | Event detail popup, quick edit, full edit page, delete confirm, **drag-and-drop reschedule** | P1 |
| **FR-CAL-004** | My Calendars: default “My Events”; toggle calendar types; colour coding | P1 |
| **FR-LEAD-005** | Lead conversion wizard (Account new/match, Contact, optional Opportunity, status → Converted, read-only, link IDs) | P1 |
| **FR-RPT-003** | Custom report builder (type → columns → filters → group → chart → preview → save) | P1 |
| **FR-RPT-005** | Export report results: CSV / Excel / PDF | P1 |
| **FR-DASH-001** | Dashboard list (folders, cards, New / View / Edit / Delete / Clone) | P1 |
| **FR-DASH-002** | Create/edit dashboard: grid layout, up to 20 widgets (Chart / Table / Metric / Gauge) from reports | P1 |
| **FR-DASH-003** | View dashboard: simultaneous load, drill-to-report, refresh, print *(auto-refresh → Phase 5 per plan)* | P1 |
| **FR-DASH-004** | Global filters (date range, owner, team); session-persisted | P1 |
| **FR-HOME-005** | Today’s Events widget on Home | P1 |
| **FR-HOME-004** | Today’s Tasks widget on Home | P1 — **blocked on Dev A Task model/API** |

**Explicitly out of Dev B Phase 4**

| Item | Owner / Phase |
|---|---|
| FR-TASK-001..003 Tasks CRUD, reminders | Dev A |
| Email templates, queues, digests, owner-change mail | Dev A |
| Data import/export | Dev A |
| FR-RPT-006 Report subscriptions | Dev A / Phase 5 |
| FR-DASH-003 auto-refresh (5/10/30/60 min) | Phase 5 (PROJECT_PLAN) |
| FR-HOME-007 Assistant recommendations | Phase 5 |
| FR-ACCT-004 Account hierarchy | Phase 5 |
| Case / Opportunity product shells | Unchanged |

---

## 2. Recommended build order

Ship as **separate small PRs** (`feature/FR-*-name`), one SRS cluster each:

```text
Wave 1  FR-CAL-001..004     Events schema + FullCalendar UI + JSON feed + DnD
Wave 2  FR-LEAD-005         Conversion wizard (transaction) + enable Convert button
Wave 3  FR-RPT-003 + 005    Saved reports + builder + CSV first, then Excel/PDF
Wave 4  FR-DASH-001..004    Dashboards consuming saved + pre-built reports
Wave 5  FR-HOME-005         Today’s Events widget (and FR-HOME-004 when Dev A ready)
```

**Why this order:** Calendar is independent and unlocks Home events. Conversion is a critical journey and already partially modelled on `leads`. Builders reuse Phase 3 `app/Reports/*`. Dashboards need saved reports. Home tasks widget waits for Dev A.

---

## 3. Dependencies

### Already satisfied (Phase 0–3)

| Dependency | Status | Why it matters |
|---|---|---|
| Accounts / Contacts / Opportunities CRUD | Done | Conversion targets |
| Lead `is_converted`, converted_* FKs, read-only helpers | Done | FR-LEAD-005 persistence |
| Convert button disabled stub on lead show | Done | Wire in Wave 2 |
| FullCalendar + `<x-calendar>` + `resources/js/lib/calendar.js` | Done | FR-CAL views / DnD |
| Chart.js + `<x-chart>` | Done | Report/dashboard charts |
| `app/Reports/PrebuiltReport` + catalog | Done | Builder query layer + dashboard sources |
| Morph map (`account`, `contact`, `lead`, `opportunity`, `case`, `user`) | Done | Extend for `event` only |
| Permissions `events.*`, `reports.*`, `dashboards.*` + sharing OWD for `event` | Seeded | Policies can attach immediately |
| Nav tabs Calendar / Dashboards (null routes) | Layout ready | Enable routes without redesign |
| Mailpit / queues | Local ready | Not Dev B primary; conversion must not depend on mail |

### Soft / cross-dev dependencies

| Dependency | From | Impact if missing |
|---|---|---|
| **Task model + `tasks` table + morph `task`** | Dev A | Conversion “transfer open tasks”; FR-HOME-004 widget |
| **Stable task query for “due today for user”** | Dev A (plan: “expose tasks/events queries”) | Home tasks widget |
| **Owner-change / assignment email** | Dev A | Event assign notify can stay stub like lead owner notify |
| **Excel / PDF composer packages** | Team decision | FR-RPT-005 — **needs approval** (see §6) |

### Dev B → Dev A contract (do not block Dev A)

Dev B will publish (document + code) once Events exist:

| Contract | Purpose |
|---|---|
| `Event` model + `scopeVisibleTo` + factory | Dev A may relate tasks, digests, owner transfer |
| `Event::query()->visibleTo($user)->occurringOn($date)` (or equivalent service) | FR-HOME-005; Dev A digests can reuse |
| Conversion service hook `transferOpenActivities()` | No-ops tasks until Task model exists; transfers events immediately |

---

## 4. Conflict avoidance with Dev A

| Shared surface | Dev B rule |
|---|---|
| `routes/web.php` | Only `require` new files: `events.php`, `calendars.php` (optional), `dashboards.php`; extend `reports.php` carefully |
| `layouts/app.blade.php` | Only set Calendar / Dashboards route names; no structural layout rewrite |
| `AppServiceProvider` morph map | Add **`event` only**; leave `task` for Dev A (coordinate if both touch same day) |
| `HomeController` / `HomeDashboardService` / `home.blade.php` | Add Events widget section; leave a clear `<!-- Dev A: FR-HOME-004 -->` placeholder for Tasks until Task API exists |
| `RolePermissionSeeder` | Prefer **not** changing unless a new permission name is required; entities already include `events` / `reports` / `dashboards` |
| Tasks / email / import modules | **Do not create** `Task` model, task routes, mailables, or import jobs |
| Lead conversion task transfer | Feature-detect: if `Task` class / table missing, skip with flash note; never invent Task schema |
| Migrations | New tables only (`events`, `saved_reports`, `dashboards`, `dashboard_widgets`, …). **Never edit** migrations already on `main`/`develop` |
| Pre-built report classes | Extend catalog; do not rewrite Dev A case/lead report query semantics without sync |

**Branch naming:** `feature/FR-CAL-001-calendar-views`, `feature/FR-LEAD-005-convert-lead`, etc.

---

## 5. Proposed schema (Dev B tables)

Subject to review before first migration PR:

### `events` (FR-CAL-*)

| Column | Notes |
|---|---|
| id | PK |
| subject | required |
| starts_at / ends_at | timestamptz; validate end ≥ start |
| is_all_day | bool |
| location | nullable |
| description | nullable text |
| show_as | picklist: busy / free / out_of_office |
| is_private | bool — only owner (and admin) sees |
| calendar_type | string — start with `my_events`; extensible for FR-CAL-004 |
| color | optional hex or token for colour coding |
| owner_id | assigned to (FK users) |
| related_type / related_id | morph: account, contact, lead, opportunity (SRS; not case) |
| name_contact_id | optional FK contacts (“Name” lookup) |
| audit fields | via `HasAuditFields` |

### Conversion (no new lead columns expected)

Uses existing `is_converted`, `converted_account_id`, `converted_contact_id`, `converted_opportunity_id`. Optional: `converted_at` timestamp if useful for reports — **ask before adding**.

### `saved_reports` (FR-RPT-003)

| Column | Notes |
|---|---|
| name, description, folder | list UI |
| report_type | lead / account / contact / opportunity / case / … |
| definition | JSON: columns, filters, group_by, chart |
| owner_id | creator |
| is_private | folder semantics |
| audit fields | |

### `dashboards` + `dashboard_widgets` (FR-DASH-*)

| Table | Notes |
|---|---|
| dashboards | name, description, folder, owner_id, layout meta, audit |
| dashboard_widgets | dashboard_id, report source (prebuilt key or saved_report_id), type (chart/table/metric/gauge), grid x/y/w/h, config JSON |

---

## 6. Locked decisions (2026-09-29)

| # | Topic | Decision |
|---|---|---|
| 1 | FR-RPT-005 export | **C** — server CSV + browser print-to-PDF stub (no new Composer packages) |
| 2 | `converted_at` on leads | **Yes** — add column; conversion sets it; reports prefer it |
| 3 | FR-CAL-004 types | **Yes** — `my_events` + `public_team` (visible non-private) toggles |
| 4 | Dashboard grid | **Yes** — vanilla JS + CSS grid only; max 20 widgets |
| 5 | FR-HOME-004 | **Yes** — placeholder until Dev A Tasks merge |
| 6 | Conversion activity transfer | **Yes** — transfer events now; skip tasks until Task model exists |
| 7 | Report builder | **Yes** — allow-listed fields for Lead/Account/Contact/Opportunity/Case; no free SQL |
| 8 | Sales Rep reports/dashboards | **Yes** — keep seeded view-only |

---

## 7. Implementation checklist (per feature)

Follow Definition of Done (PROJECT_PLAN §7):

- [ ] Migration + model + factory + seeder touch as needed  
- [ ] Policy + FormRequest  
- [ ] Controller + per-module routes  
- [ ] Blade views / components  
- [ ] Feature tests with SRS ID in name/docblock  
- [ ] Pint clean; no Vue/Inertia/React/MySQL  
- [ ] Small PR; Dev A review  

**Suggested test files**

- `tests/Feature/EventsCalendarTest.php`  
- `tests/Feature/LeadConversionTest.php`  
- `tests/Feature/ReportBuilderTest.php`  
- `tests/Feature/ReportExportTest.php`  
- `tests/Feature/DashboardsTest.php`  
- `tests/Feature/HomeEventsWidgetTest.php`  

---

## 8. Local verify (after Waves land)

```bash
docker compose up -d
php artisan migrate:fresh --seed
composer run dev
# Login: admin@crm.test / Password1!
# Smoke: Calendar CRUD + DnD → Convert Lead → custom report + export → dashboard → Home events widget
```

---

## 9. Related docs

| Doc | Role |
|---|---|
| [PHASE_3_MVP.md](PHASE_3_MVP.md) | Prerequisite MVP |
| [SETUP_AND_PHASE_PREREQUISITES.md](SETUP_AND_PHASE_PREREQUISITES.md) | Phase 4 prerequisites |
| `Master plan/PROJECT_PLAN.md` §2 / §4 | Ownership + phase table |
| SRS `docs/CRM_Requirements_Specification.docx` | Full acceptance criteria |

---

*Living document — update “What was built” when each Wave merges. Dev A Phase 4 work lives in a separate note when they start.*

---

## 10. What was built (Dev B kickoff — 2026-09-29)

| Area | Location |
|---|---|
| Events + calendar | `Event`, `EventController`, `CalendarController`, `routes/events.php`, `resources/views/calendar/*`, `resources/views/events/*`, FullCalendar DnD via `calendar.js` |
| Lead conversion | `LeadConversionService`, `leads.convert` routes/views, `converted_at` column, event transfer; tasks deferred |
| Report builder + CSV/print | `SavedReport`, `SavedReportController`, `ReportBuilderService`, `ReportExportService`, `ReportFieldCatalog`, `resources/views/saved-reports/*` |
| Dashboards | `Dashboard`, `DashboardWidget`, `DashboardController`, `routes/dashboards.php`, session filters, multi-widget form (max 20) + CSS grid view |
| Home widgets | FR-HOME-005 today’s events; FR-HOME-004 tasks placeholder for Dev A |
| Schema | `events`, `saved_reports`, `dashboards`, `dashboard_widgets`, `leads.converted_at` |
| Morph map | `event` added; `task` reserved for Dev A |

### Known deferrals (not bugs)

| Item | Why |
|---|---|
| FR-DASH-003 timed auto-refresh | Phase 5 per PROJECT_PLAN |
| Excel export | Locked decision: CSV + browser print only |
| Task transfer / FR-HOME-004 live widget | Dev A Tasks |
| Drag-resize dashboard widgets in editor | Placement via grid_x/w/h fields; view uses CSS grid |

### Tests

- `tests/Feature/EventsCalendarTest.php`
- `tests/Feature/LeadConversionTest.php`
- `tests/Feature/ReportBuilderTest.php`
- `tests/Feature/DashboardsTest.php`
- `tests/Feature/HomeEventsWidgetTest.php`

### Local verify

```bash
docker compose up -d
php artisan migrate:fresh --seed
composer run dev
# Login: admin@crm.test / Password1!
# Smoke: Calendar → Convert Lead → Custom Reports → Dashboards → Home events
```
