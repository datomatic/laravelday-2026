<?php

namespace App\Ai;

use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\ToolResultMessage;

class AgUiHistory
{
    /**
     * laravel/ai rebuilds AG-UI tool calls without their resultId, so OpenAI receives a
     * function_call item with no call_id and rejects the second turn with a 400.
     * The provider call id travels back as the id of the matching tool result message.
     *
     * @param  iterable<int, Message>  $history
     * @return list<Message>
     */
    public static function withToolCallResultIds(iterable $history): array
    {
        $history = collect($history)->values();

        $resultIds = $history
            ->whereInstanceOf(ToolResultMessage::class)
            ->flatMap(fn (ToolResultMessage $message) => $message->toolResults)
            ->filter(fn ($result) => $result->resultId !== null)
            ->mapWithKeys(fn ($result) => [$result->id => $result->resultId]);

        $history->whereInstanceOf(AssistantMessage::class)->each(
            fn (AssistantMessage $message) => $message->toolCalls->each(
                fn ($call) => $call->resultId ??= $resultIds->get($call->id),
            ),
        );

        return $history->all();
    }
}
