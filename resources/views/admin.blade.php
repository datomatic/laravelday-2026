@php($stage = App\Enums\DemoStage::Admin)

<x-layouts.app>
    <div class="mb-6 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-zinc-500 transition hover:text-zinc-900">
            ← <span class="font-mono text-xs">{{ $stage->step() }}</span> {{ $stage->label() }} <span class="text-zinc-400">· {{ $stage->protocols() }}</span>
        </a>

        <a href="{{ route('slides').session('slides.return', '') }}" class="btn-ghost inline-flex items-center gap-1.5 text-sm">← Torna alle slide</a>
    </div>

    @include('partials.chat', [
        'embedded' => true,
        'suggestions' => [
            'Fammi una dashboard delle iscrizioni ai workshop.',
            'Quali talk sono più seguiti? Dividili per track.',
            'Chi si iscrive ai workshop: ruoli e aziende.',
            "Com'è andata l'attività giorno per giorno?",
        ],
    ])
</x-layouts.app>
