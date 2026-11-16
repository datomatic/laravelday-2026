<section data-background-color="#dc2626" class="divider">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Porta 3</p>
        <h1>A2UI</h1>
        <p class="text-[48px]">L'agente compone l'interfaccia, l'app decide come mostrarla.</p>
    </div>
</section>

<section>
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Interfaccia dichiarativa</p>
            <h2>JSON dal server, componenti nel browser</h2>
        </div>
        <ol class="flex flex-col gap-6 text-[36px]">
            <li class="fragment">Il modello chiama <code>show_talk_cards</code> con gli id trovati</li>
            <li class="fragment">PHP costruisce i messaggi A2UI: <code>A2uiSurfaceBuilder</code></li>
            <li class="fragment">Viaggiano nell'evento AG-UI <code>TOOL_CALL_RESULT</code></li>
            <li class="fragment"><code>&lt;a2ui-surface&gt;</code> li disegna con il catalogo dell'app</li>
            <li class="fragment">Un clic su «Aggiungi» chiama <code>POST /agenda</code>, senza ripassare dal modello</li>
        </ol>
    </div>
    <aside class="notes">[clic] Nella chat generativa il modello ha dei tool in più, come show_talk_cards, e lo chiama con gli id che ha trovato con search_talks. [clic] Il tool non restituisce testo: A2uiSurfaceBuilder costruisce in PHP i messaggi A2UI, cioè l'elenco dei componenti con i dati dentro. [clic] Arrivano al browser dentro l'evento AG-UI TOOL_CALL_RESULT: A2UI non ha bisogno di un altro canale. [clic] Il renderer Lit di A2UI, l'elemento a2ui-surface, li disegna con i componenti del catalogo e con il nostro stile. [clic] I bottoni non ripassano dal modello: il clic su Aggiungi chiama la stessa rotta POST /agenda del pulsante della pagina classica. Qui il modello sceglie quale scheda mostrare, ma il layout l'abbiamo scritto noi. Tra poco vediamo cosa succede quando lo scrive il modello.</aside>
</section>

<section data-background-color="#1c1b22" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">app/Services/A2uiSurfaceBuilder.php</p>
            <h2>Una scheda è un albero piatto</h2>
        </div>
        @verbatim
        <pre><code class="language-php" data-trim data-line-numbers="1|2|3|4|5-6"><script type="text/template">
['id' => $key, 'component' => 'Card', 'child' => "{$key}_content"],
['id' => "{$key}_content", 'component' => 'Column', 'children' => [...]],
['id' => "{$key}_title", 'component' => 'Text', 'text' => "#### {$title}"],
['id' => "{$key}_meta", 'component' => 'Row', 'justify' => 'spaceBetween', 'children' => [...]],
['id' => "{$key}_button", 'component' => 'Button', 'variant' => 'primary',
    'child' => "{$key}_button_label", 'action' => ['event' => $action]],
</script></code></pre>
        @endverbatim
        <p class="muted text-[34px]">Catalogo base più un <code>Chart</code> nostro, con Chart.js: il catalogo è il contratto tra agente e app.</p>
    </div>
    <aside class="notes">Una scheda A2UI è una lista piatta di componenti che si riferiscono tra loro per id. La radice è una Card con un figlio, [clic] una Column con i suoi figli. [clic] Il titolo è un Text, con il Markdown per il grassetto. [clic] Una Row mette orario e track sulla stessa riga. [clic] Il bottone ha un'azione: un evento con nome e contesto, che la pagina riceve e gestisce. Il catalogo base ha card, righe, colonne, testi, bottoni e campi di form. Per grafici, KPI e tabelle abbiamo aggiunto tre componenti Lit con derive(): Chart, che disegna con Chart.js, Stat e DataTable. Il catalogo è il contratto: l'agente può usare solo quello che c'è dentro.</aside>
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

<section data-background-color="#1c1b22" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">app/Neuron/Tools/RenderSurface.php</p>
            <h2>L'interfaccia la scrive il modello</h2>
        </div>
        @verbatim
        <pre><code class="language-php" data-trim data-line-numbers="1|3|5-9|11"><script type="text/template">
public function __invoke(string $components): string
{
    $decoded = json_decode($components, true);

    $errors = $this->validator->errors($decoded);

    if ($errors !== []) {
        return "Errore: l'interfaccia non è valida. Correggi e richiama render_surface.\n- ".implode("\n- ", $errors);
    }

    return json_encode(['a2ui' => $this->surfaces->generated($decoded)]);
}
</script></code></pre>
        @endverbatim
        <p class="muted text-[34px]">I dati arrivano da <code>get_statistics</code>, in sola lettura. Il catalogo nelle istruzioni: <code>Stat</code>, <code>Chart</code>, <code>DataTable</code>, più quelli di base.</p>
    </div>
    <aside class="notes">Il tool è uno solo, render_surface, e riceve l'albero dei componenti come JSON. [clic] Lo decodifichiamo. [clic] Prima di mandarlo al browser lo validiamo: ogni componente deve essere nel catalogo, avere le proprietà giuste, e i figli devono esistere. Se qualcosa non va, non lanciamo un'eccezione: restituiamo gli errori al modello come risultato del tool. Il modello li legge, corregge l'albero e richiama il tool. [clic] Se è valido, lo avvolgiamo in una surface A2UI e la chat lo disegna come le schede di prima. Il modello non tocca mai il database direttamente: legge i numeri con get_statistics, sette report fissi in sola lettura. Nelle istruzioni gli descriviamo il catalogo: i componenti di base, più tre nostri, Stat per i KPI, Chart per i grafici con Chart.js e DataTable.</aside>
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
