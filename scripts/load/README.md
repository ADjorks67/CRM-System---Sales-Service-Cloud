# k6 load scripts (Phase 6 / NFR-PERF-002)
#
# Install k6: https://grafana.com/docs/k6/latest/set-up/install-k6/
#
# Examples (app must be running, e.g. `composer run dev`):
#
#   k6 run scripts/load/health.js
#   BASE_URL=http://127.0.0.1:8000 k6 run scripts/load/smoke.js
#
# Full 100 VU soak needs a seeded DB and realistic think-time; tune VUs in each script.
