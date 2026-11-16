<section>
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
    <aside class="notes">Prima di chiudere, la sicurezza, che vale per tutte le porte che abbiamo visto: MCP, AG-UI, A2UI e WebMCP. La regola è una: il modello non è un utente fidato, è un intermediario. [clic] Chi è l'utente lo dice il token, non il modello. Nei tool usiamo sempre $request->user(): se accettassimo uno user_id negli argomenti, basterebbe chiedere al modello di cambiarlo. [clic] Gli argomenti che manda il modello sono input come quelli di un form: si validano, con le stesse regole. [clic] La prompt injection: il modello legge testi che non abbiamo scritto noi, un abstract, un'email, una pagina web, o la risposta di un altro server MCP nella stessa conversazione. Quel testo può contenere istruzioni. Per questo le azioni distruttive, come svuotare l'agenda o prenotare, le deve confermare una persona: nella nostra chat la prenotazione passa da un form. [clic] Permessi minimi: esponiamo solo i tool che servono, e con shouldRegister li nascondiamo a chi non può usarli. Dentro, le policy e i gate restano quelli di sempre. [clic] Rate limiting: un agente non si stanca, può chiamare un tool cento volte di fila. Un middleware throttle sulla rotta MCP e su quella dell'agente costa una riga. [clic] E l'interfaccia: con A2UI il modello sceglie da un catalogo di componenti nostri e li riempie con dati, non genera HTML o JavaScript da iniettare nella pagina. Anche quando compone da solo la dashboard, l'albero passa dal validatore prima di arrivare al browser.</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Ricapitolando</p>
            <h2>Quattro porte, una riga di codice</h2>
        </div>
        <table class="w-full text-left text-[34px]">
            <thead class="font-mono text-[26px] text-zinc-500">
                <tr>
                    <th class="pb-4">Porta</th>
                    <th class="pb-4">Chi entra</th>
                    <th class="pb-4">Cosa abbiamo scritto</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 font-mono">Web</td><td>una persona</td><td>niente di nuovo</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 font-mono">MCP</td><td>Claude, Codex…</td><td>una classe per tool</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 font-mono">AG-UI</td><td>la chat nella pagina</td><td>un controller, un file JS</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 font-mono">A2UI</td><td>la stessa chat</td><td>un builder di JSON</td></tr>
                <tr class="border-t-2 border-zinc-200"><td class="py-4 font-mono">WebMCP</td><td>l'agente del browser</td><td>uno snippet JS</td></tr>
            </tbody>
        </table>
    </div>
    <aside class="notes">Tutte arrivano ad AgendaService::add().</aside>
</section>

<section>
    <div class="flex h-full flex-col gap-14">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">Da portare a casa</p>
            <h2>Tre cose</h2>
        </div>
        <div class="grid flex-1 grid-cols-3 gap-10">
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[56px] text-red-600">1</p>
                <h3>La logica sta nei service</h3>
                <p class="muted text-[32px]">Controller, tool e agenti si limitano a chiamarla.</p>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[56px] text-red-600">2</p>
                <h3>Un tool si scrive una volta</h3>
                <p class="muted text-[32px]">Lo stesso per il client MCP e per la chat.</p>
            </div>
            <div class="card fragment flex flex-col gap-6">
                <p class="font-mono text-[56px] text-red-600">3</p>
                <h3>L'app resta al comando</h3>
                <p class="muted text-[32px]">Utente dal token, conferme umane, componenti dal nostro catalogo.</p>
            </div>
        </div>
    </div>
</section>

<section data-background-color="#18181b" class="divider">
    <div class="flex h-full flex-col justify-center gap-12">
        <p class="eyebrow">LaravelDay 2026 · Verona · 18 novembre</p>
        <h1>Grazie!</h1>
        <p class="font-mono text-[44px]"><a href="https://github.com/datomatic/laravelday-2026" target="_blank">github.com/datomatic/laravelday-2026</a></p>
        <p class="text-[36px] text-zinc-300">Domande?</p>
    </div>
</section>
