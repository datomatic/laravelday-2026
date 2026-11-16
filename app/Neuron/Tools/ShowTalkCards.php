<?php

namespace App\Neuron\Tools;

use App\Models\Talk;
use App\Neuron\Tools\Concerns\RendersA2ui;
use App\Services\A2uiSurfaceBuilder;
use App\Services\AgendaService;
use NeuronAI\Tools\ArrayProperty;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ShowTalkCards extends Tool
{
    use RendersA2ui;

    public const NAME = 'show_talk_cards';

    protected string $name = self::NAME;

    protected ?string $description = "Mostra all'utente una scheda interattiva per ogni talk indicato, con il pulsante per aggiungerlo all'agenda. Usalo al posto di un elenco testuale quando presenti dei talk.";

    public function __construct(private A2uiSurfaceBuilder $surfaces, private AgendaService $agenda) {}

    protected function properties(): array
    {
        return [
            new ArrayProperty(
                name: 'talk_ids',
                description: 'Id dei talk da mostrare, presi da search_talks.',
                required: true,
                items: new ToolProperty('talk_id', PropertyType::INTEGER),
                minItems: 1,
                maxItems: 8,
            ),
        ];
    }

    /**
     * @param  list<int>  $talk_ids
     */
    public function __invoke(array $talk_ids): string
    {
        validator(['talk_ids' => $talk_ids], ['talk_ids.*' => ['integer', 'exists:talks,id']])->validate();

        $talks = Talk::with('speaker')->whereIn('id', $talk_ids)->orderBy('starts_at')->get();

        return $this->a2ui($this->surfaces->talkCards($talks, $this->agenda->talksFor(auth()->user())));
    }
}
