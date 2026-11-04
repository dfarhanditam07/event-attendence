<?php

namespace Tests\Feature;

use App\Enums\AttendanceMethod;
use App\Models\Attendance;
use App\Models\ConsumptionClaim;
use App\Models\ConsumptionPickup;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ConsumptionPickupTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_guest_is_redirected_away_from_consumption_pages(): void
    {
        $event = Event::factory()->create();

        $this->get(route('events.consumptions.index', $event))->assertRedirectToRoute('login');
        $this->get(route('events.consumptions.report', $event))->assertRedirectToRoute('login');

        $this->post(route('events.consumptions.store', $event), [
            'division' => 'Divisi IT',
            'representative_name' => 'Rina',
        ])->assertRedirectToRoute('login');
    }

    public function test_consumption_recording_is_allowed_for_both_staff_and_admin(): void
    {
        $event = Event::factory()->create();

        $this->assertTrue(Gate::forUser(User::factory()->staff()->create())->allows('manageConsumption', $event));
        $this->assertTrue(Gate::forUser(User::factory()->admin()->create())->allows('manageConsumption', $event));
    }

    public function test_recap_page_lists_divisions_that_have_present_members(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();

        $this->presentParticipant($event, 'Divisi IT', 'Andi');
        Participant::factory()->for($event)->create(['organization' => 'Divisi IT', 'name' => 'Cici']);

        $this->actingAs($staff)
            ->get(route('events.consumptions.index', $event))
            ->assertOk()
            ->assertSee('Divisi IT')
            ->assertSee('Serahkan Konsumsi');
    }

    public function test_pick_up_marks_claims_only_for_present_members(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();

        $presentA = $this->presentParticipant($event, 'Divisi IT', 'Andi');
        $presentB = $this->presentParticipant($event, 'Divisi IT', 'Budi');
        $absent = Participant::factory()->for($event)->create(['organization' => 'Divisi IT', 'name' => 'Cici']);

        $this->actingAs($staff)
            ->post(route('events.consumptions.store', $event), [
                'division' => 'Divisi IT',
                'representative_name' => 'Rina',
                'representative_contact' => '08123456789',
                'notes' => 'Meja 3',
            ])
            ->assertRedirectToRoute('events.consumptions.report', $event);

        $pickup = ConsumptionPickup::sole();
        $this->assertSame(2, $pickup->portions_count);
        $this->assertSame('Divisi IT', $pickup->division);
        $this->assertSame($staff->id, $pickup->handed_over_by);

        $this->assertDatabaseHas('consumption_claims', ['participant_id' => $presentA->id, 'consumption_pickup_id' => $pickup->id]);
        $this->assertDatabaseHas('consumption_claims', ['participant_id' => $presentB->id, 'consumption_pickup_id' => $pickup->id]);
        $this->assertDatabaseMissing('consumption_claims', ['participant_id' => $absent->id]);
        $this->assertDatabaseCount('consumption_claims', 2);
    }

    public function test_members_without_organization_are_grouped_under_unassigned_division(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();

        $ungrouped = $this->presentParticipant($event, null, 'Tanpa Organisasi');

        $this->actingAs($staff)
            ->post(route('events.consumptions.store', $event), [
                'division' => Participant::UNASSIGNED_DIVISION,
                'representative_name' => 'Rina',
            ])
            ->assertRedirectToRoute('events.consumptions.report', $event);

        $this->assertDatabaseHas('consumption_claims', ['participant_id' => $ungrouped->id]);
    }

    public function test_member_cannot_be_claimed_more_than_once(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();

        $present = $this->presentParticipant($event, 'Divisi IT', 'Andi');

        $payload = ['division' => 'Divisi IT', 'representative_name' => 'Rina'];

        $this->actingAs($staff)->post(route('events.consumptions.store', $event), $payload)
            ->assertRedirectToRoute('events.consumptions.report', $event);

        // Pengambilan kedua untuk divisi yang sama harus ditolak: tidak ada anggota yang masih berhak.
        $this->actingAs($staff)->post(route('events.consumptions.store', $event), [
            'division' => 'Divisi IT',
            'representative_name' => 'Rina Kedua',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('consumption_pickups', 1);
        $this->assertDatabaseCount('consumption_claims', 1);
        $this->assertDatabaseHas('consumption_claims', ['participant_id' => $present->id]);
    }

    public function test_database_rejects_a_second_claim_for_the_same_member(): void
    {
        $event = Event::factory()->create();
        $participant = Participant::factory()->for($event)->create();
        $pickup = ConsumptionPickup::factory()->for($event)->create();

        ConsumptionClaim::create([
            'consumption_pickup_id' => $pickup->id,
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'claimed_at' => now(),
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        ConsumptionClaim::create([
            'consumption_pickup_id' => $pickup->id,
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'claimed_at' => now(),
        ]);
    }

    public function test_report_shows_full_audit_trail(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create(['name' => 'Panitia Satu']);

        $this->presentParticipant($event, 'Divisi IT', 'Andi');
        $this->presentParticipant($event, 'Divisi IT', 'Budi');

        $this->actingAs($staff)->post(route('events.consumptions.store', $event), [
            'division' => 'Divisi IT',
            'representative_name' => 'Rina Perwakilan',
        ]);

        $this->actingAs($staff)
            ->get(route('events.consumptions.report', $event))
            ->assertOk()
            ->assertSee('Divisi IT')
            ->assertSee('Rina Perwakilan')
            ->assertSee('2 porsi')
            ->assertSee('Panitia Satu')
            ->assertSee('Andi')
            ->assertSee('Budi');
    }

    public function test_store_requires_representative_name(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();
        $this->presentParticipant($event, 'Divisi IT', 'Andi');

        $this->actingAs($staff)
            ->post(route('events.consumptions.store', $event), [
                'division' => 'Divisi IT',
                'representative_name' => '',
            ])
            ->assertSessionHasErrors('representative_name');

        $this->assertDatabaseCount('consumption_pickups', 0);
    }

    public function test_create_page_without_division_redirects_to_recap(): void
    {
        $event = Event::factory()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('events.consumptions.create', $event))
            ->assertRedirectToRoute('events.consumptions.index', $event);
    }
}
