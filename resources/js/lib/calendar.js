/**
 * FullCalendar registration for CRM calendar views (day / week / month / list + drag-and-drop).
 * Locked Phase 0 choice — use this module instead of importing FullCalendar ad hoc.
 *
 * Usage (page module):
 *   import { createCrmCalendar } from './lib/calendar';
 *   createCrmCalendar(el, { events: '/api/events', ... });
 */

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

const defaultPlugins = [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin];

/**
 * @param {HTMLElement|string} elementOrSelector
 * @param {import('@fullcalendar/core').CalendarOptions} [options]
 * @returns {Calendar}
 */
export function createCrmCalendar(elementOrSelector, options = {}) {
    const el =
        typeof elementOrSelector === 'string'
            ? document.querySelector(elementOrSelector)
            : elementOrSelector;

    if (! (el instanceof HTMLElement)) {
        throw new Error('createCrmCalendar: container element not found');
    }

    const calendar = new Calendar(el, {
        plugins: defaultPlugins,
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        height: 'auto',
        editable: true,
        selectable: true,
        nowIndicator: true,
        dayMaxEvents: true,
        eventColor: '#0176d3',
        ...options,
        plugins: options.plugins || defaultPlugins,
    });

    calendar.render();

    return calendar;
}

export { Calendar, dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin };

window.crm = window.crm || {};
window.crm.calendar = {
    Calendar,
    create: createCrmCalendar,
    plugins: {
        dayGrid: dayGridPlugin,
        timeGrid: timeGridPlugin,
        list: listPlugin,
        interaction: interactionPlugin,
    },
};
