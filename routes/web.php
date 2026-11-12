<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ClearAgendaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WorkshopBookingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', LoginController::class)->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', ScheduleController::class)->name('schedule');

    Route::delete('/agenda', ClearAgendaController::class)->name('agenda.clear');
    Route::resource('agenda', AgendaController::class)
        ->only(['store', 'destroy'])
        ->parameters(['agenda' => 'talk']);

    Route::post('/workshops/{workshop}/bookings', WorkshopBookingController::class)->name('workshops.bookings.store');

    Route::post('/agent', AgentController::class)->name('agent');

    Route::post('/logout', LogoutController::class)->name('logout');
});
