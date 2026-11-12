<?php

namespace App\Models;

use App\Enums\CompanyRole;
use Database\Factories\WorkshopBookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'workshop_id', 'company_role', 'company_name'])]
class WorkshopBooking extends Model
{
    /** @use HasFactory<WorkshopBookingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'company_role' => CompanyRole::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Workshop, $this>
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
}
