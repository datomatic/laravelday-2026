<?php

namespace App\Mcp\Tools;

use App\Enums\Track;
use App\Models\Talk;
use App\Services\AgendaService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('search_talks')]
#[Description('Cerca i talk del programma per testo libero (titolo, abstract, speaker) e/o per track. Senza filtri restituisce tutto il programma.')]
#[IsReadOnly]
class SearchTalksTool extends Tool
{
    public function handle(Request $request, AgendaService $agenda): ResponseFactory
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
            'track' => ['nullable', Rule::enum(Track::class)],
        ]);

        $talks = $agenda->searchTalks(
            $validated['query'] ?? null,
            Track::tryFrom($validated['track'] ?? ''),
        );

        return Response::structured([
            'talks' => $talks->map(fn (Talk $talk) => $talk->toSummary())->all(),
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Testo da cercare in titolo, abstract o nome dello speaker.'),
            'track' => $schema->string()->enum(Track::class)->description('Track del talk.'),
        ];
    }
}
