<?php

namespace App\Mcp\Tools;

use App\Enums\CompanyRole;
use App\Models\Workshop;
use App\Services\WorkshopBookingService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('book_workshop')]
#[Description("Prenota un posto per l'utente a un workshop. I posti sono limitati. Usa list_workshops per trovare l'id. Servono il ruolo dell'utente in azienda e il nome dell'azienda: se non li conosci chiedili, non inventarli.")]
class BookWorkshopTool extends Tool
{
    public function handle(Request $request, WorkshopBookingService $bookings): Response
    {
        $validated = $request->validate([
            'workshop_id' => ['required', 'integer', 'exists:workshops,id'],
            'company_role' => ['required', Rule::enum(CompanyRole::class)],
            'company_name' => ['required', 'string', 'max:100'],
        ]);

        $workshop = Workshop::find($validated['workshop_id']);

        try {
            $bookings->book($request->user(), $workshop, CompanyRole::from($validated['company_role']), $validated['company_name']);
        } catch (ValidationException $exception) {
            return Response::error($exception->getMessage());
        }

        return Response::text("Posto prenotato per \"{$workshop->title}\" ({$workshop->starts_at->format('H:i')}).");
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'workshop_id' => $schema->integer()->description('Id del workshop da prenotare.')->required(),
            'company_role' => $schema->string()
                ->enum(CompanyRole::class)
                ->description("Ruolo dell'utente in azienda.")
                ->required(),
            'company_name' => $schema->string()->description("Nome dell'azienda dell'utente.")->required(),
        ];
    }
}
