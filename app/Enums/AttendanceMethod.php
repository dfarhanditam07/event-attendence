<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Nik = 'nik';
    case Name = 'name';

    // Metode lama, dipertahankan hanya supaya data absensi yang sudah ada
    // tetap terbaca. Tidak dipakai lagi untuk check-in baru.
    case Qr = 'qr';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Nik => 'Input NIK',
            self::Name => 'Cari nama',
            self::Qr => 'Scan QR (lama)',
            self::Manual => 'Manual (lama)',
        };
    }

    /**
     * Metode yang boleh dipakai untuk check-in baru.
     *
     * @return list<self>
     */
    public static function active(): array
    {
        return [self::Nik, self::Name];
    }
}
