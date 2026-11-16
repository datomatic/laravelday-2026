import { html, nothing } from 'lit';
import { z } from 'zod/v3';
import { A2uiLitElement, basicCatalog } from '@a2ui/web_core/v1_0';
import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PieController,
    PointElement,
    Tooltip,
} from 'chart.js';

// Custom A2UI components: the basic catalog has no charts, tables or KPIs. Must match A2uiSurfaceBuilder::CONFERENCE_CATALOG
// and A2uiComponentValidator on the server.
export const CONFERENCE_CATALOG = 'urn:laravelday:a2ui:conference';

Chart.register(ArcElement, BarController, BarElement, CategoryScale, DoughnutController, Filler, Legend, LinearScale, LineController, LineElement, PieController, PointElement, Tooltip);
Chart.defaults.font.family = getComputedStyle(document.documentElement).fontFamily;
Chart.defaults.color = '#71717a';

// Red first, like the rest of the app; then the track colours.
const PALETTE = ['#dc2626', '#0284c7', '#f59e0b', '#059669', '#7c3aed', '#db2777', '#0d9488', '#71717a'];

const isCircular = (type) => type === 'pie' || type === 'doughnut';

// Long category names (talk and workshop titles) do not fit under vertical bars: lay those bars out horizontally.
const isHorizontal = ({ type, labels }) => type === 'bar' && labels.some((label) => label.length > 18);

const formatValue = (value, unit) => {
    const formatted = Number(value).toLocaleString('it-IT');

    if (!unit) {
        return formatted;
    }

    return unit === '%' ? `${formatted}%` : `${formatted} ${unit}`;
};

function chartConfig(props) {
    const circular = isCircular(props.type);
    const horizontal = isHorizontal(props);
    const valueAxis = horizontal ? 'x' : 'y';
    const categoryAxis = horizontal ? 'y' : 'x';

    return {
        // An area chart is a filled line chart.
        type: props.type === 'area' ? 'line' : props.type,
        data: {
            labels: props.labels,
            datasets: props.datasets.map((dataset, index) => {
                const color = PALETTE[index % PALETTE.length];

                return {
                    label: dataset.label,
                    data: dataset.data,
                    backgroundColor: circular
                        ? props.labels.map((_, i) => PALETTE[i % PALETTE.length])
                        : props.type === 'area' ? `${color}33` : color,
                    borderColor: circular ? '#ffffff' : color,
                    borderWidth: props.type === 'bar' ? 0 : 2,
                    borderRadius: props.type === 'bar' ? 6 : 0,
                    maxBarThickness: 28,
                    fill: props.type === 'area',
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: color,
                };
            }),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: horizontal ? 'y' : 'x',
            animation: { duration: 500 },
            plugins: {
                legend: {
                    display: circular || props.datasets.length > 1,
                    position: circular ? 'right' : 'top',
                    labels: { boxWidth: 12, boxHeight: 12, useBorderRadius: true, borderRadius: 3 },
                },
                tooltip: {
                    backgroundColor: '#18181b',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: (context) => `${circular ? context.label : context.dataset.label}: ${formatValue(context.raw, props.unit)}`,
                    },
                },
            },
            scales: circular ? {} : {
                [categoryAxis]: {
                    grid: { display: false },
                    border: { display: false },
                    // Long titles are cut, the tooltip shows them in full.
                    ticks: { callback(value) {
                        const label = this.getLabelForValue(value);

                        return label.length > 32 ? `${label.slice(0, 31)}…` : label;
                    } },
                },
                [valueAxis]: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: '#f4f4f5' },
                    ticks: { precision: 0, callback: (value) => formatValue(value, props.unit) },
                },
            },
        },
    };
}

class ChartElement extends A2uiLitElement {
    // Light DOM, so the card is styled by resources/css/app.css like the rest of the page.
    createRenderRoot() {
        return this;
    }

    render() {
        const props = this.controller?.props;

        if (!props) {
            return nothing;
        }

        const height = isHorizontal(props) ? Math.max(220, props.labels.length * 36 + 40) : isCircular(props.type) ? 260 : 288;

        return html`
            <figure class="chart">
                <figcaption>${props.title}</figcaption>
                <div class="chart-canvas" style="height: ${height}px"><canvas></canvas></div>
            </figure>
        `;
    }

    // Chart.js draws on the canvas after Lit has rendered it, and redraws it when the props change.
    updated() {
        const props = this.controller?.props;

        this.chart?.destroy();
        this.chart = props ? new Chart(this.querySelector('canvas'), chartConfig(props)) : null;
    }

    disconnectedCallback() {
        super.disconnectedCallback();
        this.chart?.destroy();
        this.chart = null;
    }
}

ChartElement.tagName = 'conference-chart';

class StatElement extends A2uiLitElement {
    createRenderRoot() {
        return this;
    }

    render() {
        const props = this.controller?.props;

        if (!props) {
            return nothing;
        }

        const trend = props.trend?.trim();

        return html`
            <div class="stat">
                <span class="stat-label">${props.label}</span>
                <span class="stat-value">${props.value}</span>
                ${trend || props.caption ? html`
                    <span class="stat-caption">
                        ${trend ? html`<span class=${trend.startsWith('-') ? 'stat-trend is-down' : 'stat-trend'}>${trend}</span>` : nothing}
                        ${props.caption ?? nothing}
                    </span>
                ` : nothing}
            </div>
        `;
    }
}

StatElement.tagName = 'conference-stat';

class DataTableElement extends A2uiLitElement {
    createRenderRoot() {
        return this;
    }

    render() {
        const props = this.controller?.props;

        if (!props) {
            return nothing;
        }

        return html`
            <figure class="data-table">
                ${props.title ? html`<figcaption>${props.title}</figcaption>` : nothing}
                <div class="data-table-scroll">
                    <table>
                        <thead><tr>${props.columns.map((column) => html`<th>${column}</th>`)}</tr></thead>
                        <tbody>
                            ${props.rows.map((row) => html`<tr>${row.map((cell) => html`<td class=${typeof cell === 'number' ? 'is-number' : ''}>${typeof cell === 'number' ? cell.toLocaleString('it-IT') : cell}</td>`)}</tr>`)}
                        </tbody>
                    </table>
                </div>
            </figure>
        `;
    }
}

DataTableElement.tagName = 'conference-data-table';

// weight is the flex share of a component inside a Row: the renderer reads it, the element ignores it.
const weight = z.number().optional();

const ChartComponent = {
    name: 'Chart',
    tagName: ChartElement.tagName,
    element: ChartElement,
    schema: z.object({
        title: z.string(),
        type: z.enum(['bar', 'line', 'area', 'pie', 'doughnut']),
        labels: z.array(z.string()),
        datasets: z.array(z.object({
            label: z.string(),
            data: z.array(z.number()),
        })),
        unit: z.string().optional(),
        weight,
    }).strict(),
};

const Stat = {
    name: 'Stat',
    tagName: StatElement.tagName,
    element: StatElement,
    schema: z.object({
        label: z.string(),
        value: z.union([z.string(), z.number()]),
        caption: z.string().optional(),
        trend: z.string().optional(),
        weight,
    }).strict(),
};

const DataTable = {
    name: 'DataTable',
    tagName: DataTableElement.tagName,
    element: DataTableElement,
    schema: z.object({
        title: z.string().optional(),
        columns: z.array(z.string()),
        rows: z.array(z.array(z.union([z.string(), z.number()]))),
        weight,
    }).strict(),
};

export const conferenceCatalog = basicCatalog.derive({
    catalogId: CONFERENCE_CATALOG,
    components: [...basicCatalog.components.values(), ChartComponent, Stat, DataTable],
});
