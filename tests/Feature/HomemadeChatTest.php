<?php

use App\Enums\CompanyRole;
use App\Models\ChatMessage;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use Illuminate\Testing\TestResponse;
use NeuronAI\Chat\Messages\AssistantMessage;

function homemadeChat(User $user, array $overrides = []): TestResponse
{
    return test()->actingAs($user)->postJson(route('homemade-chat'), ['thread_id' => "user-{$user->id}-thread", 'message' => 'Ciao', ...$overrides]);
}

/**
 * @return list<array{event: string, data: array<string, mixed>}>
 */
function homemadeEvents(TestResponse $response): array
{
    return collect(explode("\n\n", trim($response->streamedContent())))
        ->map(fn (string $frame) => [
            'event' => str($frame)->match('/^event: (.*)$/m')->toString(),
            'data' => json_decode(str($frame)->match('/^data: (.*)$/m')->toString(), true),
        ])
        ->all();
}

test('the homemade chat requires authentication', function () {
    $this->postJson(route('homemade-chat'))->assertUnauthorized();
});

test('a user cannot talk in the homemade thread of another user', function () {
    $provider = fakeAgents(new AssistantMessage('mai'));
    [$owner, $intruder] = User::factory()->count(2)->create();

    homemadeChat($intruder, ['thread_id' => "user-{$owner->id}-thread"])->assertForbidden();

    $provider->assertNothingSent();
});

test('the homemade chat needs a message', function () {
    homemadeChat(User::factory()->create(), ['message' => null])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');
});

test('the homemade chat streams its own events, not AG-UI ones, and stores the conversation', function () {
    fakeAgents(new AssistantMessage('Benvenuto alla conferenza!'));
    $user = User::factory()->create();

    $response = homemadeChat($user)->assertOk()->assertHeader('Content-Type', 'text/event-stream; charset=utf-8');
    $events = collect(homemadeEvents($response));

    expect($events->where('event', 'text')->pluck('data.text')->implode(''))->toBe('Benvenuto alla conferenza!')
        ->and($events->last()['event'])->toBe('done')
        ->and($response->streamedContent())->not->toContain('TEXT_MESSAGE_CONTENT')
        ->and(ChatMessage::where('thread_id', "user-{$user->id}-thread")->orderBy('id')->pluck('role')->all())->toBe(['user', 'assistant']);
});

test('the homemade chat announces the tool calls and runs them', function () {
    $user = User::factory()->create();
    $talk = Talk::factory()->create();
    fakeAgents(toolCall('add_to_agenda', ['talk_id' => $talk->id]), new AssistantMessage('Aggiunto.'));

    expect(collect(homemadeEvents(homemadeChat($user)))->whereIn('event', ['tool', 'tool_result'])->values()->all())->toBe([
        ['event' => 'tool', 'data' => ['name' => 'add_to_agenda']],
        ['event' => 'tool_result', 'data' => ['name' => 'add_to_agenda']],
    ])
        ->and($user->agendaItems()->where('talk_id', $talk->id)->exists())->toBeTrue();
});

test('without a protocol to pause the run, the homemade chat books as soon as the model asks', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();
    fakeAgents(
        toolCall('book_workshop', ['workshop_id' => $workshop->id, 'company_role' => CompanyRole::Developer->value, 'company_name' => 'Acme']),
        new AssistantMessage('Prenotato!'),
    );

    homemadeChat($user, ['message' => 'Sì, prenota'])->assertOk()->streamedContent();

    expect(WorkshopBooking::where('user_id', $user->id)->sole()->workshop_id)->toBe($workshop->id);
});

test('the homemade agent has the app tools and nothing else', function () {
    $provider = fakeAgents(new AssistantMessage('Ciao!'));

    homemadeChat(User::factory()->create())->streamedContent();

    $provider->assertToolsConfigured(['search_talks', 'show_agenda', 'add_to_agenda', 'list_workshops', 'book_workshop', 'clear_agenda']);
});
