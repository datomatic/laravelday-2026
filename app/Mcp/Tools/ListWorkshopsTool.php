<?php

namespace App\Mcp\Tools;

use App\Models\Workshop;
use App\Services\WorkshopBookingService;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_workshops')]
#[Description('Elenca i workshop della conferenza con orari e posti rimasti.')]
#[IsReadOnly]
class ListWorkshopsTool extends Tool
{
    public function handle(WorkshopBookingService $bookings): ResponseFactory
    {
        return Response::structured([
            'workshops' => $bookings->workshops()->map(fn (Workshop $workshop) => $workshop->toSummary())->all(),
        ]);
    }
}
