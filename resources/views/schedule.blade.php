<x-layouts.app back-to-slides>
    <div class="mb-6 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-zinc-500 transition hover:text-zinc-900">
            ← <span class="font-mono text-xs">{{ $stage->step() }}</span> {{ $stage->label() }} <span class="text-zinc-400">· {{ $stage->protocols() }}</span>
        </a>

        <button type="button" commandfor="reset-demo" command="show-modal" class="text-xs font-medium text-zinc-500 transition hover:text-red-600">Svuota agenda e prenotazioni</button>

        <dialog id="reset-demo" closedby="any" aria-labelledby="reset-demo-title"
                class="m-auto w-[calc(100vw-2rem)] max-w-sm rounded-2xl bg-white p-0 shadow-2xl transition duration-200 backdrop:bg-zinc-900/40 backdrop:backdrop-blur-sm starting:open:translate-y-2 starting:open:scale-95 starting:open:opacity-0">
            <form method="POST" action="{{ route('demo.reset') }}" class="grid gap-5 p-6">
                @csrf
                @method('DELETE')
                <div class="flex items-start gap-4">
                    <div class="grid size-11 shrink-0 place-items-center rounded-full bg-red-100 text-xl text-red-600" aria-hidden="true">↺</div>
                    <div>
                        <h3 id="reset-demo-title" class="font-semibold text-zinc-900">Ripartire da zero?</h3>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-600">Tolgo tutti i talk dalla tua agenda e annullo le tue prenotazioni ai workshop. Gli altri partecipanti non vengono toccati.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" commandfor="reset-demo" command="close" class="btn-ghost" autofocus>Annulla</button>
                    <button class="btn-primary">Svuota tutto</button>
                </div>
            </form>
        </dialog>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="space-y-8 lg:col-span-2">
            <div>
                <h2 class="section-title mb-3">Talk <span class="font-normal text-zinc-400">{{ $talks->first()?->starts_at->locale('it')->translatedFormat('l j F') }}</span></h2>
                <div id="talks" class="relative isolate grid gap-x-6 gap-y-3 sm:grid-cols-2 sm:before:absolute sm:before:inset-y-0 sm:before:left-1/2 sm:before:-z-10 sm:before:border-l sm:before:border-dashed sm:before:border-zinc-300" data-live-region data-agenda-url="{{ route('agenda.store') }}">
                    @include('partials.talks')
                </div>
            </div>

            <div>
                <h2 class="section-title mb-3">Workshop <span class="font-normal text-zinc-400">{{ $workshops->first()?->starts_at->locale('it')->translatedFormat('l j F') }}</span></h2>
                <ul id="workshops" class="grid gap-3 sm:grid-cols-2" data-live-region>
                    @include('partials.workshops')
                </ul>
            </div>
        </section>

        <aside class="space-y-8 lg:sticky lg:top-24 lg:self-start">
            <div>
                <h2 class="section-title mb-3">I miei workshop</h2>
                <ul id="my-workshops" class="card divide-y divide-zinc-100 p-4" data-live-region>
                    @include('partials.my-workshops')
                </ul>
            </div>

            <div>
                <h2 class="section-title mb-3">La mia agenda</h2>

                <ul id="agenda" class="card p-5" data-live-region>
                    @include('partials.agenda')
                </ul>
            </div>
        </aside>

        @if ($stage->agent())
            @include('partials.chat', $stage === App\Enums\DemoStage::Generative ? [
                'suggestions' => [
                    'Quali talk parlano di AI? Mostrameli.',
                    'Prenotami il workshop sugli agenti, sono CTO di Acme.',
                    'Come vanno le iscrizioni ai workshop?',
                ],
            ] : [])
        @endif
    </div>
</x-layouts.app>
