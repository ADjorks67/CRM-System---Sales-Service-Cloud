# Phase 5 — Dev B track (P2: Hierarchy, Auto-refresh, Assistant, Polish)

**Owner:** Dev B — Sales, Activity and Analytics  
**Sprint window:** Weeks 14–15 (PROJECT_PLAN §4)  
**Stack:** Laravel + Blade + HTML/CSS/vanilla JS + PostgreSQL only  
**Sibling:** [PHASE_5_DEV_A.md](PHASE_5_DEV_A.md) — advanced search, subscriptions, attachments, MFA, REST API  
**Prerequisite:** Phase 4 exit met — [PHASE_4_DEV_A.md](PHASE_4_DEV_A.md), [PHASE_4_DEV_B.md](PHASE_4_DEV_B.md)

---

## 1. Scope (Dev B only)

| SRS ID | Feature | Priority |
|---|---|---|
| **FR-ACCT-004** | Account hierarchy tree, cycle-safe parent, roll-ups, view-all-in-hierarchy | P2 |
| **FR-DASH-003** | Dashboard auto-refresh (5 / 10 / 30 / 60 min) + manual refresh if missing | P2 |
| **FR-HOME-007** | Rule-based assistant recommendations (dismissable) | P2 |
| **NFR-USE-001** (subset) | Breadcrumbs, empty states, tooltips on icon-only controls | P2 polish |

**Explicitly out of Dev B Phase 5**

| Item | Owner / Phase |
|---|---|
| FR-SRCH-003, FR-RPT-006, attachments, MFA, REST API | Dev A |
| Full WCAG 2.1 AA, cross-browser matrix, load tests | Phase 6 |
| AI-powered recommendations | P3 — **out of scope** (PROJECT_PLAN §1) |
| Opportunity amount roll-up into account hierarchy | Out — SRS roll-up is child-account `employees` + `annual_revenue` only |
| Layout redesign / new design system | Out — polish only |

---

## 2. Prerequisites (met)

| Prerequisite | Status | Why it matters |
|---|---|---|
| `accounts.parent_account_id` + `parentAccount()` / `childAccounts()` | Done | Hierarchy persistence |
| `employees`, `annual_revenue` on accounts | Done | Roll-up fields |
| Dashboard show + filters + drill + print | Done — `dashboards/show.blade.php` | Auto-refresh extends view |
| Home Assistant placeholder | Done — `home.blade.php` | Replace with FR-HOME-007 |
| Tasks + Events + Opportunities | Done | Assistant rules query these |
| `visibleTo` / record access on Account | Done | Tree must not leak invisible children |

---

## 3. Locked decisions (recommendations adopted)

| # | Topic | Decision |
|---|---|---|
| 1 | Build order | **5B.1 → 5B.2 → 5B.3 → 5B.4** (polish last so breadcrumbs cover new UI) |
| 2 | Parallel with Dev A | Start **5B.1** in parallel with Dev A **5A.1**; Account show uses **separate partials** |
| 3 | Parent field | Keep existing lookup; validate **no self-parent** and **no cycles** (parent not in descendant set) |
| 4 | Hierarchy UI | Tree on account detail + “View all accounts in hierarchy” page/action |
| 5 | Roll-ups | Sum **`employees`** and **`annual_revenue`** for account + all descendants (respect visibility) |
| 6 | Invisible children | Omit from tree and from roll-up totals — do not leak existence |
| 7 | Auto-refresh intervals | Nullable `dashboards.refresh_interval_minutes`: **5, 10, 30, 60**; null = off |
| 8 | Auto-refresh mechanism | Authenticated **fetch** of widget grid payload — **not** full page reload |
| 9 | Tab visibility | Pause interval while `document.hidden`; resume when visible |
| 10 | Manual refresh | Add Refresh control in same PR if still missing on dashboard show |
| 11 | Assistant rules | (a) Accounts with no Task/Event activity for **30+ days**; (b) Open opportunities with close date within **7 days** and `updated_at` older than **14 days** |
| 12 | Assistant UX | Dismissable per user; quick actions to view/update record; replace Home placeholder |
| 13 | Assistant storage | `assistant_dismissals` (user_id + recommendable morph or stable key) so dismiss persists |
| 14 | Not AI | Pure Eloquent/SQL rules — no LLM, no P3 “AI insights” |
| 15 | Polish scope | Breadcrumbs on detail/builder pages; empty states; tooltips — **one** layout-adjacent PR at end |
| 16 | New Composer packages | **None** for Phase 5 Dev B |

---

## 4. Recommended build order (waves)

```text
Wave 1  5B.1  FR-ACCT-004     Hierarchy tree + cycle guard + roll-ups
Wave 2  5B.2  FR-DASH-003     Auto-refresh + manual refresh
Wave 3  5B.3  FR-HOME-007     Assistant recommendations + dismissals
Wave 4  5B.4  NFR-USE-001     Breadcrumbs, empty states, tooltips
```

Ship as separate small PRs: `feature/FR-ACCT-004-account-hierarchy`, `feature/FR-DASH-003-auto-refresh`, etc.

---

## 5. Sub-phases

### 5B.0 — Kickoff / contracts

- Hierarchy = `resources/views/accounts/partials/hierarchy.blade.php` (or equivalent) — **do not** edit Attachments related list
- Auto-refresh = dashboard show + small `resources/js` helper (vanilla) — no FullCalendar/Chart.js changes required
- Assistant = replace placeholder section only in `home.blade.php`
- Document this file

### 5B.1 — Account hierarchy [FR-ACCT-004]

- Service: `AccountHierarchyService` — ancestors/descendants, cycle detection, roll-up aggregates
- FormRequest / Account update: reject self and cycle
- Detail: tree visualization (nested list is fine; keep accessible)
- Action: View all accounts in hierarchy (flat or tree of the subgraph)
- Roll-up display: total employees, total annual revenue for visible subtree
- Tests: `tests/Feature/AccountHierarchyTest.php`

### 5B.2 — Dashboard auto-refresh [FR-DASH-003]

- Migration: `dashboards.refresh_interval_minutes` nullable unsignedTinyInteger (or smallInteger) with allowed values validated in FormRequest
- Dashboard create/edit: select Off / 5 / 10 / 30 / 60
- Show page: Refresh button; JS interval calling JSON/HTML fragment endpoint that re-renders widgets with current session filters
- Pause when tab hidden
- Keep print + drill-to-report
- Tests: `tests/Feature/DashboardAutoRefreshTest.php` (interval persisted; refresh endpoint authorized)

### 5B.3 — Assistant [FR-HOME-007]

- `AssistantRecommendationService` — two rule queries scoped to `visibleTo`
- Migration `assistant_dismissals`: user_id, recommendation_key (or morph), timestamps
- Home widget: list reason, link, dismiss, optional quick action
- Remove Phase 5 placeholder copy
- Tests: `tests/Feature/HomeAssistantTest.php`

### 5B.4 — UI polish [NFR-USE-001 subset]

- `<x-breadcrumbs>` (or partial) on detail and builder pages
- Empty-state copy consistency on related lists / widgets still missing friendly empty text
- `title` / `aria-label` on icon-only controls
- Single PR touching layout helpers — avoid rewriting `layouts/app.blade.php` structure
- Manual smoke on 320px width; full a11y suite stays Phase 6

---

## 6. Proposed schema

### Hierarchy

No new table required — uses existing `parent_account_id`. Optional cache columns for roll-ups are **out** (compute on read; optimize in Phase 6 if needed).

### `dashboards` (additive column)

| Column | Notes |
|---|---|
| refresh_interval_minutes | nullable; allowed 5, 10, 30, 60 |

### `assistant_dismissals`

| Column | Notes |
|---|---|
| user_id | FK |
| recommendation_key | string unique per user — e.g. `inactive_account:{id}`, `stale_opportunity:{id}` |
| dismissed_at | timestamp |
| unique(user_id, recommendation_key) | |

---

## 7. Conflict avoidance with Dev A

| Shared surface | Dev B rule |
|---|---|
| Account show | Hierarchy partial only; leave Attachments to Dev A |
| `home.blade.php` | Assistant section only |
| `dashboards/show` + edit | Auto-refresh only |
| `ReportExportService` / subscriptions | **Do not** change |
| `bootstrap/app.php` | **Do not** add subscription schedule (Dev A) |
| Attachments / MFA / API routes | **Do not** create |
| `layouts/app.blade.php` | Polish PR only; no nav tab changes |
| `routes/web.php` | Prefer extending `accounts.php` / `dashboards.php`; avoid fighting Dev A requires |

**Branch naming:** `feature/FR-ACCT-004-account-hierarchy`, `feature/FR-DASH-003-auto-refresh`, `feature/FR-HOME-007-assistant`, `feature/NFR-USE-001-polish`.

---

## 8. Dependencies

```text
Phase 4 complete
    → 5B.1 Hierarchy           (accounts.parent_account_id)
    → 5B.2 Auto-refresh        (dashboard show + filters)
    → 5B.3 Assistant           (accounts + tasks + events + opportunities)
    → 5B.4 Polish              (after Waves 1–3 screens exist)

Parallel:
    Dev A 5A.1 Advanced search  ↔  Dev B 5B.1 Hierarchy   (start together)
    Dev A 5A.2 Subscriptions    ↔  Dev B 5B.2 Auto-refresh
    Dev A 5A.3 Attachments      ↔  Dev B 5B.3 Assistant     (Account show merge care)
```

**Dev B blocked on Dev A for:** nothing for Waves 1–3.  
**Coordinate merges on:** Account show when 5A.3 and 5B.1 both land.

---

## 9. Definition of Done (per wave)

Follow PROJECT_PLAN §7:

- [ ] Migration + model touch as needed  
- [ ] Policy / FormRequest when mutating  
- [ ] Controller + routes  
- [ ] Blade / vanilla JS  
- [ ] Feature tests with SRS ID  
- [ ] Pint dirty format agent  
- [ ] No Vue / Inertia / React / TypeScript / MySQL  
- [ ] Small PR; Dev A review  

**Suggested tests**

- `tests/Feature/AccountHierarchyTest.php`
- `tests/Feature/DashboardAutoRefreshTest.php`
- `tests/Feature/HomeAssistantTest.php`
- Optional: `tests/Feature/UiPolishSmokeTest.php` (breadcrumbs render on one detail page)

---

## 10. Local verify (Dev B)

```bash
docker compose up -d
php artisan migrate:fresh --seed
composer run dev
# Login: admin@crm.test / Password1!
# Smoke: set parent accounts → tree + roll-ups → dashboard interval → Home assistant dismiss → breadcrumbs on account show
```

## Exit (Dev B track)

- [x] FR-ACCT-004 tree, cycle guard, visible roll-ups, view-all-in-hierarchy
- [x] FR-DASH-003 auto-refresh 5/10/30/60 + manual refresh; pause when hidden
- [x] FR-HOME-007 rule-based dismissable recommendations on Home
- [x] Breadcrumbs / empty states / tooltips shipped in polish PR
- [x] No Dev A search / subscription / attachment / MFA / API code in Dev B PRs
- [x] Pest green; Pint clean

## Status (Dev B)

| Sub-phase | Status |
|---|---|
| 5B.0 Kickoff / docs / contracts | Done |
| 5B.1 Account hierarchy | Done |
| 5B.2 Dashboard auto-refresh | Done |
| 5B.3 Assistant recommendations | Done |
| 5B.4 UI polish | Done |

---

## 11. Related docs

| Doc | Role |
|---|---|
| [PHASE_5_DEV_A.md](PHASE_5_DEV_A.md) | Sibling track |
| [PHASE_4_DEV_B.md](PHASE_4_DEV_B.md) | Dashboard / Home baseline |
| [SETUP_AND_PHASE_PREREQUISITES.md](SETUP_AND_PHASE_PREREQUISITES.md) | Phase 5 gate |
| `Master plan/PROJECT_PLAN.md` §2 / §4 | Ownership + phase table |
| SRS `docs/CRM_Requirements_Specification.docx` | Full acceptance criteria |
