# Phase 2 — Core Objects (Accounts, Contacts, Leads)

**SRS IDs:** FR-ACCT-001..003, FR-CONT-001..003, FR-LEAD-001..004, FR-LEAD-006, FR-LEAD-007  
**Exit criteria:** Accounts, Contacts, and Leads usable with role-based + record-level access; Pest tests passing.

## What was built

| Area | Location |
|---|---|
| Accounts CRUD + bulk owner/delete | `AccountController`, `routes/accounts.php`, `resources/views/accounts/*` |
| Contacts CRUD + bulk owner/delete | `ContactController`, `routes/contacts.php`, `resources/views/contacts/*` |
| Leads CRUD + status + bulk | `LeadController`, `routes/leads.php`, `resources/views/leads/*` |
| Schema | `accounts`, `contacts`, `leads`, `ownership_histories` |
| Record access | `HasRecordAccess` + morph map (`account`, `contact`, `lead`) |
| Ownership transfer | `OwnershipHistoryService` + `HandlesRecordOwnership` |
| Shared bulk actions | `BulkRecordActionService` |
| Picklists | `PicklistOptions` helper; `account_type`, `salutation`, `rating`; SRS-aligned `lead_source` |
| Demo CRM rows | `DemoCrmDataSeeder` (after demo users) |
| Nav | Leads + Contacts tabs enabled in `layouts/app.blade.php` |

## Explicitly deferred

| Item | Phase |
|---|---|
| FR-LEAD-005 Convert Lead wizard | Phase 4 (Convert button disabled with tooltip) |
| FR-ACCT-004 hierarchy tree / roll-ups | Phase 5 (`parent_account_id` field exists) |
| Import / Campaigns | Phase 4+ |
| Opportunities / Cases / Activities related lists | Phase 3/4 — Opportunities & Cases wired in Phase 3; Activities remain Phase 4 |
| Owner-change email + activity transfer | Phase 4 (UI flags stored only) |

## Next

Phase 3 MVP: [PHASE_3_MVP.md](PHASE_3_MVP.md).  
Phase 4 Dev B (conversion + calendar + builders): [PHASE_4_DEV_B.md](PHASE_4_DEV_B.md).

## Local verify

```bash
docker compose up -d
php artisan migrate:fresh --seed
composer run dev
# Login: admin@crm.test / Password1!
# Smoke: create Account → Contact → Lead → change owner/status → readonly cannot mutate
```

## Demo data (NFR-USE-005)

| Record | Owner |
|---|---|
| Acme Corporation (+ Jane Doe) | Sales Manager |
| Globex Industries (+ John Smith) | Sales Rep |
| Initech Support Co | Service Rep |
| Read Only Sample Account | Read-Only User |
| Hot Prospects LLC lead | Sales Rep |
| Nurture Co lead | Sales Manager |

## Phase 2 decisions

| Decision | Choice |
|---|---|
| Lead status column | DB/request field `status`; picklist category `lead_status` |
| List default sort | `updated_at` desc (Recently Viewed approximation) |
| Converted lead read-only | `is_converted` or `status === converted` via `isReadOnlyConverted()` |
| Convert button | Disabled with Phase 4 tooltip (no wizard route) |
| Sharing OWD | Private for account/contact/lead; `scopeVisibleTo` on all lists |
| Bulk / owner routes | `*.bulk`, `*.change-owner`, `leads.change-status` |
| Shared helpers | `PicklistOptions`, `HandlesRecordOwnership`, `ClearsBlankStrings` |

## Tests

- `tests/Feature/AccountsTest.php`
- `tests/Feature/ContactsTest.php`
- `tests/Feature/LeadsTest.php`
- `tests/Feature/RecordSharingTest.php`
- `tests/Feature/CoreObjectsGateTest.php` (role matrix + sharing gate)
