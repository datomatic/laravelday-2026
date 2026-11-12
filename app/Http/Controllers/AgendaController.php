<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgendaItemRequest;
use App\Models\Talk;
use App\Services\AgendaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function __construct(private AgendaService $agenda) {}

    public function store(StoreAgendaItemRequest $request): JsonResponse|RedirectResponse
    {
        $talk = Talk::find($request->validated('talk_id'));

        $this->agenda->add($request->user(), $talk);

        return $request->expectsJson()
            ? response()->json(['message' => "\"{$talk->title}\" aggiunto all'agenda."], 201)
            : back()->with('status', "\"{$talk->title}\" aggiunto all'agenda.");
    }

    public function destroy(Request $request, Talk $talk): RedirectResponse
    {
        $this->agenda->remove($request->user(), $talk);

        return back()->with('status', "\"{$talk->title}\" rimosso dall'agenda.");
    }
}
