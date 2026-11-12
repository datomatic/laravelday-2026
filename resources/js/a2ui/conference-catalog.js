import { html, nothing } from 'lit';
import { z } from 'zod/v3';
import { A2uiLitElement, basicCatalog } from '@a2ui/web_core/v1_0';

// Custom A2UI component: the basic catalog has no charts. Must match A2uiSurfaceBuilder::CONFERENCE_CATALOG.
export const CONFERENCE_CATALOG = 'urn:laravelday:a2ui:conference';

class BarChartElement extends A2uiLitElement {
    // Light DOM, so the bars are styled by resources/css/app.css like the rest of the page.
    createRenderRoot() {
        return this;
    }

    render() {
        const props = this.controller?.props;

        if (!props) {
            return nothing;
        }

        const max = Math.max(1, ...props.bars.map((bar) => bar.max ?? bar.value));

        return html`
            <figure class="bar-chart">
                <figcaption>${props.title}</figcaption>
                ${props.bars.map((bar) => html`
                    <div class="bar-chart-row">
                        <span class="bar-chart-label" title=${bar.label}>${bar.label}</span>
                        <span class="bar-chart-track"><span class="bar-chart-fill" style="width: ${(bar.value / max) * 100}%"></span></span>
                        <span class="bar-chart-value">${bar.value}${bar.max ? `/${bar.max}` : ''}</span>
                    </div>
                `)}
            </figure>
        `;
    }
}

BarChartElement.tagName = 'conference-bar-chart';

const BarChart = {
    name: 'BarChart',
    tagName: BarChartElement.tagName,
    element: BarChartElement,
    schema: z.object({
        title: z.string(),
        bars: z.array(z.object({
            label: z.string(),
            value: z.number().int().min(0),
            max: z.number().int().positive().optional(),
        })),
    }).strict(),
};

export const conferenceCatalog = basicCatalog.derive({
    catalogId: CONFERENCE_CATALOG,
    components: [...basicCatalog.components.values(), BarChart],
});
