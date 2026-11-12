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
            'bisnis_unit' => fake()->randomElement([
                'Divisi IT',
                'Divisi Keuangan',
                'Divisi SDM',
                'Divisi Operasional',
                'Divisi Pemasaran',
            ]),
            'jabatan' => fake()->jobTitle(),
        ];
    }
}
