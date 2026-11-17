@props(['backToSlides' => false])

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>laravelday · Programma</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|jetbrains-mono:400,500,600" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" type="image/png" href="{{ asset('images/laravelday-icon.png') }}" sizes="180x180">
    <link rel="apple-touch-icon" href="{{ asset('images/laravelday-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-100 font-sans text-zinc-900 antialiased">
    <header class="sticky top-0 z-20 border-b border-zinc-200/80 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-[90rem] items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/laravelday-logo.svg') }}" alt="laravelday" class="h-9 w-auto">
                <span class="hidden border-l border-zinc-200 pl-3 text-xs text-zinc-500 sm:block">Programma della conferenza</span>
            </a>
            <div class="flex items-center gap-4">
                @if ($backToSlides)
                    <a href="{{ route('slides').session('slides.return', '') }}" class="btn-ghost inline-flex items-center gap-1.5 text-sm">← Torna alle slide</a>
                @else
                    <a href="{{ route('slides') }}" class="btn-ghost text-sm">Slide del talk</a>
                @endif
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="flex items-center gap-3 text-sm">
                        @csrf
                        <span class="hidden items-center gap-2 text-zinc-600 sm:flex">
                            <span class="grid size-8 place-items-center rounded-full bg-zinc-900 text-xs font-semibold text-white">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                            {{ auth()->user()->name }}
                        </span>
                        <button class="btn-ghost">Esci</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-[90rem] px-4 py-6 sm:px-6">
        @session('status')
            <p class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-100 px-4 py-3 text-sm text-emerald-800">
                <span aria-hidden="true">✓</span> {{ $value }}
            </p>
        @endsession

        @if ($errors->any())
            <p class="mb-4 flex items-center gap-2 rounded-xl border border-red-200 bg-red-100 px-4 py-3 text-sm text-red-800">
                <span aria-hidden="true">!</span> {{ $errors->first() }}
            </p>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
