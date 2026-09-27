<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_is_public(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'name' => 'Twende Market',
                'status' => 'ok',
            ]);
    }

    public function test_users_can_issue_and_revoke_a_token(): void
    {
        $user = User::factory()->withRole(UserRole::Client)->create();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'tests',
        ]);

        $login->assertOk()->assertJsonPath('token_type', 'Bearer');
        $login->assertJsonPath('user.email', $user->email);
        $login->assertJsonPath('user.roles.0', 'client');

        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->assertSame(0, \Laravel\Sanctum\PersonalAccessToken::query()->count());

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/user')
            ->assertUnauthorized();
    }

    public function test_unverified_users_cannot_obtain_a_token(): void
    {
        $user = User::factory()->unverified()->withRole(UserRole::Client)->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_the_current_user_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/user')->assertUnauthorized();

        Sanctum::actingAs(User::factory()->withRole(UserRole::Admin)->create());

        $this->getJson('/api/v1/user')->assertOk()->assertJsonPath('data.roles.0', 'admin');
    }
}
