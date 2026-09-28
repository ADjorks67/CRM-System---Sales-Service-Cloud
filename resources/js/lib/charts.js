/**
 * Chart.js registration for CRM charts (funnel/bar, donut, bar).
 * Locked Phase 0 choice — use this module instead of importing Chart.js ad hoc.
 *
 * Usage (page module):
 *   import { createCrmChart, crmChartColors } from './lib/charts';
 *   createCrmChart(canvas, { type: 'doughnut', data, options });
 */

import {
    Chart,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    DoughnutController,
    BarController,
    Title,
} from 'chart.js';

Chart.register(
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    DoughnutController,
    BarController,
    Title,
);

/** SRS §6 palette for chart series */
export const crmChartColors = {
    primary: '#032d60',
    secondary: '#0176d3',
    success: '#2e844a',
    warning: '#fe9339',
    error: '#ba0517',
    info: '#0176d3',
    muted: '#747474',
    series: ['#032d60', '#0176d3', '#2e844a', '#fe9339', '#ba0517', '#5b5fc7', '#747474'],
};

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#181818',
                font: { family: 'ui-sans-serif, system-ui, sans-serif', size: 12 },
            },
        },
        title: {
            color: '#032d60',
            font: { family: 'ui-sans-serif, system-ui, sans-serif', size: 14, weight: '600' },
        },
    },
};

/**
 * @param {HTMLCanvasElement|string} canvasOrSelector
 * @param {import('chart.js').ChartConfiguration} config
 * @returns {Chart}
 */
export function createCrmChart(canvasOrSelector, config) {
    const canvas =
        typeof canvasOrSelector === 'string'
            ? document.querySelector(canvasOrSelector)
            : canvasOrSelector;

    if (! (canvas instanceof HTMLCanvasElement)) {
        throw new Error('createCrmChart: canvas element not found');
    }

    return new Chart(canvas, {
        ...config,
        options: {
            ...defaultOptions,
            ...(config.options || {}),
            plugins: {
                ...defaultOptions.plugins,
                ...((config.options && config.options.plugins) || {}),
            },
        },
    });
}

/**
 * Horizontal bar chart used as the pipeline funnel until a dedicated funnel type is needed.
 *
 * @param {HTMLCanvasElement|string} canvasOrSelector
 * @param {{ labels: string[], values: number[], label?: string }} payload
 * @returns {Chart}
 */
export function createFunnelChart(canvasOrSelector, payload) {
    return createCrmChart(canvasOrSelector, {
        type: 'bar',
        data: {
            labels: payload.labels,
            datasets: [
                {
                    label: payload.label || 'Pipeline',
                    data: payload.values,
                    backgroundColor: crmChartColors.series,
                    borderWidth: 0,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
            },
        },
    });
}

/**
 * Donut (doughnut) chart — e.g. revenue by source (FR-HOME).
 *
 * @param {HTMLCanvasElement|string} canvasOrSelector
 * @param {{ labels: string[], values: number[], label?: string }} payload
 * @returns {Chart}
 */
export function createDonutChart(canvasOrSelector, payload) {
    return createCrmChart(canvasOrSelector, {
        type: 'doughnut',
        data: {
            labels: payload.labels,
            datasets: [
                {
                    label: payload.label || 'Series',
                    data: payload.values,
                    backgroundColor: crmChartColors.series,
                    borderWidth: 0,
                },
            ],
        },
    });
}

export { Chart };

window.crm = window.crm || {};
window.crm.charts = {
    Chart,
    colors: crmChartColors,
    create: createCrmChart,
    funnel: createFunnelChart,
    donut: createDonutChart,
};
