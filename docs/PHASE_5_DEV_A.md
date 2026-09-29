# Phase 5 — Dev A track (P2: Search, Subscriptions, Attachments, MFA, API)

**Owner:** Dev A — Identity, Service and Data  
**Sprint window:** Weeks 14–15 (PROJECT_PLAN §4)  
**Stack:** Laravel + Blade + HTML/CSS/vanilla JS + PostgreSQL only  
**Sibling:** [PHASE_5_DEV_B.md](PHASE_5_DEV_B.md) — hierarchy, dashboard auto-refresh, assistant, UI polish  
**Prerequisite:** Phase 4 exit met — [PHASE_4_DEV_A.md](PHASE_4_DEV_A.md), [PHASE_4_DEV_B.md](PHASE_4_DEV_B.md)

---

## 1. Scope (Dev A only)

| SRS ID | Feature | Priority |
|---|---|---|
| **FR-SRCH-003** | Advanced search: field criteria, AND/OR, date ranges, saved searches | P2 |
| **FR-RPT-006** | Report subscriptions: Daily/Weekly/Monthly email with CSV attachment | P2 |
| **SRS §8.2** | Attachments: 25MB, type whitelist, private storage, preview, virus scan | P2 |
| **NFR-SEC-002** | Optional MFA (email one-time code) | P2 |
| **SRS §8.3** | REST API v1 + OpenAPI docs (CRUD, filter/sort, metadata, rate limit, tokens) | P2 |

**Explicitly out of Dev A Phase 5**

| Item | Owner / Phase |
|---|---|
| FR-ACCT-004 Account hierarchy / roll-ups | Dev B |
| FR-DASH-003 timed auto-refresh | Dev B |
| FR-HOME-007 Assistant recommendations | Dev B |
| NFR-USE-001 breadcrumbs / empty states / tooltips | Dev B |
| OAuth 2.0, bulk API import, authenticator-app TOTP | Deferred (needs approval) |
| Full WCAG 2.1 AA / load / security hardening | Phase 6 |
| P3 (AI insights, workflow builder, custom objects, native apps) | Out of scope |

---

## 2. Prerequisites (met)

| Prerequisite | Status | Why it matters |
|---|---|---|
| FR-SRCH-001/002 global search | Done — `GlobalSearchService`, `SearchController`, `routes/search.php` | Advanced search extends this |
| Saved reports + `ReportExportService` (CSV) | Done | FR-RPT-006 reuses export |
| Queues + Mailpit + scheduler | Done — digests/reminders in `bootstrap/app.php` | Subscription delivery |
| Object show pages + empty Attachments shells | Done | Wire related lists |
| RBAC policies on core objects | Done | API must reuse them |
| Phase 4 mailables / notifications pattern | Done | MFA codes + subscription mail |

---

## 3. Locked decisions (recommendations adopted)

| # | Topic | Decision |
|---|---|---|
| 1 | Build order | **5A.1 → 5A.2 → 5A.3 → 5A.4 → 5A.5** (API last so it wraps stable policies) |
| 2 | Parallel with Dev B | Start **5A.1** in parallel with Dev B **5B.1** (hierarchy); no shared-file conflict |
| 3 | Advanced search logic | Flat left-to-right AND/OR conditions only — **no nested groups** |
| 4 | Saved searches | Per-user ownership; JSON `definition`; list / rerun / delete |
| 5 | Search field catalog | Reuse FR-SRCH-002 fields + date fields per object; allow-listed only |
| 6 | Report subscription export | CSV only via existing `ReportExportService` (matches Phase 4 FR-RPT-005 lock) |
| 7 | Subscription schedule | Daily / Weekly / Monthly; day + time; Artisan command + `withSchedule` entry |
| 8 | Attachment disk | Private disk path `storage/app/attachments` — **never** `public/` |
| 9 | Attachment limits | 25MB; PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, TXT; check extension **and** MIME |
| 10 | Virus scan | `AttachmentScanner` interface; local/test binding rejects **EICAR** signature and accepts others; real AV is a later binding — **no new Composer package** this phase |
| 11 | Preview | Inline for images; other allowed types force download |
| 12 | Attachment auth | Delete/download require ability on parent record (policy via attachable) |
| 13 | MFA channel | **Email OTP** only; short-lived code via existing notification stack; **no new package** |
| 14 | MFA default | Off per user; admin can disable MFA for a locked-out user |
| 15 | MFA recovery | Hashed one-time backup codes; regenerate invalidates previous set |
| 16 | TOTP / authenticator apps | **Out** unless team approves a dependency (`pragmarx/google2fa` or similar) |
| 17 | API auth | First-party **API tokens** (hashed `api_tokens` table) — **not** Sanctum, **not** OAuth |
| 18 | API surface | v1 CRUD for Leads, Accounts, Contacts, Opportunities, Cases only |
| 19 | API extras | Allow-listed filter/sort; metadata endpoint per object; Laravel `throttle` rate limit |
| 20 | OpenAPI | Hand-maintained `docs/openapi.yaml` served at `/api/docs` — **no** doc-generator package |
| 21 | Bulk API | **Out** — Phase 4 CSV import/export remains the bulk path |
| 22 | New Composer packages | **None** for Phase 5 Dev A without explicit approval |

---

## 4. Recommended build order (waves)

```text
Wave 1  5A.1  FR-SRCH-003     Advanced search + saved searches
Wave 2  5A.2  FR-RPT-006      Report subscriptions + schedule command
Wave 3  5A.3  SRS §8.2        Attachments + scanner interface + related lists
Wave 4  5A.4  NFR-SEC-002     Optional email MFA + backup codes
Wave 5  5A.5  SRS §8.3        REST API v1 + OpenAPI + tokens + throttle
```

Ship as separate small PRs: `feature/FR-SRCH-003-advanced-search`, `feature/FR-RPT-006-report-subscriptions`, etc.

---

## 5. Sub-phases

### 5A.0 — Kickoff / contracts

- Morph map alias: `attachment` (coordinate if Dev B touches `AppServiceProvider` same day)
- Route files: extend `routes/search.php`; add `routes/subscriptions.php`, `routes/attachments.php`, `routes/api.php`
- `routes/web.php`: only `require` new files (one-line PRs)
- Document this file; do **not** edit dashboard or account hierarchy views
- Do **not** enable new primary nav tabs (SRS still has 10 tabs)

### 5A.1 — Advanced search [FR-SRCH-003]

- Link from search results page (“Advanced search”)
- UI: object type → add conditions (field, operator, value) → AND/OR between rows → date range → Run
- `SavedSearch` model: `name`, `owner_id`, `object_type`, `definition` (JSON), audit fields
- Controllers: run advanced query; CRUD saved searches (owner-only)
- Results: same visibility scopes as module lists (`visibleTo` / policies)
- Tests: `tests/Feature/AdvancedSearchTest.php`

### 5A.2 — Report subscriptions [FR-RPT-006]

- Migration `report_subscriptions`: `saved_report_id`, `user_id`, frequency (`daily`/`weekly`/`monthly`), day_of_week/day_of_month nullable, `time_of_day`, `next_run_at`, `last_sent_at`, audit
- Policy: subscribe only if user can view the saved report
- UI: Subscribe from saved-report show; “My subscriptions” list with edit/delete
- Job/command: `crm:send-report-subscriptions` — generates CSV via `ReportExportService`, queues mailable
- Schedule in `bootstrap/app.php` (additive only — do not rewrite task digest lines)
- Tests: `tests/Feature/ReportSubscriptionTest.php` (Mail::fake)

### 5A.3 — Attachments [SRS §8.2]

- Migration `attachments`: morph `attachable`, `original_name`, `disk`, `path`, `mime_type`, `size_bytes`, `uploaded_by`, scan status, audit
- FormRequest: size ≤ 25MB; extension + MIME whitelist
- `AttachmentScanner` contract + `EicarAttachmentScanner` (or equivalent) for local/tests
- Controllers: upload, download, preview (images), destroy
- Wire empty **Notes & Attachments** related lists on Account / Contact / Lead / Opportunity / Case show
- Tests: `tests/Feature/AttachmentsTest.php` (fake storage + EICAR rejection)

### 5A.4 — Optional MFA [NFR-SEC-002]

- Users: `mfa_enabled`, optional columns for code hash / expires_at / backup codes JSON (hashed)
- Challenge after password login when MFA on; store pending user id in session until verified
- Notification: email OTP (Mailpit in local)
- User profile toggle; admin clear MFA on user admin screen
- Backup codes: generate set, show once, store hashed; consume on use
- Tests: `tests/Feature/MfaTest.php`

### 5A.5 — REST API [SRS §8.3]

- Migration `api_tokens`: `user_id`, `name`, `token` (hashed), `last_used_at`, `expires_at` nullable
- Middleware / guard: Bearer token → user; enforce same policies as web
- Routes under `/api/v1/...` for five objects: index, show, store, update, destroy
- Query: allow-listed `filter`, `sort`, pagination (max 200 per NFR-SCAL-002)
- `GET /api/v1/metadata/{object}` — field list
- Rate limiting via `throttle` middleware
- OpenAPI: `docs/openapi.yaml` + Blade/static page at `/api/docs`
- User UI: create/revoke personal API tokens (settings or users profile)
- Tests: `tests/Feature/Api/V1/*Test.php` (auth, policy deny, happy path)

---

## 6. Proposed schema

### `saved_searches`

| Column | Notes |
|---|---|
| name | required |
| owner_id | FK users |
| object_type | lead / account / contact / opportunity / case |
| definition | JSON: conditions[], date_from, date_to, logic |
| audit fields | HasAuditFields |

### `report_subscriptions`

| Column | Notes |
|---|---|
| saved_report_id | FK |
| user_id | subscriber |
| frequency | daily / weekly / monthly |
| day_of_week | 0–6 when weekly |
| day_of_month | 1–28 when monthly (avoid 29–31 edge cases) |
| time_of_day | time |
| next_run_at / last_sent_at | scheduling |
| audit fields | |

### `attachments`

| Column | Notes |
|---|---|
| attachable_type / attachable_id | morph |
| original_name, disk, path, mime_type, size_bytes | |
| uploaded_by | FK users |
| scan_status | pending / clean / rejected |
| audit fields | |

### MFA (users columns or small tables)

| Column / table | Notes |
|---|---|
| users.mfa_enabled | bool default false |
| mfa_challenges or session-only pending | short-lived OTP hash + expiry |
| mfa_backup_codes | user_id + code_hash + used_at |

### `api_tokens`

| Column | Notes |
|---|---|
| user_id, name | |
| token | hashed; plaintext shown once on create |
| last_used_at, expires_at | nullable expiry |

---

## 7. Conflict avoidance with Dev B

| Shared surface | Dev A rule |
|---|---|
| Account/Contact/Lead/Opportunity/Case **show** | Only add Attachments related-list partial; leave hierarchy partial to Dev B |
| `home.blade.php` | **Do not** edit (assistant is Dev B) |
| `dashboards/*` | **Do not** edit |
| `ReportExportService` | Call only; do not change CSV semantics without sync |
| `bootstrap/app.php` schedule | Append subscription command only |
| `AppServiceProvider` morph map | Add `attachment` only |
| `layouts/app.blade.php` | **Do not** restructure (polish is Dev B) |
| `routes/web.php` | `require` new files only |
| RolePermissionSeeder | Prefer existing `*.view` etc.; add `attachments.*` / API token perms only if needed — small additive PR |

**Branch naming:** `feature/FR-SRCH-003-advanced-search`, `feature/FR-RPT-006-subscriptions`, `feature/SRS-8.2-attachments`, `feature/NFR-SEC-002-mfa`, `feature/SRS-8.3-rest-api`.

---

## 8. Dependencies

```text
Phase 4 complete
    → 5A.1 Advanced search          (GlobalSearchService / search results)
        → (independent of Dev B)
    → 5A.2 Subscriptions            (SavedReport + ReportExportService + mail + schedule)
    → 5A.3 Attachments              (object show pages; scanner interface)
    → 5A.4 MFA                      (login + mail notifications)
    → 5A.5 API                      (after 5A.1–5A.4 policies/storage patterns stable)

Dev B 5B.1 hierarchy
    → shares Account show page (separate partials; merge carefully)
```

**Dev A blocked on Dev B for:** nothing required to ship Waves 1–5.  
**Dev B blocked on Dev A for:** nothing for hierarchy / auto-refresh / assistant.

---

## 9. Definition of Done (per wave)

Follow PROJECT_PLAN §7:

- [x] Migration + model + factory as needed  
- [x] Policy + FormRequest  
- [x] Controller + per-module routes  
- [x] Blade views / components  
- [x] Feature tests with SRS ID in name/docblock  
- [x] Pint: `vendor/bin/pint --dirty --format agent`  
- [x] No Vue / Inertia / React / TypeScript / MySQL  
- [ ] Small PR; Dev B review  

**Suggested tests**

- `tests/Feature/AdvancedSearchTest.php`
- `tests/Feature/ReportSubscriptionTest.php`
- `tests/Feature/AttachmentsTest.php`
- `tests/Feature/MfaTest.php`
- `tests/Feature/Api/V1/AccountsApiTest.php` (and siblings)

---

## 10. Local verify (Dev A)

```bash
docker compose up -d
php artisan migrate:fresh --seed
composer run dev
# Login: admin@crm.test / Password1!
# Mailpit: http://localhost:8025
# Smoke: Advanced search → save → Subscribe to report → upload attachment → enable MFA → create API token → curl /api/v1/accounts
```

## Exit (Dev A track)

- [x] FR-SRCH-003 advanced + saved searches with visibility scopes
- [x] FR-RPT-006 subscriptions deliver CSV via queue/Mailpit
- [x] Attachments upload/download/preview/delete + EICAR rejection
- [x] Optional email MFA + admin disable + backup codes
- [x] REST API v1 for five objects + tokens + throttle + OpenAPI page
- [x] No Dev B hierarchy / dashboard / home assistant code in Dev A PRs
- [x] Pest green for listed tests; Pint clean

## Status (Dev A)

| Sub-phase | Status |
|---|---|
| 5A.0 Kickoff / docs / contracts | Done |
| 5A.1 Advanced search | Done |
| 5A.2 Report subscriptions | Done |
| 5A.3 Attachments | Done |
| 5A.4 MFA | Done |
| 5A.5 REST API + OpenAPI | Done |

---

## 11. Related docs

| Doc | Role |
|---|---|
| [PHASE_5_DEV_B.md](PHASE_5_DEV_B.md) | Sibling track |
| [PHASE_4_DEV_A.md](PHASE_4_DEV_A.md) / [PHASE_4_DEV_B.md](PHASE_4_DEV_B.md) | Prerequisites |
| [SETUP_AND_PHASE_PREREQUISITES.md](SETUP_AND_PHASE_PREREQUISITES.md) | Phase 5 gate |
| `Master plan/PROJECT_PLAN.md` §2 / §4 | Ownership + phase table |
| SRS `docs/CRM_Requirements_Specification.docx` | Full acceptance criteria |
