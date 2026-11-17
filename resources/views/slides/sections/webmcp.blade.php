<section data-background-color="#dc2626" class="divider">
    <div class="flex h-full flex-col justify-center gap-10">
        <p class="eyebrow">Porta 3 · E domani</p>
        <h1>WebMCP</h1>
        <p class="text-[48px]">L'agente del browser usa la pagina, con la sessione dell'utente.</p>
    </div>
    <aside class="notes">L'ultima porta, e la più nuova. Con MCP l'agente stava nel client, con la chat nella nostra pagina. Con WebMCP sta nel browser: è una proposta di standard web, oggi sperimentale in Chrome.</aside>
</section>

<section data-background-color="#ffffff" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">WebMCP · sperimentale</p>
            <h2>La pagina dichiara i tool al browser</h2>
        </div>
        @verbatim
        <pre><code class="language-js" data-trim data-line-numbers="1|3-11|12-16"><script type="text/template">
const modelContext = document.modelContext ?? navigator.modelContext;

modelContext.registerTool({
    name: 'add_to_agenda',
    description: "Aggiunge un talk all'agenda personale dell'utente loggato.",
    inputSchema: {
        type: 'object',
        properties: { talk_id: { type: 'integer' } },
        required: ['talk_id'],
    },
    async execute({ talk_id }) {
        const message = await postJson(talks.dataset.agendaUrl, { talk_id });

        return { content: [{ type: 'text', text: message }] };
    },
});
</script></code></pre>
        @endverbatim
        <p class="muted text-[32px]">Sperimentale in Chrome, oggi da attivare a mano. Stessa rotta del pulsante, stessa sessione dell'utente.</p>
    </div>
    <aside class="notes">La pagina dichiara i suoi tool al browser. Prendiamo l'oggetto modelContext: durante la sperimentazione l'API è cambiata, quindi lo cerchiamo in due posti. [clic] registerTool vuole le stesse cose di un tool MCP: un nome, una descrizione per il modello e lo schema dei parametri. [clic] execute è il codice che gira quando l'agente lo chiama: una fetch alla stessa rotta del pulsante + Agenda, con la sessione e il CSRF dell'utente già loggato. Niente token, niente server MCP: l'utente è già lì. Non faccio la demo perché va attivata a mano nel browser, ma il punto è lo stesso di tutto il talk: anche questa porta arriva alla stessa azione.</aside>
</section>
