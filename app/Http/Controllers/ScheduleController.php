<?php

namespace App\Http\Controllers;

use App\Services\AgendaService;
use App\Services\WorkshopBookingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __invoke(Request $request, AgendaService $agenda, WorkshopBookingService $bookings): View
    {
        return view('schedule', [
            'talks' => $agenda->searchTalks(),
            'agendaTalks' => $agenda->talksFor($request->user()),
            'workshops' => $bookings->workshops(),
            'bookedWorkshopIds' => $request->user()->workshopBookings()->pluck('workshop_id'),
        ]);
    }
}
