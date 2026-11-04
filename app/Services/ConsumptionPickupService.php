<?php

namespace App\Services;

use App\Models\ConsumptionClaim;
use App\Models\ConsumptionPickup;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Transaksi penyerahan konsumsi kolektif per divisi.
 *
 * Aturan:
 * - Porsi hanya untuk anggota divisi yang sudah HADIR (punya baris `attendances`).
 * - Satu peserta hanya boleh diambil porsinya SEKALI. Dijaga berlapis:
 *   pengecekan "belum pernah diambil" di sini, plus UNIQUE pada `consumption_claims`.
 */
class ConsumptionPickupService
{
    /**
     * Disk penyimpanan foto bukti penyerahan. Sengaja disk privat supaya foto
     * tidak bisa diakses langsung dari URL dan hanya disajikan lewat route ber-otorisasi.
     */
    public const EVIDENCE_DISK = 'local';

    private const EVIDENCE_DIRECTORY = 'consumption-evidence';

    /**
     * Peserta divisi yang berhak: sudah hadir dan porsinya belum pernah diambil.
     *
     * @return Collection<int, Participant>
     */
    public function eligibleParticipants(Event $event, string $division): Collection
    {
        return $event->participants()
            ->with('attendance')
            ->whereHas('attendance')
            ->whereDoesntHave('consumptionClaim')
            ->inDivision($division)
            ->orderByRaw('LOWER(name) ASC')
            ->get();
    }

    /**
     * Rekap per divisi: total hadir, sudah diambil, dan sisa porsi.
     *
     * Satu query agregat (JOIN attendances + LEFT JOIN claims) supaya tidak N+1.
     * Pengelompokan dilakukan pada kolom `organization` apa adanya, lalu label divisi
     * dinormalkan di PHP (lihat Participant::normalizeDivision()) agar variasi
     * spasi/kosong tetap masuk ke satu baris.
     *
     * @return Collection<int, array{division: string, present: int, claimed: int, remaining: int}>
     */
    public function divisionSummaries(Event $event): Collection
    {
        return DB::table('participants')
            ->join('attendances', 'attendances.participant_id', '=', 'participants.id')
            ->leftJoin('consumption_claims', 'consumption_claims.participant_id', '=', 'participants.id')
            ->where('participants.event_id', $event->id)
            ->groupBy('participants.organization')
            ->select('participants.organization')
            ->selectRaw('COUNT(participants.id) AS present_count')
            ->selectRaw('COUNT(consumption_claims.id) AS claimed_count')
            ->get()
            ->groupBy(fn (object $row): string => Participant::normalizeDivision($row->organization))
            ->map(function (Collection $rows, string $division): array {
                $present = (int) $rows->sum('present_count');
                $claimed = (int) $rows->sum('claimed_count');

                return [
                    'division' => $division,
                    'present' => $present,
                    'claimed' => $claimed,
                    'remaining' => $present - $claimed,
                ];
            })
            ->sortKeys()
            ->values();
    }

    /**
     * Catat serah-terima konsumsi untuk seluruh anggota divisi yang hadir sekaligus.
     *
     * @param  User|null  $handedOverBy  Panitia penyerah bila tercatat lewat akun (alur panel).
     *                                   Null pada alur kiosk publik, nama penyerah diambil dari $data.
     * @param  UploadedFile|null  $photo  Foto bukti penyerahan (wajib pada alur kiosk).
     * @param  array<string, mixed>  $data  hasil validasi request
     */
    public function pickUp(Event $event, ?User $handedOverBy, array $data, ?UploadedFile $photo = null): ConsumptionPickupResult
    {
        $division = (string) $data['division'];
        $participants = $this->eligibleParticipants($event, $division);

        if ($participants->isEmpty()) {
            return ConsumptionPickupResult::fail([
                '_empty' => ['Tidak ada anggota divisi "'.$division.'" yang hadir dan belum diambil konsumsinya.'],
            ]);
        }

        $photoPath = $photo?->store(self::EVIDENCE_DIRECTORY, self::EVIDENCE_DISK);

        try {
            $pickup = DB::transaction(function () use ($event, $handedOverBy, $data, $division, $participants, $photoPath): ConsumptionPickup {
                $pickup = ConsumptionPickup::create([
                    'event_id' => $event->id,
                    'division' => $division,
                    'representative_name' => $data['representative_name'],
                    'representative_contact' => $data['representative_contact'] ?? null,
                    'portions_count' => $participants->count(),
                    'handed_over_by' => $handedOverBy?->id,
                    'handed_over_by_name' => $data['handed_over_by_name'] ?? null,
                    'photo_path' => $photoPath,
                    'picked_up_at' => now(),
                    'notes' => $data['notes'] ?? null,
                ]);

                $claimedAt = now();

                foreach ($participants as $participant) {
                    ConsumptionClaim::create([
                        'consumption_pickup_id' => $pickup->id,
                        'event_id' => $event->id,
                        'participant_id' => $participant->id,
                        'claimed_at' => $claimedAt,
                    ]);
                }

                return $pickup;
            });
        } catch (UniqueConstraintViolationException) {
            // Transaksi lain menandai peserta yang sama lebih dulu. Seluruh transaksi dibatalkan,
            // jadi tidak ada porsi yang tercatat ganda. Foto bukti yang terlanjur tersimpan dibuang.
            if ($photoPath !== null) {
                Storage::disk(self::EVIDENCE_DISK)->delete($photoPath);
            }

            return ConsumptionPickupResult::fail([
                '_duplicate' => ['Konsumsi divisi ini baru saja diambil pada transaksi lain. Muat ulang halaman lalu coba lagi.'],
            ]);
        }

        return ConsumptionPickupResult::ok($pickup);
    }
}
