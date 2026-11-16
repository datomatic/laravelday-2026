<?php

namespace Database\Factories;

use App\Enums\Track;
use App\Models\Speaker;
use App\Models\Talk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Talk>
 */
class TalkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'speaker_id' => Speaker::factory(),
            'title' => fake()->sentence(5),
            'abstract' => fake()->paragraph(),
            'track' => fake()->randomElement(Track::cases()),
            'room' => fake()->randomElement(['Sala Verona', 'Sala Arena']),
            'starts_at' => '2026-11-18 10:00:00',
            'ends_at' => '2026-11-18 10:40:00',
        ];
    }
}
