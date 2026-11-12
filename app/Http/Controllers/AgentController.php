<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ConferenceAssistant;
use App\Ai\AgUiHistory;
use Illuminate\Http\Request;
use Laravel\Ai\AgentUserInteraction\AgentUserInteraction;
use Laravel\Ai\Responses\StreamableAgentResponse;

class AgentController extends Controller
{
    /**
     * AG-UI endpoint: reads a RunAgentInput and streams AG-UI events back.
     * The client owns the history, so it is replayed on every run (and on approval resumes).
     */
    public function __invoke(Request $request): StreamableAgentResponse
    {
        $chat = AgentUserInteraction::chat($request);

        abort_if($chat->message() === null && $chat->decisions() === null, 422, 'The run contains no user message or approval decision.');

        return (new ConferenceAssistant)
            ->withMessages(AgUiHistory::withToolCallResultIds($chat->history()))
            ->stream($chat)
            ->usingProtocol($chat->protocol());
    }
}
