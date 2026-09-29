# CRM Requirements — Distilled (working index)

**Official SRS:** [CRM_Requirements_Specification.docx](CRM_Requirements_Specification.docx) (verified in `docs/`).  
**Purpose:** Short in-repo index for agents and developers. Prefer the Word document for full acceptance criteria.  
**Also used:** `.cursor/rule/crm-domain.mdc` and related rules (distilled from the SRS).

---

## Authentication (FR-AUTH-001 / FR-AUTH-002)

| Rule | Value |
|---|---|
| Password complexity | ≥ 8 characters; upper + lower + number |
| Failed login lockout | After **5** failed attempts |
| Lockout duration | **30 minutes** (`locked_until`); admin can clear lock early |
| Session inactivity timeout | **120 minutes** (`SESSION_LIFETIME`) |
| Remember-me | Optional; **30 days** |
| Password reset token | Valid **1 hour** |
| Password history | Cannot reuse last **5** passwords |

## Roles (FR-AUTH-003)

1. System Administrator  
2. Sales Manager  
3. Sales Representative  
4. Service Representative  
5. Read-Only User  

- CRUD permission per entity per role (seeded matrix).  
- Sharing defaults: **Private**, **Public Read Only**, **Public Read/Write**.  
- Users see records they **own** or were **granted** access to (scopes ready in Phase 1; enforced on business objects in Phase 2+).

## Navigation (SRS §6)

Primary tabs: Home, Leads, Accounts, Contacts, Opportunities, Cases, Tasks, Calendar, Reports, Dashboards.

## Pagination (NFR-SCAL-002)

Default **25** rows; hard maximum **200**.

## Out of Phase 1

MFA (Phase 5), global search backend (Phase 3), Opportunities/Cases (Phase 3). Phase 2 Accounts/Contacts/Leads CRUD is implemented — see PHASE_2_CORE_OBJECTS.md.
