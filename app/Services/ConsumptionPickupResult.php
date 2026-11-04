<?php

namespace App\Services;

use App\Models\ConsumptionPickup;

class ConsumptionPickupResult
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?ConsumptionPickup $pickup = null,
        public readonly array $errors = [],
    ) {}

    public static function ok(ConsumptionPickup $pickup): self
    {
        return new self(true, $pickup);
    }

    /**
     * @param  array<string, list<string>>  $errors
     */
    public static function fail(array $errors): self
    {
        return new self(false, null, $errors);
    }
}
