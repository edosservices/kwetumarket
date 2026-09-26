<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login_from_private_pages(): void
    {
        $this->get('/tableau-de-bord')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_clients_cannot_open_staff_dashboards(): void
    {
        $client = User::factory()->withRole(UserRole::Client)->create();

        $this->actingAs($client)->get('/tableau-de-bord')->assertOk();
        $this->actingAs($client)->get('/vendeur')->assertForbidden();
        $this->actingAs($client)->get('/livreur')->assertForbidden();
        $this->actingAs($client)->get('/admin')->assertForbidden();
        $this->assertTrue($client->can('cart.manage'));
        $this->assertFalse($client->can('users.manage'));
    }

    public function test_vendors_manage_their_space_only(): void
    {
        $vendor = User::factory()->withRole(UserRole::Vendor)->create();

        $this->actingAs($vendor)->get('/vendeur')->assertOk();
        $this->actingAs($vendor)->get('/admin')->assertForbidden();
        $this->assertTrue($vendor->can('products.manage-own'));
        $this->assertTrue($vendor->can('orders.create'));
        $this->assertFalse($vendor->can('payments.manage'));
    }

    public function test_couriers_manage_deliveries_only(): void
    {
        $courier = User::factory()->withRole(UserRole::DeliveryAgent)->create();

        $this->actingAs($courier)->get('/livreur')->assertOk();
        $this->actingAs($courier)->get('/vendeur')->assertForbidden();
        $this->assertTrue($courier->can('deliveries.update'));
        $this->assertFalse($courier->can('products.manage-own'));
    }

    public function test_admins_can_open_every_dashboard(): void
    {
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('twende-market-logo.png', false);
        $this->actingAs($admin)->get('/vendeur')->assertOk();
        $this->actingAs($admin)->get('/livreur')->assertOk();
        $this->assertTrue($admin->can('users.manage'));
        $this->assertTrue($admin->can('commissions.manage'));
        $this->assertTrue($admin->can('withdrawals.manage'));
    }
}
