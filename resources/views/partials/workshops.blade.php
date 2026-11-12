@foreach ($workshops as $workshop)
    @php($seatsLeft = $workshop->seatsLeft())
    <li class="card flex flex-col p-4">
        <p class="font-mono text-xs text-zinc-500">
            {{ $workshop->starts_at->format('H:i') }}–{{ $workshop->ends_at->format('H:i') }}
        </p>
        <p class="mt-1 font-semibold leading-snug">{{ $workshop->title }}</p>
        <p class="text-sm text-zinc-500">{{ $workshop->speaker->name }}</p>

        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-zinc-100">
            <div @class(['h-full rounded-full', 'bg-red-500' => $seatsLeft <= 3, 'bg-emerald-500' => $seatsLeft > 3])
                 style="width: {{ $workshop->capacity > 0 ? round(($workshop->capacity - $seatsLeft) / $workshop->capacity * 100) : 100 }}%"></div>
        </div>

        <div class="mt-3 flex items-center justify-between text-sm">
            <span @class(['font-medium text-red-600' => $seatsLeft <= 3, 'text-zinc-600' => $seatsLeft > 3])>Posti rimasti: {{ $seatsLeft }}</span>
            @if ($bookedWorkshopIds->contains($workshop->id))
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200 ring-inset">✓ Prenotato</span>
            @else
                <form method="POST" action="{{ route('workshops.bookings.store', $workshop) }}">
                    @csrf
                    <button @disabled($seatsLeft === 0) class="btn-primary">{{ $seatsLeft === 0 ? 'Esaurito' : 'Prenota' }}</button>
                </form>
            @endif
        </div>
    </li>
@endforeach
