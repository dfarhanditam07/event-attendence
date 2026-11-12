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
#[Fillable(['event_id', 'name', 'bisnis_unit', 'jabatan'])]
class Participant extends Model
{
    /** @use HasFactory<ParticipantFactory> */
    use HasFactory;

    /**
     * Label penampung untuk peserta yang tidak punya nilai `bisnis_unit`.
     */
    public const UNASSIGNED_DIVISION = 'Tanpa Divisi';

    protected static function booted(): void
    {
        static::creating(function (Participant $participant): void {
            $participant->qr_token ??= Str::random(40);
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
     * Nama divisi dari nilai `bisnis_unit`. Peserta tanpa bisnis unit dikelompokkan
     * ke satu penampung "Tanpa Divisi".
     */
    public static function normalizeDivision(?string $bisnisUnit): string
    {
        $bisnisUnit = trim((string) $bisnisUnit);

        return $bisnisUnit !== '' ? $bisnisUnit : self::UNASSIGNED_DIVISION;
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
                ->whereNull('bisnis_unit')
                ->orWhereRaw("TRIM(bisnis_unit) = ''"));
        }

        return $query->whereRaw('TRIM(bisnis_unit) = ?', [$division]);
    }

    /**
     * Cari berdasarkan nama, bisnis unit, atau jabatan (tidak peka huruf besar/kecil).
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
                ->orWhereRaw('LOWER(bisnis_unit) LIKE ?', [$like])
                ->orWhereRaw('LOWER(jabatan) LIKE ?', [$like]);
        });
    }

    /**
     * Cari berdasarkan NAMA saja; setiap kata harus ada di dalam nama.
     * Aman untuk halaman publik: tidak mencocokkan bisnis unit atau jabatan.
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
