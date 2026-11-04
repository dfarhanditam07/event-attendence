<?php

namespace App\Models;

use Database\Factories\ConsumptionClaimFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penandaan hak konsumsi per peserta. Keberadaan satu baris = porsi peserta tersebut
 * sudah diambil. UNIQUE pada `participant_id` menjamin satu peserta tidak bisa diambil
 * konsumsinya lebih dari sekali.
 */
#[Fillable(['consumption_pickup_id', 'event_id', 'participant_id', 'claimed_at'])]
class ConsumptionClaim extends Model
{
    /** @use HasFactory<ConsumptionClaimFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
        ];
    }

    public function pickup(): BelongsTo
    {
        return $this->belongsTo(ConsumptionPickup::class, 'consumption_pickup_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
