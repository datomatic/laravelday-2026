<x-layouts.app>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] xl:grid-cols-[minmax(0,1fr)_20rem_26rem]">
        <section class="space-y-8">
            <div>
                <h2 class="section-title mb-3"><span class="size-1.5 rounded-full bg-red-500"></span> Talk</h2>
                <ul id="talks" class="space-y-3" data-live-region>
                    @include('partials.talks')
                </ul>
            </div>

            <div>
                <h2 class="section-title mb-3"><span class="size-1.5 rounded-full bg-red-500"></span> Workshop</h2>
                <ul id="workshops" class="grid gap-3 sm:grid-cols-2" data-live-region>
                    @include('partials.workshops')
                </ul>
            </div>
        </section>

        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="card p-4">
                <div class="mb-1 flex items-center justify-between">
                    <h2 class="section-title"><span class="size-1.5 rounded-full bg-red-500"></span> La mia agenda</h2>
                    <form method="POST" action="{{ route('agenda.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-xs text-zinc-400 transition hover:text-red-600">Svuota</button>
                    </form>
                </div>
                <ul id="agenda" class="divide-y divide-zinc-100 border-l border-dashed border-zinc-200 pl-0 [&>li]:-ml-[5px]" data-live-region>
                    @include('partials.agenda')
                </ul>
            </div>
        </aside>

        <div id="chat-backdrop" class="fixed inset-0 z-40 hidden bg-zinc-900/40 backdrop-blur-sm"></div>

        <section id="chat" class="card group flex h-[calc(100vh-8rem)] flex-col overflow-hidden lg:col-span-2 xl:sticky xl:top-24 xl:col-span-1 data-fullscreen:fixed data-fullscreen:inset-4 data-fullscreen:z-50 data-fullscreen:h-auto data-fullscreen:shadow-2xl sm:data-fullscreen:inset-8"
                 data-agent-url="{{ route('agent') }}"
                 data-agenda-url="{{ route('agenda.store') }}"
                 data-booking-url="{{ url('/workshops/__ID__/bookings') }}">
            <div class="flex items-center gap-3 bg-gradient-to-r from-zinc-900 to-zinc-800 px-4 py-3 text-white">
                <span class="grid size-9 place-items-center rounded-full bg-red-600 text-base shadow-md shadow-red-900/40">✦</span>
                <div class="leading-tight">
                    <h2 class="text-sm font-semibold">Assistente</h2>
                    <p class="font-mono text-[11px] text-zinc-400">AG-UI + A2UI</p>
                </div>
                <span class="ml-auto flex items-center gap-1.5 text-xs text-zinc-300">
                    <span class="size-2 animate-pulse rounded-full bg-emerald-400"></span> online
                </span>
                <button id="chat-fullscreen" type="button" aria-pressed="false" aria-label="Apri la chat a tutto schermo"
                        class="grid size-8 place-items-center rounded-lg text-zinc-300 transition hover:bg-white/10 hover:text-white">
                    <svg class="size-4 group-data-fullscreen:hidden" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3h5v5M8 17H3v-5M17 3l-6 6M3 17l6-6"/></svg>
                    <svg class="hidden size-4 group-data-fullscreen:block" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 9h-5V4M4 11h5v5M11 9l6-6M9 11l-6 6"/></svg>
                </button>
            </div>

            <div id="chat-messages" class="flex-1 space-y-3 overflow-y-auto bg-zinc-50/60 p-4 text-sm group-data-fullscreen:px-[max(1rem,calc(50%-24rem))]">
                <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-3 text-zinc-500">
                    <p class="mb-2 font-medium text-zinc-700">Prova a chiedere:</p>
                    <ul class="space-y-1">
                        <li>“Quali talk parlano di AI? Mostrameli.”</li>
                        <li>“Prenotami il workshop sugli agenti.”</li>
                    </ul>
                </div>
            </div>

            <details class="border-t border-zinc-200 bg-white px-4 py-2 text-xs text-zinc-500">
                <summary class="cursor-pointer select-none font-medium hover:text-zinc-700">Eventi AG-UI</summary>
                <ol id="chat-events" class="mt-2 max-h-32 space-y-0.5 overflow-y-auto rounded-lg bg-zinc-900 p-2 font-mono text-[11px] text-emerald-300"></ol>
            </details>

            <form id="chat-form" class="flex gap-2 border-t border-zinc-200 bg-white p-3 group-data-fullscreen:px-[max(0.75rem,calc(50%-24rem))]">
                <input id="chat-input" type="text" autocomplete="off" placeholder="Scrivi all'assistente…"
                       class="flex-1 rounded-xl border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100 disabled:bg-zinc-50">
                <button class="btn-primary rounded-xl px-4">Invia</button>
            </form>
        </section>
    </div>
</x-layouts.app>
