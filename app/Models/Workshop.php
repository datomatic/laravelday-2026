<?php

namespace App\Models;

use Database\Factories\WorkshopFactory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\JsonSchema\Types\Type;

#[Fillable(['speaker_id', 'title', 'description', 'capacity', 'starts_at', 'ends_at'])]
class Workshop extends Model
{
    /** @use HasFactory<WorkshopFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Speaker, $this>
     */
    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    /**
     * @return HasMany<WorkshopBooking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(WorkshopBooking::class);
    }

    public function seatsLeft(): int
    {
        return max(0, $this->capacity - ($this->bookings_count ?? $this->bookings()->count()));
    }

    /**
     * @return array{id: int, title: string, description: string, day: string, starts_at: string, ends_at: string, speaker_id: int, speaker: string, seats_left: int}
     */
    public function toSummary(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'day' => $this->starts_at->toDateString(),
            'starts_at' => $this->starts_at->format('H:i'),
            'ends_at' => $this->ends_at->format('H:i'),
            'speaker_id' => $this->speaker_id,
            'speaker' => $this->speaker->name,
            'seats_left' => $this->seatsLeft(),
        ];
    }

    public static function summarySchema(JsonSchema $schema): Type
    {
        return $schema->object([
            'id' => $schema->integer()->required(),
            'title' => $schema->string()->required(),
            'description' => $schema->string()->required(),
            'day' => $schema->string()->description('Giorno, YYYY-MM-DD.')->required(),
            'starts_at' => $schema->string()->description('Ora di inizio, HH:MM.')->required(),
            'ends_at' => $schema->string()->description('Ora di fine, HH:MM.')->required(),
            'speaker_id' => $schema->integer()->required(),
            'speaker' => $schema->string()->description('Nome dello speaker.')->required(),
            'seats_left' => $schema->integer()->description('Posti ancora liberi.')->required(),
        ]);
    }
}
