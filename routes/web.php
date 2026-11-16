<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\HomemadeChatController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ResetDemoController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WorkshopBookingController;
use App\Http\Middleware\RememberSlide;
use Illuminate\Support\Facades\Route;

Route::view('/slides', 'slides.index')->name('slides');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', LoginController::class)->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::view('/', 'home')->name('home');
    Route::get('/programma/{stage}', ScheduleController::class)->middleware(RememberSlide::class)->name('schedule');
    Route::view('/admin', 'admin')->middleware([RememberSlide::class, 'can:access-admin'])->name('admin');

    Route::delete('/demo', ResetDemoController::class)->name('demo.reset');
    Route::resource('agenda', AgendaController::class)
        ->only(['store', 'destroy'])
        ->parameters(['agenda' => 'talk']);

    Route::post('/workshops/{workshop}/bookings', WorkshopBookingController::class)->name('workshops.bookings.store');

    // Every message is a paid model run: throttle the chats too.
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/agent/{stage}', AgentController::class)->name('agent');
        Route::post('/chat-fatta-in-casa', HomemadeChatController::class)->name('homemade-chat');
    });

    Route::post('/logout', LogoutController::class)->name('logout');
});
