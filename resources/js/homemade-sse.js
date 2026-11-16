/**
 * The reader for our homemade protocol: a POST answered with "event: <name>" and "data: <json>" frames.
 * EventSource only sends GET requests, so the frames are parsed by hand from the response body.
 */
export async function* readHomemadeEvents(response) {
    const reader = response.body.pipeThrough(new TextDecoderStream()).getReader();
    let buffer = '';

    while (true) {
        const { value, done } = await reader.read();

        if (done) {
            return;
        }

        buffer += value;

        let end;

        while ((end = buffer.indexOf('\n\n')) !== -1) {
            const frame = buffer.slice(0, end);
            buffer = buffer.slice(end + 2);

            const event = frame.match(/^event: (.*)$/m)?.[1] ?? 'message';
            const data = frame.match(/^data: (.*)$/m)?.[1];

            yield { event, data: data ? JSON.parse(data) : {} };
        }
    }
}
