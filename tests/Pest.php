<?php

use App\Neuron\Agents\AdminAssistant;
use App\Neuron\Agents\ConferenceAssistant;
use App\Neuron\Agents\GenerativeAssistant;
use App\Neuron\Agents\HomemadeAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Testing\FakeAIProvider;
use NeuronAI\Tools\ToolCall;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Every agent of the demo answers with the given messages: the controllers resolve these container instances.
 */
function fakeAgents(Message ...$responses): FakeAIProvider
{
    $provider = new FakeAIProvider(...$responses);

    foreach ([HomemadeAssistant::class, ConferenceAssistant::class, GenerativeAssistant::class, AdminAssistant::class] as $agent) {
        app()->instance($agent, app($agent)->setAiProvider($provider));
    }

    return $provider;
}

function toolCall(string $name, array $inputs): ToolCallMessage
{
    return new ToolCallMessage(null, [ToolCall::make(name: $name, callId: 'call_1', inputs: $inputs)]);
}

/**
 * The createSurface message of a tool result: `{"a2ui": [{"createSurface": {...}}]}`.
 *
 * @return array{surfaceId: string, catalogId: string, components: list<array<string, mixed>>, dataModel?: array<string, mixed>}
 */
function a2uiSurface(string $toolResult): array
{
    return json_decode($toolResult, true)['a2ui'][0]['createSurface'];
}
