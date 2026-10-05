<?php

namespace App\Services;

use App\Enums\AttendanceMethod;
use App\Enums\EventStatus;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

class CheckInResult
{
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
}

class CheckInService
{
    /**
     * Check-in via QR Token (scanner QR).
     */
    public function checkInByQrToken(Event $event, string $qrToken, User $checkedInBy, ?string $notes = null): CheckInResult
    {
        if (! $event->status->allowsCheckIn()) {
            return CheckInResult::fail([
                '_event' => ['Absensi hanya bisa dilakukan saat event berstatus "'.EventStatus::Open->label().'".'],
            ]);
        }

        $participant = Participant::where('event_id', $event->id)
            ->where('qr_token', $qrToken)
            ->first();

        if (! $participant) {
            return CheckInResult::fail([
                '_participant' => ['QR tidak dikenali untuk event ini.'],
            ]);
        }

        return $this->persistCheckIn($event, $participant, AttendanceMethod::Qr, $checkedInBy, $notes);
    }

    /**
     * Check-in via input NIK.
     */
    public function checkInByNik(Event $event, string $nik, User $checkedInBy, ?string $notes = null): CheckInResult
    {
        if (! $event->status->allowsCheckIn()) {
            return CheckInResult::fail([
                '_event' => ['Absensi hanya bisa dilakukan saat event berstatus "'.EventStatus::Open->label().'".'],
            ]);
        }

        $nik = trim((string) $nik);

        if ($nik === '') {
            return CheckInResult::fail([
                'nik' => ['NIK wajib diisi.'],
            ]);
        }

        $participant = Participant::where('event_id', $event->id)
            ->whereRaw('LOWER(nik) = ?', [mb_strtolower($nik)])
            ->first();

        if (! $participant) {
            return CheckInResult::fail([
                '_participant' => ['NIK "'.e($nik).'" tidak terdaftar di event ini.'],
            ]);
        }

        return $this->persistCheckIn($event, $participant, AttendanceMethod::Nik, $checkedInBy, $notes);
    }

    /**
     * Check-in manual: pilih participant dari daftar.
     */
    public function checkInManual(Event $event, int $participantId, User $checkedInBy, ?string $notes = null): CheckInResult
    {
        if (! $event->status->allowsCheckIn()) {
            return CheckInResult::fail([
                '_event' => ['Absensi hanya bisa dilakukan saat event berstatus "'.EventStatus::Open->label().'".'],
            ]);
        }

        $participant = Participant::where('event_id', $event->id)
            ->where('id', $participantId)
            ->first();

        if (! $participant) {
            return CheckInResult::fail([
                '_participant' => ['Peserta tidak ditemukan di event ini.'],
            ]);
        }

        return $this->persistCheckIn($event, $participant, AttendanceMethod::Manual, $checkedInBy, $notes);
    }

    private function persistCheckIn(
        Event $event,
        Participant $participant,
        AttendanceMethod $method,
        User $checkedInBy,
        ?string $notes,
    ): CheckInResult {
        $existing = Attendance::where('participant_id', $participant->id)->first();
        if ($existing) {
            return CheckInResult::fail([
                '_duplicate' => [
                    $participant->name.' sudah absen sebelumnya pada '.
                    Carbon::parse($existing->checked_in_at)->translatedFormat('d M Y, H:i').
                    ' via '.$existing->method->label().'.',
                ],
            ], $participant);
        }

        try {
            $attendance = Attendance::create([
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'checked_in_at' => now(),
                'method' => $method,
                'checked_in_by' => $checkedInBy->id,
                'notes' => $notes,
            ]);

            return CheckInResult::ok($attendance, $participant);
        } catch (QueryException $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();

            // Unique constraint violation (participant_id) — race condition detected.
            if (
                (is_numeric($code) && (int) $code === 23505) ||
                (is_string($code) && str_contains($msg, 'unique'))
            ) {
                $existing = Attendance::where('participant_id', $participant->id)->first();

                return CheckInResult::fail([
                    '_duplicate' => [
                        $participant->name.' sudah absen sebelumnya pada '.
                        ($existing ? Carbon::parse($existing->checked_in_at)->translatedFormat('d M Y, H:i') : '—').
                        '.',
                    ],
                ], $participant);
            }

            return CheckInResult::fail([
                '_system' => ['Gagal menyimpan absensi: '.$msg],
            ], $participant);
        }
    }
}
