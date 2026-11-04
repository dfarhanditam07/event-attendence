<?php

namespace Tests\Feature;

use App\Enums\AttendanceMethod;
use App\Models\Attendance;
use App\Models\ConsumptionPickup;
use App\Models\Event;
use App\Models\Participant;
use App\Services\ConsumptionPickupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicConsumptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private const PIN = '4821';

    /**
     * @return array{event: Event, slug: string}
     */
    private function kioskParams(Event $event): array
    {
        return ['event' => $event, 'slug' => $event->slug];
    }

    private function presentParticipant(Event $event, ?string $organization, string $name): Participant
    {
        $participant = Participant::factory()->for($event)->create([
            'name' => $name,
            'organization' => $organization,
        ]);

        Attendance::create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'checked_in_at' => now(),
            'method' => AttendanceMethod::Nik,
        ]);

        return $participant;
    }

    public function test_kiosk_is_unavailable_when_no_pin_is_set(): void
    {
        $event = Event::factory()->create(['consumption_pin' => null]);

        $this->get(route('public.events.consumption.index', $this->kioskParams($event)))
            ->assertOk()
            ->assertSee('Kiosk Konsumsi Belum Diaktifkan')
            ->assertDontSee('PIN Kiosk');
    }

    public function test_wrong_slug_returns_404(): void
    {
        $event = Event::factory()->create(['consumption_pin' => self::PIN]);

        $this->get(route('public.events.consumption.index', ['event' => $event, 'slug' => 'slug-salah']))
            ->assertNotFound();
    }

    public function test_active_kiosk_asks_for_pin_before_listing_divisions(): void
    {
        $event = Event::factory()->create(['consumption_pin' => self::PIN]);
        $this->presentParticipant($event, 'Divisi IT', 'Andi');

        $this->get(route('public.events.consumption.index', $this->kioskParams($event)))
            ->assertOk()
            ->assertSee('PIN Kiosk')
            ->assertDontSee('Pilih Divisi Pengambil');
    }

    public function test_wrong_pin_is_rejected(): void
    {
        $event = Event::factory()->create(['consumption_pin' => self::PIN]);

        $this->post(route('public.events.consumption.unlock', $this->kioskParams($event)), ['pin' => '0000'])
            ->assertSessionHasErrors('pin');

        $this->get(route('public.events.consumption.index', $this->kioskParams($event)))
            ->assertSee('PIN Kiosk');
    }

    public function test_correct_pin_unlocks_the_division_list(): void
    {
        $event = Event::factory()->create(['consumption_pin' => self::PIN]);
        $this->presentParticipant($event, 'Divisi IT', 'Andi');

        $this->post(route('public.events.consumption.unlock', $this->kioskParams($event)), ['pin' => self::PIN])
            ->assertRedirectToRoute('public.events.consumption.index', $this->kioskParams($event));

        $this->get(route('public.events.consumption.index', $this->kioskParams($event)))
            ->assertOk()
            ->assertSee('Pilih Divisi Pengambil')
            ->assertSee('Divisi IT');
    }

    public function test_pickup_form_is_blocked_while_kiosk_is_locked(): void
    {
        $event = Event::factory()->create(['consumption_pin' => self::PIN]);
        $this->presentParticipant($event, 'Divisi IT', 'Andi');

        $this->get(route('public.events.consumption.create', [
            ...$this->kioskParams($event),
            'division' => 'Divisi IT',
        ]))->assertRedirectToRoute('public.events.consumption.index', $this->kioskParams($event));

        $this->post(route('public.events.consumption.store', $this->kioskParams($event)), [
            'division' => 'Divisi IT',
            'representative_name' => 'Rina',
            'handed_over_by_name' => 'Panitia A',
        ])->assertRedirectToRoute('public.events.consumption.index', $this->kioskParams($event));

        $this->assertDatabaseCount('consumption_pickups', 0);
    }

    public function test_kiosk_pickup_records_claims_with_handed_over_name_and_photo(): void
    {
        Storage::fake(ConsumptionPickupService::EVIDENCE_DISK);

        $event = Event::factory()->create(['consumption_pin' => self::PIN]);
        $presentA = $this->presentParticipant($event, 'Divisi IT', 'Andi');
        $presentB = $this->presentParticipant($event, 'Divisi IT', 'Budi');
        $absent = Participant::factory()->for($event)->create(['organization' => 'Divisi IT', 'name' => 'Cici']);

        $this->post(route('public.events.consumption.unlock', $this->kioskParams($event)), ['pin' => self::PIN]);

        $this->get(route('public.events.consumption.create', [
            ...$this->kioskParams($event),
            'division' => 'Divisi IT',
        ]))
            ->assertOk()
            ->assertSee('Andi')
            ->assertSee('Budi')
            ->assertDontSee('Cici');

        $this->post(route('public.events.consumption.store', $this->kioskParams($event)), [
            'division' => 'Divisi IT',
            'representative_name' => 'Rina',
            'representative_contact' => '08123456789',
            'handed_over_by_name' => 'Panitia Meja 3',
            'photo' => UploadedFile::fake()->image('bukti.jpg'),
        ])
            ->assertRedirectToRoute('public.events.consumption.index', $this->kioskParams($event))
            ->assertSessionHas('public_consumption_result');

        $pickup = ConsumptionPickup::sole();
        $this->assertSame(2, $pickup->portions_count);
        $this->assertNull($pickup->handed_over_by);
        $this->assertSame('Panitia Meja 3', $pickup->handed_over_by_name);
        $this->assertTrue($pickup->hasPhoto());
        Storage::disk(ConsumptionPickupService::EVIDENCE_DISK)->assertExists($pickup->photo_path);

        $this->assertDatabaseHas('consumption_claims', ['participant_id' => $presentA->id, 'consumption_pickup_id' => $pickup->id]);
        $this->assertDatabaseHas('consumption_claims', ['participant_id' => $presentB->id, 'consumption_pickup_id' => $pickup->id]);
        $this->assertDatabaseMissing('consumption_claims', ['participant_id' => $absent->id]);
    }

    public function test_pickup_form_exposes_live_camera_capture_instead_of_file_picker(): void
    {
        $event = Event::factory()->create(['consumption_pin' => self::PIN]);
        $this->presentParticipant($event, 'Divisi IT', 'Andi');

        $this->post(route('public.events.consumption.unlock', $this->kioskParams($event)), ['pin' => self::PIN]);

        $this->get(route('public.events.consumption.create', [
            ...$this->kioskParams($event),
            'division' => 'Divisi IT',
        ]))
            ->assertOk()
            ->assertSee('data-camera-capture', false)
            ->assertSee('data-camera-video', false)
            ->assertSee('Nyalakan Kamera')
            ->assertSee('Ambil Foto');
    }

    public function test_kiosk_pickup_requires_photo_and_staff_name(): void
    {
        Storage::fake(ConsumptionPickupService::EVIDENCE_DISK);

        $event = Event::factory()->create(['consumption_pin' => self::PIN]);
        $this->presentParticipant($event, 'Divisi IT', 'Andi');

        $this->post(route('public.events.consumption.unlock', $this->kioskParams($event)), ['pin' => self::PIN]);

        $this->post(route('public.events.consumption.store', $this->kioskParams($event)), [
            'division' => 'Divisi IT',
            'representative_name' => 'Rina',
            'handed_over_by_name' => '',
        ])->assertSessionHasErrors(['photo', 'handed_over_by_name']);

        $this->assertDatabaseCount('consumption_pickups', 0);
    }

    public function test_kiosk_pickup_is_rejected_when_members_already_claimed(): void
    {
        Storage::fake(ConsumptionPickupService::EVIDENCE_DISK);

        $event = Event::factory()->create(['consumption_pin' => self::PIN]);
        $this->presentParticipant($event, 'Divisi IT', 'Andi');

        $this->post(route('public.events.consumption.unlock', $this->kioskParams($event)), ['pin' => self::PIN]);

        $payload = [
            'division' => 'Divisi IT',
            'representative_name' => 'Rina',
            'handed_over_by_name' => 'Panitia A',
            'photo' => UploadedFile::fake()->image('bukti.jpg'),
        ];

        $this->post(route('public.events.consumption.store', $this->kioskParams($event)), $payload);
        $this->post(route('public.events.consumption.store', $this->kioskParams($event)), [
            ...$payload,
            'photo' => UploadedFile::fake()->image('bukti2.jpg'),
        ]);

        $this->assertDatabaseCount('consumption_pickups', 1);
        $this->assertDatabaseCount('consumption_claims', 1);
    }

    public function test_lock_kiosk_requires_pin_again(): void
    {
        $event = Event::factory()->create(['consumption_pin' => self::PIN]);

        $this->post(route('public.events.consumption.unlock', $this->kioskParams($event)), ['pin' => self::PIN]);
        $this->post(route('public.events.consumption.lock', $this->kioskParams($event)))
            ->assertRedirectToRoute('public.events.consumption.index', $this->kioskParams($event));

        $this->get(route('public.events.consumption.index', $this->kioskParams($event)))
            ->assertSee('PIN Kiosk')
            ->assertDontSee('Pilih Divisi Pengambil');
    }
}
