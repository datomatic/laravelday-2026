<?php

namespace App\Neuron\Agents;

use App\Mcp\Tools\AddToAgendaTool;
use App\Mcp\Tools\BookWorkshopTool;
use App\Mcp\Tools\ClearAgendaTool;
use App\Mcp\Tools\ListWorkshopsTool;
use App\Mcp\Tools\SearchTalksTool;
use App\Mcp\Tools\ShowAgendaTool;
use App\Neuron\Tools\McpTool;

/**
 * The chat "as many built it": the same app actions, but no approval step on the server.
 * Without a protocol to pause the run, the confirmation is left to the model, in plain text.
 */
class HomemadeAssistant extends ConferenceAssistant
{
    protected function instructions(): string
    {
        return <<<'TEXT'
        Sei l'assistente della conferenza. Aiuti l'utente autenticato a orientarsi nel programma,
        a costruire la sua agenda personale e a prenotare i workshop. Rispondi in italiano, in modo breve,
        formattando la risposta in Markdown.

        - Quando presenti talk o workshop usa un elenco puntato: orario, titolo in grassetto, sala e speaker.
        - Per prenotare un workshop servono il ruolo in azienda e il nome dell'azienda.
          Se l'utente non li ha detti chiediglieli. Non inventarli.
        - Prima di usare book_workshop o clear_agenda riepiloga cosa stai per fare e chiedi conferma.
          Procedi solo dopo un sì esplicito dell'utente.
        - Non inventare talk, orari o id: usa sempre i tool.
        TEXT;
    }

    protected function tools(): array
    {
        return [
            new McpTool(new SearchTalksTool),
            new McpTool(new ShowAgendaTool),
            new McpTool(new AddToAgendaTool),
            new McpTool(new ListWorkshopsTool),
            new McpTool(new BookWorkshopTool),
            new McpTool(new ClearAgendaTool),
        ];
    }
}
