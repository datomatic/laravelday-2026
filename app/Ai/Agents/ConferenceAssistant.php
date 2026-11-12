<?php

namespace App\Ai\Agents;

use App\Ai\Tools\ShowBookingForm;
use App\Ai\Tools\ShowChart;
use App\Ai\Tools\ShowTalkCards;
use App\Ai\Tools\ShowWorkshopCards;
use App\Mcp\Tools\AddToAgendaTool;
use App\Mcp\Tools\BookWorkshopTool;
use App\Mcp\Tools\ClearAgendaTool;
use App\Mcp\Tools\ListWorkshopsTool;
use App\Mcp\Tools\SearchTalksTool;
use App\Mcp\Tools\ShowAgendaTool;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Laravel\Ai\Tools\McpServerTool;
use Stringable;

#[MaxSteps(8)]
class ConferenceAssistant implements Agent, HasTools
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'TEXT'
        Sei l'assistente della conferenza. Aiuti l'utente autenticato a orientarsi nel programma,
        a costruire la sua agenda personale e a prenotare i workshop. Rispondi in italiano, in modo breve.

        - Per presentare dei talk usa show_talk_cards con gli id trovati con search_talks, invece di elencarli a testo.
        - Per presentare i workshop usa show_workshop_cards.
        - Per prenotare un workshop servono il ruolo in azienda e il nome dell'azienda.
          Se l'utente li ha già detti usa book_workshop; altrimenti mostra il modulo con show_booking_form,
          senza chiederli a testo. Non inventarli.
        - Per grafici e statistiche usa show_chart: non disegnare grafici a testo e non usare show_talk_cards.
        - Dopo aver mostrato delle schede aggiungi solo una frase di commento: non ripetere il contenuto delle schede.
        - Non inventare talk, orari o id: usa sempre i tool.
        TEXT;
    }

    /**
     * The MCP server tools are the same classes exposed by ConferenceServer: they run in-process here.
     *
     * @return list<Tool|\Laravel\Mcp\Server\Tool>
     */
    public function tools(): iterable
    {
        return [
            new SearchTalksTool,
            new ShowAgendaTool,
            new AddToAgendaTool,
            new ListWorkshopsTool,
            (new McpServerTool(new BookWorkshopTool))->requireApproval('La prenotazione occupa uno dei posti limitati del workshop.'),
            (new McpServerTool(new ClearAgendaTool))->requireApproval("Tutti i talk verranno rimossi dall'agenda."),
            app(ShowTalkCards::class),
            app(ShowWorkshopCards::class),
            app(ShowBookingForm::class),
            app(ShowChart::class),
        ];
    }
}
