<?php

namespace Database\Seeders;

use App\Enums\CompanyRole;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use App\Services\AgendaService;
use App\Services\WorkshopBookingService;
use Illuminate\Database\Seeder;

/**
 * Other attendees, so the charts and the workshop seats are not empty at the start of the demo.
 * They go through the same services as the app: one talk per slot, and the bookings respect capacity.
 */
class AttendeeSeeder extends Seeder
{
    private const ATTENDEES = 20;

    /**
     * Booked out by the attendees, so the "al completo" error can be shown live in the demo.
     */
    public const FULL_WORKSHOP = 'Testing con Pest da zero';

    public function run(AgendaService $agenda, WorkshopBookingService $bookings): void
    {
        $slots = Talk::orderBy('starts_at')->get()->groupBy(fn (Talk $talk) => $talk->starts_at->toDateTimeString());
        $workshopSlots = Workshop::orderBy('starts_at')->get()->groupBy(fn (Workshop $workshop) => $workshop->starts_at->toDateTimeString());

        User::factory()
            ->count(self::ATTENDEES)
            ->state(fn () => ['name' => fake('it_IT')->name()])
            ->create()
            ->each(function (User $user) use ($agenda, $bookings, $slots, $workshopSlots): void {
                foreach ($slots as $talks) {
                    if (fake()->boolean(80)) {
                        $agenda->add($user, $talks->random());
                    }
                }

                foreach ($workshopSlots as $workshops) {
                    $full = $workshops->firstWhere('title', self::FULL_WORKSHOP);

                    if ($full && $full->bookings()->count() < $full->capacity) {
                        $bookings->book($user, $full, fake()->randomElement(CompanyRole::cases()), fake('it_IT')->company());

                        continue;
                    }

                    $workshop = $workshops->reject(fn (Workshop $workshop) => $workshop->is($full))->random();

                    // The other workshops keep their last seat free, so the attendee of the demo can still book them.
                    if (fake()->boolean(85) && $workshop->bookings()->count() < $workshop->capacity - 1) {
                        $bookings->book($user, $workshop, fake()->randomElement(CompanyRole::cases()), fake('it_IT')->company());
                    }
                }
            });

        // Spread the sign-ups over the last 15 days, so the admin dashboard has a daily trend to show.
        AgendaItem::all()->merge(WorkshopBooking::all())->each(
            fn (AgendaItem|WorkshopBooking $record) => $record->forceFill(['created_at' => fake()->dateTimeBetween('-15 days')])->saveQuietly(),
        );
    }
}
