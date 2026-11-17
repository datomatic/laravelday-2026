<section data-background-color="#dc2626" class="divider">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Porta 2</p>
        <h1>La chat nella pagina</h1>
        <p class="text-[48px]">Prima fatta in casa, poi con AG-UI e A2UI.</p>
    </div>
    <aside class="notes">La seconda porta: la chat nella nostra pagina. Ora l'agente lo portiamo dentro la nostra app, nella nostra pagina, per i nostri utenti. Prima però facciamolo come l'abbiamo fatto tutti la prima volta.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-14">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Dentro la nostra pagina</p>
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
                <p class="muted mt-4 text-[30px]">Gli stessi del server MCP: cerca, aggiungi, prenota.</p>
            </div>
        </div>
    </div>
    <aside class="notes">Con MCP l'agente sta nel client dell'utente. Ma spesso lo vogliamo nella nostra pagina, per tutti i nostri utenti. Nell'ultimo anno in molti abbiamo aggiunto una chat, e il pattern è sempre lo stesso. Un riquadro nella pagina dove l'utente scrive. [clic] Il messaggio va a un modello, che capisce la richiesta e decide cosa fare. [clic] E per fare qualcosa davvero chiama dei tool: gli stessi che abbiamo appena scritto per il server MCP, cerca un talk, aggiungilo all'agenda, prenota un workshop. Vediamolo nella demo, fatto come lo faremmo tutti la prima volta.</aside>
</section>

<section data-background-color="#18181b" class="demo">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Demo · Passaggio {{ App\Enums\DemoStage::HomemadeChat->step() }}</p>
        <h2>{{ App\Enums\DemoStage::HomemadeChat->label() }}</h2>
        <p class="text-[40px] text-zinc-300">«Quali talk parlano di AI? Aggiungi il primo alla mia agenda»</p>
        <p class="text-[32px]"><a href="{{ App\Enums\DemoStage::HomemadeChat->url() }}">Apri la chat fatta in casa →</a></p>
    </div>
    <aside class="notes">Mostrare la chat che chiama i tool, gli stessi del server MCP, e l'agenda che si aggiorna. Poi chiedere di prenotare un workshop: la conferma la chiede il modello, a testo. Dietro c'è un SSE inventato da noi.</aside>
</section>

<section data-flyby="plane">
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
    <aside class="notes">Funziona, ma guardiamo cosa abbiamo dovuto inventare. [clic] Lo streaming e l'interfaccia: nel pannello eventi si vedeva il nostro formato, event: text, pezzi di testo senza id. L'abbiamo inventato noi, con un parser scritto a mano, e nessun altro client lo sa leggere. Ed è solo testo: per una scheda o un grafico dovremmo inventare un altro formato e un altro renderer. [clic] L'approvazione: la conferma la chiede il modello, a parole. Se capisce male o se ne dimentica, prenota lo stesso, perché il server non ha modo di mettere in pausa il run. Ogni app reinventa le stesse cose. I tool ce li dava già MCP; per il resto ci sono altri due standard: AG-UI per streaming e approvazioni, A2UI per l'interfaccia. Cominciamo da AG-UI.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Agent-User Interaction Protocol</p>
            <h2>AG-UI: un flusso di eventi tra agente e pagina</h2>
        </div>
        <table class="w-full text-left text-[30px]">
            <thead class="font-mono text-[24px] text-zinc-500">
                <tr>
                    <th class="pb-3">Gruppo</th>
                    <th class="pb-3">Eventi</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t-2 border-zinc-200"><td class="py-3 pr-10 whitespace-nowrap">Ciclo di vita</td><td class="py-3 font-mono text-[24px]">RUN_STARTED · RUN_FINISHED · RUN_ERROR · STEP_STARTED · STEP_FINISHED</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-3 pr-10 whitespace-nowrap">Testo</td><td class="py-3 font-mono text-[24px]">TEXT_MESSAGE_START · _CONTENT · _END · _CHUNK</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-3 pr-10 whitespace-nowrap">Tool</td><td class="py-3 font-mono text-[24px]">TOOL_CALL_START · _ARGS · _END · _RESULT · _CHUNK</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-3 pr-10 whitespace-nowrap">Stato condiviso</td><td class="py-3 font-mono text-[24px]">STATE_SNAPSHOT · STATE_DELTA · MESSAGES_SNAPSHOT</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-3 pr-10 whitespace-nowrap">Ragionamento</td><td class="py-3 font-mono text-[24px]">REASONING_START · _END · REASONING_MESSAGE_* · …</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-3 pr-10 whitespace-nowrap">Activity</td><td class="py-3 font-mono text-[24px]">ACTIVITY_SNAPSHOT · ACTIVITY_DELTA</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-3 pr-10 whitespace-nowrap">Sub-agenti</td><td class="py-3 font-mono text-[24px]">SUBAGENT_STARTED · _FINISHED · _ERROR</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-3 pr-10 whitespace-nowrap">Estensioni</td><td class="py-3 font-mono text-[24px]">CUSTOM · RAW</td></tr>
            </tbody>
        </table>
        <div class="muted flex flex-col gap-2 text-[32px]">
            <p>Server-Sent Events su una POST, lato browser <code>@ag-ui/client</code>.</p>
            <p>Lato server va bene <code>neuron-ai</code> o <code>laravel/ai</code>: noi usiamo neuron-ai, più maturo e completo.</p>
        </div>
    </div>
    <aside class="notes">AG-UI non dice niente sul modello: standardizza solo gli eventi tra agente e interfaccia. Sono una trentina, divisi per gruppi: l'inizio e la fine del run, il testo che arriva a pezzi, le chiamate ai tool, lo stato condiviso, il ragionamento del modello, i sub-agenti, e degli eventi custom per tutto il resto. Non serve conoscerli tutti: la pagina ascolta quelli che le servono e ignora gli altri. Gli eventi viaggiano come Server-Sent Events sulla risposta di una POST, e nel browser li legge @ag-ui/client. Lato server serve un agente che produca questi eventi: in Laravel si può fare sia con neuron-ai sia con laravel/ai, l'SDK ufficiale. Noi usiamo neuron-ai perché oggi è più maturo e più completo.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">app/Neuron/Agents/ConferenceAssistant.php</p>
            <h2>Gli stessi tool, senza HTTP</h2>
        </div>
        @verbatim
        <pre class="code-compact"><code class="language-php" data-trim data-line-numbers="|4-7|8-11"><script type="text/template">
protected function tools(): array
{
    return [
        new McpTool(new SearchTalksTool),
        new McpTool(new ShowAgendaTool),
        new McpTool(new AddToAgendaTool),
        new McpTool(new ListWorkshopsTool),
        (new McpTool(new BookWorkshopTool))
            ->withApprovalPolicy(fn () => 'La prenotazione occupa uno dei posti limitati del workshop.'),
        (new McpTool(new ClearAgendaTool))
            ->withApprovalPolicy(fn () => "Tutti i talk verranno rimossi dall'agenda."),
    ];
}
</script></code></pre>
        @endverbatim
        <p class="muted text-[34px]"><code>McpTool</code>: una ventina di righe che leggono nome, descrizione e schema del tool MCP e ne chiamano <code>handle()</code> in-process.</p>
    </div>
    <aside class="notes">Come nella chat fatta in casa, l'agente non ha tool suoi: usa le stesse classi del server MCP. Vediamo come. [clic] McpTool le adatta per neuron-ai e le esegue direttamente, senza HTTP: stessa validazione, stesso service. [clic] In più c'è una cosa che MCP non ha: withApprovalPolicy. Prenotare e svuotare l'agenda chiedono conferma all'utente, e la stringa è il motivo che la pagina mostra.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col justify-center gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Human in the loop</p>
            <h2>La conferma la impone il server, non il modello</h2>
        </div>
        <div class="grid grid-cols-[1fr_120px_1fr] items-center gap-x-6 gap-y-8 text-[30px]">
            <p class="eyebrow text-center">Server</p>
            <span></span>
            <p class="eyebrow text-center">Browser</p>

            <div class="card fragment flex flex-col gap-2" data-fragment-index="0">
                <p><b>Run 1</b> · il modello chiama <code>book_workshop</code></p>
                <p class="muted text-[26px]">Il tool <b>non</b> parte: neuron-ai salva il run e si ferma.</p>
            </div>
            <p class="fragment text-center text-[56px] text-red-600" data-fragment-index="0">→</p>
            <div class="card fragment flex flex-col gap-2" data-fragment-index="0">
                <p><code>RUN_FINISHED</code> con <b>interrupt</b></p>
                <p class="muted text-[26px]">La pagina mostra il motivo e Approva / Rifiuta.</p>
            </div>

            <div class="card fragment flex flex-col gap-2" data-fragment-index="1">
                <p><b>Run 2</b> · riparte da dove si era fermato</p>
                <p class="muted text-[26px]">Approvato: il tool viene eseguito. Rifiutato: no.</p>
            </div>
            <p class="fragment text-center text-[56px] text-red-600" data-fragment-index="1">←</p>
            <div class="card fragment flex flex-col gap-2" data-fragment-index="1">
                <p>clic → nuova richiesta con <code>resume</code></p>
                <p class="muted text-[26px]">Due richieste HTTP, il run in pausa sta sul server.</p>
            </div>
        </div>
    </div>
    <aside class="notes">Nella chat fatta in casa la conferma la chiedeva il modello a parole: poteva saltarla o farsela togliere con un prompt. Qui la impone il server. [clic] Il modello vuole prenotare. Il tool ha la approval policy: neuron-ai non lo esegue, mette il run in pausa e chiude lo stream con RUN_FINISHED e un interrupt. La pagina mostra Approva e Rifiuta. [clic] Al clic il client fa una seconda richiesta con resume e la decisione: il run riparte da dove era, e il tool parte solo se l'utente ha approvato. Sono due richieste HTTP distinte, nel mezzo il run aspetta sul server. Il motivo che la pagina mostra è la stringa che abbiamo scritto in withApprovalPolicy.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col justify-center gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">AgentController · POST /agent/{stage}</p>
            <h2>L'endpoint è un controller</h2>
        </div>
        @verbatim
        <pre><code class="language-php" data-trim data-line-numbers="1|3-5|7"><script type="text/template">
$agent = ConferenceAssistant::make()->setStreamAdapter(new AGUIAdapter(…));

$events = $request->isResume()
    ? $agent->submitInputs(…)   // risposta a una conferma
    : $agent->stream(…);        // nuovo messaggio

return response()->stream(fn () => yield from SSEEncoder::encode($events));
</script></code></pre>
        @endverbatim
        <p class="muted text-[34px]">Semplificato: il codice completo è nel repo.</p>
    </div>
    <aside class="notes">Lato server basta un controller, qui semplificato. L'adapter di neuron-ai trasforma quello che fa l'agente in eventi AG-UI. La cronologia della conversazione sta sul server, legata all'utente. [clic] I due casi che abbiamo appena visto: un nuovo messaggio fa partire l'agente, la risposta a una conferma riprende il run che era in pausa. [clic] E la risposta è uno stream di Laravel: ogni evento parte verso il browser appena succede.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col justify-center gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">resources/js/chat.js</p>
            <h2>Il client si iscrive agli eventi</h2>
        </div>
        <div class="flex items-center gap-16">
            <div class="w-[960px] shrink-0">
                @verbatim
                <pre><code class="language-js" data-trim data-line-numbers="1-4|6-9|10-12" data-fragment-index="0"><script type="text/template">
const agent = new HttpAgent({
    url: chat.dataset.agentUrl,
    threadId: chat.dataset.threadId,
});

await agent.runAgent(parameters, {
    onTextMessageContentEvent({ event }) {
        appendMarkdown(assistantBubble, event.delta);
    },
    onToolCallResultEvent({ event }) {
        refreshLiveRegions();
    },
});
</script></code></pre>
                @endverbatim
            </div>
            <ol class="flex flex-col gap-10 text-[32px]">
                <li class="flex flex-col gap-2">
                    <p><b>1. Dove e quale chat</b></p>
                    <p class="muted text-[28px]">La rotta <code>POST /agent/{stage}</code> e il thread della conversazione.</p>
                </li>
                <li class="fragment flex flex-col gap-2" data-fragment-index="0">
                    <p><b>2. Arriva un pezzo di testo</b></p>
                    <p class="muted text-[28px]"><code>TEXT_MESSAGE_CONTENT</code> → lo accodo alla bolla della chat.</p>
                </li>
                <li class="fragment flex flex-col gap-2" data-fragment-index="1">
                    <p><b>3. Un tool ha finito</b></p>
                    <p class="muted text-[28px]"><code>TOOL_CALL_RESULT</code> → ricarico agenda e posti dei workshop.</p>
                </li>
            </ol>
        </div>
    </div>
    <aside class="notes">Lato browser niente React: Blade e un file JavaScript. HttpAgent di @ag-ui/client fa la richiesta al nostro controller e legge gli eventi. Gli diciamo solo dove chiamare e quale conversazione continuare. [clic] Poi, per ogni tipo di evento che ci interessa, scriviamo un metodo. Il testo arriva a pezzi, e ogni pezzo lo accodiamo alla bolla della chat: per questo la risposta si scrive mentre la leggete. [clic] Quando un tool finisce, per esempio dopo una prenotazione, ricarichiamo agenda e posti dei workshop: la pagina si aggiorna da sola. Gli eventi che non ci interessano li ignoriamo. Nella demo il pannello Eventi AG-UI mostra lo stesso flusso in diretta.</aside>
</section>

<section data-background-color="#18181b" class="demo">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Demo · Passaggio {{ App\Enums\DemoStage::TextChat->step() }}</p>
        <h2>{{ App\Enums\DemoStage::TextChat->label() }}</h2>
        <p class="text-[40px] text-zinc-300">«Prenotami il workshop sugli agenti, sono CTO di Acme»</p>
        <p class="text-[32px]"><a href="{{ App\Enums\DemoStage::TextChat->url() }}">Apri la chat agentica →</a></p>
    </div>
    <aside class="notes">Aprire il pannello eventi. Una ricerca, un'aggiunta, una prenotazione approvata e una rifiutata. Nel pannello far vedere RUN_FINISHED con l'interrupt, e il nuovo run che parte dopo il clic su Approva.</aside>
</section>
