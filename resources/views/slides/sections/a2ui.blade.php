<section data-background-color="#dc2626" class="divider" data-flyby="car">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Porta 3</p>
        <h1>A2UI</h1>
        <p class="text-[48px]">L'agente compone l'interfaccia, l'app decide come mostrarla.</p>
    </div>
    <aside class="notes">La terza porta: A2UI. Finora l'agente risponde con del testo: ora gli facciamo comporre l'interfaccia.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Cos'è A2UI · Agent to User Interface</p>
            <h2>L'agente sceglie dal catalogo, l'app disegna</h2>
        </div>
        <div class="flex flex-col gap-8">
            <p class="max-w-[1650px] text-[36px]">Un protocollo aperto, nato in Google. L'app dichiara un <b>catalogo di componenti</b>; l'agente risponde con un <b>JSON</b> che sceglie i componenti da quel catalogo e li riempie di dati. Il client li disegna con i suoi componenti e il suo stile.</p>
            <p class="muted text-[26px]">Il nostro catalogo: <b>base di A2UI</b> e <b class="text-red-700">aggiunti da noi</b></p>
            <div class="-mt-4 flex flex-wrap items-center gap-4 font-mono text-[26px]">
                @foreach (['Card', 'Column', 'Row', 'Text', 'Image', 'Button', 'TextField', 'Modal', 'Tabs'] as $component)
                    <span class="rounded-xl border-2 border-zinc-300 bg-white px-4 py-1">{{ $component }}</span>
                @endforeach
                @foreach (['Chart', 'Stat', 'DataTable'] as $component)
                    <span class="rounded-xl border-2 border-red-600 bg-red-100 px-4 py-1 text-red-700">{{ $component }}</span>
                @endforeach
            </div>
            <p class="muted text-[26px]">Tutti i componenti di base: <a href="https://a2ui.org/specification/v1.0-basic-catalog-implementation-guide/" target="_blank" class="font-mono text-red-700 underline">a2ui.org</a></p>
        </div>
        <div class="grid grid-cols-2 gap-10">
            <div class="card fragment flex flex-col gap-4">
                <p class="font-mono text-[26px] text-red-600">AG-UI</p>
                <h3>È il canale</h3>
                <p class="muted text-[28px]">Porta gli eventi della chat: testo, tool, conferme. Non dice niente sull'aspetto.</p>
            </div>
            <div class="card fragment flex flex-col gap-4 border-red-600!">
                <p class="font-mono text-[26px] text-red-600">A2UI</p>
                <h3>Mostra i dati con i tuoi componenti</h3>
                <p class="muted text-[28px]">Solo componenti del catalogo e dati, nessun codice da eseguire. Viaggia dentro AG-UI.</p>
            </div>
        </div>
    </div>
    <aside class="notes">Che cos'è A2UI. Sta per Agent to User Interface: è un protocollo aperto, nato in Google, per far mostrare all'agente delle interfacce invece del solo testo. Il punto chiave è il catalogo. L'app dichiara quali componenti sa disegnare: card, colonne, righe, testi, immagini, bottoni, campi di form, modali, tab. Questi grigi sono il catalogo base di A2UI; i tre rossi, Chart, Stat e DataTable, li abbiamo aggiunti noi. L'agente non scrive HTML: risponde con un JSON che sceglie componenti da quel catalogo e li riempie di dati, per esempio una card con dentro un titolo e un bottone. Poi è l'app che li disegna, con i suoi componenti e il suo stile: sul web con Lit, come facciamo noi, ma lo stesso JSON può diventare un'interfaccia Flutter o Angular. E AG-UI, che abbiamo appena visto? [clic] Non è un'alternativa: è il canale. Porta gli eventi della chat, il testo, le chiamate ai tool, le conferme, ma non dice niente su come appare l'interfaccia. [clic] A2UI è il pezzo che mancava: mostra i dati con i componenti della nostra app. Manda solo dati, componenti del catalogo riempiti con i nostri valori. Non c'è codice da eseguire, quindi è sicuro anche quando il JSON lo scrive un modello, e l'interfaccia sembra nativa. E viaggia dentro AG-UI, nel risultato di un tool. Vediamo come lo usiamo nella nostra chat.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">A2UI nella nostra chat</p>
            <h2>Il tool restituisce componenti, non testo</h2>
        </div>
        <ol class="flex items-center gap-4 text-[24px]">
            <li class="flow-step">il modello chiama <code>show_talk_cards</code></li>
            <li class="text-zinc-500">→</li>
            <li class="flow-step"><code>A2uiSurfaceBuilder</code> → JSON</li>
            <li class="text-zinc-500">→</li>
            <li class="flow-step">evento <code>TOOL_CALL_RESULT</code></li>
            <li class="text-zinc-500">→</li>
            <li class="flow-step"><code>&lt;a2ui-surface&gt;</code> disegna</li>
        </ol>
        <div class="flex flex-1 items-center gap-16">
            {{-- Steps: all lines, then one line per click. data-fragment-index="0" ties each line to the mockup part with the same index. --}}
            @verbatim
            <pre class="flat-tree-code min-w-0 flex-1"><code class="language-php" data-trim data-line-numbers="|1|2|3|4|5" data-fragment-index="0"><script type="text/template">
['id' => 'card', 'component' => 'Card', 'child' => 'col'],
['id' => 'col', 'component' => 'Column', 'children' => ['title', 'row', 'btn']],
['id' => 'title', 'component' => 'Text', 'text' => '#### RAG con pgvector'],
['id' => 'row', 'component' => 'Row', 'children' => ['time', 'track']],
['id' => 'btn', 'component' => 'Button', 'child' => 'label', 'action' => [...]],
// time, track e label: altri tre Text
</script></code></pre>
            @endverbatim
            <div class="flat-tree-mock mock-part fragment" data-fragment-index="0" data-label="card · Card">
                <div class="mock-part mock-column fragment" data-fragment-index="1" data-label="col · Column">
                    <p class="mock-part mock-title fragment" data-fragment-index="2" data-label="title · Text">RAG con pgvector</p>
                    <div class="mock-part mock-row fragment" data-fragment-index="3" data-label="row · Row">
                        <span>10:20–10:55 · Sala Arena</span>
                        <span class="mock-badge">AI</span>
                    </div>
                    <div class="mock-part fragment" data-fragment-index="4" data-label="btn · Button">
                        <span class="mock-button">Aggiungi all'agenda</span>
                    </div>
                </div>
            </div>
        </div>
        <p class="muted text-[30px]">Una lista piatta: ogni componente ha un id e indica i figli per id. Il clic su «Aggiungi» chiama <code>POST /agenda</code>, senza ripassare dal modello.</p>
    </div>
    <aside class="notes">Come arriva A2UI nella nostra chat. In alto il giro completo. Nella chat generativa il modello ha dei tool in più, come show_talk_cards, e lo chiama con gli id dei talk che ha trovato. Il tool non restituisce testo: A2uiSurfaceBuilder costruisce in PHP il JSON A2UI, con i dati dentro. Arriva al browser nell'evento AG-UI TOOL_CALL_RESULT, e il renderer Lit, l'elemento a2ui-surface, lo disegna con i componenti del catalogo. Qui sotto, semplificato, il JSON di una scheda: a destra come la vede l'utente. Non è annidato, è una lista piatta: ogni componente ha un id e indica i figli per id. [clic] La radice è una Card, con un solo figlio: col. [clic] col è una Column: mette uno sotto l'altro titolo, riga e bottone. [clic] Il titolo è un Text, con il Markdown per farlo grande. [clic] row è una Row: orario e badge della track sulla stessa riga. [clic] E il bottone, con la sua etichetta e un'azione: un evento con nome e contesto. Il clic su Aggiungi non ripassa dal modello: la pagina riceve l'evento e chiama la stessa POST /agenda del pulsante classico. Perché una lista piatta? Si aggiorna un pezzo mandando solo quel componente, si può mandare in streaming, e per un modello è più facile da scrivere giusta di un JSON annidato. Qui il modello sceglie solo quale scheda mostrare: il layout l'abbiamo scritto noi. Tra poco vediamo cosa succede quando lo scrive il modello.</aside>
</section>

<section data-background-color="#18181b" class="demo">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Demo · Passaggio {{ App\Enums\DemoStage::Generative->step() }}</p>
        <h2>Schede, moduli e grafici</h2>
        <p class="text-[40px] text-zinc-300">«Quali talk parlano di AI?» · «Chi è lo speaker?» · «Quanti iscritti ai workshop?»</p>
        <p class="text-[32px]"><a href="{{ App\Enums\DemoStage::Generative->url() }}">Apri la chat generativa →</a></p>
    </div>
    <aside class="notes">Schede dei talk con aggiunta all'agenda: cliccare Aggiungi e far vedere che l'agenda si aggiorna senza ripassare dal modello. Poi la scheda dello speaker, il modulo di prenotazione, e il grafico dei workshop con prenotati e capienza.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Generative UI</p>
            <h2>Chi sceglie i componenti?</h2>
        </div>
        <div class="grid grid-cols-3 gap-10">
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[26px] text-red-600">show_talk_cards</p>
                <h3>Il codice</h3>
                <p class="muted text-[28px]">Il modello sceglie il tool, il layout è scritto in PHP. È la chat che abbiamo appena visto.</p>
                <ul class="flex flex-col gap-3 text-[26px]">
                    <li><span class="text-green-700">+</span> sempre uguale, testabile</li>
                    <li><span class="text-red-600">−</span> un tool per ogni scheda</li>
                </ul>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[26px] text-red-600">render_chart · render_table</p>
                <h3>Il modello sceglie il componente</h3>
                <p class="muted text-[28px]">Pochi componenti generici: il modello decide quale usare e con quali dati.</p>
                <ul class="flex flex-col gap-3 text-[26px]">
                    <li><span class="text-green-700">+</span> risponde a domande nuove</li>
                    <li><span class="text-red-600">−</span> un componente alla volta</li>
                </ul>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[26px] text-red-600">render_surface</p>
                <h3>Il modello compone l'interfaccia</h3>
                <p class="muted text-[28px]">Il modello scrive tutto l'albero A2UI: righe, tab, KPI, grafici e tabelle.</p>
                <ul class="flex flex-col gap-3 text-[26px]">
                    <li><span class="text-green-700">+</span> una dashboard per ogni domanda</li>
                    <li><span class="text-red-600">−</span> va validato, più token, risultato meno prevedibile</li>
                </ul>
            </div>
        </div>
    </div>
    <aside class="notes">Abbiamo visto componenti generati dall'agente, ma chi li ha scelti davvero? [clic] Nella chat di prima, il modello sceglie solo quale tool chiamare: show_talk_cards. Il layout della scheda l'abbiamo scritto noi in PHP. È la soluzione più sicura: la scheda è sempre uguale, la testiamo, e i bottoni fanno quello che diciamo noi. Ma per ogni nuova scheda serve un tool nuovo. [clic] Un passo avanti: diamo al modello pochi componenti generici, una card con dei numeri, un grafico, una tabella, e lui decide quale usare e con quali dati. È l'approccio della demo generative-ui di Neuron AI: un analista BI che legge il database e risponde con grafici e tabelle. [clic] L'ultimo passo: il modello scrive l'intera interfaccia. Gli diamo il catalogo dei componenti e lui compone l'albero A2UI come vuole: una riga di KPI, delle tab, un grafico, una tabella. È il caso giusto per un pannello admin, dove le domande non si possono prevedere. Il prezzo: l'albero va validato prima di mandarlo al browser, costa più token, e due domande uguali possono dare due layout diversi.</aside>
</section>

<section data-background-color="#18181b" class="demo">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Demo · Passaggio {{ App\Enums\DemoStage::Admin->step() }}</p>
        <h2>Una dashboard a richiesta</h2>
        <p class="text-[40px] text-zinc-300">«Fammi una dashboard delle iscrizioni ai workshop» · «Quali talk sono più seguiti, per track?»</p>
        <p class="text-[32px]"><a href="{{ App\Enums\DemoStage::Admin->url() }}">Apri la dashboard admin →</a></p>
    </div>
    <aside class="notes">Pagina /admin, solo per l'organizzatore. Prima domanda: dashboard dei workshop, di solito KPI in alto, grafico e tabella. Aprire il pannello eventi e mostrare get_statistics chiamato più volte, poi render_surface con l'albero intero. Seconda domanda: talk per track, il layout cambia. Se il modello sbaglia l'albero, si vede l'errore tornare come risultato e il secondo tentativo.</aside>
</section>

<section>
    <div class="flex h-full items-center gap-20">
        <div class="flex flex-1 flex-col gap-10">
            <div class="flex flex-col gap-6">
                <p class="eyebrow">Torniamo a MCP · MCP Apps</p>
                <h2>Un tool può avere un'interfaccia</h2>
            </div>
            <ul class="flex flex-col gap-6 text-[30px]">
                <li>Un attributo sul tool, <code>#[RendersApp]</code>, collega una vista Blade</li>
                <li><code>list_workshops</code> risponde con i dati, il client mostra la nostra pagina</li>
                <li>Il bottone <b>Prenota</b> chiama <code>book_workshop</code></li>
                <li class="muted">Gira in un iframe isolato: niente cookie, niente richieste non dichiarate</li>
            </ul>
        </div>
        <img src="{{ asset('images/slides/mcp-app-claude.png') }}" alt="Claude Desktop: alla domanda «Quali workshop ci sono?» il tool list_workshops mostra le card dei workshop con orario, speaker, posti, ruolo, azienda e bottone Prenota" class="aspect-[1570/1190] w-[1000px] shrink-0 rounded-3xl object-cover object-top shadow-2xl">
    </div>
    <aside class="notes">Prima di chiudere con l'ultima porta, torniamo un attimo a MCP, perché c'è un'altra strada per mostrare un'interfaccia: le MCP Apps. Di solito è il client a decidere come mostrare i risultati di un tool. Le MCP Apps sono un'estensione ufficiale del protocollo che cambia questo: un tool può portarsi dietro un pezzo di interfaccia HTML, che il client mostra dentro la chat. Basta un attributo sul tool, RendersApp, che punta a una AppResource: restituisce una vista Blade, con un URI ui://. Ecco come appare in Claude Desktop. Chiedo «quali workshop ci sono?»: il modello chiama list_workshops, e sotto compare la nostra app con le card dei workshop. Orari, speaker e posti arrivano dal risultato del tool; il form con ruolo e azienda è quello della nostra vista Blade. Clicco Prenota: la pagina chiama book_workshop, i posti scendono di uno. Il secondo workshop è esaurito, quindi il bottone è disattivato: anche questa logica è nostra, non del modello. Il client la mostra in un iframe isolato: niente cookie della nostra app, niente richieste esterne se non le dichiariamo. È lo stesso form di prenotazione che abbiamo visto con A2UI, ma con una differenza importante: con A2UI mandavamo una descrizione, solo dati, e la disegnava la nostra pagina. Qui mandiamo una pagina intera, HTML e JavaScript scritti da noi, che gira dentro il client di qualcun altro. Un dettaglio pratico: con mcp-remote in Claude Desktop va tolto il dominio dedicato, con AppMeta domain false, altrimenti l'app non si vede.</aside>
</section>
