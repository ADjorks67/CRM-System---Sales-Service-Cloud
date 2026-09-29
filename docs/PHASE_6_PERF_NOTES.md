# Phase 6 — Performance evidence (NFR-PERF)

**Date:** 2026-09-29  
**Environment:** local Docker Postgres (`crm_database` / port 5433), default demo seed unless noted.

## Indexes added (migration `2026_09_29_152141_add_phase6_performance_indexes`)

| Table | Index | Purpose |
|---|---|---|
| `tasks` | `tasks_owner_status_due_index` | Home / list open & overdue by owner |
| `tasks` | `tasks_status_due_index` | Overdue / due-today filters |
| `opportunities` | `opportunities_archive_lookup_index` | `crm:archive-old-records` |
| `opportunities` | `opportunities_owner_stage_archived_index` | Pipeline lists |
| `cases` | `cases_closed_at_index` | Closed-case / retention filters |
| `cases` | `cases_owner_status_index` | Case list by owner + status |
| `leads` | `leads_first_name_index` | Global search `ilike` on first_name |
| `leads` | `leads_owner_status_index` | Lead list filters |
| `contacts` | `contacts_first_name_index` | Global search |
| `events` | `events_owner_starts_at_index` | Home today’s events |
| `users` | `users_role_id_index` | RBAC joins |
| `gdpr_audits` | `gdpr_audits_admin_id_index` | Admin audit lookup |

Verified by `tests/Feature/Phase6PerfIndexesTest.php`.

## Recommended local timing smoke (after `migrate` + `seed`)

```bash
# Optional volume:
# CRM_LARGE_SEED_ACCOUNTS=5000 php artisan db:seed --class=LargeDatasetSeeder

php artisan tinker --execute "\$t=hrtime(true); auth()->login(\\App\\Models\\User::first()); app(\\App\\Services\\GlobalSearchService::class)->suggest(auth()->user(), 'ac'); echo (hrtime(true)-\$t)/1e6.' ms';"
```

NFR-PERF-001 targets: page &lt; 3s, save &lt; 2s, simple search &lt; 1s, report &lt; 5s, dashboard &lt; 5s.

## Load tests

See `scripts/load/README.md` (k6). Start with `k6 run scripts/load/health.js`.

## Note on `ilike '%term%'`

Leading-wildcard search cannot fully use btree indexes; Phase 6 adds supporting indexes for equality/range filters and first_name lookups. Full trigram (`pg_trgm`) is **not** enabled yet — ask before adding the extension.
