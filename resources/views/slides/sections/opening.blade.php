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

<section>
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
        <p class="muted max-w-[1500px] text-[40px]">Partiamo da un'app come tante e la apriamo agli agenti un passo alla volta, senza riscrivere la logica che c'è già.</p>
    </div>
    <aside class="notes">La mappa del talk: cinque passaggi della stessa demo, poi un accenno a WebMCP. I primi quattro aggiungono un protocollo alla volta; l'ultimo lascia al modello anche la scelta dell'interfaccia.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-12">
        <p class="eyebrow">Fino a ieri</p>
        <h2 class="max-w-[1500px] text-[96px]!">La nostra app aveva una porta sola: l'interfaccia web.</h2>
    </div>
    <aside class="notes">Una persona, un browser, form e bottoni. Abbiamo progettato tutto attorno a questo.</aside>
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
    <div class="flex h-full flex-col justify-center gap-14">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Nell'ultimo anno</p>
            <h2>Molti hanno aggiunto una chat alla pagina</h2>
        </div>
        <div class="flex items-center gap-8 text-[34px]">
            <div class="card flex-1">
                <h3>La chat</h3>
                <p class="muted mt-4 text-[30px]">Un riquadro nella pagina dove l'utente scrive.</p>
            </div>
            <p class="text-[64px] text-red-600">→</p>
            <div class="card fragment flex-1" data-fragment-index="0">
                <h3>Il modello</h3>
                <p class="muted mt-4 text-[30px]">Capisce la richiesta e decide quale tool chiamare.</p>
            </div>
            <p class="fragment text-[64px] text-red-600" data-fragment-index="1">→</p>
            <div class="card fragment flex-1" data-fragment-index="1">
                <h3>I tool</h3>
                <p class="muted mt-4 text-[30px]">Funzioni della nostra app: cerca, aggiungi, prenota.</p>
            </div>
        </div>
    </div>
    <aside class="notes">Nell'ultimo anno in molti abbiamo aggiunto una chat alle nostre app, e il pattern è sempre lo stesso. Un riquadro nella pagina dove l'utente scrive. [clic] Il messaggio va a un modello, che capisce la richiesta e decide cosa fare. [clic] E per fare qualcosa davvero chiama dei tool: funzioni della nostra app, cerca un talk, aggiungilo all'agenda, prenota un workshop. Vediamolo nella demo, fatto come lo faremmo tutti la prima volta.</aside>
</section>

<section data-background-color="#18181b" class="demo">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Demo · Passaggio {{ App\Enums\DemoStage::HomemadeChat->step() }}</p>
        <h2>{{ App\Enums\DemoStage::HomemadeChat->label() }}</h2>
        <p class="text-[40px] text-zinc-300">«Quali talk parlano di AI? Aggiungi il primo alla mia agenda»</p>
        <p class="text-[32px]"><a href="{{ App\Enums\DemoStage::HomemadeChat->url() }}">Apri la chat fatta in casa →</a></p>
    </div>
    <aside class="notes">Mostrare la chat che chiama i tool e l'agenda che si aggiorna. Poi chiedere di prenotare un workshop: la conferma la chiede il modello, a testo. Dietro c'è un SSE inventato da noi.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Dopo la demo</p>
            <h2>Funziona, ma è tutto fatto in casa</h2>
        </div>
        <div class="grid grid-cols-2 gap-8">
            <div class="card fragment flex flex-col gap-4">
                <p class="font-mono text-[28px] text-red-600">✕ Streaming e interfaccia</p>
                <h3>Solo testo, a pezzi, in un formato che conosciamo solo noi</h3>
                <p class="muted text-[28px]"><code>event: text</code> l'abbiamo inventato: senza id, con un parser scritto a mano, e un altro client non lo sa leggere. Per una scheda, un modulo o un grafico dovremmo inventare un altro formato, e un altro renderer.</p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <p class="font-mono text-[28px] text-red-600">✕ Approvazione delle azioni</p>
                <h3>Il modello capisce cosa vogliamo solo interpretando il nostro testo</h3>
                <p class="muted text-[28px]">La conferma la chiede lui, a parole. Se capisce male o se ne dimentica, prenota lo stesso: il nostro server non ha modo di fermarsi.</p>
            </div>
        </div>
    </div>
    <aside class="notes">Funziona, ma guardiamo cosa abbiamo dovuto inventare. [clic] Lo streaming e l'interfaccia: nel pannello eventi si vedeva il nostro formato, event: text, pezzi di testo senza id. L'abbiamo inventato noi, con un parser scritto a mano, e nessun altro client lo sa leggere. Ed è solo testo: per una scheda o un grafico dovremmo inventare un altro formato e un altro renderer. [clic] L'approvazione: la conferma la chiede il modello, a parole. Se capisce male o se ne dimentica, prenota lo stesso, perché il server non ha modo di mettere in pausa il run. Ogni app reinventa le stesse cose. Per fortuna ci sono degli standard: MCP per i tool, AG-UI per streaming e approvazioni, A2UI per l'interfaccia.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-16">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Gli standard</p>
            <h2>Un agente può…</h2>
        </div>
        <div class="grid grid-cols-4 gap-8">
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[32px] text-red-600">MCP</p>
                <h3>Usare l'app come strumento</h3>
                <p class="muted text-[32px]">Un protocollo tra client e server: l'app espone tool e risorse, Claude, Codex e gli altri client li chiamano.</p>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[32px] text-red-600">AG-UI</p>
                <h3>Dialogare con l'utente dentro l'app</h3>
                <p class="muted text-[32px]">Un protocollo a eventi tra agente e frontend: testo in streaming, tool, stato e conferme.</p>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[32px] text-red-600">A2UI</p>
                <h3>Comporre l'interfaccia</h3>
                <p class="muted text-[32px]">Un formato dichiarativo per l'interfaccia: l'agente sceglie i componenti, l'app decide come mostrarli.</p>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[32px] text-red-600">WebMCP <span class="text-zinc-400">· domani</span></p>
                <h3>Usare la pagina dal browser</h3>
                <p class="muted text-[32px]">Una proposta di standard web: la pagina registra i suoi tool e l'agente del browser li chiama, con la sessione dell'utente.</p>
            </div>
        </div>
    </div>
    <aside class="notes">Ecco gli standard, ognuno apre la stessa app in un modo diverso. [clic] Con MCP un agente esterno, Claude, Codex, usa la nostra app come strumento: noi esponiamo i tool, il client li chiama. [clic] Con AG-UI l'agente sta dentro la nostra app e dialoga con l'utente: testo in streaming, chiamate ai tool, conferme. [clic] Con A2UI l'agente non risponde solo con del testo ma compone l'interfaccia, scegliendo dai nostri componenti. [clic] E domani WebMCP: è ancora una proposta, sperimentale in Chrome. La pagina stessa dichiara i suoi tool all'agente del browser. Ne parliamo alla fine. I primi tre si usano già oggi.</aside>
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
    <aside class="notes">Questo è il messaggio del talk. Oggi una persona clicca + Agenda. [clic] Domani un client MCP chiama un tool. [clic] Oppure la chat nella pagina, con AG-UI e le schede A2UI. [clic] Oppure l'agente del browser con WebMCP. Ogni porta arriva alla stessa riga di codice. Funziona perché la logica vive già in un service o in una action, non dentro al controller: se l'abbiamo scritta tutta nel controller, prima va estratta. Nella chat AG-UI collega la pagina all'agente, ma l'azione la esegue l'agente sul server, chiamando il tool. Con le schede A2UI invece l'agente disegna la scheda, e il clic su + Agenda chiama la stessa rotta della pagina classica.</aside>
</section>
