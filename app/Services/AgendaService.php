<?php

namespace App\Services;

use App\Enums\Track;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

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

    public function add(User $user, Talk $talk): AgendaItem
    {
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
