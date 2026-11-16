import { HttpAgent, buildResumeArray } from '@ag-ui/client';
import { MessageProcessor, basicCatalog, setMarkdownRenderer } from '@a2ui/web_core/v1_0';
import '@a2ui/lit'; // registers the <a2ui-surface> element
import { conferenceCatalog } from './a2ui/conference-catalog';
import { renderMarkdown } from '@a2ui/markdown-it';
import { csrfToken, postJson, refreshLiveRegions } from './live-regions';
import { createEventLog } from './ag-ui-log';
import { readHomemadeEvents } from './homemade-sse';
import { setupChatWindow } from './chat-window';

// A2UI Text components may carry Markdown: render it with markdown-it, sanitized by DOMPurify.
setMarkdownRenderer(renderMarkdown);

const chat = document.getElementById('chat');

if (chat) {
    const messages = document.getElementById('chat-messages');
    const events = document.getElementById('chat-events');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');

    // Tool results that carry A2UI messages instead of text for the model to summarise.
    const A2UI_TOOLS = ['show_talk_cards', 'show_workshop_cards', 'show_booking_form', 'show_speaker_card', 'show_chart', 'render_surface'];
    // Tools that change server state: the page regions are refreshed when they complete.
    const MUTATING_TOOLS = ['add_to_agenda', 'clear_agenda', 'book_workshop'];

    const agent = new HttpAgent({
        url: chat.dataset.agentUrl,
        // Minted by the server: Neuron stores the conversation and the paused runs under this id.
        threadId: chat.dataset.threadId,
        headers: { 'X-CSRF-TOKEN': csrfToken() },
    });

    // crypto.randomUUID() only exists in secure contexts (https or localhost), not on http://laravelday.test.
    const messageId = () => crypto.randomUUID?.() ?? `${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;

    const logEvent = createEventLog(events, chat.dataset.protocol);
    const toolNames = {};
    let assistantBubble = null;

    const append = (element) => {
        messages.append(element);
        messages.scrollTop = messages.scrollHeight;

        return element;
    };

    const bubble = (role, text = '') => {
        const element = document.createElement('div');
        element.className = role === 'user'
            ? 'ml-auto w-fit max-w-[85%] rounded-2xl rounded-br-sm bg-red-600 px-3.5 py-2 text-white shadow-sm'
            : 'chat-markdown mr-auto w-fit max-w-[85%] rounded-2xl rounded-bl-sm border border-zinc-200 bg-white px-3.5 py-2 shadow-sm';
        element.textContent = text;

        return append(element);
    };

    // The reply streams in chunks: re-render the whole text each time, dropping renders overtaken by newer chunks.
    const appendMarkdown = async (element, delta) => {
        const text = (element.dataset.markdown ?? '') + delta;
        element.dataset.markdown = text;

        const html = await renderMarkdown(text);

        if (element.dataset.markdown === text) {
            element.innerHTML = html;
            messages.scrollTop = messages.scrollHeight;
        }
    };

    const note = (text, tone = 'text-zinc-400') => {
        const element = document.createElement('p');
        element.className = `font-mono text-xs ${tone}`;
        element.textContent = text;

        return append(element);
    };

    const setBusy = (busy) => {
        form.querySelector('button').disabled = busy;
        input.disabled = busy;

        if (!busy) {
            input.focus();
        }
    };

    // A2UI: the renderer turns the declarative messages into components and reports button events here.
    const surfaces = new MessageProcessor([basicCatalog, conferenceCatalog], async (action) => {
        const { name, context = {} } = action.event ?? action;

        try {
            let message;

            if (name === 'add_to_agenda') {
                message = await postJson(chat.dataset.agendaUrl, { talk_id: context.talk_id });
            } else if (name === 'book_workshop') {
                message = await postJson(chat.dataset.bookingUrl.replace('__ID__', context.workshop_id), {
                    // ChoicePicker binds a list of values, even with a single choice.
                    company_role: [context.company_role].flat()[0],
                    company_name: context.company_name,
                });
                document.querySelectorAll('a2ui-modal').forEach((modal) => modal.closeModal());
            } else {
                return; // open_booking_form: the Modal opens on its own.
            }

            note(`✓ ${message}`, 'text-emerald-600');
        } catch (error) {
            note(`✕ ${error.message}`, 'text-red-600');
        }
    });

    surfaces.onSurfaceCreated((surface) => {
        const element = document.createElement('a2ui-surface');
        element.surface = surface;
        // Surfaces composed by the model (render_surface) are dashboards: they get roomier spacing in app.css.
        element.className = surface.id.startsWith('generated-') ? 'block a2ui-dashboard' : 'block';

        append(element);
    });

    // A tool that fails (e.g. validation) returns plain text for the model instead of A2UI messages.
    const renderSurface = (content) => {
        try {
            surfaces.processMessages(JSON.parse(content).a2ui);
        } catch {
            note(`✕ ${content}`, 'text-amber-600');
        }
    };

    const approvalCard = (interrupt) => {
        const card = document.createElement('div');
        card.className = 'rounded-xl border border-amber-300 bg-amber-100 p-3 shadow-sm';

        const title = document.createElement('p');
        title.className = 'font-medium';
        title.textContent = `Conferma richiesta: ${interrupt.metadata?.name ?? 'azione'}`;

        const reason = document.createElement('p');
        reason.className = 'text-zinc-600';
        reason.textContent = interrupt.message ?? '';

        const actions = document.createElement('div');
        actions.className = 'mt-2 flex gap-2';

        const decide = (approved) => {
            card.remove();
            note(approved ? 'Approvato.' : 'Rifiutato.');

            run({ resume: buildResumeArray([interrupt], { [interrupt.id]: { status: 'resolved', payload: { approved } } }) });
        };

        for (const [label, approved, style] of [
            ['Approva', true, 'bg-emerald-600 text-white hover:bg-emerald-700'],
            ['Rifiuta', false, 'border border-zinc-300 bg-white hover:bg-zinc-100'],
        ]) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `btn ${style}`;
            button.textContent = label;
            button.addEventListener('click', () => decide(approved));
            actions.append(button);
        }

        card.append(title, reason, actions);

        return append(card);
    };

    const subscriber = {
        onEvent({ event }) {
            logEvent(event);
        },
        onTextMessageStartEvent() {
            assistantBubble = bubble('assistant');
        },
        onTextMessageContentEvent({ event }) {
            appendMarkdown(assistantBubble, event.delta);
        },
        onToolCallStartEvent({ event }) {
            toolNames[event.toolCallId] = event.toolCallName;
            note(`→ ${event.toolCallName}`);
        },
        onToolCallResultEvent({ event }) {
            const name = toolNames[event.toolCallId];

            if (A2UI_TOOLS.includes(name)) {
                renderSurface(event.content);
            }

            if (MUTATING_TOOLS.includes(name)) {
                refreshLiveRegions();
            }
        },
        onRunErrorEvent({ event }) {
            note(`Errore: ${event.message}`, 'text-red-600');
        },
    };

    // The homemade chat: our own request and our own events, with no runs to pause or resume.
    async function runHomemade(message) {
        setBusy(true);
        assistantBubble = null;

        try {
            const response = await fetch(chat.dataset.agentUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'text/event-stream', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ thread_id: chat.dataset.threadId, message }),
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            for await (const { event, data } of readHomemadeEvents(response)) {
                logEvent({ type: event, ...data });

                if (event === 'text') {
                    assistantBubble ??= bubble('assistant');
                    appendMarkdown(assistantBubble, data.text);
                } else if (event === 'tool') {
                    // The text after a tool call is a new answer: it gets its own bubble.
                    assistantBubble = null;
                    note(`→ ${data.name}`);
                } else if (event === 'tool_result' && MUTATING_TOOLS.includes(data.name)) {
                    refreshLiveRegions();
                } else if (event === 'error') {
                    note(`Errore: ${data.message}`, 'text-red-600');
                }
            }
        } catch (error) {
            note(`Errore: ${error.message}`, 'text-red-600');
        } finally {
            setBusy(false);
        }
    }

    async function run(parameters = {}) {
        setBusy(true);

        try {
            await agent.runAgent(parameters, subscriber);

            agent.pendingInterrupts.forEach(approvalCard);
        } catch (error) {
            note(`Errore: ${error.message}`, 'text-red-600');
        } finally {
            setBusy(agent.pendingInterrupts.length > 0);
        }
    }

    setupChatWindow(chat, { messages, input });

    // A suggestion fills the input: the presenter can still edit it before sending.
    messages.addEventListener('click', (event) => {
        const suggestion = event.target.closest('[data-suggestion]');

        if (suggestion && !input.disabled) {
            input.value = suggestion.dataset.suggestion;
            input.focus();
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const content = input.value.trim();

        if (!content) {
            return;
        }

        input.value = '';
        bubble('user', content);

        if (chat.dataset.protocol === 'homemade') {
            runHomemade(content);

            return;
        }

        agent.addMessage({ id: messageId(), role: 'user', content });

        run();
    });
}
