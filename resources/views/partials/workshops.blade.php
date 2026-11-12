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
            @elseif ($seatsLeft === 0)
                <span class="text-xs font-medium text-zinc-400">Esaurito</span>
            @endif
        </div>

        @if (! $bookedWorkshopIds->contains($workshop->id) && $seatsLeft > 0)
            <form method="POST" action="{{ route('workshops.bookings.store', $workshop) }}" class="mt-3 grid gap-2 border-t border-zinc-100 pt-3">
                @csrf
                <select name="company_role" required aria-label="Ruolo in azienda"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100">
                    <option value="" disabled @selected(! old('company_role'))>Ruolo in azienda…</option>
                    @foreach (App\Enums\CompanyRole::cases() as $role)
                        <option value="{{ $role->value }}" @selected(old('company_role') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                <input name="company_name" required maxlength="100" value="{{ old('company_name') }}" placeholder="Nome azienda" aria-label="Nome azienda"
                       class="w-full rounded-lg border border-zinc-300 px-2.5 py-1.5 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100">
                <button class="btn-primary justify-center">Prenota</button>
            </form>
        @endif
    </li>
@endforeach
