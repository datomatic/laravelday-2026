<?php

namespace App\Mcp\Tools;

use App\Services\AgendaService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('clear_agenda')]
#[Description("Svuota l'agenda personale dell'utente rimuovendo tutti i talk. Azione distruttiva.")]
#[IsDestructive]
class ClearAgendaTool extends Tool
{
    public function handle(Request $request, AgendaService $agenda): Response
    {
        $removed = $agenda->clear($request->user());

        return Response::text("Agenda svuotata: {$removed} talk rimossi.");
    }
}
