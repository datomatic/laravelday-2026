/**
 * The event log under the chat: one row per event, with the time since the run started,
 * a short summary and the full JSON on click. Streaming chunks of the same message or tool call
 * are merged into one row, so the deltas read as text instead of hundreds of lines.
 * It reads both the AG-UI events and the ones of our homemade protocol, to compare them side by side.
 */

const truncate = (text, length = 80) => (text.length > length ? `${text.slice(0, length)}…` : text);

// Tool results are JSON strings (A2UI messages, structured MCP content): show them as objects.
const parseJson = (content) => {
    try {
        return typeof content === 'string' ? JSON.parse(content) : content;
    } catch {
        return content;
    }
};

const agUiSummary = (event) => {
    switch (event.type) {
        case 'RUN_STARTED':
            return `run ${event.runId}`;
        case 'RUN_FINISHED':
            return event.outcome?.type === 'interrupt'
                ? `interrupt: ${event.outcome.interrupts.map((interrupt) => interrupt.metadata?.name ?? interrupt.id).join(', ')}`
                : 'completato';
        case 'RUN_ERROR':
            return event.message;
        case 'TEXT_MESSAGE_START':
            return `${event.role} · ${event.messageId}`;
        case 'TOOL_CALL_START':
            return `${event.toolCallName} · ${event.toolCallId}`;
        case 'TOOL_CALL_END':
            return event.toolCallId;
        case 'TOOL_CALL_RESULT':
            return event.content;
        case 'STEP_STARTED':
        case 'STEP_FINISHED':
            return event.stepName;
        default:
            return '';
    }
};

const PROTOCOLS = {
    'ag-ui': {
        // Event type → [the key of the message or tool call it belongs to, the key of its streamed piece].
        streamed: { TEXT_MESSAGE_CONTENT: ['messageId', 'delta'], TOOL_CALL_ARGS: ['toolCallId', 'delta'] },
        colors: [
            [/^RUN_ERROR/, 'text-red-400'],
            [/^RUN_/, 'text-sky-300'],
            [/^TEXT_/, 'text-emerald-300'],
            [/^TOOL_/, 'text-amber-300'],
        ],
        summary: agUiSummary,
        startsRun: (event) => event.type === 'RUN_STARTED',
    },
    // One row per frame, text pieces included: the point is to see the raw stream we invented.
    homemade: {
        streamed: {},
        colors: [
            [/^error/, 'text-red-400'],
            [/^done/, 'text-sky-300'],
            [/^text/, 'text-emerald-300'],
            [/^tool/, 'text-amber-300'],
        ],
        summary: (event) => (event.text !== undefined ? JSON.stringify(event.text) : event.name ?? event.message ?? ''),
        startsRun: (event, previous) => previous === undefined || previous.type === 'done',
    },
};

export function createEventLog(list, protocolName = 'ag-ui') {
    const protocol = PROTOCOLS[protocolName];
    let startedAt = performance.now();
    let streaming = null;
    let previous;

    const row = (event) => {
        const item = document.createElement('li');
        item.innerHTML = `
            <details class="group/event">
                <summary class="flex cursor-pointer list-none gap-2 rounded px-1 hover:bg-white/5">
                    <span class="w-12 shrink-0 text-right text-zinc-500"></span>
                    <span class="shrink-0 font-semibold"></span>
                    <span class="min-w-0 truncate text-zinc-400"></span>
                </summary>
                <pre class="mt-1 mb-2 ml-14 overflow-x-auto rounded bg-black/40 p-2 whitespace-pre-wrap break-all text-zinc-300"></pre>
            </details>`;

        const [time, type, text] = item.querySelectorAll('summary span');
        time.textContent = `+${Math.round(performance.now() - startedAt)}ms`;
        type.textContent = event.type;
        type.className += ` ${protocol.colors.find(([pattern]) => pattern.test(event.type))?.[1] ?? 'text-violet-300'}`;

        list.append(item);

        return {
            update(summaryText, json) {
                text.textContent = truncate(summaryText);
                item.querySelector('pre').textContent = JSON.stringify(json, null, 2);
            },
        };
    };

    return (event) => {
        if (protocol.startsRun(event, previous)) {
            startedAt = performance.now();
        }

        previous = event;

        const [idKey, deltaKey] = protocol.streamed[event.type] ?? [];
        const id = idKey ? event[idKey] : null;

        if (deltaKey && streaming?.type === event.type && streaming.id === id) {
            streaming.delta += event[deltaKey];
            streaming.chunks++;
        } else if (deltaKey) {
            streaming = { type: event.type, id, delta: event[deltaKey], chunks: 1, row: row(event) };
        } else {
            streaming = null;
            row(event).update(protocol.summary(event), { ...event, content: parseJson(event.content) });
        }

        if (streaming) {
            const { type, delta, chunks } = streaming;
            streaming.row.update(`×${chunks} ${JSON.stringify(delta)}`, { type, ...(idKey && { [idKey]: id }), chunks, [deltaKey]: delta });
        }

        list.scrollTop = list.scrollHeight;
    };
}
