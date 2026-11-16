<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apri la tua app agli agenti · LaravelDay 2026</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|jetbrains-mono:400,500,600" rel="stylesheet">
    {{-- The slides get their own icon (a red play badge), so their tab stands out from the demo app during the talk. --}}
    <link rel="icon" href="{{ asset('favicon-slides.ico') }}" sizes="48x48">
    <link rel="icon" type="image/png" href="{{ asset('images/laravelday-slides-icon.png') }}" sizes="180x180">
    <link rel="apple-touch-icon" href="{{ asset('images/laravelday-slides-icon.png') }}">
    @vite('resources/js/slides.js')
</head>
<body>
    <div class="slides-ambient" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="reveal">
        <div class="slides">
            @include('slides.sections.opening')
            @include('slides.sections.mcp')
            @include('slides.sections.ag-ui')
            @include('slides.sections.a2ui')
            @include('slides.sections.webmcp')
            @include('slides.sections.closing')
        </div>
    </div>

    <img src="{{ asset('images/laravelday-logo.svg') }}" alt="laravelday" class="deck-logo">

    {{-- Easter eggs: Taylor drives and flies by every now and then, over everything, slides and logo included. --}}
    <div class="slides-flyby" aria-hidden="true">
        <div class="supercar">
            <img src="{{ asset('images/slides/lambo.png') }}" alt="">
            <img src="{{ asset('images/slides/lambo-wheel-front.png') }}" alt="" class="supercar-wheel supercar-wheel-front">
            <img src="{{ asset('images/slides/lambo-wheel-rear.png') }}" alt="" class="supercar-wheel supercar-wheel-rear">
        </div>

        <div class="airplane">
            <img src="{{ asset('images/slides/airplane.png') }}" alt="">
        </div>
    </div>

    <template id="slide-logo">
        <img src="{{ asset('images/laravelday-logo.svg') }}" alt="laravelday" class="slide-logo">
    </template>
</body>
</html>
