<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventEffectiveStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_status_follows_stored_status_and_end_time(): void
    {
        $running = Event::factory()->create(['starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
        $ended = Event::factory()->create(['starts_at' => now()->subHours(5), 'ends_at' => now()->subHour()]);
        $noEnd = Event::factory()->create(['starts_at' => now()->subDay(), 'ends_at' => null]);
        $draftEnded = Event::factory()->draft()->create(['starts_at' => now()->subHours(5), 'ends_at' => now()->subHour()]);
        $closed = Event::factory()->closed()->create();

        $this->assertSame(EventStatus::Open, $running->effectiveStatus());
        $this->assertSame(EventStatus::Closed, $ended->effectiveStatus());
        $this->assertSame(EventStatus::Open, $noEnd->effectiveStatus());
        $this->assertSame(EventStatus::Draft, $draftEnded->effectiveStatus());
        $this->assertSame(EventStatus::Closed, $closed->effectiveStatus());

        $this->assertTrue($running->allowsCheckIn());
        $this->assertFalse($ended->allowsCheckIn());
        $this->assertTrue($noEnd->allowsCheckIn());
        $this->assertFalse($draftEnded->allowsCheckIn());
        $this->assertFalse($closed->allowsCheckIn());
    }

    public function test_status_filter_uses_effective_status(): void
    {
        $running = Event::factory()->create(['starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
        $ended = Event::factory()->create(['starts_at' => now()->subHours(5), 'ends_at' => now()->subHour()]);
        $closed = Event::factory()->closed()->create();
        $draft = Event::factory()->draft()->create();

        $this->assertEqualsCanonicalizing(
            [$running->id],
            Event::query()->withEffectiveStatus(EventStatus::Open)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$ended->id, $closed->id],
            Event::query()->withEffectiveStatus(EventStatus::Closed)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$draft->id],
            Event::query()->withEffectiveStatus(EventStatus::Draft)->pluck('id')->all(),
        );
    }
}
