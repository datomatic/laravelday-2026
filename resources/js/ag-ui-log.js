/**
 * The AG-UI event log under the chat: one row per event, with the time since the run started,
 * a short summary and the full JSON on click. Streaming chunks of the same message or tool call
 * are merged into one row, so the deltas read as text instead of hundreds of lines.
 */
const STREAMED = { TEXT_MESSAGE_CONTENT: 'messageId', TOOL_CALL_ARGS: 'toolCallId' };

const COLORS = [
    [/^RUN_ERROR/, 'text-red-400'],
    [/^RUN_/, 'text-sky-300'],
    [/^TEXT_/, 'text-emerald-300'],
    [/^TOOL_/, 'text-amber-300'],
];

const truncate = (text, length = 80) => (text.length > length ? `${text.slice(0, length)}…` : text);

// Tool results are JSON strings (A2UI messages, structured MCP content): show them as objects.
const parseJson = (content) => {
    try {
        return typeof content === 'string' ? JSON.parse(content) : content;
    } catch {
        return content;
    }
};

const summary = (event) => {
    switch (event.type) {
        case 'RUN_STARTED':
            return `run ${event.runId}`;
        case 'RUN_FINISHED':
            return event.outcome?.type === 'interrupt'
                ? `interrupt: ${event.outcome.interrupts.map((interrupt) => interrupt.metadata?.toolName ?? interrupt.id).join(', ')}`
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

export function createEventLog(list) {
    let startedAt = performance.now();
    let streaming = null;

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
        type.className += ` ${COLORS.find(([pattern]) => pattern.test(event.type))?.[1] ?? 'text-violet-300'}`;

        list.append(item);

        return {
            update(summaryText, json) {
                text.textContent = truncate(summaryText);
                item.querySelector('pre').textContent = JSON.stringify(json, null, 2);
            },
        };
    };

    return (event) => {
        if (event.type === 'RUN_STARTED') {
            startedAt = performance.now();
        }

        const idKey = STREAMED[event.type];

        if (idKey && streaming?.type === event.type && streaming.id === event[idKey]) {
            streaming.delta += event.delta;
            streaming.chunks++;
        } else if (idKey) {
            streaming = { type: event.type, id: event[idKey], delta: event.delta, chunks: 1, row: row(event) };
        } else {
            streaming = null;
            row(event).update(summary(event), { ...event, content: parseJson(event.content) });
        }

        if (streaming) {
            const { type, id, delta, chunks } = streaming;
            streaming.row.update(`×${chunks} ${JSON.stringify(delta)}`, { type, [idKey]: id, chunks, delta });
        }

        list.scrollTop = list.scrollHeight;
    };
}
