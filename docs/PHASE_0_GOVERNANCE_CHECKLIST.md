# Phase 0 — Remaining Governance Checklist

**Status:** Local/repo setup is complete. These GitHub/process items still need a human with repo admin access.

`gh` CLI may not be available locally — apply the items below in the GitHub UI when ready.

**Note:** A separate `develop` branch is **not** required for this project (decision: stay on `main` + feature PRs).

## Checklist

| Item | Why | How |
|---|---|---|
| Protect `main` | Require PR + 1 approval + CI green | GitHub → Settings → Branches → Branch protection rules |
| Project board | Sprint tracking | GitHub Projects; columns: Backlog, In Progress, In Review, Done |
| One issue per SRS ID | Traceability | Use [`.github/ISSUE_TEMPLATE/srs-requirement.md`](../.github/ISSUE_TEMPLATE/srs-requirement.md); label P0/P1/P2 |
| Confirm CI green on first push | Proves Actions for clones | Push a PR to `main`; watch [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) |
| Paste `USER_RULES.txt` into Cursor Settings | Consistent agent behaviour | Each developer, once |

## Suggested Phase 1 issues (create when ready)

- `FR-AUTH-001` — Login, lockout, session timeout, remember-me
- `FR-AUTH-002` — Password reset, token expiry, password history
- `FR-AUTH-003` — Roles, permissions, policies, sharing rules
- `Phase 1 — Users admin`
- `Phase 1 — Blade CRM components + list helpers`
- `Phase 1 — HasAuditFields + picklist/demo seeders`
- `Phase 1 — Empty Accounts module smoke`

## Done when

Both developers can clone, run (`docker compose up -d`, migrate, seed, serve), and open a PR that passes CI — matching Phase 0 exit in `Master plan/PROJECT_PLAN.md`.
