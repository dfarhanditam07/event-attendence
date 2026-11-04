<?php

namespace Database\Factories;

use App\Models\ConsumptionClaim;
use App\Models\ConsumptionPickup;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsumptionClaim>
 */
class ConsumptionClaimFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consumption_pickup_id' => ConsumptionPickup::factory(),
            'event_id' => Event::factory(),
            'participant_id' => Participant::factory(),
            'claimed_at' => now(),
        ];
    }
}
