import { postJson } from './live-regions';

/**
 * WebMCP (Chrome origin trial): the page itself exposes a tool to the browser's agent.
 * It calls the same Laravel route as the "+ Agenda" button, with the user's session.
 * The API surface moved from navigator.modelContext to document.modelContext during the trial.
 */
const modelContext = document.modelContext ?? navigator.modelContext;
// Only the schedule has talks to add: every stage, the classic page included.
const talks = document.getElementById('talks');

if (modelContext?.registerTool && talks) {
    modelContext.registerTool({
        name: 'add_to_agenda',
        description: "Aggiunge un talk all'agenda personale dell'utente loggato. L'id del talk è nell'attributo data-talk-id di ogni talk della pagina.",
        inputSchema: {
            type: 'object',
            properties: { talk_id: { type: 'integer', description: 'Id del talk da aggiungere.' } },
            required: ['talk_id'],
        },
        async execute({ talk_id }) {
            const message = await postJson(talks.dataset.agendaUrl, { talk_id });

            return { content: [{ type: 'text', text: message }] };
        },
    });
}
