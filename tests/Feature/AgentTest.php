<?php

use App\Enums\CompanyRole;
use App\Enums\DemoStage;
use App\Models\ChatMessage;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Neuron\Agents\AdminAssistant;
use App\Neuron\Agents\ConferenceAssistant;
use App\Neuron\Agents\GenerativeAssistant;
use App\Neuron\Agents\HomemadeAssistant;
use App\Neuron\Tools\RenderSurface;
use App\Neuron\Tools\ShowTalkCards;
use App\Services\A2uiSurfaceBuilder;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Testing\FakeAIProvider;
use NeuronAI\Tools\ToolCall;

/**
 * The container's agents answer with the given messages: the controller resolves these instances.
 */
function fakeAssistant(Message ...$responses): FakeAIProvider
{
    $provider = new FakeAIProvider(...$responses);

    foreach ([ConferenceAssistant::class, GenerativeAssistant::class, HomemadeAssistant::class, AdminAssistant::class] as $agent) {
        app()->instance($agent, app($agent)->setAiProvider($provider));
    }

    return $provider;
}

function toolCall(string $name, array $inputs): ToolCallMessage
{
    return new ToolCallMessage(null, [ToolCall::make(name: $name, callId: 'call_1', inputs: $inputs)]);
}

function agUiRun(User $user, array $overrides = []): array
{
    return [
        'threadId' => "user-{$user->id}-thread",
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
    $this->postJson(route('agent', DemoStage::Generative), agUiRun(User::factory()->make(['id' => 1])))->assertUnauthorized();
});

test('a user cannot talk in the thread of another user', function () {
    $provider = fakeAssistant(new AssistantMessage('mai'));
    [$owner, $intruder] = User::factory()->count(2)->create();

    $this->actingAs($intruder)->postJson(route('agent', DemoStage::Generative), agUiRun($owner))->assertForbidden();

    $provider->assertNothingSent();
});

test('a run without a user message or decision is rejected', function () {
    $provider = fakeAssistant();
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('agent', DemoStage::Generative), agUiRun($user, ['messages' => []]))->assertUnprocessable();

    $provider->assertNothingSent();
});

test('the agent streams ag-ui events for a text answer and stores the conversation', function () {
    fakeAssistant(new AssistantMessage('Benvenuto alla conferenza!'));
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('agent', DemoStage::Generative), agUiRun($user));

    $response->assertOk()->assertHeader('Content-Type', 'text/event-stream; charset=utf-8');

    $events = agUiEvents($response->streamedContent());

    expect(array_column($events, 'type'))->toContain('RUN_STARTED', 'TEXT_MESSAGE_START', 'TEXT_MESSAGE_CONTENT', 'RUN_FINISHED')
        ->and($events[0])->toMatchArray(['type' => 'RUN_STARTED', 'threadId' => "user-{$user->id}-thread", 'runId' => 'run-1'])
        ->and(collect($events)->where('type', 'TEXT_MESSAGE_CONTENT')->pluck('delta')->implode(''))->toBe('Benvenuto alla conferenza!')
        ->and(ChatMessage::where('thread_id', "user-{$user->id}-thread")->orderBy('id')->pluck('role')->all())->toBe(['user', 'assistant']);
});

test('show talk cards returns an a2ui surface as the tool result', function () {
    $talk = Talk::factory()->create(['title' => 'RAG con pgvector']);
    Talk::factory()->create(['starts_at' => $talk->starts_at]);
    $user = User::factory()->create();
    fakeAssistant(toolCall(ShowTalkCards::NAME, ['talk_ids' => [$talk->id]]), new AssistantMessage('Ecco il talk.'));

    $events = agUiEvents($this->actingAs($user)->postJson(route('agent', DemoStage::Generative), agUiRun($user))->streamedContent());

    $result = collect($events)->firstWhere('type', 'TOOL_CALL_RESULT');
    $messages = json_decode($result['content'], true)['a2ui'];

    expect($result['toolCallId'])->toBe('call_1')
        ->and($messages)->toHaveCount(1)
        ->and($messages[0]['version'])->toBe('1.0')
        ->and($messages[0]['createSurface']['catalogId'])->toBe('https://a2ui.org/specification/v1_0/catalogs/basic/catalog.json');

    $components = collect($messages[0]['createSurface']['components'])->keyBy('id');

    expect($components['root']['children'])->toBe(["talk_{$talk->id}"])
        ->and($components["talk_{$talk->id}_title"]['text'])->toBe('#### RAG con pgvector')
        ->and($components["talk_{$talk->id}_button"]['action'])->toBe(['event' => ['name' => 'add_to_agenda', 'context' => ['talk_id' => $talk->id]]]);
});

test('adding a talk through the agent runs the mcp tool without approval', function () {
    $user = User::factory()->create();
    $talk = Talk::factory()->create();
    fakeAssistant(toolCall('add_to_agenda', ['talk_id' => $talk->id]), new AssistantMessage('Aggiunto.'));

    $events = agUiEvents($this->actingAs($user)->postJson(route('agent', DemoStage::Generative), agUiRun($user))->streamedContent());

    expect(collect($events)->firstWhere('type', 'TOOL_CALL_RESULT')['content'])->toContain($talk->title)
        ->and($user->agendaItems()->where('talk_id', $talk->id)->exists())->toBeTrue();
});

test('a failing mcp tool answers the model instead of failing the run', function () {
    $user = User::factory()->create();
    fakeAssistant(toolCall('add_to_agenda', ['talk_id' => 999]), new AssistantMessage('Talk non trovato.'));

    $events = agUiEvents($this->actingAs($user)->postJson(route('agent', DemoStage::Generative), agUiRun($user))->streamedContent());

    expect(collect($events)->firstWhere('type', 'TOOL_CALL_RESULT')['content'])->toContain('talk id')
        ->and(collect($events)->last()['type'])->toBe('RUN_FINISHED');
});

test('booking a workshop waits for approval, refuses a new message and runs once approved', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();
    $provider = fakeAssistant(
        toolCall('book_workshop', ['workshop_id' => $workshop->id, 'company_role' => CompanyRole::Developer->value, 'company_name' => 'Acme']),
        new AssistantMessage('Prenotato!'),
    );

    $events = agUiEvents($this->actingAs($user)->postJson(route('agent', DemoStage::Generative), agUiRun($user))->streamedContent());
    $finished = collect($events)->last();

    expect($finished['outcome']['type'])->toBe('interrupt')
        ->and($finished['outcome']['interrupts'][0])->toMatchArray([
            'id' => 'call_1',
            'reason' => 'confirmation',
            'message' => 'La prenotazione occupa uno dei posti limitati del workshop.',
        ])
        ->and($finished['outcome']['interrupts'][0]['metadata']['name'])->toBe('book_workshop')
        ->and(WorkshopBooking::count())->toBe(0);

    $this->postJson(route('agent', DemoStage::Generative), agUiRun($user, ['runId' => 'run-2']))->assertConflict();

    $events = agUiEvents($this->postJson(route('agent', DemoStage::Generative), agUiRun($user, [
        'runId' => 'run-3',
        'resume' => [['interruptId' => 'call_1', 'status' => 'resolved', 'payload' => ['approved' => true]]],
    ]))->assertOk()->streamedContent());

    expect(array_column($events, 'type'))->toContain('TOOL_CALL_RESULT', 'TEXT_MESSAGE_CONTENT')
        ->and(collect($events)->last()['type'])->toBe('RUN_FINISHED')
        ->and(WorkshopBooking::where('user_id', $user->id)->sole())
        ->company_role->toBe(CompanyRole::Developer)
        ->company_name->toBe('Acme');

    $provider->assertCallCount(2);
});

test('a rejected booking is not executed', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();
    fakeAssistant(
        toolCall('book_workshop', ['workshop_id' => $workshop->id, 'company_role' => CompanyRole::Developer->value, 'company_name' => 'Acme']),
        new AssistantMessage('Va bene, non prenoto.'),
    );

    $this->actingAs($user)->postJson(route('agent', DemoStage::Generative), agUiRun($user))->streamedContent();

    $this->postJson(route('agent', DemoStage::Generative), agUiRun($user, [
        'resume' => [['interruptId' => 'call_1', 'status' => 'resolved', 'payload' => ['approved' => false]]],
    ]))->assertOk()->streamedContent();

    expect(WorkshopBooking::count())->toBe(0);
});

test('a resume without a paused run is rejected', function () {
    fakeAssistant();
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('agent', DemoStage::Generative), agUiRun($user, [
        'resume' => [['interruptId' => 'call_1', 'status' => 'resolved', 'payload' => ['approved' => true]]],
    ]))->assertBadRequest();
});

test('the stages without AG-UI have no AG-UI endpoint', function (DemoStage $stage) {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('agent', $stage), agUiRun($user))->assertNotFound();
})->with([DemoStage::Classic, DemoStage::HomemadeChat]);

test('the text chat agent has only the mcp tools, the generative one adds the a2ui tools', function (DemoStage $stage, array $tools) {
    $provider = fakeAssistant(new AssistantMessage('Ciao!'));
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('agent', $stage), agUiRun($user))->streamedContent();

    $provider->assertToolsConfigured($tools);
})->with([
    'text chat' => [DemoStage::TextChat, ['search_talks', 'show_agenda', 'add_to_agenda', 'list_workshops', 'book_workshop', 'clear_agenda']],
    'generative' => [DemoStage::Generative, [
        'search_talks', 'show_agenda', 'add_to_agenda', 'list_workshops', 'book_workshop', 'clear_agenda',
        'show_talk_cards', 'show_workshop_cards', 'show_booking_form', 'show_speaker_card', 'show_chart',
    ]],
]);

test('only an admin can talk to the admin agent', function () {
    $provider = fakeAssistant(new AssistantMessage('mai'));
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('agent', DemoStage::Admin), agUiRun($user))->assertForbidden();

    $provider->assertNothingSent();
});

test('the admin agent renders the interface the model composed', function () {
    $admin = User::factory()->admin()->create();
    $components = [
        ['id' => 'root', 'component' => 'Column', 'children' => ['bookings']],
        ['id' => 'bookings', 'component' => 'Stat', 'label' => 'Prenotazioni', 'value' => 42],
    ];
    fakeAssistant(toolCall(RenderSurface::NAME, ['components' => json_encode($components)]), new AssistantMessage('Ecco la dashboard.'));

    $events = agUiEvents($this->actingAs($admin)->postJson(route('agent', DemoStage::Admin), agUiRun($admin))->streamedContent());

    $surface = json_decode(collect($events)->firstWhere('type', 'TOOL_CALL_RESULT')['content'], true)['a2ui'][0]['createSurface'];

    expect($surface['catalogId'])->toBe(A2uiSurfaceBuilder::CONFERENCE_CATALOG)
        ->and($surface['components'])->toBe($components);
});
