<?php

use App\Enums\CompanyRole;
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

    $this->actingAs($user)->get(route('schedule'))
        ->assertOk()
        ->assertSee('Talk in programma')
        ->assertSee($workshop->title)
        ->assertSee('Posti rimasti: 5')
        ->assertViewHas('agendaTalks', fn ($talks) => $talks->pluck('id')->all() === [$mine->talk_id]);
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
    $booked = AgendaItem::factory()->for($user)->for(Talk::factory()->state(['title' => 'Talk già scelto', 'starts_at' => '2026-11-26 10:00', 'ends_at' => '2026-11-26 10:40']))->create();
    $overlapping = Talk::factory()->create(['starts_at' => '2026-11-26 10:20', 'ends_at' => '2026-11-26 11:00']);

    $this->actingAs($user)
        ->from(route('schedule'))
        ->post(route('agenda.store'), ['talk_id' => $overlapping->id])
        ->assertRedirect(route('schedule'))
        ->assertSessionHasErrors(['talk_id' => 'Alle 10:00 hai già in agenda "Talk già scelto".'])
        ->assertSessionHasInput('talk_id', $overlapping->id);

    $this->actingAs($user)
        ->postJson(route('agenda.store'), ['talk_id' => $overlapping->id])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Alle 10:00 hai già in agenda "Talk già scelto".');

    expect($user->agendaItems()->pluck('talk_id')->all())->toBe([$booked->talk_id]);
});

test('talks one after the other can both be in the agenda', function () {
    $user = User::factory()->create();
    AgendaItem::factory()->for($user)->for(Talk::factory()->state(['starts_at' => '2026-11-26 10:00', 'ends_at' => '2026-11-26 10:40']))->create();
    $next = Talk::factory()->create(['starts_at' => '2026-11-26 10:40', 'ends_at' => '2026-11-26 11:20']);

    $this->actingAs($user)->postJson(route('agenda.store'), ['talk_id' => $next->id])->assertCreated();

    expect($user->agendaItems()->count())->toBe(2);
});

test('adding an unknown talk is rejected', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('schedule'))
        ->post(route('agenda.store'), ['talk_id' => 999])
        ->assertRedirect(route('schedule'))
        ->assertSessionHasErrors('talk_id')
        ->assertSessionHasInput('talk_id', 999);

    expect(AgendaItem::count())->toBe(0);
});

test('a user can remove a talk and clear only their own agenda', function () {
    $user = User::factory()->create();
    [$first, $second] = AgendaItem::factory()->for($user)->count(2)->create();
    $other = AgendaItem::factory()->create();

    $this->actingAs($user)->delete(route('agenda.destroy', $first->talk))->assertRedirect();

    expect($user->agendaItems()->pluck('talk_id')->all())->toBe([$second->talk_id]);

    $this->actingAs($user)->delete(route('agenda.clear'))->assertRedirect();

    expect($user->agendaItems()->count())->toBe(0)
        ->and(AgendaItem::where('id', $other->id)->exists())->toBeTrue();
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
        ->from(route('schedule'))
        ->post(route('workshops.bookings.store', $workshop), ['company_role' => 'astronaut', 'company_name' => ''])
        ->assertRedirect(route('schedule'))
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
