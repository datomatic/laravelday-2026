<x-layouts.app>
    <div class="mx-auto max-w-3xl py-6">
        <h1 class="text-3xl font-bold tracking-tight">Apri la tua app agli agenti</h1>
        <p class="mt-2 text-zinc-500">La stessa pagina del programma, un passaggio alla volta: dalla versione classica alle interfacce generate dall'agente.</p>

        <ol class="mt-8 space-y-3">
            @foreach (App\Enums\DemoStage::cases() as $stage)
                @continue($stage === App\Enums\DemoStage::Admin && auth()->user()->cannot('access-admin'))
                <li>
                    <a href="{{ $stage->url() }}" class="card group flex items-center gap-4 p-5 transition hover:border-red-200 hover:shadow-md">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-zinc-900 font-mono text-sm font-semibold text-white transition group-hover:bg-red-600">{{ $stage->step() }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-baseline gap-x-3">
                                <span class="text-lg font-bold tracking-tight">{{ $stage->label() }}</span>
                                <span class="rounded-md bg-zinc-100 px-1.5 py-0.5 font-mono text-[11px] text-zinc-600">{{ $stage->protocols() }}</span>
                            </span>
                            <span class="mt-0.5 block text-sm text-zinc-500">{{ $stage->description() }}</span>
                        </span>
                        <span class="text-zinc-300 transition group-hover:translate-x-1 group-hover:text-red-600" aria-hidden="true">→</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </div>
</x-layouts.app>
