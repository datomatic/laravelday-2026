<?php

use App\Ai\Tools\ShowChart;
use App\Enums\ChartSubject;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Services\A2uiSurfaceBuilder;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Tools\Request;

/**
 * @return array<string, mixed>
 */
function chartFor(ChartSubject|string $subject): array
{
    $content = app(ShowChart::class)->handle(new Request(['subject' => $subject instanceof ChartSubject ? $subject->value : $subject]));
    $surface = json_decode($content, true)['a2ui'][0]['createSurface'];

    expect($surface['catalogId'])->toBe(A2uiSurfaceBuilder::CONFERENCE_CATALOG);

    return collect($surface['components'])->firstWhere('component', 'BarChart');
}

test('the chart shows participants and capacity for each workshop', function () {
    $workshop = Workshop::factory()->create(['title' => 'Testing con Pest', 'capacity' => 12]);
    WorkshopBooking::factory()->for($workshop)->count(3)->create();

    $chart = chartFor(ChartSubject::WorkshopBookings);

    expect($chart['title'])->toBe(ChartSubject::WorkshopBookings->title())
        ->and($chart['bars'])->toBe([['label' => 'Testing con Pest', 'value' => 3, 'max' => 12]]);
});

test('the chart shows how many people added each talk to their agenda, most popular first', function () {
    $quiet = Talk::factory()->create(['title' => 'Talk tranquillo']);
    $popular = Talk::factory()->create(['title' => 'Talk popolare']);
    AgendaItem::factory()->for($popular)->count(2)->create();
    AgendaItem::factory()->for($quiet)->create();

    expect(chartFor(ChartSubject::TalkAgendas)['bars'])->toBe([
        ['label' => 'Talk popolare', 'value' => 2],
        ['label' => 'Talk tranquillo', 'value' => 1],
    ]);
});

test('an unknown chart subject is rejected', function () {
    chartFor('speaker_ratings');
})->throws(ValidationException::class);
