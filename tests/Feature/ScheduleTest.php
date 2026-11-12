<?php

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

test('a user can book a workshop once', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();

    $this->actingAs($user)->post(route('workshops.bookings.store', $workshop))->assertRedirect();
    $this->actingAs($user)->postJson(route('workshops.bookings.store', $workshop))->assertCreated();

    expect(WorkshopBooking::where('user_id', $user->id)->count())->toBe(1);
});

test('a full workshop cannot be booked', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create(['capacity' => 1]);
    WorkshopBooking::factory()->for($workshop)->create();

    $this->actingAs($user)->postJson(route('workshops.bookings.store', $workshop))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('workshop_id');

    expect(WorkshopBooking::where('user_id', $user->id)->exists())->toBeFalse();
});
