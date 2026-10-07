<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Participant;

class CheckInResult
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?Attendance $attendance = null,
        public readonly ?Participant $participant = null,
        public readonly array $errors = [],
    ) {}

    public static function ok(Attendance $attendance, Participant $participant): self
    {
        return new self(true, $attendance, $participant);
    }

    /**
     * @param  array<string, list<string>>  $errors
     */
    public static function fail(array $errors, ?Participant $participant = null): self
    {
        return new self(false, null, $participant, $errors);
    }

    /**
     * Data sederhana (hanya string/bool/array) untuk disimpan di session flash.
     * Sengaja tidak menyimpan objek Carbon/enum supaya aman saat dirender ulang.
     *
     * @return array<string, mixed>
     */
    public function toFlash(): array
    {
        return [
            'success' => $this->success,
            'participant_name' => $this->participant?->name,
            'participant_organization' => $this->participant?->organization,
            'checked_in_at' => $this->attendance?->checked_in_at?->translatedFormat('d M Y, H:i:s'),
            'method_label' => $this->attendance?->method->label(),
            'errors' => $this->errors,
        ];
    }
}
