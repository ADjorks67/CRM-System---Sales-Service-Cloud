/**
 * Shared frontend bootstrap (CSRF + JSON fetch helpers).
 * Loaded from app.js on every page that uses the main layout.
 */

export function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.getAttribute('content') : '';
}

/**
 * fetch() wrapper that always sends the Laravel CSRF header for same-origin calls.
 *
 * @param {string} url
 * @param {RequestInit} [options]
 * @returns {Promise<Response>}
 */
export function crmFetch(url, options = {}) {
    const headers = new Headers(options.headers || {});

    if (! headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken());
    }

    if (! headers.has('Accept')) {
        headers.set('Accept', 'application/json');
    }

    if (options.body && ! headers.has('Content-Type') && !(options.body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
    }

    return fetch(url, {
        ...options,
        headers,
        credentials: options.credentials ?? 'same-origin',
    });
}

window.crm = window.crm || {};
window.crm.csrfToken = csrfToken;
window.crm.fetch = crmFetch;
