<?php

use App\Models\Workshop;
use App\Neuron\Tools\ShowBookingForm;
use App\Services\A2uiSurfaceBuilder;
use Illuminate\Validation\ValidationException;

test('the booking form is a card with the form fields bound to its data model', function () {
    $workshop = Workshop::factory()->create(['title' => 'Testing con Pest']);
    $key = "workshop_{$workshop->id}";

    $surface = json_decode(app(ShowBookingForm::class)($workshop->id), true)['a2ui'][0]['createSurface'];
    $components = collect($surface['components'])->keyBy('id');

    expect($surface['catalogId'])->toBe(A2uiSurfaceBuilder::BASIC_CATALOG)
        ->and($surface['dataModel'])->toBe([$key => ['company_role' => [], 'company_name' => '']])
        ->and($components['root'])->toMatchArray(['component' => 'Card', 'child' => "{$key}_form"])
        ->and($components["{$key}_form_title"]['text'])->toBe('#### Prenota: Testing con Pest')
        ->and($components["{$key}_submit"]['action']['event']['name'])->toBe('book_workshop')
        ->and($components->has("{$key}_booking"))->toBeFalse();
});

test('the booking form rejects an unknown workshop', function () {
    app(ShowBookingForm::class)(999);
})->throws(ValidationException::class);
