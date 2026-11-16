<section data-background-color="#1c1b22" class="code-slide">
    <div class="flex h-full flex-col gap-12">
        <div class="flex flex-col gap-6">
            <p class="eyebrow">E domani · WebMCP</p>
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
        <p class="muted text-[32px]">Origin trial in Chrome. Stessa rotta del pulsante, stessa sessione dell'utente.</p>
    </div>
    <aside class="notes">Nessuna demo: è sperimentale e l'API ha già cambiato posto durante il trial. Il punto è che anche qui chiamiamo la stessa azione.</aside>
</section>
