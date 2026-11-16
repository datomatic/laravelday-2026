<?php

namespace App\Http\Controllers;

use App\Services\AgendaService;
use App\Services\WorkshopBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Starts the demo over: empties the user's agenda and cancels their workshop bookings.
 */
class ResetDemoController extends Controller
{
    public function __invoke(Request $request, AgendaService $agenda, WorkshopBookingService $bookings): RedirectResponse
    {
        $agenda->clear($request->user());
        $bookings->cancelAll($request->user());

        return back()->with('status', 'Agenda e prenotazioni svuotate.');
    }
}
