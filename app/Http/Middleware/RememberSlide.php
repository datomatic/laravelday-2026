<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A demo page opened from the slides remembers the slide, so the "back to the slides" link returns to it.
 */
class RememberSlide
{
    public function handle(Request $request, Closure $next): Response
    {
        if (preg_match('~^#/\d+(/\d+)*$~', (string) $request->query('slide'))) {
            session(['slides.return' => $request->query('slide')]);
        }

        return $next($request);
    }
}
