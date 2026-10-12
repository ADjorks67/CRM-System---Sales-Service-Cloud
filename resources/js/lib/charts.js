/**
 * Chart.js registration for CRM charts (funnel/bar, donut, bar).
 * Obsidian & Butter palette — butter / navy / espresso; emerald for positive only.
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

export const crmChartColors = {
    primary: '#E6C85C',
    secondary: '#F1D979',
    success: '#10B981',
    warning: '#D9A441',
    error: '#D94A4A',
    info: '#BBCBE4',
    violet: '#122235',
    violetBright: '#BBCBE4',
    muted: '#92908C',
    text: '#F5F3EE',
    series: ['#E6C85C', '#122235', '#705342', '#BBCBE4', '#10B981', '#D9A441', '#D94A4A'],
};

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#F5F3EE',
                font: { family: 'Inter, ui-sans-serif, system-ui, sans-serif', size: 12 },
            },
        },
        title: {
            color: '#F5F3EE',
            font: { family: 'Plus Jakarta Sans, ui-sans-serif, system-ui, sans-serif', size: 14, weight: '600' },
        },
    },
    scales: {
        x: {
            ticks: { color: '#92908C' },
            grid: { color: 'rgba(48, 49, 49, 0.9)' },
        },
        y: {
            ticks: { color: '#92908C' },
            grid: { color: 'rgba(48, 49, 49, 0.9)' },
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
                ...(config.options?.plugins || {}),
            },
        },
    });
}

/**
 * @param {HTMLCanvasElement} canvas
 * @param {{ labels: string[], values: number[], label?: string }} config
 */
export function createDonutChart(canvas, config) {
    return createCrmChart(canvas, {
        type: 'doughnut',
        data: {
            labels: config.labels || [],
            datasets: [
                {
                    label: config.label || 'Series',
                    data: config.values || [],
                    backgroundColor: crmChartColors.series,
                    borderWidth: 0,
                },
            ],
        },
        options: {
            plugins: {
                legend: { position: 'bottom' },
            },
            scales: {},
        },
    });
}

/**
 * @param {HTMLCanvasElement} canvas
 * @param {{ labels: string[], values: number[], label?: string }} config
 */
export function createFunnelChart(canvas, config) {
    return createCrmChart(canvas, {
        type: 'bar',
        data: {
            labels: config.labels || [],
            datasets: [
                {
                    label: config.label || 'Pipeline',
                    data: config.values || [],
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

window.crm = window.crm || {};
window.crm.charts = {
    create: createCrmChart,
    donut: createDonutChart,
    funnel: createFunnelChart,
    colors: crmChartColors,
};
