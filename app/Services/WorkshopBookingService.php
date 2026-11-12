<?php

namespace App\Services;

use App\Enums\CompanyRole;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkshopBookingService
{
    /**
     * @return Collection<int, Workshop>
     */
    public function workshops(): Collection
    {
        return Workshop::with('speaker')->withCount('bookings')->orderBy('starts_at')->get();
    }

    /**
     * @throws ValidationException
     */
    public function book(User $user, Workshop $workshop, CompanyRole $companyRole, string $companyName): WorkshopBooking
    {
        return DB::transaction(function () use ($user, $workshop, $companyRole, $companyName): WorkshopBooking {
            $existing = $workshop->bookings()->where('user_id', $user->id)->first();

            if ($existing) {
                return $existing;
            }

            if ($workshop->bookings()->count() >= $workshop->capacity) {
                throw ValidationException::withMessages([
                    'workshop_id' => "Il workshop \"{$workshop->title}\" è al completo.",
                ]);
            }

            return $workshop->bookings()->create([
                'user_id' => $user->id,
                'company_role' => $companyRole,
                'company_name' => $companyName,
            ]);
        });
    }
}
