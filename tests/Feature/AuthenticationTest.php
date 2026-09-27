<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_the_authentication_screens(): void
    {
        $this->get('/login')->assertOk()->assertSee(__('ui.auth.login_title'));
        $this->get('/register')->assertOk()->assertSee(__('ui.auth.register_title'));
        $this->get('/forgot-password')->assertOk();
    }

    public function test_users_can_register_and_receive_the_client_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Amina Test',
            'email' => 'amina@example.com',
            'phone' => '+243 81 000 0099',
            'password' => 'Twende2026',
            'password_confirmation' => 'Twende2026',
            'role' => 'admin',
        ]);

        $response->assertRedirect('/tableau-de-bord');

        $user = User::query()->where('email', 'amina@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('+243810000099', $user->phone);
        $this->assertTrue($user->hasRole(UserRole::Customer->value));
        $this->assertFalse($user->hasRole(UserRole::SuperAdmin->value));
        $this->assertAuthenticatedAs($user);

        $this->get('/tableau-de-bord')->assertRedirect(route('verification.notice'));
    }

    public function test_registration_rejects_an_invalid_phone_number(): void
    {
        $this->post('/register', [
            'name' => 'Amina Test',
            'email' => 'amina@example.com',
            'phone' => 'pas-un-numero',
            'password' => 'Twende2026',
            'password_confirmation' => 'Twende2026',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_users_can_login_and_logout(): void
    {
        $user = User::factory()->withRole(UserRole::Customer)->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/tableau-de-bord');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'mauvais-mot',
            ])->assertSessionHasErrors('email');
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'mauvais-mot',
        ])->assertStatus(429);
    }

    public function test_users_can_reset_their_password(): void
    {
        Notification::fake();

        $user = User::factory()->withRole(UserRole::Customer)->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');

        $token = null;

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Nouveau2026',
            'password_confirmation' => 'Nouveau2026',
        ])->assertRedirect('/login');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Nouveau2026',
        ])->assertRedirect('/tableau-de-bord');
    }

    public function test_verified_users_can_update_profile_and_password(): void
    {
        $user = User::factory()->withRole(UserRole::Customer)->create([
            'phone' => '+243810000001',
        ]);

        $this->actingAs($user)
            ->from('/profil')
            ->put('/user/profile-information', [
                'name' => 'Nouveau Nom',
                'email' => $user->email,
                'phone' => '+243810000002',
                'locale' => 'en',
                'currency' => 'USD',
            ])
            ->assertRedirect('/profil')
            ->assertSessionHas('status', 'profile-information-updated');

        $user->refresh();

        $this->assertSame('Nouveau Nom', $user->name);
        $this->assertSame('+243810000002', $user->phone);
        $this->assertNull($user->phone_verified_at);
        $this->assertSame('en', $user->locale);
        $this->assertSame('USD', $user->currency);

        $this->actingAs($user)
            ->from('/profil')
            ->put('/user/password', [
                'current_password' => 'password',
                'password' => 'Nouveau2026',
                'password_confirmation' => 'Nouveau2026',
            ])
            ->assertRedirect('/profil')
            ->assertSessionHas('status', 'password-updated');

        $this->post('/logout');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Nouveau2026',
        ])->assertRedirect('/tableau-de-bord');
    }
}
