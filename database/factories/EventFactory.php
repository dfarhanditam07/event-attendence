<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+30 days');

        return [
            'name' => fake()->unique()->sentence(3),
            'description' => fake()->paragraph(),
            'location' => fake()->city(),
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+4 hours'),
            'status' => EventStatus::Open,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Draft]);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Closed]);
    }
}
