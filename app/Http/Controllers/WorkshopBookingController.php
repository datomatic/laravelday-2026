<?php

namespace App\Http\Controllers;

use App\Enums\CompanyRole;
use App\Http\Requests\StoreWorkshopBookingRequest;
use App\Models\Workshop;
use App\Services\WorkshopBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class WorkshopBookingController extends Controller
{
    public function __invoke(StoreWorkshopBookingRequest $request, Workshop $workshop, WorkshopBookingService $bookings): JsonResponse|RedirectResponse
    {
        $bookings->book(
            $request->user(),
            $workshop,
            $request->enum('company_role', CompanyRole::class),
            $request->validated('company_name'),
        );

        return $request->expectsJson()
            ? response()->json(['message' => "Posto prenotato per \"{$workshop->title}\"."], 201)
            : back()->with('status', "Posto prenotato per \"{$workshop->title}\".");
    }
}
