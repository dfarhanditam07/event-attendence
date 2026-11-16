<?php

namespace App\Models;

use App\Enums\MealSession;
use Database\Factories\ConsumptionClaimFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penandaan hak konsumsi per peserta PER SESI MAKAN. Keberadaan satu baris =
 * porsi peserta tersebut pada sesi itu sudah diambil. UNIQUE pada
 * (`participant_id`, `meal_session`) menjamin satu peserta tidak bisa mengambil
 * porsi yang sama lebih dari sekali, tetapi tetap boleh mengambil makan siang
 * dan makan malam.
 */
#[Fillable(['consumption_pickup_id', 'event_id', 'participant_id', 'meal_session', 'claimed_at'])]
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
            'meal_session' => MealSession::class,
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
