<section data-background-color="#18181b" class="divider">
    <div class="flex h-full flex-col justify-between">
        <p class="eyebrow">LaravelDay 2026 · Verona · 18 novembre</p>
        <div class="flex flex-col gap-10">
            <h1 class="max-w-[1500px]">Apri la tua app agli agenti</h1>
            <p class="font-mono text-[44px] text-red-300">MCP · AG-UI · A2UI</p>
        </div>
        <p class="text-[32px] text-zinc-300">Alberto Peripolli · Datomatic</p>
    </div>
    <aside class="notes">Benvenuti. Le domande alla fine.</aside>
</section>

<section data-flyby="car">
    <div class="flex h-full items-center justify-between gap-24">
        <div class="flex flex-1 flex-col gap-10">
            <p class="eyebrow">Chi sono</p>
            <div class="flex flex-col gap-5">
                <h2>Alberto Peripolli</h2>
                <p class="text-[36px] text-zinc-600">Sviluppatore senior freelance<br>Tecrail · Datomatic · Rima · Tecnobit</p>
            </div>
            <ul class="bio-list text-[34px]">
                <li>Admin/Membro nelle community di Laravel Italia</li>
                <li>Open source: <a href="https://github.com/datomatic" target="_blank" class="font-mono text-[30px]">github.com/datomatic</a></li>
                <li>Giocatore di hockey e tennis + papà</li>
            </ul>
            <p class="font-mono text-[30px] text-red-600"><a href="https://albertoperipolli.com" target="_blank">albertoperipolli.com</a> · <a href="https://tecrail.com" target="_blank">tecrail.com</a></p>
        </div>
        <div class="portrait">
            <img src="{{ asset('images/slides/alberto.jpg') }}" alt="Alberto Peripolli">
        </div>
    </div>
    <aside class="notes">Presentazione breve, massimo trenta secondi.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-12">
        <p class="eyebrow">Oggi vedremo</p>
        <h2 class="max-w-[1600px] text-[96px]!">Come modernizzare un'app Laravel standard nell'era dell'IA agentica</h2>
        <p class="muted max-w-[1500px] text-[40px]">Partiamo da un'app come tante, con una porta sola: l'interfaccia web. La apriamo agli agenti un passo alla volta, senza riscrivere la logica che c'è già.</p>
    </div>
    <aside class="notes">Fino a ieri le nostre app avevano una porta sola: una persona, un browser, form e bottoni. Abbiamo progettato tutto attorno a questo. Oggi le apriamo agli agenti. La mappa del talk: cinque passaggi della stessa demo, poi un accenno a WebMCP. I primi quattro aggiungono un protocollo alla volta; l'ultimo lascia al modello anche la scelta dell'interfaccia.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-14">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">La demo</p>
            <h2>La nostra app: il programma di una conferenza</h2>
        </div>
        <div class="grid grid-cols-3 gap-10">
            <div class="card flex flex-col gap-6">
                <p class="font-mono text-[32px] text-red-600">Talk</p>
                <h3>Cerco nel programma</h3>
                <p class="muted text-[32px]">Una giornata di talk su due sale, divisi per track.</p>
            </div>
            <div class="card flex flex-col gap-6">
                <p class="font-mono text-[32px] text-red-600">Agenda</p>
                <h3>Mi costruisco la giornata</h3>
                <p class="muted text-[32px]">Aggiungo e tolgo talk, senza sovrapposizioni di orario.</p>
            </div>
            <div class="card flex flex-col gap-6">
                <p class="font-mono text-[32px] text-red-600">Workshop</p>
                <h3>Prenoto un posto</h3>
                <p class="muted text-[32px]">Posti limitati: servono ruolo in azienda e nome dell'azienda.</p>
            </div>
        </div>
        <p class="muted max-w-[1600px] text-[36px]">Un'app Laravel normale: Blade, Tailwind e la logica in due service, <code>AgendaService</code> e <code>WorkshopBookingService</code>.</p>
    </div>
    <aside class="notes">Un'app volutamente semplice, come tante che abbiamo in produzione. Nessun agente, per ora.</aside>
</section>

<section data-background-color="#18181b" class="demo">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Demo · Passaggio {{ App\Enums\DemoStage::Classic->step() }}</p>
        <h2>{{ App\Enums\DemoStage::Classic->label() }}</h2>
        <p class="text-[40px] text-zinc-300">Cerco un talk, lo aggiungo all'agenda, prenoto un workshop. A mano.</p>
        <p class="text-[32px]"><a href="{{ App\Enums\DemoStage::Classic->url() }}">Apri la pagina classica →</a></p>
    </div>
    <aside class="notes">Un giro veloce: aggiungo un talk con + Agenda, provo a prenotare «Testing con Pest da zero», che è al completo.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-14">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Il filo del talk</p>
            <h2>Una sola azione, quattro porte</h2>
        </div>
        <div class="flex flex-1 items-center gap-16">
            <div class="flex w-[760px] flex-col gap-6 text-[34px]">
                <div class="card">Una persona clicca <b>+ Agenda</b></div>
                <div class="card fragment">Un client <b>MCP</b> chiama un tool</div>
                <div class="card fragment">La chat nella pagina, tramite <b>AG-UI</b> e le schede <b>A2UI</b></div>
                <div class="card fragment">L'agente del browser, con <b>WebMCP</b></div>
            </div>
            <p class="text-[96px] text-red-600">→</p>
            <div class="flex flex-1 flex-col gap-6 rounded-3xl bg-zinc-900 p-12 text-zinc-100">
                <p class="font-mono text-[44px]">AgendaService::add()</p>
                <p class="text-[32px] text-zinc-400">La logica c'è già. Non la riscriviamo: aggiungiamo porte.</p>
            </div>
        </div>
    </div>
    <aside class="notes">Questo è il messaggio del talk. Oggi una persona clicca + Agenda. [clic] Domani un client MCP chiama un tool. [clic] Oppure la chat nella pagina, con AG-UI e le schede A2UI. [clic] Oppure l'agente del browser con WebMCP: è ancora una proposta, sperimentale in Chrome, e ne parliamo alla fine. Le prime tre si usano già oggi. Ogni porta arriva alla stessa riga di codice. Funziona perché la logica vive già in un service o in una action, non dentro al controller: se l'abbiamo scritta tutta nel controller, prima va estratta. Nella chat AG-UI collega la pagina all'agente, ma l'azione la esegue l'agente sul server, chiamando il tool. Con le schede A2UI invece l'agente disegna la scheda, e il clic su + Agenda chiama la stessa rotta della pagina classica.</aside>
</section>
