<?php

namespace App\Models;

use Database\Factories\ConsumptionPickupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu transaksi serah-terima konsumsi (satu divisi per transaksi).
 * `portions_count` = jumlah porsi yang benar-benar diserahkan pada transaksi ini.
 */
#[Fillable(['event_id', 'division', 'representative_name', 'representative_contact', 'portions_count', 'handed_over_by', 'handed_over_by_name', 'photo_path', 'picked_up_at', 'notes'])]
class ConsumptionPickup extends Model
{
    /** @use HasFactory<ConsumptionPickupFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'picked_up_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Panitia yang menyerahkan konsumsi (alur panel, wajib login).
     */
    public function handedOverBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }

    /**
     * Nama panitia penyerah untuk laporan: utamakan akun panitia (alur panel),
     * jatuh ke nama yang diisi di kiosk publik bila penyerahan tanpa login.
     */
    public function handedOverByLabel(): string
    {
        return $this->handedOverBy?->name
            ?? $this->handed_over_by_name
            ?? '(tidak tercatat)';
    }

    public function hasPhoto(): bool
    {
        return filled($this->photo_path);
    }

    /**
     * Hak konsumsi peserta yang ditandai pada transaksi ini.
     */
    public function claims(): HasMany
    {
        return $this->hasMany(ConsumptionClaim::class);
    }
}
