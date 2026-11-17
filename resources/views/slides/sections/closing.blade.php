<section data-flyby="plane">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Sicurezza · vale per tutte le porte</p>
            <h2>Il modello non è un utente fidato</h2>
        </div>
        <div class="grid flex-1 grid-cols-3 gap-8">
            <div class="card fragment flex flex-col gap-4">
                <h3>L'utente viene dal token</h3>
                <p class="muted text-[28px]">Sempre <code>$request->user()</code>, mai uno <code>user_id</code> negli argomenti.</p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <h3>Input validato</h3>
                <p class="muted text-[28px]">Gli argomenti del modello si validano come un form.</p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <h3>Prompt injection</h3>
                <p class="muted text-[28px]">Un testo letto dal modello può dargli ordini: le azioni distruttive le conferma una persona.</p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <h3>Permessi minimi</h3>
                <p class="muted text-[28px]">Si espone solo il necessario: <code>shouldRegister()</code> e le policy di sempre.</p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <h3>Rate limiting</h3>
                <p class="muted text-[28px]">Un agente chiama un tool cento volte di fila: <code>throttle</code> sulla rotta.</p>
            </div>
            <div class="card fragment flex flex-col gap-4">
                <h3>Interfaccia dal catalogo</h3>
                <p class="muted text-[28px]">Con A2UI il modello sceglie i componenti, non scrive HTML.</p>
            </div>
        </div>
    </div>
    <aside class="notes">Prima di chiudere, la sicurezza, che vale per tutte le porte che abbiamo visto: MCP, AG-UI, A2UI e WebMCP. La regola è una: il modello non è un utente fidato, è un intermediario. [clic] Chi è l'utente lo dice il token, non il modello. Nei tool usiamo sempre $request->user(): se accettassimo uno user_id negli argomenti, basterebbe chiedere al modello di cambiarlo. [clic] Gli argomenti che manda il modello sono input come quelli di un form: si validano, con le stesse regole. [clic] La prompt injection: il modello legge testi che non abbiamo scritto noi, un abstract, un'email, una pagina web, o la risposta di un altro server MCP nella stessa conversazione. Quel testo può contenere istruzioni. Per questo le azioni che contano, come svuotare l'agenda o prenotare, le deve confermare una persona: nella nostra chat lo impone il server, con Approva e Rifiuta. [clic] Permessi minimi: esponiamo solo i tool che servono, e con shouldRegister li nascondiamo a chi non può usarli. Dentro, le policy e i gate restano quelli di sempre. [clic] Rate limiting: un agente non si stanca, può chiamare un tool cento volte di fila. Un middleware throttle sulla rotta MCP e su quella dell'agente costa una riga. [clic] E l'interfaccia: con A2UI il modello sceglie da un catalogo di componenti nostri e li riempie con dati, non genera HTML o JavaScript da iniettare nella pagina. Anche quando compone da solo la dashboard, l'albero passa dal validatore prima di arrivare al browser.</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Ricapitolando</p>
            <h2>Quattro porte, la stessa azione</h2>
        </div>
        <table class="w-full text-left text-[30px]">
            <thead class="font-mono text-[24px] text-zinc-500">
                <tr>
                    <th class="w-[340px] pb-4">Porta</th>
                    <th class="w-[720px] pb-4">Quando sceglierla</th>
                    <th class="pb-4">Cosa abbiamo scritto</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-8 font-mono whitespace-nowrap">Web</td><td class="pr-8">sempre, per le persone</td><td>niente di nuovo</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-8 font-mono whitespace-nowrap">MCP</td><td class="pr-8">i tuoi utenti usano già un assistente</td><td>una classe per tool (+ Blade per le MCP Apps)</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-8 font-mono whitespace-nowrap">AG-UI + A2UI</td><td class="pr-8">l'agente nella tua pagina, con le tue regole</td><td>un controller, un file JS, un builder di JSON</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 pr-8 font-mono whitespace-nowrap">WebMCP</td><td class="pr-8">sperimentale, da tenere d'occhio</td><td>uno snippet JS</td></tr>
            </tbody>
        </table>
        <p class="fragment text-[40px]"><span class="text-red-600">→</span> tutte chiamano <code>AgendaService::add()</code></p>
    </div>
    <aside class="notes">Ricapitoliamo, con una domanda pratica: quale porta scelgo? La pagina web per le persone c'è sempre, e non abbiamo scritto niente di nuovo. MCP quando i vostri utenti usano già Claude, Codex o un altro assistente: non dovete scrivere un'interfaccia, basta una classe per tool, più una vista Blade se volete una MCP App. AG-UI e A2UI quando volete l'agente dentro la vostra pagina, con le conferme e i componenti decisi da voi: un controller, un file JavaScript e un builder di JSON. WebMCP è sperimentale: uno snippet JavaScript, da tenere d'occhio. [clic] E soprattutto: quattro porte, e tutte arrivano alla stessa riga, AgendaService::add().</aside>
</section>

<section>
    <div class="flex h-full flex-col justify-center gap-14">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Per chiudere</p>
            <h2>Tre cose da portare a casa</h2>
        </div>
        <div class="grid grid-cols-3 gap-10">
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[56px] text-red-600">1</p>
                <h3>Non reinventare il protocollo</h3>
                <p class="muted text-[32px]">Streaming, conferme e interfaccia fatti in casa li capisce solo la tua pagina. Gli standard li hanno già risolti.</p>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[56px] text-red-600">2</p>
                <h3>Scrivi i tool per il modello</h3>
                <p class="muted text-[32px]">Nome, descrizione e schema sono tutto ciò che sa della tua app. Sono il prompt.</p>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[56px] text-red-600">3</p>
                <h3>Decidi quanto lasciare al modello</h3>
                <p class="muted text-[32px]">Layout scritto da te, componenti scelti da lui o interfaccia intera: più libertà, più controlli.</p>
            </div>
        </div>
        <p class="fragment text-[36px]"><span class="text-red-600">Lunedì:</span> un server MCP con un tool in sola lettura, e provatelo in Claude.</p>
    </div>
    <aside class="notes">Se vi portate a casa tre cose. [clic] Prima: non reinventate il protocollo. La chat fatta in casa funzionava, ma lo streaming, le conferme e l'interfaccia li avevamo inventati noi: li capiva solo la nostra pagina, e la conferma la chiedeva il modello a parole. MCP, AG-UI e A2UI hanno già risolto questi problemi, e vi rendono compatibili con client che non avete scritto voi. [clic] Seconda: scrivete i tool per il modello. Il modello non vede il vostro codice: conosce la vostra app solo dal nome, dalla descrizione e dallo schema di ogni tool. Sono il suo prompt. Una descrizione che dice «se non conosci ruolo e azienda chiedili, non inventarli» vale più di dieci righe di codice difensivo. Scrivetele come la documentazione per un collega appena arrivato. [clic] Terza: decidete quanto lasciare al modello. Abbiamo visto tre livelli: la scheda scritta da noi in PHP, il modello che sceglie un componente, il modello che compone tutta la dashboard. Non c'è un livello giusto: per le azioni dei nostri utenti vogliamo schede prevedibili e testate, per un pannello admin con domande imprevedibili possiamo lasciargli comporre l'interfaccia. Più libertà gli diamo, più dobbiamo validare quello che produce. [clic] E se volete fare una cosa sola lunedì: un server MCP con un tool in sola lettura, una ricerca o un elenco. Lo collegate a Claude e vedete la vostra app usata da un agente.</aside>
</section>

<section data-background-color="#18181b" class="divider">
    <div class="flex h-full items-center justify-between gap-24">
        <div class="flex flex-col gap-12">
            <p class="eyebrow">LaravelDay 2026 · Verona · 18 novembre</p>
            <h1>Grazie!</h1>
            <div class="flex flex-col gap-6 font-mono text-[34px] whitespace-nowrap">
                <a href="https://github.com/datomatic/laravelday-2026" target="_blank">github.com/datomatic/laravelday-2026</a>
                <a href="https://albertoperipolli.com" target="_blank">albertoperipolli.com</a>
            </div>
            <p class="text-[36px] text-zinc-300">Domande?</p>
        </div>
        <div class="flex shrink-0 flex-col items-center gap-10">
            <div class="flex gap-10">
                <figure class="flex flex-col items-center gap-4">
                    <img src="{{ asset('images/slides/qr-repo.png') }}" alt="QR code per github.com/datomatic/laravelday-2026" class="size-[200px] rounded-2xl bg-white p-4">
                    <figcaption class="font-mono text-[22px] text-zinc-300">Codice e slide</figcaption>
                </figure>
                <figure class="flex flex-col items-center gap-4">
                    <img src="{{ asset('images/slides/qr-alberto.png') }}" alt="QR code per albertoperipolli.com" class="size-[200px] rounded-2xl bg-white p-4">
                    <figcaption class="font-mono text-[22px] text-zinc-300">albertoperipolli.com</figcaption>
                </figure>
            </div>
            <figure class="flex flex-col items-center gap-4">
                <img src="{{ asset('images/slides/qr-review.png') }}" alt="QR code per lasciare una recensione del talk" class="size-[440px] rounded-3xl bg-white">
                <figcaption class="text-[32px] text-white">Com'è andato il talk? Lascia una recensione</figcaption>
            </figure>
        </div>
    </div>
    <aside class="notes">Grazie. Il QR grande serve per lasciare una recensione del talk: mi aiuta molto, bastano trenta secondi. Sopra, il codice della demo e le slide su GitHub, e il mio sito con i contatti. Domande?</aside>
</section>
