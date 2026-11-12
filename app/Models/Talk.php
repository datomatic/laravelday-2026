<?php

namespace App\Models;

use App\Enums\Track;
use Database\Factories\TalkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['speaker_id', 'title', 'abstract', 'track', 'room', 'starts_at', 'ends_at'])]
class Talk extends Model
{
    /** @use HasFactory<TalkFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'track' => Track::class,
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
     * @return HasMany<AgendaItem, $this>
     */
    public function agendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class);
    }

    /**
     * @return array{id: int, title: string, abstract: string, track: string, room: string, starts_at: string, ends_at: string, speaker: string}
     */
    public function toSummary(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'abstract' => $this->abstract,
            'track' => $this->track->value,
            'room' => $this->room,
            'starts_at' => $this->starts_at->format('H:i'),
            'ends_at' => $this->ends_at->format('H:i'),
            'speaker' => $this->speaker->name,
        ];
    }
}
