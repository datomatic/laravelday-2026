<?php

use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use Database\Seeders\AttendeeSeeder;
use Database\Seeders\ConferenceSeeder;

test('the attendees have agendas without overlaps, book out one workshop and leave a free seat in the others', function () {
    $this->seed([ConferenceSeeder::class, AttendeeSeeder::class]);

    expect(User::count())->toBe(20);

    User::with('agendaItems.talk')->get()->each(function (User $user) {
        $talks = $user->agendaItems->pluck('talk');

        $talks->each(fn (Talk $talk) => expect($talks->contains(fn (Talk $other) => $other->overlaps($talk)))->toBeFalse());
    });

    [$full, $others] = Workshop::withCount('bookings')->get()->partition(fn (Workshop $workshop) => $workshop->title === AttendeeSeeder::FULL_WORKSHOP);

    expect($full->sole()->bookings_count)->toBe($full->sole()->capacity);

    $others->each(fn (Workshop $workshop) => expect($workshop->bookings_count)->toBeLessThan($workshop->capacity));
});
