<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Services\WorkshopBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkshopBookingController extends Controller
{
    public function __invoke(Request $request, Workshop $workshop, WorkshopBookingService $bookings): JsonResponse|RedirectResponse
    {
        $bookings->book($request->user(), $workshop);

        return $request->expectsJson()
            ? response()->json(['message' => "Posto prenotato per \"{$workshop->title}\"."], 201)
            : back()->with('status', "Posto prenotato per \"{$workshop->title}\".");
    }
}
