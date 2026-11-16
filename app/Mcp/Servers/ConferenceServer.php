<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\PlanMyDayPrompt;
use App\Mcp\Resources\ScheduleResource;
use App\Mcp\Resources\WorkshopsApp;
use App\Mcp\Tools\AddToAgendaTool;
use App\Mcp\Tools\BookWorkshopTool;
use App\Mcp\Tools\ClearAgendaTool;
use App\Mcp\Tools\ListWorkshopsTool;
use App\Mcp\Tools\SearchTalksTool;
use App\Mcp\Tools\ShowAgendaTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Conference Server')]
#[Version('1.0.0')]
#[Instructions("Programma di una conferenza Laravel. Cerca i talk, gestisci l'agenda personale dell'utente autenticato e prenota i workshop. Gli orari sono nel fuso della conferenza.")]
class ConferenceServer extends Server
{
    protected array $tools = [
        SearchTalksTool::class,
        ShowAgendaTool::class,
        AddToAgendaTool::class,
        ClearAgendaTool::class,
        ListWorkshopsTool::class,
        BookWorkshopTool::class,
    ];

    protected array $resources = [
        ScheduleResource::class,
        WorkshopsApp::class,
    ];

    protected array $prompts = [
        PlanMyDayPrompt::class,
    ];
}
