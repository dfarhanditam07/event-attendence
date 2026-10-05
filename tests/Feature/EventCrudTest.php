<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCrudTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Seminar Teknologi 2026',
            'location' => 'Aula Utama',
            'description' => 'Seminar tahunan.',
            'starts_at' => '2026-12-01 09:00',
            'ends_at' => '2026-12-01 12:00',
            'status' => 'open',
        ], $override);
    }

    public function test_panitia_bisa_melihat_daftar_dan_detail_event(): void
    {
        $event = Event::factory()->create(['name' => 'Rapat Akbar']);
        Participant::factory()->count(3)->create(['event_id' => $event->id]);

        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get('/events')
            ->assertOk()
            ->assertSee('Rapat Akbar');

        $this->actingAs($staff)->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Rapat Akbar');
    }

    public function test_pencarian_dan_filter_status(): void
    {
        Event::factory()->create(['name' => 'Workshop Laravel', 'status' => EventStatus::Open]);
        Event::factory()->create(['name' => 'Gathering Tahunan', 'status' => EventStatus::Closed]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/events?q=laravel')
            ->assertSee('Workshop Laravel')
            ->assertDontSee('Gathering Tahunan');

        $this->actingAs($admin)->get('/events?status=closed')
            ->assertSee('Gathering Tahunan')
            ->assertDontSee('Workshop Laravel');
    }

    public function test_panitia_tidak_bisa_membuat_mengubah_atau_menghapus_event(): void
    {
        $staff = User::factory()->staff()->create();
        $event = Event::factory()->create();

        $this->actingAs($staff)->get('/events/create')->assertForbidden();
        $this->actingAs($staff)->post('/events', $this->payload())->assertForbidden();
        $this->actingAs($staff)->get(route('events.edit', $event))->assertForbidden();
        $this->actingAs($staff)->put(route('events.update', $event), $this->payload())->assertForbidden();
        $this->actingAs($staff)->delete(route('events.destroy', $event))->assertForbidden();

        $this->assertDatabaseCount('events', 1);
    }

    public function test_admin_bisa_membuat_event(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/events', $this->payload());

        $event = Event::firstOrFail();
        $response->assertRedirect(route('events.show', $event));

        $this->assertSame('Seminar Teknologi 2026', $event->name);
        $this->assertSame('seminar-teknologi-2026', $event->slug);
        $this->assertSame(EventStatus::Open, $event->status);
        $this->assertSame($admin->id, $event->created_by);
    }

    public function test_slug_tetap_unik_untuk_nama_yang_sama(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/events', $this->payload());
        $this->actingAs($admin)->post('/events', $this->payload());

        $this->assertEqualsCanonicalizing(
            ['seminar-teknologi-2026', 'seminar-teknologi-2026-2'],
            Event::pluck('slug')->all(),
        );
    }

    public function test_validasi_event(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/events', $this->payload([
            'name' => '',
            'ends_at' => '2026-12-01 08:00',
            'status' => 'ngawur',
        ]))->assertSessionHasErrors(['name', 'ends_at', 'status']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_admin_bisa_mengubah_event_tanpa_mengubah_slug(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create(['name' => 'Nama Lama']);
        $slug = $event->slug;

        $this->actingAs($admin)
            ->put(route('events.update', $event), $this->payload(['name' => 'Nama Baru', 'status' => 'closed']))
            ->assertRedirect(route('events.show', $event));

        $event->refresh();
        $this->assertSame('Nama Baru', $event->name);
        $this->assertSame(EventStatus::Closed, $event->status);
        $this->assertSame($slug, $event->slug);
    }

    public function test_admin_menghapus_event_secara_soft_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        Participant::factory()->create(['event_id' => $event->id]);

        $this->actingAs($admin)->delete(route('events.destroy', $event))
            ->assertRedirect(route('events.index'));

        $this->assertSoftDeleted($event);
        $this->assertDatabaseCount('participants', 1);
    }
}
