<?php

namespace App\Enums;

/**
 * Sesi pengambilan konsumsi. Satu peserta yang hadir berhak atas satu porsi
 * pada SETIAP sesi, sehingga satu kehadiran menghasilkan dua porsi
 * (makan siang dan makan malam).
 */
enum MealSession: string
{
    case Lunch = 'lunch';
    case Dinner = 'dinner';

    public function label(): string
    {
        return match ($this) {
            self::Lunch => 'Makan Siang',
            self::Dinner => 'Makan Malam',
        };
    }
}
