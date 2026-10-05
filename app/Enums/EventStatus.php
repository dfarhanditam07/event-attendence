<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Open => 'Dibuka',
            self::Closed => 'Ditutup',
        };
    }

    /**
     * Absensi hanya boleh dilakukan saat event berstatus "Dibuka".
     */
    public function allowsCheckIn(): bool
    {
        return $this === self::Open;
    }
}
