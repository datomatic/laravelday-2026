import { HttpAgent, buildResumeArray } from '@ag-ui/client';
import { MessageProcessor, basicCatalog, setMarkdownRenderer } from '@a2ui/web_core/v1_0';
import '@a2ui/lit'; // registers the <a2ui-surface> element
import { conferenceCatalog } from './a2ui/conference-catalog';
import { renderMarkdown } from '@a2ui/markdown-it';
import { csrfToken, postJson, refreshLiveRegions } from './live-regions';

// A2UI Text components may carry Markdown: render it with markdown-it, sanitized by DOMPurify.
setMarkdownRenderer(renderMarkdown);

const chat = document.getElementById('chat');

if (chat) {
    const messages = document.getElementById('chat-messages');
    const events = document.getElementById('chat-events');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');

    // Tool results that carry A2UI messages instead of text for the model to summarise.
    const A2UI_TOOLS = ['show_talk_cards', 'show_workshop_cards', 'show_chart'];
    // Tools that change server state: the page regions are refreshed when they complete.
    const MUTATING_TOOLS = ['add_to_agenda', 'clear_agenda', 'book_workshop'];

    const agent = new HttpAgent({
        url: chat.dataset.agentUrl,
        headers: { 'X-CSRF-TOKEN': csrfToken() },
    });

    // crypto.randomUUID() only exists in secure contexts (https or localhost), not on http://laravelday.test.
    const messageId = () => crypto.randomUUID?.() ?? `${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;

    const toolNames = {};
    let assistantBubble = null;

    const append = (element) => {
        messages.append(element);
        messages.scrollTop = messages.scrollHeight;

        return element;
    };

    const bubble = (role, text = '') => {
        const element = document.createElement('p');
        element.className = role === 'user'
            ? 'ml-auto w-fit max-w-[85%] rounded-2xl rounded-br-sm bg-red-600 px-3.5 py-2 text-white shadow-sm'
            : 'mr-auto w-fit max-w-[85%] whitespace-pre-wrap rounded-2xl rounded-bl-sm border border-zinc-200 bg-white px-3.5 py-2 shadow-sm';
        element.textContent = text;

        return append(element);
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
            const message = name === 'add_to_agenda'
                ? await postJson(chat.dataset.agendaUrl, { talk_id: context.talk_id })
                : await postJson(chat.dataset.bookingUrl.replace('__ID__', context.workshop_id));

            note(`✓ ${message}`, 'text-emerald-600');
        } catch (error) {
            note(`✕ ${error.message}`, 'text-red-600');
        }
    });

    surfaces.onSurfaceCreated((surface) => {
        const element = document.createElement('a2ui-surface');
        element.surface = surface;
        element.className = 'block';

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
        card.className = 'rounded-xl border border-amber-300 bg-amber-50 p-3 shadow-sm';

        const title = document.createElement('p');
        title.className = 'font-medium';
        title.textContent = `Conferma richiesta: ${interrupt.metadata?.toolName ?? 'azione'}`;

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
            const item = document.createElement('li');
            item.textContent = event.type;
            events.append(item);
            events.scrollTop = events.scrollHeight;
        },
        onTextMessageStartEvent() {
            assistantBubble = bubble('assistant');
        },
        onTextMessageContentEvent({ event }) {
            assistantBubble.textContent += event.delta;
            messages.scrollTop = messages.scrollHeight;
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

    const fullscreenButton = document.getElementById('chat-fullscreen');
    const backdrop = document.getElementById('chat-backdrop');

    const setFullscreen = (fullscreen) => {
        chat.toggleAttribute('data-fullscreen', fullscreen);
        backdrop.classList.toggle('hidden', !fullscreen);
        document.body.classList.toggle('overflow-hidden', fullscreen);
        fullscreenButton.setAttribute('aria-pressed', String(fullscreen));
        fullscreenButton.setAttribute('aria-label', fullscreen ? 'Riduci la chat' : 'Apri la chat a tutto schermo');
        messages.scrollTop = messages.scrollHeight;
        input.focus();
    };

    fullscreenButton.addEventListener('click', () => setFullscreen(!chat.hasAttribute('data-fullscreen')));
    backdrop.addEventListener('click', () => setFullscreen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && chat.hasAttribute('data-fullscreen')) {
            setFullscreen(false);
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
        agent.addMessage({ id: messageId(), role: 'user', content });

        run();
    });
}
