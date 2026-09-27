<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\CourierProfile;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RbacWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authorized_admin_can_suspend_and_reactivate_a_user(): void
    {
        $admin = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $catalog = User::factory()->withRole(UserRole::CatalogManager)->create();
        $customer = User::factory()->withRole(UserRole::Customer)->create();

        $this->actingAs($catalog)->post('/admin/utilisateurs/'.$customer->id.'/suspendre')->assertForbidden();
        $this->actingAs($catalog)->post('/admin/utilisateurs/'.$customer->id.'/reactiver')->assertForbidden();

        $this->actingAs($admin)->post('/admin/utilisateurs/'.$customer->id.'/suspendre')->assertRedirect();
        $this->actingAs($customer->fresh())->get('/compte')->assertForbidden();

        $this->actingAs($admin)->post('/admin/utilisateurs/'.$customer->id.'/reactiver')->assertRedirect();
        $this->assertNull($customer->fresh()->suspended_at);
        $this->actingAs($customer->fresh())->get('/compte')->assertOk();
        $this->assertDatabaseHas('audit_logs', ['action' => 'users.reactivate', 'subject_id' => $customer->id]);
    }

    public function test_product_edits_ignore_a_spoofed_vendor_and_stay_in_scope(): void
    {
        [$vendorA, $shopA] = $this->vendor('Alpha');
        [$vendorB, $shopB] = $this->vendor('Beta');
        $productA = $this->product($shopA, 'A', 'published');
        $productB = $this->product($shopB, 'B', 'published');
        $catalog = User::factory()->withRole(UserRole::CatalogManager)->create();
        $staff = User::factory()->withRole(UserRole::VendorCatalogManager)->create();
        $staff->forceFill(['vendor_id' => $vendorA->vendor_id])->save();

        $this->actingAs($vendorA)->put('/vendeur/produits/'.$productA->id, [
            'name' => 'A modifié',
            'price_minor' => 1200,
            'stock' => 2,
            'vendor_id' => $vendorB->vendor_id,
            'shop_id' => $shopB->id,
        ])->assertRedirect();

        $this->assertSame($vendorA->vendor_id, $productA->refresh()->vendor_id);
        $this->assertSame($shopA->id, $productA->shop_id);
        $this->assertSame('A modifié', $productA->name);

        $this->actingAs($vendorA)->put('/vendeur/produits/'.$productB->id, [
            'name' => 'Volé',
            'price_minor' => 1,
            'stock' => 1,
            'vendor_id' => $vendorA->vendor_id,
        ])->assertForbidden();

        $this->actingAs($staff)->put('/vendeur/produits/'.$productB->id, [
            'name' => 'Volé',
            'price_minor' => 1,
            'stock' => 1,
        ])->assertForbidden();

        $this->actingAs($catalog)->put('/admin/produits/'.$productB->id, [
            'name' => 'Catalogue',
            'price_minor' => 900,
            'stock' => 8,
            'vendor_id' => $vendorA->vendor_id,
            'shop_id' => $shopA->id,
        ])->assertRedirect();

        $this->assertSame('Catalogue', $productB->refresh()->name);
        $this->assertSame($vendorB->vendor_id, $productB->vendor_id);
        $this->assertSame($shopB->id, $productB->shop_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'product.update', 'subject_id' => $productB->id]);
    }

    public function test_order_processing_follows_the_transition_map_and_the_vendor_scope(): void
    {
        [$vendorA] = $this->vendor('Alpha');
        [$vendorB] = $this->vendor('Beta');
        $manager = User::factory()->withRole(UserRole::OrderManager)->create();
        $finance = User::factory()->withRole(UserRole::FinanceManager)->create();
        $orderA = $this->paidOrder($vendorA, 'CMD-A');
        $orderB = $this->paidOrder($vendorB, 'CMD-B');

        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$orderB->id, ['status' => 'preparing'])->assertForbidden();
        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$orderA->id, ['status' => 'delivered'])->assertSessionHasErrors('status');
        $this->assertSame('confirmed', $orderA->refresh()->status);

        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$orderA->id, ['status' => 'preparing'])->assertRedirect();
        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$orderA->id, ['status' => 'ready'])->assertRedirect();
        $this->assertSame('ready', $orderA->refresh()->status);

        $this->actingAs($finance)->put('/admin/commandes/'.$orderA->id, ['status' => 'shipped'])->assertSessionHasErrors('status');
        $this->actingAs($manager)->put('/admin/commandes/'.$orderA->id, ['status' => 'delivered'])->assertSessionHasErrors('status');
        $this->actingAs($manager)->put('/admin/commandes/'.$orderA->id, ['status' => 'cancelled'])->assertRedirect();
        $this->assertSame('cancelled', $orderA->refresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.process', 'subject_id' => $orderA->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.cancel', 'subject_id' => $orderA->id]);
    }

    public function test_delivery_statuses_move_only_along_the_allowed_path(): void
    {
        $agent = User::factory()->withRole(UserRole::DeliveryAgent)->create();
        $other = User::factory()->withRole(UserRole::DeliveryAgent)->create();
        $mission = $this->mission($agent, 'pending');
        $foreign = $this->mission($other, 'accepted');

        $this->actingAs($agent)->put('/livreur/missions/'.$foreign->id, ['status' => 'departed'])->assertForbidden();
        $this->actingAs($agent)->put('/livreur/missions/'.$mission->id, ['status' => 'delivered'])->assertSessionHasErrors('status');

        foreach (['accepted', 'departed', 'en_route', 'arrived', 'delivered'] as $status) {
            $payload = [
                'status' => $status,
                'agent_id' => $other->id,
                'delivery_id' => $foreign->id,
            ];

            if ($status === 'en_route') {
                $payload['eta_minutes'] = 25;
            }

            $this->actingAs($agent)->put('/livreur/missions/'.$mission->id, $payload)->assertRedirect();
            $this->assertSame($status, $mission->refresh()->status);
            $this->assertSame($agent->id, $mission->agent_id);
        }

        $this->assertSame('delivered', $mission->order->refresh()->status);
        $this->actingAs($agent)->put('/livreur/missions/'.$mission->id, ['status' => 'accepted'])->assertSessionHasErrors('status');

        $paused = User::factory()->withRole(UserRole::DeliveryAgent)->create();
        $blocked = $this->mission($paused, 'pending');
        CourierProfile::query()->create([
            'user_id' => $paused->id,
            'availability' => 'paused',
        ]);
        $this->actingAs($paused)->put('/livreur/missions/'.$blocked->id, ['status' => 'accepted'])->assertSessionHasErrors('status');
        $manager = User::factory()->withRole(UserRole::DeliveryManager)->create();
        $this->actingAs($manager)->put('/livreur/missions/'.$blocked->id, ['status' => 'accepted'])->assertRedirect();
        $this->assertSame('accepted', $blocked->refresh()->status);
    }

    public function test_finance_refund_uses_the_order_not_a_posted_customer(): void
    {
        $finance = User::factory()->withRole(UserRole::FinanceManager)->create();
        $catalog = User::factory()->withRole(UserRole::CatalogManager)->create();
        [$vendor] = $this->vendor('Alpha');
        $order = $this->paidOrder($vendor, 'CMD-R');
        $stranger = User::factory()->withRole(UserRole::Customer)->create();

        $returnRequest = ReturnRequest::query()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'reason' => 'Article abîmé à la réception du colis',
            'status' => 'approved',
        ]);

        $this->actingAs($catalog)->post('/admin/remboursements', [
            'return_request_id' => $returnRequest->id,
            'order_id' => $order->id,
            'amount_minor' => 1000,
            'user_id' => $stranger->id,
        ])->assertForbidden();

        $this->actingAs($finance)->post('/admin/remboursements', [
            'return_request_id' => $returnRequest->id,
            'order_id' => $stranger->id,
            'amount_minor' => 1000,
            'user_id' => $stranger->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('refunds', [
            'order_id' => $order->id,
            'return_request_id' => $returnRequest->id,
            'amount_minor' => 1000,
        ]);
        $this->assertSame('refunded', $returnRequest->refresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'refund.create']);

        $this->actingAs($order->customer)->get('/compte/retours')->assertOk()->assertSee('10');
    }

    public function test_audit_page_filters_by_action(): void
    {
        $admin = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $customer = User::factory()->withRole(UserRole::Customer)->create();
        AuditLog::record($admin, 'users.suspend', $customer, ['module' => 'users']);
        AuditLog::record($admin, 'product.update', $customer, ['module' => 'products']);

        $this->actingAs($admin)->get('/admin/audit?action=users.suspend')
            ->assertOk()
            ->assertSee('users.suspend')
            ->assertDontSee('product.update');
    }

    /**
     * @return array{0: User, 1: Shop}
     */
    private function vendor(string $name): array
    {
        $user = User::factory()->withRole(UserRole::Vendor)->create();
        $record = Vendor::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'status' => 'active',
        ]);
        $user->forceFill(['vendor_id' => $record->id])->save();
        $shop = Shop::query()->create([
            'vendor_id' => $record->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.$record->id,
            'status' => 'active',
        ]);

        return [$user->refresh(), $shop];
    }

    private function product(Shop $shop, string $name, string $status): Product
    {
        return Product::query()->create([
            'vendor_id' => $shop->vendor_id,
            'shop_id' => $shop->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'sku' => 'SKU-'.Str::upper(Str::random(6)),
            'status' => $status,
            'price_minor' => 1000,
            'currency' => 'CDF',
            'stock' => 4,
        ]);
    }

    private function paidOrder(User $vendor, string $number): Order
    {
        $customer = User::factory()->withRole(UserRole::Customer)->create();
        $shop = Shop::query()->where('vendor_id', $vendor->vendor_id)->firstOrFail();
        $product = $this->product($shop, 'Article '.$number, 'published');
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'number' => $number,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_minor' => 5000,
            'currency' => 'CDF',
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->vendor_id,
            'shop_id' => $shop->id,
            'quantity' => 1,
            'unit_price_minor' => 5000,
            'line_total_minor' => 5000,
        ]);
        Payment::query()->create([
            'order_id' => $order->id,
            'amount_minor' => 5000,
            'currency' => 'CDF',
            'status' => 'successful',
        ]);

        return $order->refresh();
    }

    private function mission(User $agent, string $status): Delivery
    {
        $customer = User::factory()->withRole(UserRole::Customer)->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'number' => 'LIV-'.Str::upper(Str::random(4)),
            'status' => 'ready',
            'payment_status' => 'unpaid',
            'total_minor' => 1000,
            'currency' => 'CDF',
        ]);

        return Delivery::query()->create([
            'order_id' => $order->id,
            'agent_id' => $agent->id,
            'status' => $status,
            'fee_minor' => 200,
            'currency' => 'CDF',
        ]);
    }
}
