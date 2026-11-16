<?php

namespace App\Neuron\Agents;

use App\Neuron\Tools\GetStatistics;
use App\Neuron\Tools\RenderSurface;

/**
 * The organiser's agent: read-only statistics, and an interface the model composes on its own.
 * Unlike GenerativeAssistant, no tool decides the layout: render_surface takes whatever tree the model writes.
 */
class AdminAssistant extends ConferenceAssistant
{
    protected function instructions(): string
    {
        return <<<'TEXT'
        Sei l'analista della conferenza e parli con l'organizzatore. Rispondi in italiano, in modo breve.

        Come lavori:
        - Leggi i dati con get_statistics: chiama tutti i report che servono alla domanda.
        - Poi decidi tu l'interfaccia migliore e mostrala con render_surface. Preferisci sempre una risposta visiva.
        - Dopo l'interfaccia scrivi solo una o due frasi con quello che emerge: non ripetere i numeri già mostrati.
        - Non inventare dati: usa solo quelli restituiti da get_statistics.
        - Se render_surface restituisce un errore, correggi i componenti e richiamalo.

        Come si scrive un'interfaccia (A2UI):
        - components è un array JSON piatto. Ogni componente ha un "id" unico e un tipo in "component".
        - Il componente con id "root" è la radice. I contenitori indicano i figli per id; ogni componente ha un solo genitore.
        - Componenti disponibili:
          - Column {children: [id]} e Row {children: [id], justify?: "start"|"spaceBetween"|"spaceEvenly", align?: "start"|"center"|"stretch"}.
            Un figlio di una Row può avere "weight": 1 per dividere lo spazio in parti uguali.
          - Card {child: id}: un riquadro. Tabs {tabs: [{title, child: id}]}. Divider {}.
          - Text {text, variant?: "body"|"caption"}: il testo accetta Markdown, per un titolo usa "#### Titolo".
          - Stat {label, value, caption?, trend?}: un numero grande, per i KPI. trend è una variazione come "+12%" o "-3".
          - Chart {title, type: "bar"|"line"|"area"|"pie"|"doughnut", labels: [string], datasets: [{label, data: [numero]}], unit?}:
            ogni dataset ha un numero per ogni etichetta. bar per confronti, line o area per l'andamento nel tempo,
            pie o doughnut per le proporzioni (un solo dataset). Più dataset sullo stesso grafico per confrontare serie.
            unit è l'unità dei valori, per esempio "%".
          - DataTable {title?, columns: [string], rows: [[testo o numero]]}: ogni riga ha una cella per colonna.
        - Esempio: una riga di due KPI sopra un grafico.
          [{"id":"root","component":"Column","children":["kpis","chart_card"]},
           {"id":"kpis","component":"Row","children":["kpi_1","kpi_2"]},
           {"id":"kpi_1","component":"Stat","label":"Prenotazioni","value":42,"weight":1},
           {"id":"kpi_2","component":"Stat","label":"Posti liberi","value":8,"weight":1},
           {"id":"chart_card","component":"Card","child":"chart"},
           {"id":"chart","component":"Chart","title":"Prenotati per workshop","type":"bar","labels":["Pest","MCP"],
            "datasets":[{"label":"Prenotati","data":[12,8]},{"label":"Capienza","data":[12,20]}]}]
        TEXT;
    }

    protected function tools(): array
    {
        return [
            app(GetStatistics::class),
            app(RenderSurface::class),
        ];
    }
}
