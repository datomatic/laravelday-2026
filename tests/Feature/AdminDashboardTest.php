<?php

use App\Enums\CompanyRole;
use App\Enums\DemoStage;
use App\Enums\StatisticsReport;
use App\Enums\Track;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Neuron\Tools\GetStatistics;
use App\Neuron\Tools\RenderSurface;

/**
 * @return array<mixed>
 */
function statistics(StatisticsReport $report): array
{
    return json_decode(app(GetStatistics::class)($report->value), true);
}

test('guests are sent to the login page', function () {
    $this->get(route('admin'))->assertRedirect(route('login'));
});

test('an attendee cannot open the admin dashboard', function () {
    $this->actingAs(User::factory()->create())->get(route('admin'))->assertForbidden();
});

test('the admin dashboard is an embedded chat with the admin agent and a full screen button', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin'))
        ->assertOk()
        ->assertSee('data-fullscreen', false)
        ->assertSee(route('agent', DemoStage::Admin))
        ->assertSee('chat-expand')
        ->assertDontSee('chat-minimize');
});

test('the home page links the admin dashboard only for admins', function () {
    $this->actingAs(User::factory()->create())->get(route('home'))->assertDontSee(route('admin'));
    $this->actingAs(User::factory()->admin()->create())->get(route('home'))->assertSee(route('admin'));
});

test('the admin stage has no schedule page', function () {
    $this->actingAs(User::factory()->admin()->create())->get(route('schedule', DemoStage::Admin))->assertNotFound();
});

test('the overview counts attendees, sign-ups and free seats', function () {
    User::factory()->admin()->create();
    $workshop = Workshop::factory()->create(['capacity' => 10]);
    WorkshopBooking::factory()->for($workshop)->count(3)->create();
    AgendaItem::factory()->count(2)->create();

    expect(statistics(StatisticsReport::Overview))->toMatchArray([
        'attendees' => 5,
        'workshops' => 1,
        'agenda_items' => 2,
        'workshop_bookings' => 3,
        'workshop_seats' => 10,
        'workshop_seats_left' => 7,
    ]);
});

test('the workshop report shows capacity, booked and free seats', function () {
    $workshop = Workshop::factory()->create(['title' => 'Testing con Pest', 'capacity' => 12]);
    WorkshopBooking::factory()->for($workshop)->count(4)->create();

    expect(statistics(StatisticsReport::WorkshopBookings)[0])->toMatchArray([
        'title' => 'Testing con Pest',
        'capacity' => 12,
        'booked' => 4,
        'seats_left' => 8,
    ]);
});

test('the talk report lists the most added talks first', function () {
    $quiet = Talk::factory()->create(['title' => 'Talk tranquillo']);
    $popular = Talk::factory()->create(['title' => 'Talk popolare']);
    AgendaItem::factory()->for($popular)->count(3)->create();
    AgendaItem::factory()->for($quiet)->create();

    expect(array_column(statistics(StatisticsReport::TalkAgendas), 'in_agendas', 'title'))->toBe(['Talk popolare' => 3, 'Talk tranquillo' => 1]);
});

test('the track report covers every track, empty ones included', function () {
    $talk = Talk::factory()->create(['track' => Track::Ai]);
    AgendaItem::factory()->for($talk)->count(2)->create();

    $tracks = collect(statistics(StatisticsReport::Tracks))->keyBy('track');

    expect($tracks)->toHaveCount(count(Track::cases()))
        ->and($tracks[Track::Ai->label()])->toBe(['track' => Track::Ai->label(), 'talks' => 1, 'agenda_items' => 2])
        ->and($tracks[Track::Frontend->label()]['talks'])->toBe(0);
});

test('the company reports group the bookings by role and by company', function () {
    WorkshopBooking::factory()->count(2)->create(['company_role' => CompanyRole::Cto, 'company_name' => 'Acme']);
    WorkshopBooking::factory()->create(['company_role' => CompanyRole::Developer, 'company_name' => 'Globex']);

    expect(statistics(StatisticsReport::CompanyRoles)[0])->toBe(['role' => CompanyRole::Cto->label(), 'bookings' => 2])
        ->and(statistics(StatisticsReport::Companies))->toBe([
            ['company' => 'Acme', 'bookings' => 2],
            ['company' => 'Globex', 'bookings' => 1],
        ]);
});

test('the daily report counts the sign-ups of each day', function () {
    AgendaItem::factory()->count(2)->create(['created_at' => '2026-11-10 09:00:00']);
    WorkshopBooking::factory()->create(['created_at' => '2026-11-12 18:00:00']);

    expect(statistics(StatisticsReport::DailyActivity))->toBe([
        ['date' => '2026-11-10', 'agenda_items' => 2, 'workshop_bookings' => 0],
        ['date' => '2026-11-12', 'agenda_items' => 0, 'workshop_bookings' => 1],
    ]);
});

test('render surface sends the errors back to the model so it can retry', function (string $components, string $error) {
    $result = app(RenderSurface::class)($components);

    expect($result->isError())->toBeTrue()
        ->and($result->getText())->toContain($error);
})->with([
    'invalid tree' => [json_encode([['id' => 'root', 'component' => 'Column', 'children' => ['missing']]]), 'il figlio "missing" non esiste'],
    'not json' => ['non è json', 'components non è un array JSON valido'],
]);
