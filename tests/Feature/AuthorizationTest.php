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
        $this->get('/vendeur')->assertRedirect(route('login'));
        $this->get('/livreur')->assertRedirect(route('login'));
        $this->get('/compte')->assertRedirect(route('login'));
    }

    public function test_verified_users_can_open_their_profile(): void
    {
        $customer = User::factory()->withRole(UserRole::Customer)->create();

        $this->actingAs($customer)
            ->get('/profil')
            ->assertOk()
            ->assertSee('twende-market-logo.png', false)
            ->assertSee(__('ui.dashboard.profile_title'));
    }

    public function test_customers_stay_in_their_account(): void
    {
        $customer = User::factory()->withRole(UserRole::Customer)->create();

        $this->actingAs($customer)->get('/tableau-de-bord')->assertRedirect(route('customer.dashboard'));
        $this->actingAs($customer)->get('/compte')->assertOk()->assertSee(__('ui.areas.customer'));
        $this->actingAs($customer)->get('/vendeur')->assertForbidden();
        $this->actingAs($customer)->get('/livreur')->assertForbidden();
        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($customer)->get('/admin/utilisateurs')->assertForbidden();
        $this->assertTrue($customer->can('orders.view'));
        $this->assertFalse($customer->can('users.view'));
    }

    public function test_vendors_cannot_open_administration(): void
    {
        $vendor = User::factory()->withRole(UserRole::Vendor)->create();

        $this->actingAs($vendor)->get('/vendeur')->assertOk()->assertSee(__('ui.areas.vendor'));
        $this->actingAs($vendor)->get('/admin')->assertForbidden();
        $this->actingAs($vendor)->get('/admin/utilisateurs')->assertForbidden();
        $this->actingAs($vendor)->get('/compte')->assertForbidden();
        $this->assertTrue($vendor->can('products.view'));
        $this->assertTrue($vendor->can('orders.view'));
        $this->assertFalse($vendor->can('users.view'));
        $this->assertFalse($vendor->can('finance.reports'));
    }

    public function test_couriers_only_open_delivery(): void
    {
        $courier = User::factory()->withRole(UserRole::DeliveryAgent)->create();

        $this->actingAs($courier)->get('/livreur')->assertOk()->assertSee(__('ui.areas.delivery'));
        $this->actingAs($courier)->get('/vendeur')->assertForbidden();
        $this->actingAs($courier)->get('/admin')->assertForbidden();
        $this->assertTrue($courier->can('delivery.update'));
        $this->assertFalse($courier->can('delivery.manage'));
        $this->assertFalse($courier->can('products.view'));
    }

    public function test_super_admin_uses_the_admin_workspace(): void
    {
        $admin = User::factory()->withRole(UserRole::SuperAdmin)->create();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('twende-market-logo.png', false)->assertSee(__('ui.areas.admin'));
        $this->actingAs($admin)->get('/vendeur')->assertForbidden();
        $this->actingAs($admin)->get('/livreur')->assertForbidden();
        $this->actingAs($admin)->get('/compte')->assertForbidden();
        $this->assertTrue($admin->can('users.view'));
        $this->assertTrue($admin->can('finance.commissions'));
        $this->assertTrue($admin->can('audit.view'));
    }
}
