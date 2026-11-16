<?php

namespace App\Http\Controllers;

use App\Enums\DemoStage;
use App\Services\AgendaService;
use App\Services\WorkshopBookingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __invoke(Request $request, DemoStage $stage, AgendaService $agenda, WorkshopBookingService $bookings): View
    {
        abort_if($stage === DemoStage::Admin, 404);

        return view('schedule', [
            'stage' => $stage,
            'talks' => $agenda->searchTalks(),
            'agendaTalks' => $agenda->talksFor($request->user()),
            'workshops' => $bookings->workshops(),
            'bookedWorkshopIds' => $request->user()->workshopBookings()->pluck('workshop_id'),
        ]);
    }
}
