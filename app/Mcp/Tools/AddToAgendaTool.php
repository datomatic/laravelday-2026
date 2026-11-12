<?php

namespace App\Mcp\Tools;

use App\Models\Talk;
use App\Services\AgendaService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('add_to_agenda')]
#[Description("Aggiunge un talk all'agenda personale dell'utente. Usa search_talks per trovare l'id del talk.")]
#[IsIdempotent]
class AddToAgendaTool extends Tool
{
    public function handle(Request $request, AgendaService $agenda): Response
    {
        $validated = $request->validate([
            'talk_id' => ['required', 'integer', 'exists:talks,id'],
        ]);

        $talk = Talk::find($validated['talk_id']);

        $agenda->add($request->user(), $talk);

        return Response::text("\"{$talk->title}\" aggiunto all'agenda ({$talk->starts_at->format('H:i')}, {$talk->room}).");
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'talk_id' => $schema->integer()->description('Id del talk da aggiungere.')->required(),
        ];
    }
}
