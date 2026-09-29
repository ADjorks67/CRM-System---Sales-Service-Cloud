/**
 * Vite entry: load on calendar pages.
 * Expects a container with data-crm-calendar; optional data-crm-calendar-events URL for JSON feed.
 * Optional: data-crm-calendar-create, data-crm-calendar-reschedule (__ID__ placeholder), data-crm-csrf.
 */

import './bootstrap';
import { createCrmCalendar } from './lib/calendar';

function formatLocalDateTime(date, allDay) {
    if (! (date instanceof Date)) {
        return '';
    }

    if (allDay) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');

        return `${y}-${m}-${d}T00:00`;
    }

    const pad = (n) => String(n).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function bootCalendars() {
    document.querySelectorAll('[data-crm-calendar]').forEach((el) => {
        if (! (el instanceof HTMLElement) || el.dataset.crmCalendarBooted === '1') {
            return;
        }

        el.dataset.crmCalendarBooted = '1';

        const eventsUrl = el.getAttribute('data-crm-calendar-events');
        const initialView = el.getAttribute('data-crm-calendar-view') || 'dayGridMonth';
        const createUrl = el.getAttribute('data-crm-calendar-create');
        const rescheduleTemplate = el.getAttribute('data-crm-calendar-reschedule');
        const csrf = el.getAttribute('data-crm-csrf');

        createCrmCalendar(el, {
            initialView,
            ...(eventsUrl
                ? {
                      events: eventsUrl,
                  }
                : {
                      events: [],
                  }),
            dateClick(info) {
                if (! createUrl) {
                    return;
                }

                const start = formatLocalDateTime(info.date, info.allDay);
                const endDate = new Date(info.date.getTime() + (info.allDay ? 0 : 60 * 60 * 1000));
                const end = formatLocalDateTime(endDate, info.allDay);
                const params = new URLSearchParams({
                    starts_at: start,
                    ends_at: end,
                    all_day: info.allDay ? '1' : '0',
                });

                window.location.href = `${createUrl}?${params.toString()}`;
            },
            select(info) {
                if (! createUrl) {
                    return;
                }

                const params = new URLSearchParams({
                    starts_at: formatLocalDateTime(info.start, info.allDay),
                    ends_at: formatLocalDateTime(info.end, info.allDay),
                    all_day: info.allDay ? '1' : '0',
                });

                window.location.href = `${createUrl}?${params.toString()}`;
            },
            eventClick(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            },
            eventDrop(info) {
                void persistReschedule(info, rescheduleTemplate, csrf);
            },
            eventResize(info) {
                void persistReschedule(info, rescheduleTemplate, csrf);
            },
        });
    });
}

async function persistReschedule(info, rescheduleTemplate, csrf) {
    if (! rescheduleTemplate || ! csrf) {
        info.revert();

        return;
    }

    const url = rescheduleTemplate.replace('__ID__', String(info.event.id));
    const body = {
        starts_at: info.event.start ? info.event.start.toISOString() : null,
        ends_at: info.event.end
            ? info.event.end.toISOString()
            : info.event.start
              ? new Date(info.event.start.getTime() + 60 * 60 * 1000).toISOString()
              : null,
        is_all_day: info.event.allDay,
    };

    try {
        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
            credentials: 'same-origin',
        });

        if (! response.ok) {
            info.revert();
        }
    } catch {
        info.revert();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootCalendars);
} else {
    bootCalendars();
}
