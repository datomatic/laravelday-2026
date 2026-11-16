<section data-background-color="#dc2626" class="divider">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Porta 2</p>
        <h1>AG-UI</h1>
        <p class="text-[48px]">Un agente dentro l'app, in streaming.</p>
    </div>
</section>

<section>
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Agent-User Interaction Protocol</p>
            <h2>Un flusso di eventi tra agente e pagina</h2>
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

<section data-background-color="#1c1b22" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">app/Neuron/Agents/ConferenceAssistant.php</p>
            <h2>Gli stessi tool, senza HTTP</h2>
        </div>
        @verbatim
        <pre><code class="language-php" data-trim data-line-numbers="|3-6|7-8"><script type="text/template">
protected function tools(): array
{
    return [
        new McpTool(new SearchTalksTool),
        new McpTool(new ShowAgendaTool),
        new McpTool(new AddToAgendaTool),
        (new McpTool(new BookWorkshopTool))
            ->withApprovalPolicy(fn () => 'La prenotazione occupa un posto limitato.'),
    ];
}
</script></code></pre>
        @endverbatim
        <p class="muted text-[34px]"><code>McpTool</code>: una ventina di righe che leggono nome, descrizione e schema del tool MCP e ne chiamano <code>handle()</code> in-process.</p>
    </div>
    <aside class="notes">Ecco il punto centrale del talk: l'agente della chat non ha tool suoi. [clic] Prende le classi dei tool MCP e le avvolge in McpTool: una ventina di righe che leggono nome, descrizione e schema dal tool MCP e, al momento della chiamata, eseguono handle() direttamente, in-process, senza passare da HTTP. Stessa validazione, stesso service. [clic] E qui c'è una cosa che MCP da solo non ha: withApprovalPolicy. La prenotazione occupa un posto limitato, quindi prima di eseguirla l'agente si ferma e chiede conferma all'utente. La stringa che restituiamo è il motivo che la pagina mostra. Lo stesso vale per clear_agenda.</aside>
</section>

<section data-background-color="#1c1b22" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">AgentController · POST /agent/{stage}</p>
            <h2>L'endpoint è un controller</h2>
        </div>
        @verbatim
        <pre><code class="language-php" data-trim data-line-numbers="1-2|4-6|8"><script type="text/template">
$adapter = new AGUIAdapter($request->threadId(), $request->input('runId'), $request->input('messages'));
$agent = app($stage->agent())->for($request->threadId())->setStreamAdapter(fn () => $adapter);

$events = $request->isResume()
    ? $agent->submitInputs($request->all(), new AGUIInputTranslator)->events()
    : $agent->stream(new UserMessage($request->prompt()));

return response()->stream(fn () => yield from SSEEncoder::encode($events), 200, $adapter->getHeaders());
</script></code></pre>
        @endverbatim
        <p class="muted text-[34px]">La cronologia sta sul server: il thread appartiene all'utente, altrimenti 403.</p>
    </div>
    <aside class="notes">Lato server l'endpoint AG-UI è un controller invocabile. L'AGUIAdapter di neuron-ai traduce gli eventi dell'agente in eventi AG-UI, e lo stage della demo sceglie quale agente usare. Il thread id lo genera la pagina e contiene l'id dell'utente: la FormRequest risponde 403 a chi prova a scrivere nel thread di un altro. [clic] Due casi. Un nuovo messaggio dell'utente fa partire un run in streaming. Se invece arriva resume, è la risposta a una conferma, e submitInputs riprende il run messo in pausa. La cronologia la salva neuron-ai nel database, quindi dal client ci serve solo l'ultimo messaggio. [clic] La risposta è una normale risposta streamed di Laravel: SSEEncoder trasforma ogni evento in un frame data: dei Server-Sent Events.</aside>
</section>

<section data-background-color="#1c1b22" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">resources/js/chat.js</p>
            <h2>Il client si iscrive agli eventi</h2>
        </div>
        @verbatim
        <pre><code class="language-js" data-trim data-line-numbers="1-4|6-9|10-12"><script type="text/template">
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
    <aside class="notes">Nel browser niente React: una pagina Blade e un file JavaScript con @ag-ui/client. HttpAgent sa già fare la POST e leggere gli eventi: gli diamo l'URL e il thread. [clic] runAgent accetta un oggetto con un metodo per ogni tipo di evento. Ogni pezzo di testo lo accodiamo alla bolla e lo mostriamo come Markdown. [clic] Quando arriva il risultato di un tool che cambia i dati, ricarichiamo le zone della pagina, agenda e posti dei workshop, così la pagina resta allineata a quello che ha fatto l'agente. Nella demo il pannello "Eventi AG-UI" mostra lo stesso flusso in diretta.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Human in the loop</p>
            <h2>Le azioni che contano chiedono conferma</h2>
        </div>
        <ol class="flex flex-col gap-6 text-[36px]">
            <li class="fragment">Il modello vuole chiamare <code>book_workshop</code></li>
            <li class="fragment">neuron-ai si ferma, salva il run e chiude con un <b>interrupt</b></li>
            <li class="fragment">La pagina mostra il motivo e i pulsanti Approva / Rifiuta</li>
            <li class="fragment">Il client rilancia il run con <code>resume</code> e la decisione</li>
            <li class="fragment">Il run riparte da dove si era fermato</li>
        </ol>
    </div>
    <aside class="notes">[clic] Il modello decide di prenotare. [clic] Il tool ha una approval policy: neuron-ai non lo esegue, salva lo stato del run nel database e chiude lo stream con RUN_FINISHED e un interrupt, che porta il motivo. [clic] La pagina legge l'interrupt e mostra una card con Approva e Rifiuta. [clic] Al clic, il client rilancia lo stesso run con resume e la decisione. [clic] neuron-ai ricarica lo stato e riparte da dove si era fermato: se l'utente ha approvato esegue il tool, altrimenti dice al modello che l'utente ha rifiutato. È il problema della chat fatta in casa, risolto: lì la conferma la chiedeva il modello a parole, qui la impone il server. add_to_agenda invece non chiede conferma: è idempotente e si annulla con un clic.</aside>
</section>

<section data-background-color="#18181b" class="demo">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Demo · Passaggio {{ App\Enums\DemoStage::TextChat->step() }}</p>
        <h2>{{ App\Enums\DemoStage::TextChat->label() }}</h2>
        <p class="text-[40px] text-zinc-300">«Prenotami il workshop sugli agenti»</p>
        <p class="text-[32px]"><a href="{{ App\Enums\DemoStage::TextChat->url() }}">Apri la chat agentica →</a></p>
    </div>
    <aside class="notes">Aprire il pannello eventi. Una ricerca, un'aggiunta, una prenotazione approvata e una rifiutata. Nel pannello far vedere RUN_FINISHED con l'interrupt, e il nuovo run che parte dopo il clic su Approva.</aside>
</section>
