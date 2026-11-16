<?php

namespace App\Neuron\Tools;

use App\Neuron\Tools\Concerns\RendersA2ui;
use App\Services\A2uiSurfaceBuilder;
use App\Services\WorkshopBookingService;
use NeuronAI\Tools\Tool;

class ShowWorkshopCards extends Tool
{
    use RendersA2ui;

    public const NAME = 'show_workshop_cards';

    protected string $name = self::NAME;

    protected ?string $description = "Mostra all'utente una scheda interattiva per ogni workshop, con i posti rimasti e un modulo di prenotazione (ruolo in azienda e nome azienda). Usalo al posto di un elenco testuale quando presenti i workshop.";

    public function __construct(private A2uiSurfaceBuilder $surfaces, private WorkshopBookingService $bookings) {}

    public function __invoke(): string
    {
        return $this->a2ui($this->surfaces->workshopCards($this->bookings->workshops()));
    }
}
