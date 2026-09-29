# Accessibility automation (Phase 6 / NFR-USE-003)

Requires a running app (`composer run dev` or `php artisan serve`) and a **ChromeDriver** build that matches your installed Chrome major version.

## Setup

```bash
npm install
# If axe fails with ChromeDriver version mismatch:
npm install chromedriver@<chrome-major> --save-dev
node node_modules/chromedriver/install.js
```

Example (Chrome 153): `npm install chromedriver@153 --save-dev`

## Run

```bash
npm run a11y -- http://127.0.0.1:8000/login
npm run a11y -- http://127.0.0.1:8000/password/reset
```

Authenticated pages need a session cookie / headed flow — use the manual checklist in `docs/PHASE_6_CHECKLIST.md` section E plus `tests/Feature/AccessibilitySmokeTest.php`.

## Results (2026-09-29)

| URL | Result |
|---|---|
| `/login` | **0 violations** (axe-core WCAG 2A/2AA tags) |

Manual WCAG checklist remains required (keyboard, contrast, browsers).
