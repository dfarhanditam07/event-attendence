<?php

namespace App\Models;

use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'location', 'starts_at', 'ends_at', 'status', 'consumption_pin', 'created_by'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => EventStatus::class,
            // PIN disimpan sebagai hash; tidak pernah dikembalikan dalam bentuk asli.
            'consumption_pin' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event): void {
            if (blank($event->slug)) {
                $event->slug = static::makeUniqueSlug($event->name);
            }
        });
    }

    public static function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'event';
        $slug = $base;
        $i = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function consumptionPickups(): HasMany
    {
        return $this->hasMany(ConsumptionPickup::class);
    }

    public function consumptionClaims(): HasMany
    {
        return $this->hasMany(ConsumptionClaim::class);
    }

    /**
     * Status yang berlaku saat ini. Event berstatus "Dibuka" otomatis dianggap
     * "Ditutup" begitu waktu selesai (ends_at) terlewati, tanpa perlu scheduler/cron.
     * Kalau ends_at kosong, event tetap terbuka sampai status diubah manual.
     */
    public function effectiveStatus(): EventStatus
    {
        if ($this->status === EventStatus::Open && $this->ends_at?->isPast()) {
            return EventStatus::Closed;
        }

        return $this->status;
    }

    public function allowsCheckIn(): bool
    {
        return $this->effectiveStatus()->allowsCheckIn();
    }

    /**
     * Kiosk pengambilan konsumsi aktif hanya bila panitia sudah menetapkan PIN konsumsi.
     */
    public function hasConsumptionKiosk(): bool
    {
        return filled($this->consumption_pin);
    }

    /**
     * Cocokkan PIN yang dimasukkan panitia di kiosk dengan PIN event (tersimpan ter-hash).
     */
    public function verifyConsumptionPin(string $pin): bool
    {
        if (! $this->hasConsumptionKiosk()) {
            return false;
        }

        return Hash::check($pin, $this->consumption_pin);
    }

    /**
     * Filter berdasarkan status efektif (lihat effectiveStatus()), supaya hasil filter
     * sejalan dengan badge status yang tampil di layar.
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeWithEffectiveStatus(Builder $query, EventStatus $status): Builder
    {
        $open = EventStatus::Open->value;

        return match ($status) {
            EventStatus::Open => $query->where('status', $open)
                ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now())),
            EventStatus::Closed => $query->where(fn (Builder $q) => $q
                ->where('status', EventStatus::Closed->value)
                ->orWhere(fn (Builder $o) => $o->where('status', $open)->where('ends_at', '<', now()))),
            default => $query->where('status', $status->value),
        };
    }
}
