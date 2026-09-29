import http from 'k6/http';
import { check, sleep } from 'k6';

/**
 * Concurrent-user smoke toward NFR-PERF-002 (100 VUs).
 * Authenticated CRM pages need cookies/session — extend with login helper for full journeys.
 */
export const options = {
  stages: [
    { duration: '30s', target: 20 },
    { duration: '1m', target: 100 },
    { duration: '30s', target: 0 },
  ],
  thresholds: {
    http_req_failed: ['rate<0.05'],
    http_req_duration: ['p(95)<3000'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
  const health = http.get(`${BASE_URL}/health`);
  check(health, { 'health 200': (r) => r.status === 200 });

  const loginPage = http.get(`${BASE_URL}/login`);
  check(loginPage, { 'login page loads': (r) => r.status === 200 });

  sleep(1);
}
