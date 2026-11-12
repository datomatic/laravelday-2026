<?php

use App\Enums\Track;
use App\Models\Talk;
use App\Models\Workshop;
use App\Services\A2uiSurfaceBuilder;

test('talk cards show the time and a labelled track badge in a row', function () {
    $talk = Talk::factory()->create([
        'track' => Track::Ai,
        'room' => 'Sala Arena',
        'starts_at' => today()->setTime(10, 20),
        'ends_at' => today()->setTime(10, 55),
    ]);

    $messages = app(A2uiSurfaceBuilder::class)->talkCards(collect([$talk->load('speaker')]));
    $components = collect($messages[0]['createSurface']['components'])->keyBy('id');
    $key = "talk_{$talk->id}";

    expect($components["{$key}_meta"])->toMatchArray([
        'component' => 'Row',
        'justify' => 'spaceBetween',
        'children' => ["{$key}_time", "{$key}_badge"],
    ])
        ->and($components["{$key}_time"]['text'])->toBe('10:20–10:55 · Sala Arena')
        ->and($components["{$key}_badge"])->toMatchArray([
            'text' => 'AI',
            'accessibility' => ['label' => 'Track AI'],
        ]);
});

test('workshop cards keep a plain caption without a badge', function () {
    $workshop = Workshop::factory()->create();

    $messages = app(A2uiSurfaceBuilder::class)->workshopCards(collect([$workshop->load('speaker')]));
    $components = collect($messages[0]['createSurface']['components'])->keyBy('id');

    expect($components["workshop_{$workshop->id}_meta"]['component'])->toBe('Text')
        ->and($components->has("workshop_{$workshop->id}_badge"))->toBeFalse();
});

test('component ids are valid identifiers as required by a2ui 1.0', function () {
    $talk = Talk::factory()->create();
    $workshop = Workshop::factory()->create();
    $builder = app(A2uiSurfaceBuilder::class);

    $ids = collect([
        ...$builder->talkCards(collect([$talk->load('speaker')])),
        ...$builder->workshopCards(collect([$workshop->load('speaker')])),
    ])->flatMap(fn (array $message) => array_column($message['createSurface']['components'], 'id'));

    expect($ids)->each->toMatch('/^[\p{L}_][\p{L}\p{N}_]*$/u');
});
