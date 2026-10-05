<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->name(),
            'nik' => fake()->unique()->numerify('EMP-######'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'organization' => fake()->company(),
        ];
    }
}
