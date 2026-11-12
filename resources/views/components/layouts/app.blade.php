<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-100 font-sans text-zinc-900 antialiased">
    <header class="sticky top-0 z-20 border-b border-zinc-200/80 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-[90rem] items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ route('schedule') }}" class="flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-red-500 to-red-700 font-bold text-white shadow-md shadow-red-600/30">L</span>
                <span class="leading-tight">
                    <span class="block text-lg font-bold tracking-tight"><span class="text-red-600">Laravel</span>Day</span>
                    <span class="block text-xs text-zinc-500">Programma della conferenza</span>
                </span>
            </a>
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
    </header>

    <main class="mx-auto max-w-[90rem] px-4 py-6 sm:px-6">
        @session('status')
            <p class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <span aria-hidden="true">✓</span> {{ $value }}
            </p>
        @endsession

        @if ($errors->any())
            <p class="mb-4 flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <span aria-hidden="true">!</span> {{ $errors->first() }}
            </p>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
