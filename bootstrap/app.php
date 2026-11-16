<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use NeuronAI\Exceptions\InputTranslationException;
use NeuronAI\Exceptions\RunInFlightException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The AG-UI payload reaches the agent verbatim: an empty tool result must not become null.
        $middleware->trimStrings(except: [fn (Request $request) => $request->is('agent/*')]);
        $middleware->convertEmptyStringsToNull(except: [fn (Request $request) => $request->is('agent/*')]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'mcp/*', 'agent/*') || $request->expectsJson(),
        );

        // Neuron refuses a stale or malformed resume, and a new message while an approval is pending.
        $exceptions->render(fn (InputTranslationException $exception) => response()->json(['message' => $exception->getMessage()], 400));
        $exceptions->render(fn (RunInFlightException $exception) => response()->json(['message' => 'La conversazione è in attesa di una conferma.', 'status' => $exception->status->value], 409));
        $exceptions->dontReport([InputTranslationException::class, RunInFlightException::class]);
    })->create();
