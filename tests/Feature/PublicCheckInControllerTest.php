<?php

namespace Tests\Feature;

use App\Enums\AttendanceMethod;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCheckInControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{event: Event, slug: string}
     */
    private function publicParams(Event $event): array
    {
        return ['event' => $event, 'slug' => $event->slug];
    }

    public function test_welcome_page_only_offers_name_and_nik_check_in(): void
    {
        $event = Event::factory()->create();

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Cari Nama')
            ->assertSee('Input NIK')
            ->assertDontSee('Scan QR')
            ->assertDontSee('Manual');
    }

    public function test_wrong_slug_returns_404(): void
    {
        $event = Event::factory()->create();

        $this->get(route('public.events.check-in', ['event' => $event, 'slug' => 'slug-salah']))
            ->assertNotFound();
    }

    public function test_unknown_check_in_mode_returns_404(): void
    {
        $event = Event::factory()->create();

        $this->get('/e/'.$event->id.'/'.$event->slug.'/check-in/qr')
            ->assertNotFound();
    }

    public function test_closed_event_hides_check_in_forms(): void
    {
        $event = Event::factory()->closed()->create();

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Check-in sudah ditutup')
            ->assertDontSee('Cari Nama');
    }

    public function test_draft_event_hides_check_in_forms_and_rejects_submit(): void
    {
        $event = Event::factory()->draft()->create();
        $participant = Participant::factory()->for($event)->create(['nik' => '3201010101900001']);

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Check-in belum dibuka')
            ->assertDontSee('Cari Nama');

        $this->post(route('public.events.check-in.submit-nik', $this->publicParams($event)), [
            'nik' => '3201010101900001',
        ])->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseMissing('attendances', ['participant_id' => $participant->id]);
    }

    public function test_open_event_is_closed_automatically_after_end_time(): void
    {
        $event = Event::factory()->create([
            'starts_at' => now()->subHours(5),
            'ends_at' => now()->subHour(),
        ]);
        $participant = Participant::factory()->for($event)->create(['nik' => '3201010101900001']);

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Check-in sudah ditutup')
            ->assertSee('Ditutup')
            ->assertDontSee('Cari Nama');

        $this->post(route('public.events.check-in.submit-nik', $this->publicParams($event)), [
            'nik' => '3201010101900001',
        ])->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseMissing('attendances', ['participant_id' => $participant->id]);
    }

    public function test_open_event_accepts_check_in_until_the_end_time_passes(): void
    {
        $event = Event::factory()->create([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);
        $participant = Participant::factory()->for($event)->create(['nik' => '3201010101900001']);

        $this->post(route('public.events.check-in.submit-nik', $this->publicParams($event)), [
            'nik' => '3201010101900001',
        ])->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === true);

        $this->assertDatabaseHas('attendances', ['participant_id' => $participant->id]);
    }

    public function test_open_event_without_end_time_stays_open(): void
    {
        $event = Event::factory()->create([
            'starts_at' => now()->subDays(3),
            'ends_at' => null,
        ]);

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Cari Nama');
    }

    public function test_name_search_requires_three_characters(): void
    {
        $event = Event::factory()->create();

        $this->get(route('public.events.check-in.mode', [...$this->publicParams($event), 'mode' => 'name', 'q' => 'ab']))
            ->assertOk()
            ->assertSee('Ketik minimal 3 huruf nama Anda.');
    }

    public function test_name_search_masks_nik_and_does_not_show_full_nik(): void
    {
        $event = Event::factory()->create();
        Participant::factory()->for($event)->create([
            'name' => 'Budi Santoso',
            'nik' => '3201010101900001',
        ]);

        $this->get(route('public.events.check-in.mode', [...$this->publicParams($event), 'mode' => 'name', 'q' => 'Budi']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('************0001')
            ->assertDontSee('3201010101900001');
    }

    public function test_name_search_escapes_html_in_the_query(): void
    {
        $event = Event::factory()->create();

        $this->get(route('public.events.check-in.mode', [
            ...$this->publicParams($event),
            'mode' => 'name',
            'q' => '<script>alert(1)</script>',
        ]))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_check_in_by_nik_records_attendance(): void
    {
        $this->freezeTime();

        $event = Event::factory()->create();
        $participant = Participant::factory()->for($event)->create([
            'nik' => '32.01.01.01.0190.0001',
        ]);

        $this->assertSame('3201010101900001', $participant->fresh()->nik);

        $this->from(route('public.events.check-in.mode', [...$this->publicParams($event), 'mode' => 'nik']))
            ->post(route('public.events.check-in.submit-nik', $this->publicParams($event)), [
                'nik' => '3201 0101 0190 0001',
            ])
            ->assertRedirectToRoute('public.events.check-in.mode', [...$this->publicParams($event), 'mode' => 'nik'])
            ->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === true);

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'method' => AttendanceMethod::Nik->value,
            'checked_in_by' => null,
        ]);
    }

    public function test_check_in_by_name_records_attendance(): void
    {
        $event = Event::factory()->create();
        $participant = Participant::factory()->for($event)->create(['name' => 'Siti Aminah']);

        $this->from(route('public.events.check-in.mode', [...$this->publicParams($event), 'mode' => 'name', 'q' => 'Siti']))
            ->post(route('public.events.check-in.submit-name', $this->publicParams($event)), [
                'participant_id' => $participant->id,
                'confirmation' => '1',
            ])
            ->assertRedirectToRoute('public.events.check-in.mode', [...$this->publicParams($event), 'mode' => 'name'])
            ->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === true);

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'method' => AttendanceMethod::Name->value,
        ]);
    }

    public function test_duplicate_nik_check_in_does_not_create_a_second_row(): void
    {
        $event = Event::factory()->create();
        $participant = Participant::factory()->for($event)->create(['nik' => '3201010101900001']);

        $this->post(route('public.events.check-in.submit-nik', $this->publicParams($event)), ['nik' => '3201010101900001']);
        $this->post(route('public.events.check-in.submit-nik', $this->publicParams($event)), ['nik' => '3201010101900001'])
            ->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseCount('attendances', 1);
        $this->assertSame($participant->id, (int) Attendance::query()->value('participant_id'));
    }

    public function test_name_check_in_rejects_participant_from_another_event(): void
    {
        $event = Event::factory()->create();
        $other = Participant::factory()->create();

        $this->from(route('public.events.check-in.mode', [...$this->publicParams($event), 'mode' => 'name']))
            ->post(route('public.events.check-in.submit-name', $this->publicParams($event)), [
                'participant_id' => $other->id,
                'confirmation' => '1',
            ])
            ->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_closed_event_rejects_check_in_submit(): void
    {
        $event = Event::factory()->closed()->create();
        $participant = Participant::factory()->for($event)->create(['nik' => '3201010101900001']);

        $this->post(route('public.events.check-in.submit-nik', $this->publicParams($event)), [
            'nik' => '3201010101900001',
        ])->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseMissing('attendances', ['participant_id' => $participant->id]);
    }

    public function test_nik_check_in_requires_nik(): void
    {
        $event = Event::factory()->create();

        $this->from(route('public.events.check-in.mode', [...$this->publicParams($event), 'mode' => 'nik']))
            ->post(route('public.events.check-in.submit-nik', $this->publicParams($event)), ['nik' => ''])
            ->assertSessionHasErrors(['nik' => 'NIK wajib diisi.']);
    }

    public function test_committee_check_in_routes_are_gone(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get('/events/'.$event->id.'/attendances/check-in')
            ->assertNotFound();
    }
}
