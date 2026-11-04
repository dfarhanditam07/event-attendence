<?php

namespace App\Models;

use Database\Factories\ParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

// `qr_token` dibuat otomatis oleh sistem (kolom lama, NOT NULL) dan tidak boleh diisi dari input.
#[Fillable(['event_id', 'name', 'nik', 'email', 'phone', 'organization'])]
class Participant extends Model
{
    /** @use HasFactory<ParticipantFactory> */
    use HasFactory;

    /**
     * Label penampung untuk peserta yang tidak punya nilai `organization`.
     */
    public const UNASSIGNED_DIVISION = 'Tanpa Divisi';

    protected static function booted(): void
    {
        static::creating(function (Participant $participant): void {
            $participant->qr_token ??= Str::random(40);
        });

        static::saving(function (Participant $participant): void {
            $participant->nik = static::normalizeNik($participant->nik);
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class);
    }

    public function consumptionClaim(): HasOne
    {
        return $this->hasOne(ConsumptionClaim::class);
    }

    /**
     * Nama divisi dari nilai `organization`. Peserta tanpa organisasi dikelompokkan
     * ke satu penampung "Tanpa Divisi".
     */
    public static function normalizeDivision(?string $organization): string
    {
        $organization = trim((string) $organization);

        return $organization !== '' ? $organization : self::UNASSIGNED_DIVISION;
    }

    /**
     * Filter peserta yang berada di divisi tertentu (lihat normalizeDivision()).
     *
     * @param  Builder<Participant>  $query
     * @return Builder<Participant>
     */
    public function scopeInDivision(Builder $query, string $division): Builder
    {
        if ($division === self::UNASSIGNED_DIVISION) {
            return $query->where(fn (Builder $q): Builder => $q
                ->whereNull('organization')
                ->orWhereRaw("TRIM(organization) = ''"));
        }

        return $query->whereRaw('TRIM(organization) = ?', [$division]);
    }

    /**
     * Samakan format NIK dari input (form/CSV) supaya pencocokan konsisten.
     *
     * - Kosong -> null.
     * - Kalau isinya hanya angka + pemisah (spasi, titik, strip) -> sisakan angka saja.
     * - Selain itu (mis. "EMP-001") dibiarkan apa adanya.
     */
    public static function normalizeNik(?string $nik): ?string
    {
        $nik = trim((string) $nik);

        if ($nik === '') {
            return null;
        }

        if (preg_match('/^[\d\s.\-]+$/', $nik) === 1) {
            return preg_replace('/\D/', '', $nik);
        }

        return $nik;
    }

    /**
     * NIK yang disamarkan, hanya 4 karakter terakhir yang terlihat.
     * Dipakai di halaman publik supaya NIK lengkap tidak pernah tampil.
     */
    public function maskedNik(): ?string
    {
        $nik = (string) $this->nik;

        if ($nik === '') {
            return null;
        }

        $length = mb_strlen($nik);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4).mb_substr($nik, -4);
    }

    /**
     * Cari berdasarkan nama, NIK, email, atau organisasi (tidak peka huruf besar/kecil).
     * Untuk panel panitia (sudah login). JANGAN dipakai di halaman publik.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.mb_strtolower($term).'%';

        return $query->where(function (Builder $q) use ($like): void {
            $q->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(nik) LIKE ?', [$like])
                ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                ->orWhereRaw('LOWER(organization) LIKE ?', [$like]);
        });
    }

    /**
     * Cari berdasarkan NAMA saja; setiap kata harus ada di dalam nama.
     * Aman untuk halaman publik: tidak mencocokkan NIK, email, atau organisasi.
     */
    public function scopeNameMatches(Builder $query, string $term): Builder
    {
        $words = preg_split('/\s+/u', mb_strtolower(trim($term)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($words as $word) {
            // Buang karakter wildcard supaya "%" / "_" tidak bisa dipakai menarik semua data.
            $word = str_replace(['%', '_', '\\'], '', $word);

            if ($word !== '') {
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.$word.'%']);
            }
        }

        return $query;
    }
}
