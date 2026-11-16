<?php

namespace App\Neuron\Agents;

use App\Mcp\Tools\AddToAgendaTool;
use App\Mcp\Tools\BookWorkshopTool;
use App\Mcp\Tools\ClearAgendaTool;
use App\Mcp\Tools\ListWorkshopsTool;
use App\Mcp\Tools\SearchTalksTool;
use App\Mcp\Tools\ShowAgendaTool;
use App\Neuron\Tools\McpTool;
use NeuronAI\Agent\Agent;
use NeuronAI\Chat\History\MessageStoreInterface;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\Responses\OpenAIResponses;
use NeuronAI\Workflow\Persistence\PersistenceInterface;
use Throwable;

/**
 * The base chat agent: the MCP tools and plain Markdown answers. GenerativeAssistant adds the A2UI tools.
 */
class ConferenceAssistant extends Agent
{
    /**
     * The conversation and the paused runs are stored in the database, keyed by the thread id (see for()).
     */
    public function __construct(
        protected MessageStoreInterface $conversations,
        protected PersistenceInterface $runs,
    ) {
        parent::__construct();
    }

    protected function provider(): AIProviderInterface
    {
        return new OpenAIResponses(
            key: config('services.openai.key'),
            model: config('services.openai.model'),
        );
    }

    protected function instructions(): string
    {
        return <<<'TEXT'
        Sei l'assistente della conferenza. Aiuti l'utente autenticato a orientarsi nel programma,
        a costruire la sua agenda personale e a prenotare i workshop. Rispondi in italiano, in modo breve,
        formattando la risposta in Markdown.

        - Quando presenti talk o workshop usa un elenco puntato: orario, titolo in grassetto, sala e speaker.
        - Per prenotare un workshop servono il ruolo in azienda e il nome dell'azienda.
          Se l'utente non li ha detti chiediglieli, poi usa book_workshop. Non inventarli.
        - Non inventare talk, orari o id: usa sempre i tool.
        TEXT;
    }

    /**
     * The MCP server tools are the same classes exposed by ConferenceServer: they run in-process here.
     */
    protected function tools(): array
    {
        return [
            new McpTool(new SearchTalksTool),
            new McpTool(new ShowAgendaTool),
            new McpTool(new AddToAgendaTool),
            new McpTool(new ListWorkshopsTool),
            // The returned string requires approval and is the reason shown on the approval card.
            (new McpTool(new BookWorkshopTool))->withApprovalPolicy(fn () => 'La prenotazione occupa uno dei posti limitati del workshop.'),
            (new McpTool(new ClearAgendaTool))->withApprovalPolicy(fn () => "Tutti i talk verranno rimossi dall'agenda."),
        ];
    }

    protected function messageStore(): MessageStoreInterface
    {
        return $this->conversations;
    }

    protected function persistence(): PersistenceInterface
    {
        return $this->runs;
    }

    /**
     * A failing tool (e.g. a validation error) answers the model instead of failing the run.
     */
    protected function resolveToolErrorHandler(): callable
    {
        return fn (Throwable $exception): string => "Errore: {$exception->getMessage()}";
    }
}
