<?php

namespace App\Http\Controllers;

use App\Enums\DemoStage;
use App\Http\Requests\RunAgentRequest;
use Generator;
use NeuronAI\Agent\Adapters\AGUIAdapter;
use NeuronAI\Agent\Frontend\AGUIInputTranslator;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Workflow\Streaming\SSEEncoder;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AgentController extends Controller
{
    /**
     * AG-UI endpoint: reads a RunAgentInput and streams AG-UI events back.
     * A user message starts a turn; a "resume" array answers the approval interrupts of the paused run.
     * The demo stage picks the agent: the base one answers with text, the generative one also with A2UI.
     */
    public function __invoke(RunAgentRequest $request, DemoStage $stage): StreamedResponse
    {
        abort_if(! $stage->usesAgUi(), 404);
        abort_if($stage === DemoStage::Admin && $request->user()->cannot('access-admin'), 403);
        abort_if(! $request->isResume() && $request->prompt() === null, 422, 'The run contains no user message or approval decision.');

        $adapter = new AGUIAdapter($request->threadId(), $request->input('runId'), $request->input('messages'));
        $agent = app($stage->agent())->for($request->threadId())->setStreamAdapter(fn () => $adapter);

        $events = $request->isResume()
            ? $agent->submitInputs($request->all(), new AGUIInputTranslator)->events()
            : $agent->stream(new UserMessage($request->prompt()));

        // A turn chains several model calls (the admin one writes a whole interface, and retries it if invalid):
        // the default 60 seconds would kill the stream halfway, without even an error event for the page.
        set_time_limit(180);

        // Start the run now: a refused run (e.g. an approval still pending) becomes an HTTP error, not a stream.
        $events->valid();

        return response()->stream(function () use ($events, $adapter): Generator {
            try {
                yield from SSEEncoder::encode($events);
            } catch (Throwable $exception) {
                report($exception);

                foreach ($adapter->error($exception) as $event) {
                    yield SSEEncoder::frame($event);
                }
            }
        }, 200, $adapter->getHeaders());
    }
}
