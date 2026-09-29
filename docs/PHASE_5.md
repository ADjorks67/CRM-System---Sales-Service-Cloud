# Phase 5 — Enhancements (P2)

**Sprint:** Weeks 14–15 · **Priority:** P2 only (SRS §9.2 Could Have)  
**Tracks:** [PHASE_5_DEV_A.md](PHASE_5_DEV_A.md) · [PHASE_5_DEV_B.md](PHASE_5_DEV_B.md)  
**Prerequisite:** Phase 4 complete ([PHASE_4_DEV_A.md](PHASE_4_DEV_A.md), [PHASE_4_DEV_B.md](PHASE_4_DEV_B.md))

---

## Scope map

| Track | SRS | Feature |
|---|---|---|
| Dev A | FR-SRCH-003 | Advanced search + saved searches |
| Dev A | FR-RPT-006 | Report subscriptions (email + CSV) |
| Dev A | SRS §8.2 | Attachments (25MB, whitelist, preview, scan) |
| Dev A | NFR-SEC-002 | Optional MFA (email OTP) |
| Dev A | SRS §8.3 | REST API v1 + OpenAPI |
| Dev B | FR-ACCT-004 | Account hierarchy + roll-ups |
| Dev B | FR-DASH-003 | Dashboard auto-refresh 5/10/30/60 |
| Dev B | FR-HOME-007 | Rule-based assistant |
| Dev B | NFR-USE-001 | Breadcrumbs, empty states, tooltips |

P3 remains out of scope (AI insights, workflow builder, custom objects, native apps).

---

## Locked recommendations (both tracks)

| # | Recommendation | Locked as |
|---|---|---|
| 1 | Start with **5A.1 + 5B.1** in parallel | First wave both tracks |
| 2 | Then **5A.2 + 5B.2** | Subscriptions ‖ auto-refresh |
| 3 | Then **5A.3 + 5B.3** | Attachments ‖ assistant (careful Account show merge) |
| 4 | MFA = email OTP only | No TOTP package unless approved |
| 5 | API last, narrow | Tokens + 5 objects + hand-written OpenAPI; no OAuth/bulk |
| 6 | Polish last | Dev B Wave 4 after feature screens exist |
| 7 | **No new Composer packages** this phase without approval | Scanner interface, first-party tokens, CSV-only subscriptions |
| 8 | Attachments on private disk + `AttachmentScanner` (EICAR in tests) | Real AV later |
| 9 | Hierarchy roll-ups = `employees` + `annual_revenue` only | Visibility-aware |
| 10 | Assistant = rule queries, not AI | Matches PROJECT_PLAN §1 |

Full decision tables live in each track doc.

---

## Parallel schedule

```text
Week 14 early   5A.1 Advanced search     ‖  5B.1 Hierarchy
Week 14 late    5A.2 Subscriptions       ‖  5B.2 Auto-refresh
Week 15 early   5A.3 Attachments         ‖  5B.3 Assistant
Week 15 mid     5A.4 MFA
Week 15 late    5A.5 REST API            ‖  5B.4 Polish
```

---

## Shared-file rules

| File | Owner |
|---|---|
| Account (etc.) show Attachments partial | Dev A |
| Account show hierarchy partial | Dev B |
| `home.blade.php` assistant | Dev B |
| `dashboards/show` refresh | Dev B |
| `bootstrap/app.php` subscription schedule | Dev A |
| `layouts/app.blade.php` polish | Dev B (final PR) |
| `routes/web.php` | Additive `require` only |

---

## Exit (phase)

Both tracks’ exit checklists complete; sprint demo covers: saved search, subscription in Mailpit, attachment preview, optional MFA, one API call, account tree + roll-ups, timed dashboard refresh, dismissable assistant. Then Phase 6 hardening.

## Status

| Track | Status |
|---|---|
| Docs / kickoff | Done |
| Dev A implementation | Done — see PHASE_5_DEV_A |
| Dev B implementation | Done — see PHASE_5_DEV_B |
