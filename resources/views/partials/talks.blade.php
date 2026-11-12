@foreach ($talks as $talk)
    <li class="card group flex items-stretch gap-4 p-4 transition hover:border-red-200 hover:shadow-md" data-talk-id="{{ $talk->id }}">
        <div class="flex w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-zinc-50 font-mono text-xs text-zinc-500">
            <span class="text-sm font-semibold text-zinc-900">{{ $talk->starts_at->format('H:i') }}</span>
            <span>{{ $talk->ends_at->format('H:i') }}</span>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                <span class="rounded-full px-2 py-0.5 font-medium ring-1 ring-inset {{ $talk->track->badgeClasses() }}">{{ $talk->track->label() }}</span>
                <span>{{ $talk->room }}</span>
            </div>
            <p class="mt-1 font-semibold leading-snug text-zinc-900">{{ $talk->title }}</p>
            <p class="text-sm text-zinc-500">{{ $talk->speaker->name }}</p>
        </div>

        <div class="flex items-center">
            @if ($agendaTalks->contains($talk))
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200 ring-inset">✓ In agenda</span>
            @else
                <form method="POST" action="{{ route('agenda.store') }}">
                    @csrf
                    <input type="hidden" name="talk_id" value="{{ $talk->id }}">
                    <button class="btn-ghost">+ Agenda</button>
                </form>
            @endif
        </div>
    </li>
@endforeach
