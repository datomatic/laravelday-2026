<?php

namespace App\Ai\Tools;

use App\Services\A2uiSurfaceBuilder;
use App\Services\WorkshopBookingService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ShowWorkshopCards implements Tool
{
    public const NAME = 'show_workshop_cards';

    public function __construct(private A2uiSurfaceBuilder $surfaces, private WorkshopBookingService $bookings) {}

    public function name(): string
    {
        return self::NAME;
    }

    public function description(): Stringable|string
    {
        return "Mostra all'utente una scheda interattiva per ogni workshop, con i posti rimasti e un modulo di prenotazione (ruolo in azienda e nome azienda). Usalo al posto di un elenco testuale quando presenti i workshop.";
    }

    public function handle(Request $request): Stringable|string
    {
        return json_encode(['a2ui' => $this->surfaces->workshopCards($this->bookings->workshops())], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
