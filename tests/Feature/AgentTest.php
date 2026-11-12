<?php

use App\Ai\Agents\ConferenceAssistant;
use App\Ai\Tools\ShowTalkCards;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use Laravel\Ai\Responses\Data\ToolCall;

function agUiRun(array $overrides = []): array
{
    return [
        'threadId' => 'thread-1',
        'runId' => 'run-1',
        'messages' => [['id' => 'm1', 'role' => 'user', 'content' => 'Ciao']],
        'tools' => [],
        'context' => [],
        ...$overrides,
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function agUiEvents(string $stream): array
{
    return collect(explode("\n\n", trim($stream)))
        ->map(fn (string $frame) => json_decode(str($frame)->after('data: ')->toString(), true))
        ->filter()
        ->values()
        ->all();
}

test('the agent endpoint requires authentication', function () {
    $this->postJson(route('agent'), agUiRun())->assertUnauthorized();
});

test('a run without a user message or decision is rejected', function () {
    ConferenceAssistant::fake()->preventStrayPrompts();

    $this->actingAs(User::factory()->create())
        ->postJson(route('agent'), agUiRun(['messages' => []]))
        ->assertUnprocessable();
});

test('the agent streams ag-ui events for a text answer', function () {
    ConferenceAssistant::fake(['Benvenuto alla conferenza!']);

    $response = $this->actingAs(User::factory()->create())->postJson(route('agent'), agUiRun());

    $response->assertOk()->assertHeader('Content-Type', 'text/event-stream; charset=utf-8');

    $events = agUiEvents($response->streamedContent());
    $types = array_column($events, 'type');

    expect($types)->toContain('RUN_STARTED', 'TEXT_MESSAGE_START', 'TEXT_MESSAGE_CONTENT', 'RUN_FINISHED')
        ->and($events[0])->toMatchArray(['type' => 'RUN_STARTED', 'threadId' => 'thread-1', 'runId' => 'run-1'])
        ->and(collect($events)->where('type', 'TEXT_MESSAGE_CONTENT')->pluck('delta')->implode(''))->toBe('Benvenuto alla conferenza!');

    ConferenceAssistant::assertPrompted('Ciao');
});

test('show talk cards returns an a2ui surface as the tool result', function () {
    $talk = Talk::factory()->create(['title' => 'RAG con pgvector']);

    ConferenceAssistant::fake([
        new ToolCall('call_1', ShowTalkCards::NAME, ['talk_ids' => [$talk->id]]),
        'Ecco il talk.',
    ]);

    $events = agUiEvents($this->actingAs(User::factory()->create())->postJson(route('agent'), agUiRun())->streamedContent());

    $result = collect($events)->firstWhere('type', 'TOOL_CALL_RESULT');
    $messages = json_decode($result['content'], true)['a2ui'];

    expect($result['toolCallId'])->toBe('call_1')
        ->and($messages)->toHaveCount(1)
        ->and($messages[0]['version'])->toBe('1.0')
        ->and($messages[0]['createSurface']['catalogId'])->toBe('https://a2ui.org/specification/v1_0/catalogs/basic/catalog.json')
        ->and(collect($messages[0]['createSurface']['components'])->pluck('id'))->toContain('root', "talk_{$talk->id}");

    $components = collect($messages[0]['createSurface']['components'])->keyBy('id');

    expect($components['root']['children'])->toBe(["talk_{$talk->id}"])
        ->and($components["talk_{$talk->id}_title"]['text'])->toBe('#### RAG con pgvector')
        ->and($components["talk_{$talk->id}_button"]['action'])->toBe(['event' => ['name' => 'add_to_agenda', 'context' => ['talk_id' => $talk->id]]]);
});

test('adding a talk through the agent runs without approval', function () {
    $user = User::factory()->create();
    $talk = Talk::factory()->create();

    ConferenceAssistant::fake([new ToolCall('call_1', 'add_to_agenda', ['talk_id' => $talk->id]), 'Aggiunto.']);

    $events = agUiEvents($this->actingAs($user)->postJson(route('agent'), agUiRun())->streamedContent());

    expect(array_column($events, 'type'))->toContain('TOOL_CALL_RESULT')
        ->and($user->agendaItems()->where('talk_id', $talk->id)->exists())->toBeTrue();
});

test('booking a workshop pauses the run with an approval interrupt', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();

    ConferenceAssistant::fake([new ToolCall('call_1', 'book_workshop', ['workshop_id' => $workshop->id, 'company_role' => 'developer', 'company_name' => 'Acme'])]);

    $events = agUiEvents($this->actingAs($user)->postJson(route('agent'), agUiRun())->streamedContent());

    $finished = collect($events)->firstWhere('type', 'RUN_FINISHED');

    expect($finished['outcome']['type'])->toBe('interrupt')
        ->and($finished['outcome']['interrupts'][0])->toMatchArray(['id' => 'call_1', 'reason' => 'approval_required'])
        ->and($finished['outcome']['interrupts'][0]['metadata']['toolName'])->toBe('book_workshop')
        ->and(WorkshopBooking::count())->toBe(0);
});

test('a resume payload with an approval decision is accepted as a run', function () {
    $workshop = Workshop::factory()->create();

    // The faked gateway does not execute the approved tool, so only the request handling is covered here.
    ConferenceAssistant::fake(['Fatto.']);

    $events = agUiEvents($this->actingAs(User::factory()->create())->postJson(route('agent'), agUiRun([
        'messages' => [
            ['id' => 'm1', 'role' => 'user', 'content' => 'Prenotami il workshop'],
            ['id' => 'm2', 'role' => 'assistant', 'toolCalls' => [[
                'id' => 'call_1',
                'type' => 'function',
                'function' => ['name' => 'book_workshop', 'arguments' => json_encode(['workshop_id' => $workshop->id, 'company_role' => 'developer', 'company_name' => 'Acme'])],
            ]]],
        ],
        'resume' => [['interruptId' => 'call_1', 'status' => 'resolved', 'payload' => ['approved' => true]]],
    ]))->assertOk()->streamedContent());

    expect(array_column($events, 'type'))->toContain('RUN_STARTED', 'RUN_FINISHED');
});
