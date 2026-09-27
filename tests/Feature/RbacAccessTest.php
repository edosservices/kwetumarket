<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_reaches_only_its_dashboard(): void
    {
        $expectations = [
            [UserRole::SuperAdmin, '/admin', '/vendeur'],
            [UserRole::AdminManager, '/admin', '/vendeur'],
            [UserRole::CatalogManager, '/admin', '/admin/finances'],
            [UserRole::OrderManager, '/admin', '/admin/utilisateurs'],
            [UserRole::FinanceManager, '/admin/finances', '/admin/utilisateurs'],
            [UserRole::SupportAgent, '/admin/support', '/admin/finances'],
            [UserRole::MarketingManager, '/admin/marketing', '/admin/roles'],
            [UserRole::Moderator, '/admin/produits', '/admin/finances'],
            [UserRole::Vendor, '/vendeur', '/admin'],
            [UserRole::VendorManager, '/vendeur', '/admin'],
            [UserRole::VendorCatalogManager, '/vendeur', '/admin/produits'],
            [UserRole::VendorOrderManager, '/vendeur', '/livreur'],
            [UserRole::DeliveryManager, '/livreur', '/admin'],
            [UserRole::DeliveryAgent, '/livreur', '/vendeur'],
            [UserRole::Customer, '/compte', '/admin'],
        ];

        foreach ($expectations as [$role, $allowed, $denied]) {
            $user = User::factory()->withRole($role)->create();

            $this->actingAs($user)->get($allowed)->assertOk();
            $this->actingAs($user)->get($denied)->assertForbidden();
        }
    }

    public function test_catalog_manager_sees_products_and_not_global_finance(): void
    {
        $manager = User::factory()->withRole(UserRole::CatalogManager)->create();

        $this->actingAs($manager)->get('/admin')->assertOk()->assertDontSee(__('ui.stats.revenue'));
        $this->actingAs($manager)->get('/admin/produits')->assertOk();
        $this->actingAs($manager)->get('/admin/finances')->assertForbidden();
        $this->actingAs($manager)->post('/admin/utilisateurs/'.$manager->id.'/suspendre')->assertForbidden();
    }

    public function test_vendor_cannot_read_or_edit_another_vendors_product(): void
    {
        [$ownerA, $shopA] = $this->vendorShop('Alpha');
        [$ownerB] = $this->vendorShop('Beta');
        $productA = $this->product($shopA, 'Riz Alpha');
        $productB = $this->product($this->shopFor($ownerB), 'Riz Beta');

        $this->actingAs($ownerA)->get('/vendeur/produits')->assertOk()->assertSee('Riz Alpha')->assertDontSee('Riz Beta');
        $this->actingAs($ownerA)->get('/vendeur/produits/'.$productA->id)->assertOk();
        $this->actingAs($ownerA)->get('/vendeur/produits/'.$productB->id)->assertForbidden();
        $this->actingAs($ownerA)->put('/vendeur/produits/'.$productB->id, [
            'name' => 'Volé',
            'price_minor' => 100,
            'stock' => 1,
        ])->assertForbidden();

        $this->assertSame('Riz Beta', $productB->refresh()->name);
    }

    public function test_vendor_staff_stays_inside_the_owner_vendor(): void
    {
        [$ownerA, $shopA] = $this->vendorShop('Alpha');
        [$ownerB] = $this->vendorShop('Beta');
        $productA = $this->product($shopA, 'Farine A');
        $productB = $this->product($this->shopFor($ownerB), 'Farine B');

        $staff = User::factory()->withRole(UserRole::VendorCatalogManager)->create();
        $staff->forceFill(['vendor_id' => $ownerA->vendor_id])->save();

        $this->actingAs($staff)->get('/vendeur/produits/'.$productA->id)->assertOk();
        $this->actingAs($staff)->get('/vendeur/produits/'.$productB->id)->assertForbidden();
        $this->actingAs($staff)->get('/admin')->assertForbidden();
        $this->actingAs($staff)->get('/vendeur')->assertOk()->assertDontSee(__('ui.stats.revenue'));
    }

    public function test_product_creation_ignores_a_foreign_vendor_id(): void
    {
        [$owner, $shop] = $this->vendorShop('Alpha');
        [$other] = $this->vendorShop('Beta');

        $this->actingAs($owner)->post('/vendeur/produits', [
            'name' => 'Huile',
            'price_minor' => 250000,
            'stock' => 4,
            'vendor_id' => $other->vendor_id,
            'shop_id' => $this->shopFor($other)->id,
        ])->assertRedirect();

        $product = Product::query()->where('name', 'Huile')->first();

        $this->assertNotNull($product);
        $this->assertSame($owner->vendor_id, $product->vendor_id);
        $this->assertSame($shop->id, $product->shop_id);
    }

    public function test_delivery_agent_cannot_open_another_agents_mission(): void
    {
        $agentA = User::factory()->withRole(UserRole::DeliveryAgent)->create();
        $agentB = User::factory()->withRole(UserRole::DeliveryAgent)->create();
        $manager = User::factory()->withRole(UserRole::DeliveryManager)->create();
        $own = $this->delivery($agentA);
        $other = $this->delivery($agentB);

        $this->actingAs($agentA)->get('/livreur/missions/'.$own->id)->assertOk();
        $this->actingAs($agentA)->get('/livreur/missions/'.$other->id)->assertForbidden();
        $this->actingAs($agentA)->put('/livreur/missions/'.$other->id, ['status' => 'delivered'])->assertForbidden();
        $this->actingAs($agentA)->get('/livreur/livreurs')->assertForbidden();

        $this->actingAs($manager)->get('/livreur/missions/'.$other->id)->assertOk();
        $this->actingAs($manager)->post('/livreur/missions/'.$other->id.'/affecter', [
            'agent_id' => $agentA->id,
        ])->assertRedirect();

        $this->assertSame($agentA->id, $other->refresh()->agent_id);
    }

    public function test_customer_cannot_open_another_customers_order(): void
    {
        $customerA = User::factory()->withRole(UserRole::Customer)->create();
        $customerB = User::factory()->withRole(UserRole::Customer)->create();
        $orderA = $this->order($customerA, 'TWD-A');
        $orderB = $this->order($customerB, 'TWD-B');

        $this->actingAs($customerA)->get('/compte/commandes')->assertOk()->assertSee('TWD-A')->assertDontSee('TWD-B');
        $this->actingAs($customerA)->get('/compte/commandes/'.$orderA->id)->assertOk();
        $this->actingAs($customerA)->get('/compte/commandes/'.$orderB->id)->assertForbidden();
    }

    public function test_super_admin_stats_come_from_the_database(): void
    {
        $admin = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $customer = User::factory()->withRole(UserRole::Customer)->create();
        $order = $this->order($customer, 'TWD-STAT');
        Payment::query()->create([
            'order_id' => $order->id,
            'amount_minor' => 180000,
            'currency' => 'CDF',
            'status' => 'successful',
        ]);
        Vendor::query()->create([
            'user_id' => User::factory()->withRole(UserRole::Vendor)->create()->id,
            'name' => 'En attente',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['orders'] === 1
                && $stats['payments'] === 1
                && $stats['revenue'] === 180000
                && $stats['pending_vendors'] === 1
                && $stats['customers'] >= 1;
        });
    }

    public function test_vendor_customers_are_limited_to_their_orders(): void
    {
        [$ownerA] = $this->vendorShop('Alpha');
        [$ownerB] = $this->vendorShop('Beta');
        $buyerA = User::factory()->withRole(UserRole::Customer)->create(['name' => 'Amina Cliente']);
        $buyerB = User::factory()->withRole(UserRole::Customer)->create(['name' => 'Boris Client']);
        $this->orderForVendor($buyerA, $ownerA, 'TWD-VA');
        $this->orderForVendor($buyerB, $ownerB, 'TWD-VB');

        $this->actingAs($ownerA)->get('/vendeur/clients')
            ->assertOk()
            ->assertSee('Amina Cliente')
            ->assertDontSee('Boris Client');
    }

    public function test_suspending_a_user_blocks_the_next_request_and_writes_an_audit_log(): void
    {
        $admin = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $customer = User::factory()->withRole(UserRole::Customer)->create();

        $this->actingAs($admin)->post('/admin/utilisateurs/'.$customer->id.'/suspendre')->assertRedirect();
        $this->assertNotNull($customer->refresh()->suspended_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'users.suspend', 'subject_id' => $customer->id]);

        $this->actingAs($customer)->get('/compte')->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Shop}
     */
    private function vendorShop(string $name): array
    {
        $user = User::factory()->withRole(UserRole::Vendor)->create();
        $vendor = Vendor::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'status' => 'active',
        ]);
        $user->forceFill(['vendor_id' => $vendor->id])->save();

        return [$user->refresh(), $this->shopFor($user)];
    }

    private function shopFor(User $owner): Shop
    {
        return Shop::query()->firstOrCreate(
            ['vendor_id' => $owner->vendor_id],
            [
                'name' => 'Boutique '.$owner->vendor_id,
                'slug' => 'boutique-'.$owner->vendor_id,
                'status' => 'active',
            ],
        );
    }

    private function product(Shop $shop, string $name): Product
    {
        return Product::query()->create([
            'vendor_id' => $shop->vendor_id,
            'shop_id' => $shop->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.$shop->id.'-'.Str::lower(Str::random(3)),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'status' => 'published',
            'price_minor' => 100000,
            'currency' => 'CDF',
            'stock' => 5,
        ]);
    }

    private function delivery(User $agent): Delivery
    {
        $customer = User::factory()->withRole(UserRole::Customer)->create();
        $order = $this->order($customer, 'TWD-'.Str::upper(Str::random(4)));

        return Delivery::query()->create([
            'order_id' => $order->id,
            'agent_id' => $agent->id,
            'status' => 'pending',
            'fee_minor' => 50000,
            'currency' => 'CDF',
        ]);
    }

    private function order(User $customer, string $number): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'number' => $number,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_minor' => 100000,
            'currency' => 'CDF',
        ]);
    }

    private function orderForVendor(User $customer, User $owner, string $number): Order
    {
        $shop = $this->shopFor($owner);
        $product = $this->product($shop, 'Article '.$number);
        $order = $this->order($customer, $number);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $owner->vendor_id,
            'shop_id' => $shop->id,
            'quantity' => 1,
            'unit_price_minor' => 100000,
            'line_total_minor' => 100000,
        ]);

        return $order;
    }
}
