<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitors_can_switch_between_prepared_locales(): void
    {
        $this->get('/langue/en')->assertRedirect(route('home'));
        $this->get('/')->assertSee('Sign in');

        $this->get('/langue/sw')->assertRedirect(route('home'));
        $this->get('/')->assertSee('Ingia');

        $this->get('/langue/de')->assertNotFound();
    }

    public function test_a_signed_in_user_keeps_the_chosen_locale(): void
    {
        $user = User::factory()->withRole(UserRole::Client)->create(['locale' => 'fr']);

        $this->actingAs($user)->get('/langue/en')->assertRedirect();

        $this->assertSame('en', $user->fresh()->locale);
    }
}
