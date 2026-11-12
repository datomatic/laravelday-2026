<?php

namespace App\Ai\Tools;

use App\Models\Talk;
use App\Services\A2uiSurfaceBuilder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ShowTalkCards implements Tool
{
    public const NAME = 'show_talk_cards';

    public function __construct(private A2uiSurfaceBuilder $surfaces) {}

    public function name(): string
    {
        return self::NAME;
    }

    public function description(): Stringable|string
    {
        return "Mostra all'utente una scheda interattiva per ogni talk indicato, con il pulsante per aggiungerlo all'agenda. Usalo al posto di un elenco testuale quando presenti dei talk.";
    }

    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'talk_ids' => ['required', 'array', 'min:1', 'max:8'],
            'talk_ids.*' => ['integer', 'exists:talks,id'],
        ]);

        $talks = Talk::with('speaker')->whereIn('id', $validated['talk_ids'])->orderBy('starts_at')->get();

        return json_encode(['a2ui' => $this->surfaces->talkCards($talks)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'talk_ids' => $schema->array()->items($schema->integer())->description('Id dei talk da mostrare, presi da search_talks.')->required(),
        ];
    }
}
