<?php

namespace App\Models;

use App\Enums\Track;
use Database\Factories\TalkFactory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\JsonSchema\Types\Type;

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

    public function overlaps(Talk $other): bool
    {
        return $other->id !== $this->id
            && $other->starts_at < $this->ends_at
            && $other->ends_at > $this->starts_at;
    }

    /**
     * A talk alone in its time slot, such as the opening: everyone attends, there is nothing to choose.
     */
    public function isPlenary(): bool
    {
        return ! static::where('starts_at', $this->starts_at)->where('id', '!=', $this->id)->exists();
    }

    /**
     * @return array{id: int, title: string, abstract: string, track: string, room: string, starts_at: string, ends_at: string, speaker_id: int, speaker: string}
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
            'speaker_id' => $this->speaker_id,
            'speaker' => $this->speaker->name,
        ];
    }

    public static function summarySchema(JsonSchema $schema): Type
    {
        return $schema->object([
            'id' => $schema->integer()->required(),
            'title' => $schema->string()->required(),
            'abstract' => $schema->string()->required(),
            'track' => $schema->string()->enum(Track::class)->required(),
            'room' => $schema->string()->required(),
            'starts_at' => $schema->string()->description('Ora di inizio, HH:MM.')->required(),
            'ends_at' => $schema->string()->description('Ora di fine, HH:MM.')->required(),
            'speaker_id' => $schema->integer()->required(),
            'speaker' => $schema->string()->description('Nome dello speaker.')->required(),
        ]);
    }
}
