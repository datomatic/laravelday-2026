/**
 * The floating chat window of the schedule: minimize, full screen (with the event log on the right) and Escape.
 * The embedded chat of the admin dashboard is the page itself: it only has a button for the browser full screen.
 */
export function setupChatWindow(chat, { messages, input }) {
    const showLatest = () => {
        messages.scrollTop = messages.scrollHeight;
        input.focus();
    };

    const expandButton = document.getElementById('chat-expand');

    if (expandButton) {
        setupBrowserFullscreen(chat, expandButton, showLatest);

        return;
    }

    const fullscreenButton = document.getElementById('chat-fullscreen');
    const minimizeButton = document.getElementById('chat-minimize');
    const backdrop = document.getElementById('chat-backdrop');

    const setMinimized = (minimized) => {
        chat.toggleAttribute('data-minimized', minimized);
        minimizeButton.setAttribute('aria-pressed', String(minimized));
        minimizeButton.setAttribute('aria-label', minimized ? 'Ripristina la chat' : 'Riduci a icona la chat');
    };

    const setFullscreen = (fullscreen) => {
        if (fullscreen) {
            setMinimized(false);
            document.getElementById('chat-log').open = true;
        }

        chat.toggleAttribute('data-fullscreen', fullscreen);
        backdrop.classList.toggle('hidden', !fullscreen);
        document.body.classList.toggle('overflow-hidden', fullscreen);
        fullscreenButton.setAttribute('aria-pressed', String(fullscreen));
        fullscreenButton.setAttribute('aria-label', fullscreen ? 'Riduci la chat' : 'Apri la chat a tutto schermo');
        showLatest();
    };

    fullscreenButton.addEventListener('click', () => setFullscreen(!chat.hasAttribute('data-fullscreen')));

    minimizeButton.addEventListener('click', () => {
        const minimized = !chat.hasAttribute('data-minimized');

        if (minimized && chat.hasAttribute('data-fullscreen')) {
            setFullscreen(false);
        }

        setMinimized(minimized);

        if (!minimized) {
            showLatest();
        }
    });

    backdrop.addEventListener('click', () => setFullscreen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && chat.hasAttribute('data-fullscreen')) {
            setFullscreen(false);
        }
    });
}

function setupBrowserFullscreen(chat, button, showLatest) {
    button.addEventListener('click', () => {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            chat.requestFullscreen();
        }
    });

    document.addEventListener('fullscreenchange', () => {
        const fullscreen = document.fullscreenElement === chat;

        button.setAttribute('aria-pressed', String(fullscreen));
        button.setAttribute('aria-label', fullscreen ? 'Esci dallo schermo intero' : 'Schermo intero');
        showLatest();
    });
}
