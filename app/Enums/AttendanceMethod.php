<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Name = 'name';

    public function label(): string
    {
        return match ($this) {
            self::Name => 'Cari nama',
        };
    }
}
