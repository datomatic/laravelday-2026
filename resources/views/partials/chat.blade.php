{{-- Embedded: the chat is the page itself (admin dashboard), always in the wide two-column layout, with no window controls. --}}
@php
    $embedded ??= false;
    $embeddedClasses = 'card group flex h-[calc(100dvh-9rem)] flex-col overflow-hidden lg:grid lg:grid-cols-[minmax(0,1fr)_26rem] lg:grid-rows-[auto_minmax(0,1fr)_auto]';
    $floatingClasses = 'card group fixed right-4 bottom-4 z-40 flex h-[min(42rem,calc(100dvh-2rem))] w-[calc(100vw-2rem)] flex-col overflow-hidden shadow-2xl sm:w-[26rem] data-fullscreen:inset-4 data-fullscreen:z-50 data-fullscreen:h-auto data-fullscreen:w-auto! sm:data-fullscreen:inset-8 lg:data-fullscreen:grid lg:data-fullscreen:grid-cols-[minmax(0,1fr)_34rem] lg:data-fullscreen:grid-rows-[auto_minmax(0,1fr)_auto] data-minimized:h-auto data-minimized:w-80';
@endphp

@unless ($embedded)
    <div id="chat-backdrop" class="fixed inset-0 z-40 hidden bg-zinc-900/40 backdrop-blur-sm"></div>
@endunless

<section id="chat" {{ $embedded ? 'data-fullscreen' : 'data-minimized' }}
         class="{{ $embedded ? $embeddedClasses : $floatingClasses }}"
         data-protocol="{{ $stage->usesAgUi() ? 'ag-ui' : 'homemade' }}"
         data-agent-url="{{ $stage->usesAgUi() ? route('agent', $stage) : route('homemade-chat') }}"
         data-thread-id="user-{{ auth()->id() }}-{{ str()->uuid() }}"
         data-agenda-url="{{ route('agenda.store') }}"
         data-booking-url="{{ url('/workshops/__ID__/bookings') }}">
    <div class="flex items-center gap-3 lg:group-data-fullscreen:col-span-2 bg-gradient-to-r from-zinc-900 to-zinc-800 px-4 py-3 text-white">
        <span class="grid size-9 place-items-center rounded-full bg-red-600 text-base shadow-md shadow-red-900/40">✦</span>
        <div class="leading-tight">
            <h2 class="text-sm font-semibold">{{ $embedded ? 'Analista' : 'Assistente' }}</h2>
            <p class="font-mono text-[11px] text-zinc-400">{{ $stage->protocols() }}</p>
        </div>
        <span class="ml-auto flex items-center gap-1.5 text-xs text-zinc-300">
            <span class="size-2 animate-pulse rounded-full bg-emerald-400"></span> online
        </span>
        @unless ($embedded)
            <button id="chat-minimize" type="button" aria-pressed="true" aria-label="Ripristina la chat"
                    class="grid size-8 place-items-center rounded-lg text-zinc-300 transition hover:bg-white/10 hover:text-white">
                <svg class="size-4 group-data-minimized:hidden" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M4 10h12"/></svg>
                <svg class="hidden size-4 group-data-minimized:block" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5-5 5 5"/></svg>
            </button>
            <button id="chat-fullscreen" type="button" aria-pressed="false" aria-label="Apri la chat a tutto schermo"
                    class="grid size-8 place-items-center rounded-lg text-zinc-300 transition hover:bg-white/10 hover:text-white">
                <svg class="size-4 group-data-fullscreen:hidden" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3h5v5M8 17H3v-5M17 3l-6 6M3 17l6-6"/></svg>
                <svg class="hidden size-4 group-data-fullscreen:block" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 9h-5V4M4 11h5v5M11 9l6-6M9 11l-6 6"/></svg>
            </button>
        @else
            {{-- On the projector: the browser Fullscreen API, closed with Esc. --}}
            <button id="chat-expand" type="button" aria-pressed="false" aria-label="Schermo intero"
                    class="group/expand grid size-8 place-items-center rounded-lg text-zinc-300 transition hover:bg-white/10 hover:text-white">
                <svg class="size-4 group-aria-pressed/expand:hidden" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3h5v5M8 17H3v-5M17 3l-6 6M3 17l6-6"/></svg>
                <svg class="hidden size-4 group-aria-pressed/expand:block" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 9h-5V4M4 11h5v5M11 9l6-6M9 11l-6 6"/></svg>
            </button>
        @endunless
    </div>

    <div id="chat-messages" class="flex-1 space-y-3 overflow-y-auto scrollbar-none bg-white p-4 group-data-minimized:hidden text-sm group-data-fullscreen:px-[max(1rem,calc(50%-24rem))] lg:group-data-fullscreen:col-start-1 lg:group-data-fullscreen:row-start-2">
        <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-3 text-zinc-500">
            <p class="mb-2 font-medium text-zinc-700">Prova a chiedere:</p>
            <ul class="space-y-1">
                @foreach ($suggestions ?? ['Quali talk parlano di AI? Mostrameli.', 'Prenotami il workshop sugli agenti, sono CTO di Acme.'] as $suggestion)
                    <li>
                        <button type="button" data-suggestion="{{ $suggestion }}" class="text-left transition hover:text-red-600">“{{ $suggestion }}”</button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- In the full screen chat the log sits on the right, open and as tall as the conversation. --}}
    <details id="chat-log" @if ($embedded) open @endif class="group-data-minimized:hidden border-t border-zinc-200 bg-white px-4 py-2 text-xs text-zinc-500 lg:group-data-fullscreen:col-start-2 lg:group-data-fullscreen:row-span-2 lg:group-data-fullscreen:row-start-2 lg:group-data-fullscreen:border-t-0 lg:group-data-fullscreen:border-l lg:group-data-fullscreen:py-3">
        <summary class="cursor-pointer select-none font-medium hover:text-zinc-700">{{ $stage->usesAgUi() ? 'Eventi AG-UI' : 'Eventi SSE fatti in casa' }}</summary>
        <ol id="chat-events" class="mt-2 max-h-56 space-y-px overflow-y-auto scrollbar-none group-data-fullscreen:max-h-80 lg:group-data-fullscreen:h-[calc(100dvh-11rem)] lg:group-data-fullscreen:max-h-none rounded-lg bg-zinc-900 p-2 font-mono text-[11px] text-emerald-300"></ol>
    </details>

    <form id="chat-form" class="flex gap-2 group-data-minimized:hidden border-t border-zinc-200 bg-white p-3 group-data-fullscreen:px-[max(0.75rem,calc(50%-24rem))] lg:group-data-fullscreen:col-start-1 lg:group-data-fullscreen:row-start-3">
        <input id="chat-input" type="text" autocomplete="off" placeholder="Scrivi all'assistente…"
               class="flex-1 rounded-xl border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100 disabled:bg-zinc-100">
        <button class="btn-primary rounded-xl px-4">Invia</button>
    </form>
</section>
