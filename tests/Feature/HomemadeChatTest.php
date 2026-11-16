<?php

use App\Enums\CompanyRole;
use App\Models\ChatMessage;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Neuron\Agents\HomemadeAssistant;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Testing\FakeAIProvider;
use NeuronAI\Tools\ToolCall;

function fakeHomemadeAssistant(Message ...$responses): FakeAIProvider
{
    $provider = new FakeAIProvider(...$responses);

    app()->instance(HomemadeAssistant::class, app(HomemadeAssistant::class)->setAiProvider($provider));

    return $provider;
}

function homemadeToolCall(string $name, array $inputs): ToolCallMessage
{
    return new ToolCallMessage(null, [ToolCall::make(name: $name, callId: 'call_1', inputs: $inputs)]);
}

/**
 * @return list<array{event: string, data: array<string, mixed>}>
 */
function homemadeEvents(string $stream): array
{
    return collect(explode("\n\n", trim($stream)))
        ->map(fn (string $frame) => [
            'event' => str($frame)->match('/^event: (.*)$/m')->toString(),
            'data' => json_decode(str($frame)->match('/^data: (.*)$/m')->toString(), true),
        ])
        ->all();
}

test('the homemade chat requires authentication', function () {
    $this->postJson(route('homemade-chat'), ['thread_id' => 'user-1-thread', 'message' => 'Ciao'])->assertUnauthorized();
});

test('a user cannot talk in the homemade thread of another user', function () {
    $provider = fakeHomemadeAssistant(new AssistantMessage('mai'));
    [$owner, $intruder] = User::factory()->count(2)->create();

    $this->actingAs($intruder)
        ->postJson(route('homemade-chat'), ['thread_id' => "user-{$owner->id}-thread", 'message' => 'Ciao'])
        ->assertForbidden();

    $provider->assertNothingSent();
});

test('the homemade chat needs a message', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('homemade-chat'), ['thread_id' => "user-{$user->id}-thread"])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');
});

test('the homemade chat streams its own events, not AG-UI ones, and stores the conversation', function () {
    fakeHomemadeAssistant(new AssistantMessage('Benvenuto alla conferenza!'));
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('homemade-chat'), ['thread_id' => "user-{$user->id}-thread", 'message' => 'Ciao']);

    $response->assertOk()->assertHeader('Content-Type', 'text/event-stream; charset=utf-8');

    $events = homemadeEvents($response->streamedContent());

    expect(collect($events)->where('event', 'text')->pluck('data.text')->implode(''))->toBe('Benvenuto alla conferenza!')
        ->and(collect($events)->last()['event'])->toBe('done')
        ->and($response->streamedContent())->not->toContain('TEXT_MESSAGE_CONTENT')
        ->and(ChatMessage::where('thread_id', "user-{$user->id}-thread")->orderBy('id')->pluck('role')->all())->toBe(['user', 'assistant']);
});

test('the homemade chat announces the tool calls and runs them', function () {
    $user = User::factory()->create();
    $talk = Talk::factory()->create();
    fakeHomemadeAssistant(homemadeToolCall('add_to_agenda', ['talk_id' => $talk->id]), new AssistantMessage('Aggiunto.'));

    $events = homemadeEvents($this->actingAs($user)
        ->postJson(route('homemade-chat'), ['thread_id' => "user-{$user->id}-thread", 'message' => 'Aggiungilo'])
        ->streamedContent());

    expect(collect($events)->whereIn('event', ['tool', 'tool_result'])->values()->all())->toBe([
        ['event' => 'tool', 'data' => ['name' => 'add_to_agenda']],
        ['event' => 'tool_result', 'data' => ['name' => 'add_to_agenda']],
    ])
        ->and($user->agendaItems()->where('talk_id', $talk->id)->exists())->toBeTrue();
});

test('without a protocol to pause the run, the homemade chat books as soon as the model asks', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();
    fakeHomemadeAssistant(
        homemadeToolCall('book_workshop', ['workshop_id' => $workshop->id, 'company_role' => CompanyRole::Developer->value, 'company_name' => 'Acme']),
        new AssistantMessage('Prenotato!'),
    );

    $this->actingAs($user)
        ->postJson(route('homemade-chat'), ['thread_id' => "user-{$user->id}-thread", 'message' => 'Sì, prenota'])
        ->assertOk()
        ->streamedContent();

    expect(WorkshopBooking::where('user_id', $user->id)->sole()->workshop_id)->toBe($workshop->id);
});

test('the homemade agent has the app tools and nothing else', function () {
    $provider = fakeHomemadeAssistant(new AssistantMessage('Ciao!'));
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('homemade-chat'), ['thread_id' => "user-{$user->id}-thread", 'message' => 'Ciao'])
        ->streamedContent();

    $provider->assertToolsConfigured(['search_talks', 'show_agenda', 'add_to_agenda', 'list_workshops', 'book_workshop', 'clear_agenda']);
});
