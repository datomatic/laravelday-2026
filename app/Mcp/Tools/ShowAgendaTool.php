<?php

namespace App\Mcp\Tools;

use App\Models\Talk;
use App\Services\AgendaService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('show_agenda')]
#[Description("Mostra l'agenda personale dell'utente: i talk che ha deciso di seguire.")]
#[IsReadOnly]
class ShowAgendaTool extends Tool
{
    public function handle(Request $request, AgendaService $agenda): ResponseFactory
    {
        return Response::structured([
            'talks' => $agenda->talksFor($request->user())->map(fn (Talk $talk) => $talk->toSummary())->all(),
        ]);
    }
}
