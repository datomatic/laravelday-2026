/**
 * Re-fetches the current page and swaps every [data-live-region], so the agenda and the
 * workshop seats stay in sync after the agent (or an A2UI button) changes server state.
 */
export async function refreshLiveRegions() {
    const response = await fetch(window.location.href, { headers: { Accept: 'text/html' } });
    const fresh = new DOMParser().parseFromString(await response.text(), 'text/html');

    document.querySelectorAll('[data-live-region]').forEach((region) => {
        const replacement = fresh.getElementById(region.id);

        if (replacement) {
            region.innerHTML = replacement.innerHTML;
        }
    });
}

export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

export async function postJson(url, body = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify(body),
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(payload.message ?? `Richiesta fallita (${response.status}).`);
    }

    await refreshLiveRegions();

    return payload.message ?? 'Fatto.';
}
