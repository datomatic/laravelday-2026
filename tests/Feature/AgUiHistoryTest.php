<?php

use App\Ai\AgUiHistory;
use Illuminate\Http\Request;
use Laravel\Ai\AgentUserInteraction\AgentUserInteraction;
use Laravel\Ai\Messages\AssistantMessage;

test('replayed tool calls get the provider call id from their tool result', function () {
    $request = Request::create('/agent', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
        'threadId' => 'thread-1',
        'runId' => 'run-2',
        'messages' => [
            ['id' => 'm1', 'role' => 'user', 'content' => 'Quali talk parlano di AI?'],
            ['id' => 'fc_1', 'role' => 'assistant', 'toolCalls' => [
                ['id' => 'fc_1', 'type' => 'function', 'function' => ['name' => 'search_talks', 'arguments' => '{"track":"ai"}']],
            ]],
            ['id' => 'call_1', 'role' => 'tool', 'toolCallId' => 'fc_1', 'content' => '{"talks":[]}'],
            ['id' => 'm2', 'role' => 'assistant', 'content' => 'Nessun talk.'],
            ['id' => 'm3', 'role' => 'user', 'content' => 'E di backend?'],
        ],
        'tools' => [],
        'context' => [],
    ]));

    $history = AgUiHistory::withToolCallResultIds(AgentUserInteraction::chat($request)->history());

    $call = collect($history)->whereInstanceOf(AssistantMessage::class)->first()->toolCalls->first();

    expect($call->id)->toBe('fc_1')
        ->and($call->resultId)->toBe('call_1');
});

test('tool calls without a matching result are left untouched', function () {
    $request = Request::create('/agent', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
        'threadId' => 'thread-1',
        'runId' => 'run-2',
        'messages' => [
            ['id' => 'm1', 'role' => 'user', 'content' => 'Prenotami il workshop.'],
            ['id' => 'fc_1', 'role' => 'assistant', 'toolCalls' => [
                ['id' => 'fc_1', 'type' => 'function', 'function' => ['name' => 'book_workshop', 'arguments' => '{"workshop_id":1}']],
            ]],
        ],
        'tools' => [],
        'context' => [],
    ]));

    $history = AgUiHistory::withToolCallResultIds(AgentUserInteraction::chat($request)->history());

    expect(collect($history)->whereInstanceOf(AssistantMessage::class)->first()->toolCalls->first()->resultId)->toBeNull();
});
