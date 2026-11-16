<?php

use App\Models\Speaker;
use App\Models\Talk;
use App\Models\Workshop;
use App\Neuron\Tools\ShowSpeakerCard;
use App\Services\A2uiSurfaceBuilder;
use Illuminate\Validation\ValidationException;

test('the speaker card shows the photo, the bio and the sessions in time order', function () {
    $speaker = Speaker::factory()->create([
        'name' => 'Marco Bellini',
        'company' => 'Nodo Digitale',
        'photo' => 'images/speakers/marco-bellini.jpg',
        'bio' => 'Sviluppatore Laravel.',
    ]);
    Talk::factory()->for($speaker)->create(['title' => 'Pomeriggio', 'room' => 'Sala Verona', 'starts_at' => today()->setTime(15, 10)]);
    Talk::factory()->for($speaker)->create(['title' => 'Mattina', 'room' => 'Sala Arena', 'starts_at' => today()->setTime(10, 20)]);
    Workshop::factory()->for($speaker)->create(['title' => 'Server MCP', 'starts_at' => today()->setTime(14, 0)]);

    $surface = a2uiSurface(app(ShowSpeakerCard::class)($speaker->id));
    $components = collect($surface['components'])->keyBy('id');

    expect($surface['catalogId'])->toBe(A2uiSurfaceBuilder::BASIC_CATALOG)
        ->and($components['root'])->toMatchArray(['component' => 'Card', 'child' => 'profile'])
        ->and($components['photo'])->toMatchArray([
            'component' => 'Image',
            'url' => asset('images/speakers/marco-bellini.jpg'),
            'variant' => 'avatar',
        ])
        ->and($components['name']['text'])->toBe('#### Marco Bellini')
        ->and($components['company']['text'])->toBe('Nodo Digitale')
        ->and($components['bio']['text'])->toBe('Sviluppatore Laravel.')
        ->and($components['sessions']['text'])->toBe(
            "**Sessioni**\n\n- 10:20 · Sala Arena — Mattina\n- 15:10 · Sala Verona — Pomeriggio\n- 14:00 · Workshop — Server MCP"
        );
});

test('the speaker card rejects an unknown speaker', function () {
    app(ShowSpeakerCard::class)(999);
})->throws(ValidationException::class);
