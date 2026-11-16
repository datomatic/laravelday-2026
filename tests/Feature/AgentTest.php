<?php

use App\Enums\CompanyRole;
use App\Enums\DemoStage;
use App\Models\ChatMessage;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Neuron\Tools\RenderSurface;
use App\Neuron\Tools\ShowTalkCards;
use App\Services\A2uiSurfaceBuilder;
use Illuminate\Testing\TestResponse;
use NeuronAI\Chat\Messages\AssistantMessage;

function agUiRun(User $user, DemoStage $stage = DemoStage::Generative, array $overrides = []): TestResponse
{
    return test()->actingAs($user)->postJson(route('agent', $stage), [
        'threadId' => "user-{$user->id}-thread",
        'runId' => 'run-1',
        'messages' => [['id' => 'm1', 'role' => 'user', 'content' => 'Ciao']],
        'tools' => [],
        'context' => [],
        ...$overrides,
    ]);
}

function approval(bool $approved): array
{
    return ['resume' => [['interruptId' => 'call_1', 'status' => 'resolved', 'payload' => ['approved' => $approved]]]];
}

/**
 * @return list<array<string, mixed>>
 */
function agUiEvents(TestResponse $response): array
{
    return collect(explode("\n\n", trim($response->streamedContent())))
        ->map(fn (string $frame) => json_decode(str($frame)->after('data: ')->toString(), true))
        ->filter()
        ->values()
        ->all();
}

function toolResult(TestResponse $response): string
{
    return collect(agUiEvents($response))->firstWhere('type', 'TOOL_CALL_RESULT')['content'];
}

test('the agent endpoint requires authentication', function () {
    $this->postJson(route('agent', DemoStage::Generative))->assertUnauthorized();
});

test('a user cannot talk in the thread of another user', function () {
    $provider = fakeAgents(new AssistantMessage('mai'));
    [$owner, $intruder] = User::factory()->count(2)->create();

    agUiRun($intruder, overrides: ['threadId' => "user-{$owner->id}-thread"])->assertForbidden();

    $provider->assertNothingSent();
});

test('a run without a user message or decision is rejected', function () {
    $provider = fakeAgents();

    agUiRun(User::factory()->create(), overrides: ['messages' => []])->assertUnprocessable();

    $provider->assertNothingSent();
});

test('the agent streams ag-ui events for a text answer and stores the conversation', function () {
    fakeAgents(new AssistantMessage('Benvenuto alla conferenza!'));
    $user = User::factory()->create();

    $response = agUiRun($user)->assertOk()->assertHeader('Content-Type', 'text/event-stream; charset=utf-8');
    $events = collect(agUiEvents($response));

    expect($events->pluck('type'))->toContain('RUN_STARTED', 'TEXT_MESSAGE_START', 'TEXT_MESSAGE_CONTENT', 'RUN_FINISHED')
        ->and($events->first())->toMatchArray(['threadId' => "user-{$user->id}-thread", 'runId' => 'run-1'])
        ->and($events->where('type', 'TEXT_MESSAGE_CONTENT')->pluck('delta')->implode(''))->toBe('Benvenuto alla conferenza!')
        ->and(ChatMessage::where('thread_id', "user-{$user->id}-thread")->orderBy('id')->pluck('role')->all())->toBe(['user', 'assistant']);
});

test('an a2ui tool travels as the tool call result', function () {
    $talk = Talk::factory()->create();
    fakeAgents(toolCall(ShowTalkCards::NAME, ['talk_ids' => [$talk->id]]), new AssistantMessage('Ecco il talk.'));

    $surface = a2uiSurface(toolResult(agUiRun(User::factory()->create())));

    expect($surface['catalogId'])->toBe(A2uiSurfaceBuilder::BASIC_CATALOG)
        ->and(collect($surface['components'])->firstWhere('id', 'root')['children'])->toBe(["talk_{$talk->id}"]);
});

test('an mcp tool runs in-process without approval', function () {
    $user = User::factory()->create();
    $talk = Talk::factory()->create();
    fakeAgents(toolCall('add_to_agenda', ['talk_id' => $talk->id]), new AssistantMessage('Aggiunto.'));

    expect(toolResult(agUiRun($user)))->toContain($talk->title)
        ->and($user->agendaItems()->where('talk_id', $talk->id)->exists())->toBeTrue();
});

test('a failing mcp tool answers the model instead of failing the run', function () {
    fakeAgents(toolCall('add_to_agenda', ['talk_id' => 999]), new AssistantMessage('Talk non trovato.'));

    $events = agUiEvents(agUiRun(User::factory()->create()));

    expect(collect($events)->firstWhere('type', 'TOOL_CALL_RESULT')['content'])->toContain('talk id')
        ->and(collect($events)->last()['type'])->toBe('RUN_FINISHED');
});

test('booking a workshop waits for approval, refuses a new message and runs once approved', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();
    $provider = fakeAgents(
        toolCall('book_workshop', ['workshop_id' => $workshop->id, 'company_role' => CompanyRole::Developer->value, 'company_name' => 'Acme']),
        new AssistantMessage('Prenotato!'),
    );

    $finished = collect(agUiEvents(agUiRun($user)))->last();

    expect($finished['outcome']['type'])->toBe('interrupt')
        ->and($finished['outcome']['interrupts'][0])->toMatchArray([
            'id' => 'call_1',
            'reason' => 'confirmation',
            'message' => 'La prenotazione occupa uno dei posti limitati del workshop.',
        ])
        ->and(WorkshopBooking::count())->toBe(0);

    agUiRun($user, overrides: ['runId' => 'run-2'])->assertConflict();

    $events = agUiEvents(agUiRun($user, overrides: ['runId' => 'run-3', ...approval(true)])->assertOk());

    expect(collect($events)->last()['type'])->toBe('RUN_FINISHED')
        ->and(WorkshopBooking::where('user_id', $user->id)->sole())
        ->company_role->toBe(CompanyRole::Developer)
        ->company_name->toBe('Acme');

    $provider->assertCallCount(2);
});

test('a rejected booking is not executed', function () {
    $user = User::factory()->create();
    fakeAgents(
        toolCall('book_workshop', ['workshop_id' => Workshop::factory()->create()->id, 'company_role' => CompanyRole::Developer->value, 'company_name' => 'Acme']),
        new AssistantMessage('Va bene, non prenoto.'),
    );

    agUiRun($user)->streamedContent();
    agUiRun($user, overrides: approval(false))->assertOk()->streamedContent();

    expect(WorkshopBooking::count())->toBe(0);
});

test('a resume without a paused run is rejected', function () {
    fakeAgents();

    agUiRun(User::factory()->create(), overrides: approval(true))->assertBadRequest();
});

test('the stages without AG-UI have no AG-UI endpoint', function (DemoStage $stage) {
    agUiRun(User::factory()->create(), $stage)->assertNotFound();
})->with([DemoStage::Classic, DemoStage::HomemadeChat]);

test('each stage gives its agent its own tools', function (DemoStage $stage, array $tools) {
    $provider = fakeAgents(new AssistantMessage('Ciao!'));

    agUiRun(User::factory()->admin()->create(), $stage)->streamedContent();

    $provider->assertToolsConfigured($tools);
})->with([
    'text chat: the mcp tools' => [DemoStage::TextChat, ['search_talks', 'show_agenda', 'add_to_agenda', 'list_workshops', 'book_workshop', 'clear_agenda']],
    'generative: plus the a2ui tools' => [DemoStage::Generative, [
        'search_talks', 'show_agenda', 'add_to_agenda', 'list_workshops', 'book_workshop', 'clear_agenda',
        'show_talk_cards', 'show_workshop_cards', 'show_booking_form', 'show_speaker_card', 'show_chart',
    ]],
    'admin: only read-only statistics and the free interface' => [DemoStage::Admin, ['get_statistics', 'render_surface']],
]);

test('only an admin can talk to the admin agent', function () {
    $provider = fakeAgents(new AssistantMessage('mai'));

    agUiRun(User::factory()->create(), DemoStage::Admin)->assertForbidden();

    $provider->assertNothingSent();
});

test('the admin agent renders the interface the model composed', function () {
    $components = [
        ['id' => 'root', 'component' => 'Column', 'children' => ['bookings']],
        ['id' => 'bookings', 'component' => 'Stat', 'label' => 'Prenotazioni', 'value' => 42],
    ];
    fakeAgents(toolCall(RenderSurface::NAME, ['components' => json_encode($components)]), new AssistantMessage('Ecco la dashboard.'));

    $surface = a2uiSurface(toolResult(agUiRun(User::factory()->admin()->create(), DemoStage::Admin)));

    expect($surface['catalogId'])->toBe(A2uiSurfaceBuilder::CONFERENCE_CATALOG)
        ->and($surface['components'])->toBe($components);
});
