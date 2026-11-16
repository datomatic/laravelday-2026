@php($inAgenda = $agendaTalks->contains($talk))
@php($isBlocked = ! $inAgenda && $agendaTalks->contains(fn ($chosen) => $chosen->overlaps($talk)))

<article @class([
    'card relative flex flex-col gap-3 p-4 transition hover:shadow-md',
    'sm:col-span-2' => $plenary,
    'ring-2 ring-emerald-300' => $inAgenda,
    'opacity-60 hover:opacity-100' => $isBlocked,
]) data-talk-id="{{ $talk->id }}">
    <div class="flex items-center justify-between gap-2">
        <p class="font-mono text-sm">
            <span class="font-semibold text-zinc-900">{{ $talk->starts_at->format('H:i') }}</span>
            <span class="text-zinc-400">– {{ $talk->ends_at->format('H:i') }}</span>
            <span @class(['ml-1 font-sans text-xs text-zinc-500', 'sm:hidden' => ! $plenary])>· {{ $talk->room }}</span>
        </p>
        <span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $talk->track->badgeClasses() }}">{{ $talk->track->label() }}</span>
    </div>

    <h3 class="text-base leading-snug font-bold tracking-tight text-zinc-900">{{ $talk->title }}</h3>

    <div class="mt-auto flex items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-2 text-sm">
            @if ($talk->speaker->photo_url)
                <img src="{{ $talk->speaker->photo_url }}" alt="" class="size-7 shrink-0 rounded-full">
            @endif
            <p class="min-w-0 truncate">
                <span class="font-medium text-zinc-800">{{ $talk->speaker->name }}</span>
                <span class="text-zinc-400">· {{ $talk->speaker->company }}</span>
            </p>
        </div>

        @if ($inAgenda)
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">✓ In agenda</span>
        @elseif (! $isBlocked)
            <form method="POST" action="{{ route('agenda.store') }}" class="shrink-0">
                @csrf
                <input type="hidden" name="talk_id" value="{{ $talk->id }}">
                <button class="btn-ghost px-2.5 py-1 text-xs">+ Agenda</button>
            </form>
        @endif
    </div>
</article>
