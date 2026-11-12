<?php

namespace App\Ai\Tools;

use App\Models\Workshop;
use App\Services\A2uiSurfaceBuilder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ShowBookingForm implements Tool
{
    public const NAME = 'show_booking_form';

    public function __construct(private A2uiSurfaceBuilder $surfaces) {}

    public function name(): string
    {
        return self::NAME;
    }

    public function description(): Stringable|string
    {
        return "Mostra all'utente il modulo di prenotazione di un workshop, dove sceglie il ruolo in azienda e scrive il nome dell'azienda. La prenotazione parte quando l'utente conferma il modulo.";
    }

    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'workshop_id' => ['required', 'integer', 'exists:workshops,id'],
        ]);

        return json_encode(['a2ui' => $this->surfaces->bookingFormCard(Workshop::find($validated['workshop_id']))], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'workshop_id' => $schema->integer()->description('Id del workshop, preso da list_workshops.')->required(),
        ];
    }
}
