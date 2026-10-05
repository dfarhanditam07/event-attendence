<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    // Catatan: trait WithoutModelEvents sengaja dihapus karena model Event dan
    // Participant mengisi slug / qr_token lewat model event "creating".

    public function run(): void
    {
        $this->seedAdmin();

        if (app()->isLocal() && Event::count() === 0) {
            $event = Event::factory()->create(['name' => 'Event Demo']);
            Participant::factory()->count(20)->create(['event_id' => $event->id]);
        }
    }

    private function seedAdmin(): void
    {
        $config = config('attendance.admin');

        $email = $config['email'] ?: (app()->isLocal() ? 'admin@example.com' : null);
        $password = $config['password'] ?: (app()->isLocal() ? 'password' : null);

        if (! $email || ! $password) {
            throw new RuntimeException('Set ADMIN_EMAIL dan ADMIN_PASSWORD di environment sebelum menjalankan seeder.');
        }

        $user = User::firstOrNew(['email' => $email]);

        // forceFill: `role` dan `is_active` memang tidak fillable.
        $user->forceFill([
            'name' => $config['name'] ?? 'Administrator',
            'password' => $password,
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();
    }
}
