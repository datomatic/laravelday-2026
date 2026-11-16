<?php

use App\Enums\CompanyRole;
use App\Enums\DemoStage;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;

test('the schedule shows talks, workshops and only the user agenda', function () {
    $user = User::factory()->create();
    $talk = Talk::factory()->create(['title' => 'Talk in programma']);
    $workshop = Workshop::factory()->create(['capacity' => 5]);
    $mine = AgendaItem::factory()->for($user)->create();
    $other = AgendaItem::factory()->create();

    $this->actingAs($user)->get(route('schedule', DemoStage::Classic))
        ->assertOk()
        ->assertSee('Talk in programma')
        ->assertSee($workshop->title)
        ->assertSee('Posti rimasti: 5')
        ->assertViewHas('agendaTalks', fn ($talks) => $talks->pluck('id')->all() === [$mine->talk_id]);
});

test('the schedule sums up the workshops the user booked', function () {
    $user = User::factory()->create();
    $booked = Workshop::factory()->create(['title' => 'Testing con Pest']);
    Workshop::factory()->create(['title' => 'Workshop non prenotato']);
    WorkshopBooking::factory()->for($user)->for($booked)->create();

    $html = $this->actingAs($user)->get(route('schedule', DemoStage::Classic))->assertOk()->getContent();
    $summary = str($html)->after('id="my-workshops"')->before('</ul>')->toString();

    expect($summary)->toContain('Testing con Pest')
        ->not->toContain('Workshop non prenotato');
});

test('a user can add a talk to the agenda', function () {
    $user = User::factory()->create();
    $talk = Talk::factory()->create();

    $this->actingAs($user)->post(route('agenda.store'), ['talk_id' => $talk->id])->assertRedirect();
    $this->actingAs($user)->postJson(route('agenda.store'), ['talk_id' => $talk->id])->assertCreated();

    expect($user->agendaItems()->where('talk_id', $talk->id)->count())->toBe(1);
});

test('a talk overlapping one already in the agenda is rejected', function () {
    $user = User::factory()->create();
    $booked = AgendaItem::factory()->for($user)->for(Talk::factory()->state(['title' => 'Talk già scelto', 'starts_at' => '2026-11-18 10:00', 'ends_at' => '2026-11-18 10:40']))->create();
    $overlapping = Talk::factory()->create(['starts_at' => '2026-11-18 10:20', 'ends_at' => '2026-11-18 11:00']);

    $this->actingAs($user)
        ->from(route('schedule', DemoStage::Classic))
        ->post(route('agenda.store'), ['talk_id' => $overlapping->id])
        ->assertRedirect(route('schedule', DemoStage::Classic))
        ->assertSessionHasErrors(['talk_id' => 'Alle 10:00 hai già in agenda "Talk già scelto".'])
        ->assertSessionHasInput('talk_id', $overlapping->id);

    $this->actingAs($user)
        ->postJson(route('agenda.store'), ['talk_id' => $overlapping->id])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Alle 10:00 hai già in agenda "Talk già scelto".');

    expect($user->agendaItems()->pluck('talk_id')->all())->toBe([$booked->talk_id]);
});

test('the schedule hides the add button of talks overlapping the agenda', function () {
    $user = User::factory()->create();
    $chosen = Talk::factory()->create(['starts_at' => today()->setTime(10, 20), 'ends_at' => today()->setTime(10, 55)]);
    $overlapping = Talk::factory()->create(['starts_at' => today()->setTime(10, 20), 'ends_at' => today()->setTime(10, 55)]);
    $later = Talk::factory()->create(['starts_at' => today()->setTime(11, 20), 'ends_at' => today()->setTime(12, 0)]);
    AgendaItem::factory()->for($user)->for($chosen)->create();

    $html = $this->actingAs($user)->get(route('schedule', DemoStage::Classic))->assertOk()->getContent();
    $card = fn (Talk $talk) => str($html)->after("data-talk-id=\"{$talk->id}\"")->before('</article>')->toString();

    expect($card($chosen))->toContain('In agenda')
        ->and($card($overlapping))->not->toContain('+ Agenda')
        ->and($card($later))->toContain('+ Agenda');
});

test('talks one after the other can both be in the agenda', function () {
    $user = User::factory()->create();
    AgendaItem::factory()->for($user)->for(Talk::factory()->state(['starts_at' => '2026-11-18 10:00', 'ends_at' => '2026-11-18 10:40']))->create();
    $next = Talk::factory()->create(['starts_at' => '2026-11-18 10:40', 'ends_at' => '2026-11-18 11:20']);

    $this->actingAs($user)->postJson(route('agenda.store'), ['talk_id' => $next->id])->assertCreated();

    expect($user->agendaItems()->count())->toBe(2);
});

test('adding an unknown talk is rejected', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('schedule', DemoStage::Classic))
        ->post(route('agenda.store'), ['talk_id' => 999])
        ->assertRedirect(route('schedule', DemoStage::Classic))
        ->assertSessionHasErrors('talk_id')
        ->assertSessionHasInput('talk_id', 999);

    expect(AgendaItem::count())->toBe(0);
});

test('a user can remove a talk from the agenda', function () {
    $user = User::factory()->create();
    [$first, $second] = AgendaItem::factory()->for($user)->count(2)->create();

    $this->actingAs($user)->delete(route('agenda.destroy', $first->talk))->assertRedirect();

    expect($user->agendaItems()->pluck('talk_id')->all())->toBe([$second->talk_id]);
});

test('resetting the demo empties only the user agenda and bookings', function () {
    $user = User::factory()->create();
    AgendaItem::factory()->for($user)->count(2)->create();
    WorkshopBooking::factory()->for($user)->create();
    $otherItem = AgendaItem::factory()->create();
    $otherBooking = WorkshopBooking::factory()->create();

    $this->actingAs($user)->delete(route('demo.reset'))
        ->assertRedirect()
        ->assertSessionHas('status', 'Agenda e prenotazioni svuotate.');

    expect($user->agendaItems()->count())->toBe(0)
        ->and($user->workshopBookings()->count())->toBe(0)
        ->and(AgendaItem::where('id', $otherItem->id)->exists())->toBeTrue()
        ->and(WorkshopBooking::where('id', $otherBooking->id)->exists())->toBeTrue();
});

test('the reset button asks for confirmation in a dialog before deleting', function () {
    $this->actingAs(User::factory()->create())->get(route('schedule', DemoStage::Classic))
        ->assertOk()
        ->assertSeeInOrder([
            'commandfor="reset-demo" command="show-modal"',
            '<dialog id="reset-demo"',
            'action="'.route('demo.reset').'"',
            'name="_method" value="DELETE"',
            'Svuota tutto',
        ], false)
        ->assertDontSee('confirm(', false);
});

test('a user can book a workshop once with role and company', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();
    $details = ['company_role' => CompanyRole::TechLead->value, 'company_name' => 'Acme'];

    $this->actingAs($user)->post(route('workshops.bookings.store', $workshop), $details)->assertRedirect();
    $this->actingAs($user)->postJson(route('workshops.bookings.store', $workshop), $details)->assertCreated();

    $booking = WorkshopBooking::where('user_id', $user->id)->sole();

    expect($booking->company_role)->toBe(CompanyRole::TechLead)
        ->and($booking->company_name)->toBe('Acme');
});

test('booking a workshop requires a valid role and the company name', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();

    $this->actingAs($user)
        ->from(route('schedule', DemoStage::Classic))
        ->post(route('workshops.bookings.store', $workshop), ['company_role' => 'astronaut', 'company_name' => ''])
        ->assertRedirect(route('schedule', DemoStage::Classic))
        ->assertSessionHasErrors(['company_role', 'company_name'])
        ->assertSessionHasInput('company_role', 'astronaut');

    expect(WorkshopBooking::count())->toBe(0);
});

test('a full workshop cannot be booked', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create(['capacity' => 1]);
    WorkshopBooking::factory()->for($workshop)->create();

    $this->actingAs($user)
        ->postJson(route('workshops.bookings.store', $workshop), ['company_role' => CompanyRole::Developer->value, 'company_name' => 'Acme'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('workshop_id');

    expect(WorkshopBooking::where('user_id', $user->id)->exists())->toBeFalse();
});
