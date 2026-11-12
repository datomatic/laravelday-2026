<?php

namespace App\Mcp\Resources;

use App\Models\Talk;
use App\Services\AgendaService;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Uri('conference://schedule')]
#[MimeType('application/json')]
#[Description('Il programma completo della conferenza: talk, speaker, sale e orari.')]
class ScheduleResource extends Resource
{
    public function handle(AgendaService $agenda): Response
    {
        return Response::json($agenda->searchTalks()->map(fn (Talk $talk) => $talk->toSummary())->all());
    }
}
