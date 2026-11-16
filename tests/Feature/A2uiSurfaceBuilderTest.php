<?php

use App\Enums\CompanyRole;
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

    $messages = app(A2uiSurfaceBuilder::class)->talkCards(collect([$talk->load('speaker')]), collect());
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

test('talk cards show the add button only when the talk can be added', function () {
    $opening = Talk::factory()->create(['starts_at' => today()->setTime(9, 30), 'ends_at' => today()->setTime(10, 10)]);
    $chosen = Talk::factory()->create(['starts_at' => today()->setTime(10, 20), 'ends_at' => today()->setTime(10, 55)]);
    $overlapping = Talk::factory()->create(['starts_at' => today()->setTime(10, 20), 'ends_at' => today()->setTime(10, 55)]);
    $free = Talk::factory()->create(['starts_at' => today()->setTime(11, 20), 'ends_at' => today()->setTime(12, 0)]);
    $freeParallel = Talk::factory()->create(['starts_at' => today()->setTime(11, 20), 'ends_at' => today()->setTime(12, 0)]);

    $messages = app(A2uiSurfaceBuilder::class)->talkCards(Talk::with('speaker')->orderBy('id')->get(), collect([$chosen]));
    $components = collect($messages[0]['createSurface']['components'])->keyBy('id');

    expect($components["talk_{$opening->id}_content"]['children'])->toBe(["talk_{$opening->id}_title", "talk_{$opening->id}_meta", "talk_{$opening->id}_body"])
        ->and($components["talk_{$chosen->id}_in_agenda"]['text'])->toBe('✓ Nella tua agenda')
        ->and($components["talk_{$overlapping->id}_content"]['children'])->toHaveCount(3)
        ->and($components["talk_{$free->id}_button"]['action']['event']['name'])->toBe('add_to_agenda')
        ->and($components->keys()->filter(fn (string $id) => str_ends_with($id, '_button'))->values()->all())
        ->toBe(["talk_{$free->id}_button", "talk_{$freeParallel->id}_button"]);
});

test('workshop cards open a booking form bound to the surface data model', function () {
    $workshop = Workshop::factory()->create();
    $key = "workshop_{$workshop->id}";

    $surface = app(A2uiSurfaceBuilder::class)->workshopCards(collect([$workshop->load('speaker')]))[0]['createSurface'];
    $components = collect($surface['components'])->keyBy('id');

    expect($surface['dataModel'])->toBe([$key => ['company_role' => [], 'company_name' => '']])
        ->and($components["{$key}_booking"])->toMatchArray(['component' => 'Modal', 'trigger' => "{$key}_open", 'content' => "{$key}_form"])
        ->and($components["{$key}_role"]['options'])->toHaveCount(count(CompanyRole::cases()))
        ->and($components["{$key}_role"]['value'])->toBe(['@path' => "/{$key}/company_role"])
        ->and($components["{$key}_company"]['value'])->toBe(['@path' => "/{$key}/company_name"])
        ->and($components["{$key}_submit"]['checks'])->toHaveCount(2)
        ->and($components["{$key}_submit"]['action']['event'])->toBe([
            'name' => 'book_workshop',
            'context' => [
                'workshop_id' => $workshop->id,
                'company_role' => ['@path' => "/{$key}/company_role"],
                'company_name' => ['@path' => "/{$key}/company_name"],
            ],
        ]);
});

test('component ids are valid identifiers as required by a2ui 1.0', function () {
    $talk = Talk::factory()->create();
    $workshop = Workshop::factory()->create();
    $builder = app(A2uiSurfaceBuilder::class);

    $ids = collect([
        ...$builder->talkCards(collect([$talk->load('speaker')]), collect()),
        ...$builder->workshopCards(collect([$workshop->load('speaker')])),
    ])->flatMap(fn (array $message) => array_column($message['createSurface']['components'], 'id'));

    expect($ids)->each->toMatch('/^[\p{L}_][\p{L}\p{N}_]*$/u');
});

test('a talk is not plenary when its parallel talk is not among the cards', function () {
    $shown = Talk::factory()->create(['starts_at' => today()->setTime(10, 20), 'ends_at' => today()->setTime(10, 55)]);
    Talk::factory()->create(['starts_at' => today()->setTime(10, 20), 'ends_at' => today()->setTime(10, 55)]);

    $messages = app(A2uiSurfaceBuilder::class)->talkCards(collect([$shown->load('speaker')]), collect());
    $components = collect($messages[0]['createSurface']['components'])->keyBy('id');

    expect($components)->toHaveKey("talk_{$shown->id}_button");
});
