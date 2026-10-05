<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Qr = 'qr';
    case Nik = 'nik';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Qr => 'Scan QR',
            self::Nik => 'Input NIK',
            self::Manual => 'Manual oleh panitia',
        };
    }
}
