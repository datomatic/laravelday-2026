@foreach ($workshops as $workshop)
    @php($seatsLeft = $workshop->seatsLeft())
    @php($isBooked = $bookedWorkshopIds->contains($workshop->id))
    <li @class(['card flex flex-col gap-4 p-5 transition hover:shadow-md', 'ring-2 ring-emerald-200' => $isBooked])>
        <div class="flex items-center justify-between gap-2">
            <span class="rounded-lg bg-zinc-100 px-2 py-1 font-mono text-xs font-medium text-zinc-700">
                {{ $workshop->starts_at->format('H:i') }}–{{ $workshop->ends_at->format('H:i') }}
            </span>
            @if ($isBooked)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200 ring-inset">✓ Prenotato</span>
            @elseif ($seatsLeft === 0)
                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500">Esaurito</span>
            @elseif ($seatsLeft <= 3)
                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">Ultimi posti</span>
            @endif
        </div>

        <div>
            <h3 class="text-lg font-bold leading-snug tracking-tight text-zinc-900">{{ $workshop->title }}</h3>
            <p class="mt-1 line-clamp-2 text-sm text-zinc-500">{{ $workshop->description }}</p>
        </div>

        <div class="flex items-center gap-3">
            @if ($workshop->speaker->photo_url)
                <img src="{{ $workshop->speaker->photo_url }}" alt="" class="size-9 rounded-full">
            @endif
            <div class="min-w-0 text-sm leading-tight">
                <p class="font-medium text-zinc-900">{{ $workshop->speaker->name }}</p>
                <p class="text-zinc-500">{{ $workshop->speaker->company }}</p>
            </div>
        </div>

        <div class="mt-auto border-t border-zinc-100 pt-4">
            <div class="flex items-baseline justify-between text-xs">
                <span @class(['font-semibold text-red-600' => $seatsLeft <= 3, 'font-medium text-zinc-700' => $seatsLeft > 3])>Posti rimasti: {{ $seatsLeft }}</span>
                <span class="font-mono text-zinc-400">{{ $workshop->capacity - $seatsLeft }}/{{ $workshop->capacity }}</span>
            </div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-100">
                <div @class(['h-full rounded-full', 'bg-red-500' => $seatsLeft <= 3, 'bg-emerald-500' => $seatsLeft > 3])
                     style="width: {{ $workshop->capacity > 0 ? round(($workshop->capacity - $seatsLeft) / $workshop->capacity * 100) : 100 }}%"></div>
            </div>

            @if (! $isBooked && $seatsLeft > 0)
                <button type="button" commandfor="booking-{{ $workshop->id }}" command="show-modal" class="btn-primary mt-4 w-full justify-center">Prenota un posto</button>

                <dialog id="booking-{{ $workshop->id }}" closedby="any"
                        class="m-auto w-[calc(100vw-2rem)] max-w-sm rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-zinc-900/40 backdrop:backdrop-blur-sm">
                    <form method="POST" action="{{ route('workshops.bookings.store', $workshop) }}" class="grid gap-3 p-5">
                        @csrf
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-mono text-xs text-zinc-500">{{ $workshop->starts_at->format('H:i') }}–{{ $workshop->ends_at->format('H:i') }}</p>
                                <h3 class="mt-1 font-semibold leading-snug">Prenota: {{ $workshop->title }}</h3>
                            </div>
                            <button type="button" commandfor="booking-{{ $workshop->id }}" command="close" aria-label="Chiudi"
                                    class="grid size-8 shrink-0 place-items-center rounded-lg text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700">✕</button>
                        </div>
                        <select name="company_role" required aria-label="Ruolo in azienda"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-2 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100">
                            <option value="" disabled @selected(! old('company_role'))>Ruolo in azienda…</option>
                            @foreach (App\Enums\CompanyRole::cases() as $role)
                                <option value="{{ $role->value }}" @selected(old('company_role') === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        <input name="company_name" required maxlength="100" value="{{ old('company_name') }}" placeholder="Nome azienda" aria-label="Nome azienda"
                               class="w-full rounded-lg border border-zinc-300 px-2.5 py-2 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100">
                        <button class="btn-primary justify-center">Conferma prenotazione</button>
                    </form>
                </dialog>
            @endif
        </div>
    </li>
@endforeach
