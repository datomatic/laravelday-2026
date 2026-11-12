<?php

namespace Database\Factories;

use App\Models\Speaker;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
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
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'capacity' => 20,
            'starts_at' => '2026-11-26 14:00:00',
            'ends_at' => '2026-11-26 16:00:00',
        ];
    }
}
