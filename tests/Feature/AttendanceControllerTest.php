<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_away_from_attendance_list(): void
    {
        $event = Event::factory()->create();

        $this->get(route('events.attendances.index', $event))
            ->assertRedirectToRoute('login');
    }

    public function test_committee_attendance_page_has_no_check_in_form(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('events.attendances.index', $event))
            ->assertOk()
            ->assertSee('Daftar kehadiran')
            ->assertDontSee('Konfirmasi Hadir')
            ->assertDontSee('Scan QR')
            ->assertDontSee('Cari nama Anda')
            ->assertDontSee('Check-in dengan NIK');
    }

    public function test_event_detail_shares_public_link_but_has_no_check_in_form(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();
        $publicUrl = route('public.events.check-in', ['event' => $event, 'slug' => $event->slug]);

        $this->actingAs($staff)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Link Check-in Publik')
            ->assertSee($publicUrl)
            ->assertDontSee('Konfirmasi Hadir')
            ->assertDontSee('Scan QR');
    }
}
