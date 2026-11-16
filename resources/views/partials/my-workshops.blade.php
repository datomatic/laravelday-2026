@forelse ($workshops->whereIn('id', $bookedWorkshopIds) as $workshop)
    <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
        <div class="w-16 shrink-0 rounded-xl bg-zinc-100 py-1.5 text-center font-mono text-xs text-zinc-500">
            <span class="block font-semibold text-zinc-900">{{ $workshop->starts_at->locale('it')->translatedFormat('D j') }}</span>
            <span class="block">{{ $workshop->starts_at->format('H:i') }}</span>
            <span class="block text-zinc-400">{{ $workshop->ends_at->format('H:i') }}</span>
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate font-semibold leading-snug text-zinc-900">{{ $workshop->title }}</p>
            <p class="text-sm text-zinc-500">{{ $workshop->speaker->name }}</p>
        </div>
    </li>
@empty
    <li class="rounded-xl border border-dashed border-zinc-300 px-4 py-4 text-center text-sm text-zinc-500">Nessun workshop prenotato.</li>
@endforelse
