<?php

namespace App\Neuron\Tools;

use App\Models\Speaker;
use App\Neuron\Tools\Concerns\RendersA2ui;
use App\Services\A2uiSurfaceBuilder;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ShowSpeakerCard extends Tool
{
    use RendersA2ui;

    public const NAME = 'show_speaker_card';

    protected string $name = self::NAME;

    protected ?string $description = "Mostra all'utente la scheda profilo di uno speaker: foto, azienda, biografia e sessioni. Usalo quando l'utente chiede chi è uno speaker.";

    public function __construct(private A2uiSurfaceBuilder $surfaces) {}

    protected function properties(): array
    {
        return [
            new ToolProperty('speaker_id', PropertyType::INTEGER, 'Id dello speaker, preso dal campo speaker_id di search_talks o list_workshops.', required: true),
        ];
    }

    public function __invoke(int $speaker_id): string
    {
        validator(['speaker_id' => $speaker_id], ['speaker_id' => ['exists:speakers,id']])->validate();

        $speaker = Speaker::with(['talks' => fn ($talks) => $talks->orderBy('starts_at'), 'workshops'])->find($speaker_id);

        return $this->a2ui($this->surfaces->speakerCard($speaker));
    }
}
