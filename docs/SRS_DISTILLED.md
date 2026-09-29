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

## Phase status (working index)

| Phase | Status | Doc |
|---|---|---|
| 0–2 | Done | PHASE_0 / PHASE_1 / PHASE_2 docs |
| 3 MVP | Done | [PHASE_3_MVP.md](PHASE_3_MVP.md) |
| **4 P1 — Dev B** | Kickoff | [PHASE_4_DEV_B.md](PHASE_4_DEV_B.md) |
| 4 P1 — Dev A | Parallel (Tasks, email, import) | Not started in this track |
| 5+ | Later | MFA, attachments, hierarchy, API, … |

## Phase 4 Dev B — SRS quick index

| ID | One-liner |
|---|---|
| FR-CAL-001..004 | Events + calendar views, DnD reschedule, My Calendars |
| FR-LEAD-005 | Convert Lead → Account / Contact / optional Opportunity (transaction, then read-only) |
| FR-RPT-003 | Custom report builder (no free SQL; allow-listed fields) |
| FR-RPT-005 | Export CSV / Excel / PDF |
| FR-DASH-001..004 | Dashboard CRUD, widgets from reports, filters (auto-refresh → Phase 5) |
| FR-HOME-005 | Today’s Events widget |
| FR-HOME-004 | Today’s Tasks widget — **depends on Dev A Tasks** |
