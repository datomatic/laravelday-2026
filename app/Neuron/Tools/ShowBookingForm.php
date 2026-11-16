<?php

namespace App\Neuron\Tools;

use App\Models\Workshop;
use App\Neuron\Tools\Concerns\RendersA2ui;
use App\Services\A2uiSurfaceBuilder;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ShowBookingForm extends Tool
{
    use RendersA2ui;

    public const NAME = 'show_booking_form';

    protected string $name = self::NAME;

    protected ?string $description = "Mostra all'utente il modulo di prenotazione di un workshop, dove sceglie il ruolo in azienda e scrive il nome dell'azienda. La prenotazione parte quando l'utente conferma il modulo.";

    public function __construct(private A2uiSurfaceBuilder $surfaces) {}

    protected function properties(): array
    {
        return [
            new ToolProperty('workshop_id', PropertyType::INTEGER, 'Id del workshop, preso da list_workshops.', required: true),
        ];
    }

    public function __invoke(int $workshop_id): string
    {
        validator(['workshop_id' => $workshop_id], ['workshop_id' => ['exists:workshops,id']])->validate();

        return $this->a2ui($this->surfaces->bookingFormCard(Workshop::find($workshop_id)));
    }
}
