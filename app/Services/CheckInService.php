<?php

namespace App\Services;

use App\Enums\AttendanceMethod;
use App\Enums\EventStatus;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Check-in peserta. Hanya dua cara: input NIK, atau pilih nama dari hasil pencarian.
 * Satu peserta hanya bisa check-in sekali (dijaga juga oleh UNIQUE di database).
 */
class CheckInService
{
    public function checkInByNik(Event $event, string $nik, ?User $checkedInBy = null): CheckInResult
    {
        if ($blocked = $this->guardEvent($event)) {
            return $blocked;
        }

        $nik = Participant::normalizeNik($nik);

        if ($nik === null) {
            return CheckInResult::fail(['nik' => ['NIK wajib diisi.']]);
        }

        $participant = Participant::where('event_id', $event->id)
            ->whereRaw('LOWER(nik) = ?', [mb_strtolower($nik)])
            ->first();

        if (! $participant) {
            return CheckInResult::fail([
                '_participant' => ['NIK tidak ditemukan di event ini. Periksa kembali atau hubungi panitia.'],
            ]);
        }

        return $this->persist($event, $participant, AttendanceMethod::Nik, $checkedInBy);
    }

    public function checkInByName(Event $event, int $participantId, ?User $checkedInBy = null): CheckInResult
    {
        if ($blocked = $this->guardEvent($event)) {
            return $blocked;
        }

        $participant = Participant::where('event_id', $event->id)
            ->whereKey($participantId)
            ->first();

        if (! $participant) {
            return CheckInResult::fail([
                '_participant' => ['Peserta tidak ditemukan di event ini.'],
            ]);
        }

        return $this->persist($event, $participant, AttendanceMethod::Name, $checkedInBy);
    }

    private function guardEvent(Event $event): ?CheckInResult
    {
        if ($event->allowsCheckIn()) {
            return null;
        }

        return CheckInResult::fail([
            '_event' => ['Check-in hanya bisa dilakukan saat event berstatus "'.EventStatus::Open->label().'".'],
        ]);
    }

    private function persist(Event $event, Participant $participant, AttendanceMethod $method, ?User $checkedInBy): CheckInResult
    {
        $existing = Attendance::where('participant_id', $participant->id)->first();

        if ($existing) {
            return $this->duplicate($participant, $existing);
        }

        try {
            $attendance = Attendance::create([
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'checked_in_at' => now(),
                'method' => $method,
                'checked_in_by' => $checkedInBy?->id,
            ]);

            return CheckInResult::ok($attendance, $participant);
        } catch (UniqueConstraintViolationException) {
            // Dua permintaan masuk bersamaan; yang kedua kalah di constraint database.
            return $this->duplicate(
                $participant,
                Attendance::where('participant_id', $participant->id)->first(),
            );
        }
    }

    private function duplicate(Participant $participant, ?Attendance $existing): CheckInResult
    {
        $detail = $existing
            ? ' pada '.$existing->checked_in_at->translatedFormat('d M Y, H:i').' ('.$existing->method->label().')'
            : '';

        return CheckInResult::fail([
            '_duplicate' => [$participant->name.' sudah check-in sebelumnya'.$detail.'.'],
        ], $participant);
    }
}
