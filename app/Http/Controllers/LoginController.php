<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): RedirectResponse
    {
        if (! auth()->attempt($request->validated(), remember: true)) {
            throw ValidationException::withMessages(['email' => 'Credenziali non valide.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('schedule'));
    }
}
