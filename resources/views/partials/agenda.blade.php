@forelse ($agendaTalks as $talk)
    <li class="group grid grid-cols-[3rem_minmax(0,1fr)] gap-3">
        <div class="pt-1 text-right font-mono text-xs text-zinc-400">
            <span class="block text-sm font-semibold text-zinc-900">{{ $talk->starts_at->format('H:i') }}</span>
            {{ $talk->ends_at->format('H:i') }}
        </div>

        <div class="relative pb-3 pl-5 before:absolute before:inset-y-0 before:left-0 before:w-px before:bg-zinc-200 group-first:before:top-3 group-last:pb-0 group-last:before:bottom-auto group-last:before:h-3">
            <span class="absolute top-3 -left-[5px] size-[11px] rounded-full border-2 border-red-500 bg-white ring-4 ring-white"></span>

            <div class="-mt-1 flex items-start gap-3 rounded-xl p-2 transition hover:bg-zinc-100">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                        <span class="rounded-full px-2 py-0.5 font-medium ring-1 ring-inset {{ $talk->track->badgeClasses() }}">{{ $talk->track->label() }}</span>
                        <span>{{ $talk->room }}</span>
                    </div>
                    <p class="mt-1 font-semibold leading-snug text-zinc-900">{{ $talk->title }}</p>
                    <p class="text-sm text-zinc-500">{{ $talk->speaker->name }}</p>
                </div>

                <form method="POST" action="{{ route('agenda.destroy', $talk) }}">
                    @csrf
                    @method('DELETE')
                    <button class="grid size-7 place-items-center rounded-lg text-xs text-zinc-400 transition hover:bg-red-100 hover:text-red-600" aria-label="Rimuovi {{ $talk->title }}">✕</button>
                </form>
            </div>
        </div>
    </li>
@empty
    <li class="flex flex-col items-center rounded-xl border border-dashed border-zinc-300 px-6 py-10 text-center">
        <span class="mb-3 grid size-12 place-items-center rounded-full bg-red-100 text-xl" aria-hidden="true">🗓️</span>
        <p class="font-medium text-zinc-900">Nessun talk in agenda</p>
        <p class="mt-1 max-w-xs text-sm text-zinc-500">Aggiungi i talk con “+ Agenda” oppure chiedi all'assistente di farlo per te.</p>
    </li>
@endforelse
