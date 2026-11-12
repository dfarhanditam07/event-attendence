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

    public function test_check_in_page_only_offers_name_search(): void
    {
        $event = Event::factory()->create();

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Cari Nama Peserta')
            ->assertDontSee('Scan QR')
            ->assertDontSee('Input NIK')
            ->assertDontSee('Check-in dengan NIK');
    }

    public function test_wrong_slug_returns_404(): void
    {
        $event = Event::factory()->create();

        $this->get(route('public.events.check-in', ['event' => $event, 'slug' => 'slug-salah']))
            ->assertNotFound();
    }

    public function test_unknown_check_in_path_returns_404(): void
    {
        $event = Event::factory()->create();

        $this->get('/e/'.$event->id.'/'.$event->slug.'/check-in/qr')
            ->assertNotFound();
    }

    public function test_closed_event_hides_check_in_form(): void
    {
        $event = Event::factory()->closed()->create();

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Sesi Presensi Telah Ditutup')
            ->assertDontSee('Cari Nama Peserta');
    }

    public function test_draft_event_hides_check_in_form_and_rejects_submit(): void
    {
        $event = Event::factory()->draft()->create();
        $participant = Participant::factory()->for($event)->create();

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Sesi Presensi Belum Dibuka')
            ->assertDontSee('Cari Nama Peserta');

        $this->post(route('public.events.check-in.submit', $this->publicParams($event)), [
            'participant_id' => $participant->id,
            'confirmation' => '1',
        ])->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseMissing('attendances', ['participant_id' => $participant->id]);
    }

    public function test_open_event_is_closed_automatically_after_end_time(): void
    {
        $event = Event::factory()->create([
            'starts_at' => now()->subHours(5),
            'ends_at' => now()->subHour(),
        ]);
        $participant = Participant::factory()->for($event)->create();

        $this->get(route('public.events.check-in', $this->publicParams($event)))
            ->assertOk()
            ->assertSee('Sesi Presensi Telah Ditutup')
            ->assertDontSee('Cari Nama Peserta');

        $this->post(route('public.events.check-in.submit', $this->publicParams($event)), [
            'participant_id' => $participant->id,
            'confirmation' => '1',
        ])->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseMissing('attendances', ['participant_id' => $participant->id]);
    }

    public function test_open_event_accepts_check_in_until_the_end_time_passes(): void
    {
        $event = Event::factory()->create([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);
        $participant = Participant::factory()->for($event)->create();

        $this->post(route('public.events.check-in.submit', $this->publicParams($event)), [
            'participant_id' => $participant->id,
            'confirmation' => '1',
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
            ->assertSee('Cari Nama Peserta');
    }

    public function test_name_search_requires_three_characters(): void
    {
        $event = Event::factory()->create();

        $this->get(route('public.events.check-in', [...$this->publicParams($event), 'q' => 'ab']))
            ->assertOk()
            ->assertSee('minimal 3 huruf');
    }

    public function test_name_search_lists_candidates_with_bisnis_unit_and_jabatan(): void
    {
        $event = Event::factory()->create();
        Participant::factory()->for($event)->create([
            'name' => 'Budi Santoso',
            'bisnis_unit' => 'Divisi Keuangan',
            'jabatan' => 'Manajer',
        ]);

        $this->get(route('public.events.check-in', [...$this->publicParams($event), 'q' => 'Budi']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Divisi Keuangan')
            ->assertSee('Manajer');
    }

    public function test_name_search_does_not_offer_participants_who_already_checked_in(): void
    {
        $event = Event::factory()->create();
        Participant::factory()->for($event)->create(['name' => 'Budi Santoso']);

        $this->post(route('public.events.check-in.submit', $this->publicParams($event)), [
            'participant_id' => Participant::sole()->id,
            'confirmation' => '1',
        ]);

        $this->get(route('public.events.check-in', [...$this->publicParams($event), 'q' => 'Budi']))
            ->assertOk()
            ->assertSee('tidak ditemukan');
    }

    public function test_name_search_escapes_html_in_the_query(): void
    {
        $event = Event::factory()->create();

        $this->get(route('public.events.check-in', [
            ...$this->publicParams($event),
            'q' => '<script>alert(1)</script>',
        ]))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_check_in_by_name_records_attendance(): void
    {
        $event = Event::factory()->create();
        $participant = Participant::factory()->for($event)->create(['name' => 'Siti Aminah']);

        $this->from(route('public.events.check-in', [...$this->publicParams($event), 'q' => 'Siti']))
            ->post(route('public.events.check-in.submit', $this->publicParams($event)), [
                'participant_id' => $participant->id,
                'confirmation' => '1',
            ])
            ->assertRedirectToRoute('public.events.check-in', $this->publicParams($event))
            ->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === true);

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'method' => AttendanceMethod::Name->value,
            'checked_in_by' => null,
        ]);
    }

    public function test_duplicate_check_in_does_not_create_a_second_row(): void
    {
        $event = Event::factory()->create();
        $participant = Participant::factory()->for($event)->create(['name' => 'Siti Aminah']);

        $payload = ['participant_id' => $participant->id, 'confirmation' => '1'];

        $this->post(route('public.events.check-in.submit', $this->publicParams($event)), $payload);
        $this->post(route('public.events.check-in.submit', $this->publicParams($event)), $payload)
            ->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseCount('attendances', 1);
        $this->assertSame($participant->id, (int) Attendance::query()->value('participant_id'));
    }

    public function test_name_check_in_rejects_participant_from_another_event(): void
    {
        $event = Event::factory()->create();
        $other = Participant::factory()->create();

        $this->from(route('public.events.check-in', $this->publicParams($event)))
            ->post(route('public.events.check-in.submit', $this->publicParams($event)), [
                'participant_id' => $other->id,
                'confirmation' => '1',
            ])
            ->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_closed_event_rejects_check_in_submit(): void
    {
        $event = Event::factory()->closed()->create();
        $participant = Participant::factory()->for($event)->create();

        $this->post(route('public.events.check-in.submit', $this->publicParams($event)), [
            'participant_id' => $participant->id,
            'confirmation' => '1',
        ])->assertSessionHas('public_check_in_result', fn (array $result): bool => $result['success'] === false);

        $this->assertDatabaseMissing('attendances', ['participant_id' => $participant->id]);
    }

    public function test_check_in_submit_requires_participant_and_confirmation(): void
    {
        $event = Event::factory()->create();

        $this->from(route('public.events.check-in', $this->publicParams($event)))
            ->post(route('public.events.check-in.submit', $this->publicParams($event)), [
                'participant_id' => '',
            ])
            ->assertSessionHasErrors(['participant_id', 'confirmation']);

        $this->assertDatabaseCount('attendances', 0);
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
