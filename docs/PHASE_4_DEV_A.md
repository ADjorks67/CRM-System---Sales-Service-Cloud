# Phase 4 — Dev A track (Productivity: Tasks, Email, Import)

**Owner:** Dev A — Identity, Service and Data  
**SRS IDs (Dev A):** FR-TASK-001..003; system notifications (task assignment, ownership change, password reset templates, digests/overdue); data import/export (CSV)  
**Not Dev A:** FR-CAL-*, FR-LEAD-005, FR-RPT-003/005, FR-DASH-*, HOME-004/005 **widgets** (Dev B)

**Companion:** Dev B builds calendar, lead conversion, report/dashboard builders, and Home task/event widgets that consume Dev A query services.

---

## Prerequisites (met)

| Prerequisite | Status |
|---|---|
| Phase 3 MVP objects | Done |
| Queue tables + `QUEUE_CONNECTION=database` | Done |
| Mailpit in Compose (`:1025` / `:8025`) | Done |
| Morph map (core objects) | Done — add `task` / `event` |
| RBAC `tasks.*` / `events.*` + Private OWD | Already seeded |
| FullCalendar registered | Done (Dev B uses it) |

---

## Dev A scope (sub-phases)

### 4A.0 — Kickoff / contracts (no Dev B UI)

- Morph map aliases: `task`, `event`
- Picklists: `task_status`, `task_priority` (and event picklists only if Event schema is in this track)
- New services (Dev B–safe): `TaskQueryService` (and `EventQueryService` if Event model exists)
- Doc this file + update SETUP Phase 4 status when exit met
- **Do not** enable Calendar/Dashboards nav; **do** enable Tasks nav when CRUD is green

### 4A.1 — Tasks CRUD [FR-TASK-001..003 core]

- Migration `tasks`: subject, assigned_to, related morph (`related_type`/`related_id`), contact_name_id optional, due_date, status, priority, comments, reminder_set, reminder_at, completed_at, owner/audit as needed
- Model `Task` + Policy + FormRequests + factory + `routes/tasks.php`
- List: Recently Viewed; filters Open / Completed / Today / Overdue; inline complete
- Related-to: Account, Contact, Lead, Opportunity, Case (morph)
- Wire **Open Activities** related lists on Account/Contact/Opportunity/Case **for tasks only** (events appear when Event CRUD exists)
- Tests: `tests/Feature/TasksTest.php`

### 4A.2 — Email + queues + scheduler

- Mailables/Notifications with Blade templates + merge fields:
  - Task assignment
  - Ownership change (honor `notify_new_owner` on `OwnershipHistoryService`)
  - Password reset (custom CRM template wrapping Laravel reset flow)
- Jobs: queued sends; `php artisan queue:work` / `composer run dev` queue
- Schedule in `routes/console.php` + `bootstrap/app.php` `withSchedule`:
  - Daily digest (tasks due today)
  - Overdue task notifications
- Browser/popup reminder: server stores `reminder_at`; UI toast or simple due-soon banner on task list/show (full push notifications out of scope)
- Tests: Mail fakes for assignment + owner change; schedule command feature tests

### 4A.3 — Query API for Home widgets (Dev B)

- `TaskQueryService::dueToday(User)`, `overdue(User)`, `openForUser(User)` etc.
- `EventQueryService` same shape **if** Event model exists; otherwise stub interface + doc for Dev B
- **Do not** edit `home.blade.php` widgets (Dev B FR-HOME-004/005)

### 4A.4 — Data import / export (CSV)

- Routes: `routes/imports.php` (and export endpoints under same or `routes/exports.php`)
- Objects: Leads, Accounts, Contacts, Opportunities
- Field mapping UI, validation, error report download, update-or-insert option
- List export CSV for those objects (Excel/PDF report export remains Dev B FR-RPT-005)
- Tests: import happy path + error report; export CSV

---

## Explicitly out of Dev A (Dev B / later)

| Item | Owner |
|---|---|
| Events calendar UI, drag-drop, `routes/events.php` views | Dev B |
| Lead conversion wizard | Dev B |
| Report builder + FR-RPT-005 Excel/PDF | Dev B |
| Dashboard builder + Home Tasks/Events **widgets** | Dev B |
| Attachments / advanced search | Phase 5 |
| Report subscriptions | Phase 5 (Dev A later) |

---

## Dependencies

```text
4A.0 morph + picklists
    → 4A.1 Tasks CRUD
        → 4A.2 assignment email + reminders + digests
        → 4A.3 TaskQueryService (Dev B HOME-004)
4A.2 OwnershipHistory notify hook
    → usable by all Phase 2/3 owner-change UIs
Event model (Dev A schema OR Dev B full)
    → transfer_open_activities for events
    → EventQueryService → Dev B HOME-005
4A.4 Import/export
    → independent of calendar/conversion; can parallelize after 4A.1
```

**Dev B blocked on Dev A for:** TaskQueryService (and EventQueryService if Dev A owns Event schema).  
**Dev A blocked on Dev B for:** nothing required to ship Tasks + email; Event-related transfer/HOME-005 only if Event is deferred entirely to Dev B.

---

## Conflict avoidance (shared files)

| File | Dev A may | Dev B owns |
|---|---|---|
| `layouts/app.blade.php` | Tasks tab only | Calendar, Dashboards |
| `routes/web.php` | `require tasks.php` (+ imports/exports) | `events`, conversion, dashboards, report-builder |
| `AppServiceProvider` morph map | Add `task` (+ `event` if agreed) | Coordinate Event alias |
| `PicklistSeeder` | `task_*` (+ `event_*` if agreed) | Same file — small additive PRs |
| `OwnershipHistoryService` | Notify + transfer open **tasks** | Conversion may call transfer |
| `HomeDashboardService` / `home.blade.php` | Prefer **no** widget UI edits | HOME-004/005 widgets |
| Object `show` blades Activities | Task rows | Event rows (or both if Event schema exists) |
| `app/Reports/*` | Do not change | Builder builds on these |

**Rule:** one concern per PR; never flip Calendar/Dashboards nav in a Tasks PR.

---

## Recommendations (defaults)

1. **Event schema:** Dev A adds migration + model + morph only; Dev B owns calendar UI (avoids blocking `transfer_open_activities` and HOME-005).
2. **Sequence:** 4A.1 → 4A.2 → 4A.3 → 4A.4 (import last).
3. **Password reset:** keep Laravel reset flow; replace notification view with CRM-branded Blade (do not reinvent tokens).
4. **Reminders:** persist `reminder_at`; enqueue reminder job; browser Notification API optional enhancement — not required for P1 exit.
5. **Import:** Leads/Accounts/Contacts/Opportunities only (SRS §8.4); Cases/Tasks import later if needed.
6. **Export naming:** Dev A = “Data export” (record CSV); Dev B = “Report export” (FR-RPT-005) — separate routes/controllers.

---

## Local verify (Dev A)

```bash
docker compose up -d
# .env: DB_PORT=5433, Mailpit MAIL_HOST=127.0.0.1 MAIL_PORT=1025
php artisan migrate:fresh --seed
composer run dev   # serves app + queue + schedule if configured
# Mailpit UI: http://localhost:8025
# Smoke: create Task related to Account → assign → see mail → complete → digest command
```

## Exit (Dev A track)

- [x] Tasks list/create/detail/complete with RBAC + related-to morph
- [x] Assignment + ownership-change emails queued
- [x] Daily digest + overdue scheduled
- [x] `TaskQueryService` + `EventQueryService` for Dev B
- [x] CSV import/export with mapping + error report
- [x] Docs + Pest for Tasks/Mail/Import/Event schema
- [x] No Calendar/Conversion/Dashboard/Report-builder code in Dev A PRs

## Status (Dev A)

| Sub-phase | Status |
|---|---|
| 4A.0 Kickoff / docs / morph `task` | Done |
| 4A.1 Tasks CRUD + related lists | Done |
| 4A.2 Email + digests + reminders + owner notify | Done |
| 4A.3 `TaskQueryService` for Dev B HOME-004 | Done |
| 4A.4 CSV import/export | Done — `/imports`, `/exports`, mapping UI, error CSV |
| Event schema (model only) | Done — migration + model + factory + morph `event` + `EventQueryService` (no calendar UI) |

## Next after Dev A exit

Dev B: calendar, conversion, builders, HOME-004/005 widgets consuming query services.
