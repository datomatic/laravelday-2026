<section data-background-color="#dc2626" class="divider" data-flyby="plane">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Porta 1</p>
        <h1>MCP</h1>
        <p class="text-[48px]">L'app diventa uno strumento per Claude, Codex e gli altri client.</p>
    </div>
    <aside class="notes">La prima porta: MCP. L'agente sta fuori dalla nostra app, nel client dell'utente, e usa la nostra app come uno strumento.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Model Context Protocol</p>
            <h2>Il client chiede all'app cosa sa fare</h2>
        </div>
        <div class="flex items-stretch gap-10">
            <div class="card flex w-[440px] flex-col justify-center gap-4">
                <h3>Client</h3>
                <p class="muted text-[30px]">Claude Desktop, Codex, VS Code. Il modello vive qui.</p>
            </div>
            <div class="flex flex-1 flex-col justify-center font-mono">
                <div class="-mx-10 -mt-12 flex flex-col items-center gap-3 text-red-600">
                    <p class="text-[30px] font-semibold">JSON-RPC</p>
                    <div class="flex w-full items-center">
                        <span class="h-0 w-0 border-y-[14px] border-r-[22px] border-y-transparent border-r-red-600"></span>
                        <span class="h-[4px] flex-1 bg-red-600"></span>
                        <span class="h-0 w-0 border-y-[14px] border-l-[22px] border-y-transparent border-l-red-600"></span>
                    </div>
                </div>
            </div>
            <div class="card flex w-[440px] flex-col justify-center gap-4">
                <h3>Server</h3>
                <p class="muted text-[30px]">La nostra app Laravel con <code>laravel/mcp</code>, su HTTP con il token dell'utente.</p>
            </div>
        </div>
        <div class="flex flex-col gap-4 self-center font-mono text-[28px]">
            <p class="fragment"><span class="text-red-600">→ tools/list</span> <span class="muted">cosa sai fare?</span></p>
            <p class="fragment"><span class="text-red-600">← </span><span class="muted">nome, descrizione e schema di ogni tool</span></p>
            <p class="fragment"><span class="text-red-600">→ tools/call</span> add_to_agenda <span class="muted">{"talk_id": 7}</span></p>
            <p class="fragment"><span class="text-red-600">← </span><span class="muted">"… aggiunto all'agenda (10:30, Sala A)."</span></p>
        </div>
        <div class="grid grid-cols-3 gap-10 border-t-2 border-zinc-200 pt-12 text-[32px]">
            <div class="flex flex-col gap-3">
                <p><b>Tool</b> <span class="muted">· li sceglie il modello</span></p>
                <p class="muted">Azioni: <code>add_to_agenda</code>, <code>book_workshop</code></p>
            </div>
            <div class="flex flex-col gap-3">
                <p><b>Resource</b> <span class="muted">· le allega il client</span></p>
                <p class="muted">Dati da leggere: <code>conference://schedule</code></p>
            </div>
            <div class="flex flex-col gap-3">
                <p><b>Prompt</b> <span class="muted">· li sceglie l'utente</span></p>
                <p class="muted">Richieste pronte: <code>plan_my_day</code></p>
            </div>
        </div>
    </div>
    <aside class="notes">MCP è un protocollo tra due parti. Da una parte il client: Claude Desktop, Codex, VS Code, cioè l'applicazione che ha dentro il modello. Dall'altra il server, che per noi è la nostra app Laravel con il pacchetto laravel/mcp. Parlano JSON-RPC su HTTP, e l'utente si autentica con il suo token. [clic] La prima cosa che fa il client è chiedere: cosa sai fare? [clic] Il server risponde con l'elenco dei tool: nome, descrizione e schema dei parametri. Il modello non conosce la nostra app, la scopre da qui. Quindi descrizioni scritte bene contano quanto il codice. [clic] Quando l'utente chiede di aggiungere un talk, il modello sceglie add_to_agenda e il client lo chiama con i parametri. [clic] Il server esegue il nostro codice e restituisce il risultato, che torna al modello. Le primitive sono tre e cambia chi decide di usarle. I tool li sceglie il modello: sono le azioni. Le resource sono dati che il client legge e allega al contesto: noi esponiamo il programma della conferenza. I prompt sono richieste pronte che l'utente sceglie dal menu: noi ne abbiamo uno, plan_my_day, che vediamo nella demo.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">routes/ai.php</p>
            <h2>Un server è una rotta</h2>
        </div>
        @verbatim
        <pre class="code-compact"><code class="language-php" data-trim data-line-numbers="1|3-12|14-17"><script type="text/template">
Mcp::web('/mcp/conference', ConferenceServer::class)->middleware(['auth:sanctum', 'throttle:60,1']);

class ConferenceServer extends Server
{
    protected array $tools = [
        SearchTalksTool::class,
        ShowAgendaTool::class,
        AddToAgendaTool::class,
        ClearAgendaTool::class,
        ListWorkshopsTool::class,
        BookWorkshopTool::class,
    ];

    protected array $resources = [ScheduleResource::class, WorkshopsApp::class];

    protected array $prompts = [PlanMyDayPrompt::class];
}
</script></code></pre>
        @endverbatim
    </div>
    <aside class="notes">Una riga in routes/ai.php, con i middleware che già conosciamo: l'autenticazione con Sanctum e il throttle, perché un agente può chiamare un tool cento volte di fila. [clic] Il server è una classe che elenca le sue primitive: prima i tool, le azioni. [clic] Poi le resource, dati che l'utente allega alla conversazione, come il programma della conferenza, e i prompt, richieste pronte che l'utente sceglie dal menu. Li vediamo all'opera nella demo. Noi abbiamo sei tool, ma un client collegato a dieci server ne vede centinaia, e ogni definizione occupa contesto. Alcuni client, come Claude Code, ormai li cercano invece di caricarli tutti. Si può fare anche dal server, con ToolSearch: lo trovate tra le cose da guardare a casa.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">app/Mcp/Tools/AddToAgendaTool.php</p>
            <h2>Un tool è una classe</h2>
        </div>
        @verbatim
        <pre><code class="language-php" data-trim data-line-numbers="1-3|6-9|13|15|16"><script type="text/template">
#[Name('add_to_agenda')]
#[Description("Aggiunge un talk all'agenda personale dell'utente.")]
#[IsIdempotent]
class AddToAgendaTool extends Tool
{
    public function schema(JsonSchema $schema): array
    {
        return ['talk_id' => $schema->integer()->description('Id del talk.')->required()];
    }

    public function handle(Request $request, AgendaService $agenda): Response
    {
        $validated = $request->validate(['talk_id' => 'required|integer|exists:talks,id']);
        $talk = Talk::find($validated['talk_id']);
        $agenda->add($request->user(), $talk);
        return Response::text("\"{$talk->title}\" aggiunto all'agenda.");
    }
}
</script></code></pre>
        @endverbatim
    </div>
    <aside class="notes">Nome e descrizione li legge il modello: sono il prompt del tool. [clic] Lo schema descrive i parametri: diventa il JSON Schema che il client riceve con tools/list, ed è così che il modello sa di dover passare un talk_id intero. C'è anche outputSchema, che descrive cosa restituisce il tool. Serve al client più che al modello: i tool di lettura della demo restituiscono dati strutturati con Response::structured. [clic] Lo schema non basta: la validazione Laravel resta, perché il modello può sbagliare. [clic] La riga che conta è la chiamata al service. [clic] Il testo di ritorno torna al modello.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-10">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Laravel\Mcp\Response</p>
            <h2>Cosa restituisce un tool</h2>
        </div>
        <table class="w-full text-left text-[28px]">
            <thead class="font-mono text-[24px] text-zinc-500">
                <tr>
                    <th class="pb-4">Metodo</th>
                    <th class="pb-4">Cosa restituisce</th>
                    <th class="pb-4">Nella demo</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-10 font-mono text-red-600">Response::text()</td><td class="py-4 pr-10">Testo per il modello. Anche <code>json()</code> e <code>view()</code>.</td><td class="py-4 font-mono text-[24px] text-zinc-500">add_to_agenda</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-10 font-mono text-red-600">Response::structured()</td><td class="py-4 pr-10">Dati con la forma di <code>outputSchema</code>, più una copia in testo.</td><td class="py-4 font-mono text-[24px] text-zinc-500">search_talks</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-10 font-mono text-red-600">Response::error()</td><td class="py-4 pr-10">Il tool è fallito: il modello legge il motivo e può correggersi.</td><td class="py-4 font-mono text-[24px] text-zinc-500">book_workshop</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-10 font-mono text-red-600">Response::image()</td><td class="py-4 pr-10">Un'immagine, o un audio con <code>audio()</code>. Anche da <code>fromStorage()</code>.</td><td class="py-4 font-mono text-[24px] text-zinc-500"></td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-10 font-mono text-red-600">Response::resourceLink()</td><td class="py-4 pr-10">Un link a una resource: il client la legge se serve.</td><td class="py-4 font-mono text-[24px] text-zinc-500"></td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-10 font-mono text-red-600">Response::make([...])</td><td class="py-4 pr-10">Più contenuti nella stessa risposta.</td><td class="py-4 font-mono text-[24px] text-zinc-500"></td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-10 font-mono text-red-600">Streaming</td><td class="py-4 pr-10"><code>handle()</code> è un generatore: le notifiche partono subito.</td><td class="py-4 font-mono text-[24px] text-zinc-500"></td></tr>
            </tbody>
        </table>
    </div>
    <aside class="notes">Un tool non restituisce solo testo. Il più comune è Response::text: una stringa che il modello legge. json e view sono scorciatoie che producono sempre testo. Response::structured restituisce dati veri e propri, con la forma dichiarata in outputSchema. Il client li può usare come dati, e laravel/mcp mette anche una copia in testo, così il modello li legge comunque. È quello che fanno i nostri tool di lettura. Response::error segna la risposta come errore: non è un'eccezione che rompe la chiamata, è un risultato che il modello legge, e può riprovare o chiedere all'utente. Lo usiamo per il workshop al completo. Anche gli errori di validazione arrivano al modello così, da soli. Poi immagini e audio, con il loro MIME type, anche presi direttamente da un disco di Storage. resourceLink restituisce un riferimento a una resource invece del contenuto: utile quando il contenuto è grande e il client decide se leggerlo. E con make si mettono più contenuti nella stessa risposta, per esempio un testo e un'immagine. Infine lo streaming: se handle è un generatore, ogni yield di Response::notification parte subito verso il client, per esempio una notifica di progresso su un'operazione lunga. Attenzione però: il risultato vero arriva tutto alla fine, non a pezzi. Per lo streaming del testo del modello, quello parola per parola, serve un altro protocollo: AG-UI, che vediamo tra poco.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col justify-center gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Streaming</p>
            <h2>Le notifiche partono subito</h2>
        </div>
        @verbatim
        <pre class="code-compact"><code class="language-php" data-trim data-line-numbers="1|4|6-13|15"><script type="text/template">
public function handle(Request $request, AgendaService $agenda): Generator
{
    $talks = $agenda->talksFor($request->user());
    $token = $request->meta()['progressToken'] ?? null;

    foreach ($talks as $index => $talk) {
        if ($token !== null) {
            yield Response::notification('notifications/progress', [
                'progressToken' => $token,
                'progress' => $index + 1,
                'total' => $talks->count(),
            ]);
        }

        yield Response::text($this->exportToCalendar($talk));
    }
}
</script></code></pre>
        @endverbatim
    </div>
    <aside class="notes">Un esempio che nella demo non c'è: esportare l'agenda in un calendario, un talk alla volta. handle restituisce un Generator invece di una Response. [clic] Il client che vuole vedere l'avanzamento manda un progressToken nei metadati della richiesta: se non c'è, niente notifiche. [clic] A ogni giro facciamo yield di notifications/progress, la notifica standard di MCP: laravel/mcp apre uno stream SSE e la manda subito, e il client può mostrare "3 di 8". Attenzione: la documentazione di Laravel usa un nome inventato, processing/progress, che i client ricevono ma non mostrano. [clic] Le Response normali invece vengono raccolte e il client le riceve tutte insieme alla fine, come risultato del tool. Quindi in streaming va l'avanzamento, non il risultato.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-14">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Annotazioni</p>
            <h2>Quattro suggerimenti per il client</h2>
        </div>
        <div class="grid grid-cols-2 gap-8">
            <div class="card flex flex-col gap-3">
                <p class="font-mono text-[32px] text-red-600">#[IsReadOnly]</p>
                <p class="text-[32px]">Legge e basta, non modifica niente.</p>
                <p class="muted font-mono text-[24px]">readOnlyHint · default false</p>
            </div>
            <div class="card flex flex-col gap-3">
                <p class="font-mono text-[32px] text-red-600">#[IsDestructive]</p>
                <p class="text-[32px]">Può cancellare o sovrascrivere dati.</p>
                <p class="muted font-mono text-[24px]">destructiveHint · default true</p>
            </div>
            <div class="card flex flex-col gap-3">
                <p class="font-mono text-[32px] text-red-600">#[IsIdempotent]</p>
                <p class="text-[32px]">Ripeterlo con gli stessi argomenti non cambia altro.</p>
                <p class="muted font-mono text-[24px]">idempotentHint · default false</p>
            </div>
            <div class="card flex flex-col gap-3">
                <p class="font-mono text-[32px] text-red-600">#[IsOpenWorld]</p>
                <p class="text-[32px]">Esce dall'app: web, API di terzi.</p>
                <p class="muted font-mono text-[24px]">openWorldHint · default true</p>
            </div>
        </div>
        <div class="flex flex-col gap-3 text-[32px]">
            <p class="muted">Sono suggerimenti, non garanzie. Senza annotazioni il client assume il caso peggiore.</p>
            <p>Quindi dichiariamo anche cosa un tool <b>non</b> fa: <code>add_to_agenda</code> è <code>#[IsDestructive(false)]</code>, e nessuno dei nostri esce dall'app.</p>
        </div>
    </div>
    <aside class="notes">Prima di vedere le nostre, ecco cosa prevede MCP. Le annotazioni sono quattro e in laravel/mcp sono attributi PHP sulla classe del tool. IsReadOnly: il tool legge e non cambia niente. IsDestructive: può cancellare o sovrascrivere dati. IsIdempotent: se lo chiami due volte con gli stessi argomenti, la seconda chiamata non cambia nulla. IsOpenWorld: il tool parla con il mondo esterno, per esempio il web o un'API di terzi, invece di restare dentro l'app. Guardate i default: se non dite niente, il client deve pensare al caso peggiore, cioè un tool che scrive, può distruggere ed esce dall'app. Distruttivo e idempotente contano solo se il tool non è in sola lettura. E sono suggerimenti, non garanzie: il client li usa per decidere quando chiedere conferma, ma la sicurezza vera resta sul server, con policy e validazione. Proprio perché i default sono prudenti, conviene dichiarare anche quello che un tool non fa. add_to_agenda scrive, quindi senza dire niente il client lo considererebbe distruttivo: gli diciamo IsDestructive false. E tutti i nostri tool sono IsOpenWorld false, perché lavorano solo sui dati della nostra app. L'unico distruttivo è clear_agenda, ed è lì che il client dovrebbe chiedere conferma.</aside>
</section>

<section data-flyby="car">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Autenticazione</p>
            <h2>Ogni chiamata ha un utente</h2>
        </div>
        <div class="flex flex-1 items-center gap-16">
            <ul class="flex flex-1 flex-col gap-8 text-[36px]">
                <li>Token personale <b>Sanctum</b> nell'header <code>Authorization: Bearer …</code><br><span class="muted text-[32px]">middleware <code>auth:sanctum</code></span></li>
                <li class="fragment" data-fragment-index="0">Oppure OAuth con <b>Passport</b>: l'utente fa login dal browser, niente token da copiare<br><span class="muted text-[32px]">middleware <code>auth:api</code></span></li>
                <li class="fragment" data-fragment-index="1"><code>$request->user()</code> nel tool: il client agisce per conto di una persona precisa</li>
                <li class="fragment" data-fragment-index="2"><code>shouldRegister()</code>: ogni utente vede solo i tool che può usare<br><span class="muted text-[32px]">se restituisce <code>false</code>, il tool non compare in <code>tools/list</code> e non si può chiamare</span></li>
            </ul>
            <figure class="fragment flex w-[560px] shrink-0 flex-col items-center gap-4" data-fragment-index="0">
                <img src="{{ asset('images/slides/passport-authorize.png') }}" alt="Schermata di autorizzazione OAuth di laravel/mcp: Authorize, Cancel, utente collegato e permesso Use MCP" class="w-full">
                <figcaption class="muted text-center text-[24px]">La schermata di laravel/mcp: l'utente approva il client</figcaption>
            </figure>
        </div>
    </div>
    <aside class="notes">Prima scelta: come si autentica il client. Nella demo uso un token Sanctum perché è il modo più semplice: lo genero e lo incollo nella configurazione del client, che lo manda nell'header Authorization. [clic] Ma la specifica MCP prevede OAuth 2.1, ed è quello che la maggior parte dei client supporta meglio. Con Passport, laravel/mcp registra da solo le rotte che servono a OAuth, e sulla rotta del server il middleware diventa auth:api invece di auth:sanctum. Il client apre il browser, l'utente fa login nella nostra app e approva l'accesso da questa schermata, che laravel/mcp ci dà già pronta, e il token arriva al client da solo. Ed è quello che serve per aggiungerla come connettore remoto nei client che lo supportano. La documentazione di laravel/mcp consiglia Passport quando possibile, e Sanctum se l'app lo usa già. [clic] Qualunque sia la scelta, nel tool l'utente arriva da $request->user(), come in un controller: il client agisce per conto di una persona precisa. Policy, gate e scope funzionano come sempre. [clic] E visto che sappiamo chi è l'utente, possiamo decidere anche cosa vede. Ogni tool può avere un metodo shouldRegister che riceve la richiesta: se restituisce false, il tool sparisce dall'elenco e non si può chiamare. Per esempio un tool di amministrazione che vedono solo gli admin, o una funzione riservata a chi ha un abbonamento. Il modello non vede nemmeno che esiste, quindi non spreca contesto e non prova a usarlo.</aside>
</section>

<section data-background-color="#18181b" class="demo">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Demo</p>
        <h2>Claude usa la nostra app</h2>
        <p class="text-[40px] text-zinc-300">«Trova i talk sull'AI e mettili nella mia agenda»</p>
        <p class="text-[32px]"><a href="{{ App\Enums\DemoStage::Classic->url() }}">Apri il programma →</a></p>
    </div>
    <aside class="notes">Claude Desktop è già collegato con il token. Chiedere «aggiungi alla mia agenda il talk su RAG»: si vedono search_talks e add_to_agenda. Poi ricaricare la pagina classica: il talk è in agenda. Poi il prompt: dal menu più scegliere Pianifica la mia giornata, scrivere «AI e testing» come interessi. Il messaggio pronto arriva nella chat, il modello usa show_agenda e search_talks, propone la tabella e aspetta la conferma prima di chiamare add_to_agenda. Il prompt non ha logica sua: orchestra i tool che esistono già.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-10">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">MCP · considerazioni</p>
            <h2>Cosa ci guadagniamo, cosa no</h2>
        </div>
        <div class="grid grid-cols-2 gap-10">
            <div class="card flex flex-col gap-6">
                <h3><span class="text-green-700">Pro</span></h3>
                <ul class="flex flex-col gap-5 text-[30px]">
                    <li class="fragment flex flex-col gap-1"><span>L'agente lo paga chi lo usa</span><span class="muted text-[24px]">modello e token sono quelli del client, non i nostri</span></li>
                    <li class="fragment flex flex-col gap-1"><span>Nessuna interfaccia da sviluppare</span><span class="muted text-[24px]">la chat, le conferme e la cronologia le ha già il client</span></li>
                    <li class="fragment flex flex-col gap-1"><span>Un server, tutti i client</span><span class="muted text-[24px]">Claude, Codex, VS Code e quelli che arriveranno</span></li>
                </ul>
            </div>
            <div class="card flex flex-col gap-6">
                <h3><span class="text-red-600">Contro</span></h3>
                <ul class="flex flex-col gap-5 text-[30px]">
                    <li class="fragment flex flex-col gap-1"><span>Ancora di nicchia</span><span class="muted text-[24px]">serve un client MCP: oggi lo usano soprattutto gli sviluppatori</span></li>
                    <li class="fragment flex flex-col gap-1"><span>L'esperienza non è nostra</span><span class="muted text-[24px]">modello e interfaccia li sceglie il client, e ognuno supporta resource e prompt a modo suo</span></li>
                    <li class="fragment flex flex-col gap-1"><span>Visibilità limitata</span><span class="muted text-[24px]">vediamo le chiamate ai tool, non cosa chiede l'utente</span></li>
                </ul>
            </div>
        </div>
    </div>
    <aside class="notes">Cosa ci portiamo a casa da MCP. [clic] Il primo vantaggio è economico: l'agente lo paga chi lo usa. Il modello, i token, l'abbonamento sono del client, non nostri. Noi esponiamo solo la logica. [clic] Non dobbiamo sviluppare nessuna interfaccia: la chat, la conferma, la cronologia le ha già il client. [clic] Scriviamo un server solo, che funziona con tutti i client compatibili, anche con quelli che usciranno domani. [clic] Il rovescio: è ancora una cosa di nicchia. Per usarla serve un client MCP configurato, e oggi lo fanno soprattutto gli sviluppatori, non i nostri utenti finali. [clic] L'esperienza non è nostra: quale modello, quale prompt, come vengono mostrati i risultati lo decide il client. E non tutti i client supportano le stesse cose: i tool funzionano ovunque, ma resource, prompt e annotazioni ognuno li tratta a modo suo. [clic] E vediamo poco: nei log abbiamo le chiamate ai tool, ma non sappiamo cosa ha chiesto l'utente né cosa gli ha risposto il modello. Se vogliamo l'agente dentro la nostra app, per i nostri utenti, con la nostra interfaccia e sotto il nostro controllo, serve altro. Ci arriviamo tra poco, prima due esempi veri.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">MCP · Nei miei progetti</p>
            <h2>Dove l'ho usato davvero</h2>
        </div>
        <div class="grid grid-cols-2 gap-10">
            <div class="card fragment flex flex-col gap-6">
                <div class="flex items-start justify-between gap-6">
                    <div class="flex flex-col gap-6">
                        <p class="font-mono text-[26px] text-red-600">datomatic/laravel-database-mcp</p>
                        <h3>Il database, in sola lettura</h3>
                    </div>
                    <img src="/images/qr-laravel-database-mcp.svg" alt="QR code per github.com/datomatic/laravel-database-mcp" class="size-[180px] shrink-0 rounded-lg">
                </div>
                <p class="muted text-[30px]">Due tool, <code>describe_database</code> e <code>query_database</code>: l'assistente esplora tabelle e relazioni e legge i dati.</p>
                <ul class="flex flex-col gap-3 text-[28px]">
                    <li>Solo lettura, mai SQL</li>
                    <li>Dati sensibili esclusi, quelli personali anonimizzati</li>
                    <li>Multi-tenant: ogni query filtrata sul tenant corrente</li>
                    <li>Descrizioni per tabelle e colonne</li>
                    <li>Audit log di ogni chiamata</li>
                </ul>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[26px] text-red-600">E-commerce</p>
                <h3>Il lavoro ripetitivo sul catalogo</h3>
                <p class="muted text-[30px]">Un server MCP sopra le action che già aggiornano i prodotti.</p>
                <ul class="flex flex-col gap-3 text-[28px]">
                    <li>Riscrivere e tradurre i testi dei prodotti</li>
                    <li>Caricare e associare le immagini</li>
                    <li>Scegliere i prodotti suggeriti</li>
                </ul>
            </div>
        </div>
    </div>
    <aside class="notes">Due esempi da progetti veri. [clic] Il primo è un pacchetto open source: laravel-database-mcp. Dà a Claude Code o a Cursor due tool, uno per descrivere il database e uno per leggerlo. Lo uso per rispondere a domande sui dati senza scrivere query a mano: quanti ordini questa settimana, quali clienti non hanno mai comprato. Il modello non scrive SQL: passa parametri strutturati, e il pacchetto esegue solo query di lettura. I dati sensibili, come token e password, sono esclusi. Quelli personali si possono anonimizzare: l'email diventa uno pseudonimo stabile, sempre lo stesso per lo stesso valore, così il modello può contare e incrociare i record senza mai leggere il dato vero. E funziona anche nelle app multi-tenant con un solo database: si dice quale colonna identifica il tenant, e ogni query viene filtrata sul tenant dell'utente collegato, anche nelle join. Il modello non può uscire dai dati del suo cliente, perché il filtro lo aggiunge il pacchetto, non il modello. Si possono descrivere tabelle e colonne, per esempio «il fatturato è la somma delle fatture pagate», e il modello trova da solo la query giusta. E ogni chiamata finisce in un audit log. [clic] Il secondo è un e-commerce. C'erano lavori ripetitivi sul catalogo: riscrivere e tradurre le descrizioni dei prodotti, caricare le immagini e associarle, scegliere i prodotti suggeriti. Invece di fare un pannello apposta, abbiamo esposto come tool le action che già usava il backoffice. Chi gestisce il catalogo lo chiede a Claude: "riscrivi le descrizioni della categoria scarpe e suggerisci tre prodotti abbinati per ognuna", controlla la proposta e conferma. Stessa idea del talk: la logica c'era già, abbiamo aperto una porta in più.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-10">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">MCP · Non ne ho parlato</p>
            <h2>Quattro cose da guardare a casa</h2>
        </div>
        <div class="grid grid-cols-2 gap-8">
            <div class="card fragment flex flex-col gap-4">
                <div class="flex items-baseline gap-6">
                    <p class="font-mono text-[28px] text-red-600">Mcp::client()</p>
                    <h3>Chiamare server esterni</h3>
                </div>
                <p class="muted text-[30px]">laravel/mcp è anche un client: la nostra app usa i tool di un altro server MCP, configurato in <code>config/mcp.php</code>.</p>
                <p class="text-[26px]"><code>Mcp::client('github')->callTool(…)</code></p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <div class="flex items-baseline gap-6">
                    <p class="font-mono text-[28px] text-red-600">Pest</p>
                    <h3>Testare i tool</h3>
                </div>
                <p class="muted text-[30px]">Si chiama il tool dal server, senza HTTP e senza client.</p>
                <p class="text-[22px]"><code class="whitespace-nowrap">ConferenceServer::tool(SearchTalksTool::class, […])->assertSee(…)</code></p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <div class="flex items-baseline gap-6">
                    <p class="font-mono text-[28px] text-red-600">MCP Inspector</p>
                    <h3>Provarlo a mano</h3>
                </div>
                <p class="muted text-[30px]">Un'interfaccia web per elencare e chiamare tool, resource e prompt.</p>
                <p class="text-[26px]"><code>php artisan mcp:inspector mcp/conference</code></p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <div class="flex items-baseline gap-6">
                    <p class="font-mono text-[28px] text-red-600">ToolSearch</p>
                    <h3>Troppi tool</h3>
                </div>
                <p class="muted text-[30px]">Il client vede solo <code>search_tools</code> ed <code>execute_tools</code>: il modello cerca i tool che gli servono invece di caricarli tutti.</p>
                <p class="text-[26px]"><code>ToolSearch::class => [ShowAgendaTool::class, …]</code></p>
            </div>
        </div>
    </div>
    <aside class="notes">Quattro cose di MCP che non ho fatto in tempo a mostrare. [clic] laravel/mcp non è solo un server, è anche un client. Si registra un server esterno in config/mcp.php, con un URL o un comando locale, e da Mcp::client si elencano i tool e si chiamano con callTool. Così la nostra app può usare i tool di un altro servizio, per esempio GitHub o un server interno dell'azienda. [clic] I test. Ogni server ha dei metodi statici, tool, resource e prompt, che chiamano la primitiva direttamente, senza HTTP e senza client. Poi si fanno le asserzioni come su una risposta HTTP: assertOk, assertSee, assertHasErrors. Con actingAs si sceglie l'utente. Tutti i tool di questa demo hanno i loro test Pest. [clic] Per provarlo a mano c'è MCP Inspector, il client di debug ufficiale. Il comando artisan lo avvia già collegato al nostro server: si vedono i tool, si chiamano con i parametri che vogliamo e si leggono le risposte JSON. Attenzione: se il server è protetto, va passato il token nell'header Authorization, altrimenti l'Inspector prova a fare il login OAuth e fallisce. [clic] E se i tool diventano decine, ToolSearch: nell'array dei tool si mettono sotto la chiave ToolSearch quelli che non servono sempre. Il client vede solo due tool, search_tools per cercare nel catalogo ed execute_tools per chiamarli, e il modello carica solo quello che gli serve. Il prezzo: le annotazioni dei tool nel catalogo non arrivano al client, quindi i tool rischiosi è meglio lasciarli fuori. E con questo chiudiamo MCP: l'agente è nel client di qualcun altro. Ora lo portiamo dentro la nostra app, con AG-UI.</aside>
</section>
