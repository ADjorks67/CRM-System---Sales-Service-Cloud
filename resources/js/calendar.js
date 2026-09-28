/**
 * Vite entry: load on calendar pages.
 * Expects a container with data-crm-calendar; optional data-crm-calendar-events URL for JSON feed.
 *
 * Prefer importing createCrmCalendar from ./lib/calendar in calendar.js module pages.
 */

import './bootstrap';
import { createCrmCalendar } from './lib/calendar';

function bootCalendars() {
    document.querySelectorAll('[data-crm-calendar]').forEach((el) => {
        if (! (el instanceof HTMLElement)) {
            return;
        }

        const eventsUrl = el.getAttribute('data-crm-calendar-events');
        const initialView = el.getAttribute('data-crm-calendar-view') || 'dayGridMonth';

        createCrmCalendar(el, {
            initialView,
            ...(eventsUrl
                ? {
                      events: eventsUrl,
                  }
                : {
                      events: [],
                  }),
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootCalendars);
} else {
    bootCalendars();
}
