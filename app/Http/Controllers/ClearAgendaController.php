<?php

namespace App\Http\Controllers;

use App\Services\AgendaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClearAgendaController extends Controller
{
    public function __invoke(Request $request, AgendaService $agenda): RedirectResponse
    {
        $agenda->clear($request->user());

        return back()->with('status', 'Agenda svuotata.');
    }
}
