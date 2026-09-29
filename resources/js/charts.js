/**
 * Vite entry: load on pages that render charts.
 * Expects <canvas data-crm-chart> elements; JSON config via data-crm-chart-config or a sibling script.
 *
 * Prefer importing helpers from ./lib/charts in module-specific files (home.js, reports.js).
 */

import './bootstrap';
import { createCrmChart, createDonutChart, createFunnelChart, crmChartColors } from './lib/charts';

function parseConfig(el) {
    const raw = el.getAttribute('data-crm-chart-config');

    if (! raw) {
        return null;
    }

    try {
        return JSON.parse(raw);
    } catch {
        console.error('Invalid data-crm-chart-config JSON', el);
        return null;
    }
}

function bootCharts(root = document) {
    root.querySelectorAll('[data-crm-chart]').forEach((el) => {
        if (! (el instanceof HTMLCanvasElement)) {
            return;
        }

        if (el.dataset.crmChartReady === '1') {
            return;
        }

        const kind = el.getAttribute('data-crm-chart') || 'bar';
        const config = parseConfig(el);

        if (! config) {
            return;
        }

        if (kind === 'donut' || kind === 'doughnut') {
            createDonutChart(el, config);
        } else if (kind === 'funnel') {
            createFunnelChart(el, config);
        } else {
            createCrmChart(el, {
                type: kind,
                data: {
                    labels: config.labels || [],
                    datasets: [
                        {
                            label: config.label || 'Series',
                            data: config.values || [],
                            backgroundColor: config.backgroundColor || crmChartColors.series,
                            borderWidth: 0,
                        },
                    ],
                },
            });
        }

        el.dataset.crmChartReady = '1';
    });
}

window.crmBootCharts = bootCharts;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => bootCharts());
} else {
    bootCharts();
}
