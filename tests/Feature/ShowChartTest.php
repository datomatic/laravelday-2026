<?php

use App\Enums\ChartSubject;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Neuron\Tools\ShowChart;
use App\Services\A2uiSurfaceBuilder;

/**
 * @return array<string, mixed>
 */
function chartFor(ChartSubject $subject): array
{
    $surface = a2uiSurface(app(ShowChart::class)($subject->value));

    expect($surface['catalogId'])->toBe(A2uiSurfaceBuilder::CONFERENCE_CATALOG);

    return collect($surface['components'])->firstWhere('component', 'Chart');
}

test('the chart shows participants and capacity for each workshop', function () {
    $workshop = Workshop::factory()->create(['title' => 'Testing con Pest', 'capacity' => 12]);
    WorkshopBooking::factory()->for($workshop)->count(3)->create();

    $chart = chartFor(ChartSubject::WorkshopBookings);

    expect($chart['title'])->toBe(ChartSubject::WorkshopBookings->title())
        ->and($chart['type'])->toBe('bar')
        ->and($chart['labels'])->toBe(['Testing con Pest'])
        ->and($chart['datasets'])->toBe([
            ['label' => 'Prenotati', 'data' => [3]],
            ['label' => 'Capienza', 'data' => [12]],
        ]);
});

test('the chart shows how many people added each talk to their agenda, most popular first', function () {
    $quiet = Talk::factory()->create(['title' => 'Talk tranquillo']);
    $popular = Talk::factory()->create(['title' => 'Talk popolare']);
    AgendaItem::factory()->for($popular)->count(2)->create();
    AgendaItem::factory()->for($quiet)->create();

    $chart = chartFor(ChartSubject::TalkAgendas);

    expect($chart['labels'])->toBe(['Talk popolare', 'Talk tranquillo'])
        ->and($chart['datasets'])->toBe([['label' => 'In agenda', 'data' => [2, 1]]]);
});
