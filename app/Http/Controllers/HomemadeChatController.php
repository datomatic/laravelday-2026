<?php

namespace App\Http\Controllers;

use App\Http\Requests\HomemadeChatRequest;
use App\Neuron\Agents\HomemadeAssistant;
use Generator;
use NeuronAI\Chat\Messages\Stream\Chunks\TextChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\ToolCallChunk;
use NeuronAI\Chat\Messages\Stream\Chunks\ToolResultChunk;
use NeuronAI\Chat\Messages\UserMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class HomemadeChatController extends Controller
{
    /**
     * The chat before AG-UI: Server-Sent Events with event names and payloads we made up.
     * Only our frontend understands them, and every app invents its own.
     */
    public function __invoke(HomemadeChatRequest $request, HomemadeAssistant $agent): StreamedResponse
    {
        $chunks = $agent->for($request->validated('thread_id'))->stream(new UserMessage($request->validated('message')));

        return response()->stream(function () use ($chunks): Generator {
            try {
                foreach ($chunks as $chunk) {
                    $frame = match (true) {
                        $chunk instanceof TextChunk => $this->frame('text', ['text' => $chunk->content]),
                        $chunk instanceof ToolCallChunk => $this->frame('tool', ['name' => $chunk->tool->getName()]),
                        $chunk instanceof ToolResultChunk => $this->frame('tool_result', ['name' => $chunk->tool->getName()]),
                        default => null,
                    };

                    if ($frame !== null) {
                        yield $frame;
                    }
                }
            } catch (Throwable $exception) {
                report($exception);

                yield $this->frame('error', ['message' => 'Qualcosa è andato storto.']);
            }

            yield $this->frame('done', []);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * @param  array<string, string>  $data
     */
    private function frame(string $event, array $data): string
    {
        return "event: {$event}\ndata: ".json_encode((object) $data, JSON_UNESCAPED_UNICODE)."\n\n";
    }
}
