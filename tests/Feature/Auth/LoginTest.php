<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_bisa_dibuka(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $this->get('/events')->assertRedirect('/login');
        $this->get('/')->assertRedirect('/login');
    }

    public function test_admin_bisa_login(): void
    {
        $user = User::factory()->admin()->create(['email' => 'admin@example.com']);

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect(route('events.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_salah_ditolak(): void
    {
        User::factory()->create(['email' => 'staff@example.com']);

        $this->from('/login')
            ->post('/login', ['email' => 'staff@example.com', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        User::factory()->inactive()->create(['email' => 'off@example.com']);

        $this->post('/login', ['email' => 'off@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_akun_yang_dinonaktifkan_saat_login_langsung_dikeluarkan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/events')->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->actingAs($user->fresh())->get('/events')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
