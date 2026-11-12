<?php

namespace App\Services;

use App\Enums\Track;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class AgendaService
{
    /**
     * @return Collection<int, Talk>
     */
    public function searchTalks(?string $query = null, ?Track $track = null): Collection
    {
        return Talk::with('speaker')
            ->when($query, fn ($talks) => $talks->where(fn ($talks) => $talks
                ->whereLike('title', "%{$query}%")
                ->orWhereLike('abstract', "%{$query}%")
                ->orWhereHas('speaker', fn ($speakers) => $speakers->whereLike('name', "%{$query}%"))))
            ->when($track, fn ($talks) => $talks->where('track', $track))
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * @return Collection<int, Talk>
     */
    public function talksFor(User $user): Collection
    {
        return Talk::with('speaker')
            ->whereHas('agendaItems', fn ($items) => $items->where('user_id', $user->id))
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * @throws ValidationException
     */
    public function add(User $user, Talk $talk): AgendaItem
    {
        $overlapping = Talk::where('id', '!=', $talk->id)
            ->whereHas('agendaItems', fn ($items) => $items->where('user_id', $user->id))
            ->where('starts_at', '<', $talk->ends_at)
            ->where('ends_at', '>', $talk->starts_at)
            ->first();

        if ($overlapping) {
            throw ValidationException::withMessages([
                'talk_id' => "Alle {$overlapping->starts_at->format('H:i')} hai già in agenda \"{$overlapping->title}\".",
            ]);
        }

        return AgendaItem::firstOrCreate(['user_id' => $user->id, 'talk_id' => $talk->id]);
    }

    public function remove(User $user, Talk $talk): void
    {
        $user->agendaItems()->where('talk_id', $talk->id)->delete();
    }

    public function clear(User $user): int
    {
        return $user->agendaItems()->delete();
    }
}
