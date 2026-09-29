# Phase 1 — Foundation (implemented)

**SRS IDs:** FR-AUTH-001, FR-AUTH-002, FR-AUTH-003 (+ users admin, shared UI, seeders)  
**Exit criteria:** Login works by role; layout in place; first empty module renders inside the shell.

## What was built

| Area | Location |
|---|---|
| Guest layout (login/reset) | `resources/views/layouts/guest.blade.php` |
| App layout (user menu, Accounts tab) | `resources/views/layouts/app.blade.php` |
| Auth routes | `routes/auth.php` |
| Users admin routes | `routes/users.php` |
| Accounts stub | `routes/accounts.php` |
| Login / lockout / remember-me | `App\Http\Controllers\Auth\*` |
| Password reset + history | `password_histories` + `PasswordHistoryService` |
| RBAC | `roles`, `permissions`, `role_permission`; Policies/Gates |
| Sharing schema + scopes | `sharing_defaults`, `record_shares`; `HasRecordAccess` |
| Blade components | `x-data-table`, `x-form-field`, `x-modal`, `x-toast`, `x-related-list` |
| List helpers | `App\Support\ListQuery` (sort, paginate max 200, columns) |
| Audit trait | `App\Models\Concerns\HasAuditFields` |
| Seeders | Role/permission, picklist, demo users |

## Local verify

```bash
docker compose up -d
php artisan migrate:fresh --seed
composer run dev
# Login: admin@crm.test / Password1!
# Mailpit: http://localhost:8025
```

## Demo users (NFR-USE-005)

| Email | Role | Password |
|---|---|---|
| `admin@crm.test` | System Administrator | `Password1!` |
| `sales.manager@crm.test` | Sales Manager | `Password1!` |
| `sales.rep@crm.test` | Sales Representative | `Password1!` |
| `service.rep@crm.test` | Service Representative | `Password1!` |
| `readonly@crm.test` | Read-Only User | `Password1!` |

## Phase 1 decisions (documented)

| Decision | Choice |
|---|---|
| RBAC package | Custom tables (no Spatie) |
| Role assignment | Single `role_id` per user |
| Lockout duration | 30 minutes after 5 failures |
| Remember-me lifetime | 30 days |
| Sharing | Schema + `scopeVisibleTo` in Phase 1; object CRUD in Phase 2 |

**Official SRS:** [docs/CRM_Requirements_Specification.docx](CRM_Requirements_Specification.docx) (present in repo).  
Lockout remains **5 attempts / 30 minutes** (FR-AUTH-001).

## Still manual (Phase 0 governance)

See [PHASE_0_GOVERNANCE_CHECKLIST.md](PHASE_0_GOVERNANCE_CHECKLIST.md). No `develop` branch — PRs target `main`.

**Next:** [PHASE_2_CORE_OBJECTS.md](PHASE_2_CORE_OBJECTS.md) — Accounts, Contacts, Leads.
