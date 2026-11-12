@forelse ($agendaTalks as $talk)
    <li class="group relative flex items-start justify-between gap-2 py-3 pl-5">
        <span class="absolute top-4 left-0 size-2.5 rounded-full border-2 border-red-500 bg-white"></span>
        <div class="min-w-0">
            <p class="font-mono text-xs text-zinc-500">{{ $talk->starts_at->format('H:i') }} · {{ $talk->room }}</p>
            <p class="text-sm font-medium leading-snug">{{ $talk->title }}</p>
        </div>
        <form method="POST" action="{{ route('agenda.destroy', $talk) }}">
            @csrf
            @method('DELETE')
            <button class="grid size-6 place-items-center rounded-md text-xs text-zinc-400 opacity-60 transition group-hover:opacity-100 hover:bg-red-50 hover:text-red-600" aria-label="Rimuovi {{ $talk->title }}">✕</button>
        </form>
    </li>
@empty
    <li class="py-8 text-center text-sm text-zinc-500">
        <span class="mb-2 block text-2xl" aria-hidden="true">🗓️</span>
        Nessun talk in agenda.
    </li>
@endforelse
