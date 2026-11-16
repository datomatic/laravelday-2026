@php($rooms = $talks->pluck('room')->unique()->values())

@foreach ($rooms as $room)
    <h3 class="hidden px-1 font-mono text-xs font-semibold tracking-wide text-zinc-500 uppercase sm:block">{{ $room }}</h3>
@endforeach

{{-- One row per time slot: parallel talks side by side, a talk alone in its slot spans every room. --}}
@foreach ($talks->groupBy(fn ($talk) => $talk->starts_at->format('H:i')) as $slot)
    @if ($slot->count() === 1)
        @include('partials.talk-card', ['talk' => $slot->first(), 'plenary' => true])
    @else
        @foreach ($rooms as $room)
            @if ($talk = $slot->firstWhere('room', $room))
                @include('partials.talk-card', ['talk' => $talk, 'plenary' => false])
            @else
                <div class="hidden sm:block"></div>
            @endif
        @endforeach
    @endif
@endforeach
