<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            :root {
                --color-background-primary: light-dark(#ffffff, #18181b);
                --color-background-secondary: light-dark(#f4f4f5, #27272a);
                --color-text-primary: light-dark(#18181b, #fafafa);
                --color-text-secondary: light-dark(#52525b, #a1a1aa);
                --color-border-primary: light-dark(#e4e4e7, #3f3f46);
                --font-sans: system-ui, -apple-system, sans-serif;
                --border-radius-md: 10px;
            }

            body {
                margin: 0;
                padding: 12px;
                font-family: var(--font-sans);
                background: var(--color-background-primary);
                color: var(--color-text-primary);
            }

            .workshops { display: grid; gap: 12px; }
            .card { padding: 16px; border: 1px solid var(--color-border-primary); border-radius: var(--border-radius-md); background: var(--color-background-secondary); }
            .card h3 { margin: 0 0 4px; font-size: 16px; }
            .meta { margin: 0; font-size: 13px; color: var(--color-text-secondary); }
            .seats { font-weight: 600; color: #dc2626; }
            form { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
            select, input, button { font: inherit; font-size: 14px; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--color-border-primary); background: var(--color-background-primary); color: var(--color-text-primary); }
            input { flex: 1; min-width: 140px; }
            button { background: #dc2626; border-color: #dc2626; color: #fff; cursor: pointer; }
            button:disabled { opacity: .5; cursor: default; }
            .message { margin: 8px 0 0; font-size: 13px; }
            .message.error { color: #dc2626; }
        </style>

        <script type="module">
            const roles = JSON.parse(document.getElementById('app').dataset.roles);

            createMcpApp(async (app) => {
                const container = document.getElementById('workshops');
                app.autoResize();

                function workshopsFrom(result) {
                    return result.structuredContent?.workshops
                        ?? JSON.parse(result.content?.[0]?.text ?? '{}').workshops
                        ?? [];
                }

                function render(workshops) {
                    container.replaceChildren(...workshops.map(card));
                }

                function card(workshop) {
                    const element = document.createElement('article');
                    element.className = 'card';
                    element.innerHTML = `
                        <h3></h3>
                        <p class="meta"><span class="when"></span> · <span class="speaker"></span> · <span class="seats"></span></p>
                        <form>
                            <select name="company_role" required>
                                ${roles.map((role) => `<option value="${role.value}">${role.label}</option>`).join('')}
                            </select>
                            <input name="company_name" placeholder="Azienda" required maxlength="100">
                            <button type="submit">Prenota</button>
                        </form>
                        <p class="message" hidden></p>
                    `;
                    element.querySelector('h3').textContent = workshop.title;
                    element.querySelector('.when').textContent = `${workshop.starts_at}–${workshop.ends_at}`;
                    element.querySelector('.speaker').textContent = workshop.speaker;
                    element.querySelector('.seats').textContent = `${workshop.seats_left} posti`;

                    const form = element.querySelector('form');
                    const button = form.querySelector('button');
                    button.disabled = workshop.seats_left === 0;

                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        button.disabled = true;

                        const result = await app.callServerTool('book_workshop', {
                            workshop_id: workshop.id,
                            company_role: form.company_role.value,
                            company_name: form.company_name.value,
                        });

                        const message = element.querySelector('.message');
                        message.hidden = false;
                        message.classList.toggle('error', result.isError === true);
                        message.textContent = result.content?.[0]?.text ?? '';

                        if (result.isError) {
                            button.disabled = false;

                            return;
                        }

                        await app.updateModelContext({
                            content: [{ type: 'text', text: `L'utente ha prenotato il workshop "${workshop.title}" dall'interfaccia.` }],
                        });

                        const refreshed = await app.callServerTool('list_workshops', {});
                        const seats = workshopsFrom(refreshed).find((item) => item.id === workshop.id)?.seats_left;
                        element.querySelector('.seats').textContent = `${seats ?? workshop.seats_left} posti`;
                    });

                    return element;
                }

                app.onToolResult((params) => {
                    const result = params.result ?? params;

                    if (result.isError) {
                        container.textContent = result.content?.[0]?.text ?? 'Errore';

                        return;
                    }

                    render(workshopsFrom(result));
                });
            });
        </script>
    </x-slot:head>

    <div id="app" data-roles="{{ json_encode($roles) }}">
        <div id="workshops" class="workshops">
            <p class="meta">Carico i workshop…</p>
        </div>
    </div>
</x-mcp::app>
