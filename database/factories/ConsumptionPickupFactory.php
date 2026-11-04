<?php

namespace Database\Factories;

use App\Models\ConsumptionPickup;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsumptionPickup>
 */
class ConsumptionPickupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'division' => fake()->company(),
            'representative_name' => fake()->name(),
            'representative_contact' => fake()->numerify('08##########'),
            'portions_count' => fake()->numberBetween(1, 20),
            'handed_over_by' => User::factory(),
            'picked_up_at' => now(),
            'notes' => null,
        ];
    }
}
