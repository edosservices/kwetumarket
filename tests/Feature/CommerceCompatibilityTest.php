<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use App\Support\Rbac\PermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommerceCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_stay_on_the_order_and_inside_each_role_scope(): void
    {
        [$vendorA] = $this->vendor('Alpha');
        [$vendorB] = $this->vendor('Beta');
        $customerA = User::factory()->withRole(UserRole::Customer)->create();
        $customerB = User::factory()->withRole(UserRole::Customer)->create();
        $orderA = $this->deliveredOrder($customerA, $vendorA, 'RET-A');
        $orderB = $this->deliveredOrder($customerB, $vendorB, 'RET-B');
        $reason = 'Le colis est arrivé abîmé';

        $this->actingAs($customerA)->post('/compte/commandes/'.$orderB->id.'/retour', [
            'reason' => $reason,
            'user_id' => $customerA->id,
        ])->assertForbidden();

        $this->actingAs($customerA)->post('/compte/commandes/'.$orderA->id.'/retour', [
            'reason' => $reason,
            'user_id' => $customerB->id,
        ])->assertRedirect();

        $returnA = $orderA->returns()->firstOrFail();
        $this->assertSame($customerA->id, $returnA->user_id);
        $this->assertSame('requested', $returnA->status);

        $this->actingAs($vendorB)->post('/vendeur/retours/'.$returnA->id, [
            'decision' => 'vendor_accepted',
        ])->assertForbidden();

        $this->actingAs($vendorA)->post('/vendeur/retours/'.$returnA->id, [
            'decision' => 'vendor_accepted',
            'vendor_note' => 'Reçu',
        ])->assertRedirect();
        $this->assertSame('returned', $orderA->refresh()->status);

        $finance = User::factory()->withRole(UserRole::FinanceManager)->create();
        $this->actingAs($finance)->post('/admin/retours/'.$returnA->id, [
            'decision' => 'approved',
        ])->assertForbidden();

        $manager = User::factory()->withRole(UserRole::OrderManager)->create();
        $this->actingAs($manager)->post('/admin/retours/'.$returnA->id, [
            'decision' => 'approved',
            'admin_note' => 'OK',
        ])->assertRedirect();

        $this->actingAs($finance)->post('/admin/remboursements', [
            'return_request_id' => $returnA->id,
            'amount_minor' => 1500,
            'user_id' => $customerB->id,
            'order_id' => $orderB->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('refunds', [
            'order_id' => $orderA->id,
            'return_request_id' => $returnA->id,
            'amount_minor' => 1500,
        ]);
        $this->assertSame('refunded', $returnA->refresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'refund.create']);

        $admin = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $orderC = $this->deliveredOrder($customerA, $vendorA, 'RET-C');
        $this->actingAs($customerA)->post('/compte/commandes/'.$orderC->id.'/retour', [
            'reason' => $reason,
        ])->assertRedirect();
        $returnC = $orderC->returns()->firstOrFail();
        $this->actingAs($admin)->post('/admin/retours/'.$returnC->id, [
            'decision' => 'rejected',
        ])->assertRedirect();
        $this->assertSame('rejected', $returnC->refresh()->status);
        $this->actingAs($admin)->post('/admin/remboursements', [
            'return_request_id' => $returnC->id,
            'amount_minor' => 100,
        ])->assertSessionHasErrors('return_request_id');
    }

    public function test_reviews_follow_the_author_vendor_and_moderator_scope(): void
    {
        [$vendorA, $shopA] = $this->vendor('Alpha');
        [$vendorB] = $this->vendor('Beta');
        $customer = User::factory()->withRole(UserRole::Customer)->create();
        $other = User::factory()->withRole(UserRole::Customer)->create();
        $product = $this->product($shopA, 'Savon');
        $order = $this->deliveredOrder($customer, $vendorA, 'AVIS-1', $product);

        $this->actingAs($other)->post('/compte/avis', [
            'product_id' => $product->id,
            'rating' => 5,
            'user_id' => $customer->id,
        ])->assertForbidden();

        $this->actingAs($customer)->post('/compte/avis', [
            'product_id' => $product->id,
            'rating' => 4,
            'body' => 'Correct',
            'user_id' => $other->id,
        ])->assertRedirect();

        $review = Review::query()->where('product_id', $product->id)->firstOrFail();
        $this->assertSame($customer->id, $review->user_id);
        $this->assertSame('pending', $review->status);
        $this->assertSame($order->user_id, $review->user_id);

        $this->actingAs($other)->put('/compte/avis/'.$review->id, ['rating' => 1])->assertForbidden();
        $this->actingAs($customer)->put('/compte/avis/'.$review->id, [
            'rating' => 5,
            'body' => 'Mieux',
            'user_id' => $other->id,
            'product_id' => 999,
        ])->assertRedirect();
        $this->assertSame(5, $review->refresh()->rating);
        $this->assertSame($product->id, $review->product_id);

        $this->actingAs($vendorB)->post('/vendeur/avis/'.$review->id, [
            'vendor_reply' => 'Pas nous',
        ])->assertForbidden();
        $this->actingAs($vendorA)->post('/vendeur/avis/'.$review->id, [
            'vendor_reply' => 'Merci',
        ])->assertRedirect();
        $this->assertSame('Merci', $review->refresh()->vendor_reply);
        $this->assertDatabaseHas('audit_logs', ['action' => 'review.reply', 'subject_id' => $review->id]);

        $catalog = User::factory()->withRole(UserRole::CatalogManager)->create();
        $this->actingAs($catalog)->post('/admin/avis/'.$review->id.'/moderer', [
            'decision' => 'approve',
        ])->assertForbidden();

        $moderator = User::factory()->withRole(UserRole::Moderator)->create();
        $this->actingAs($moderator)->post('/admin/avis/'.$review->id.'/moderer', [
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertSame('published', $review->refresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'review.moderate', 'subject_id' => $review->id]);

        $this->actingAs($other)->post('/avis/'.$review->id.'/signaler', [
            'reason' => 'Injure',
            'user_id' => $customer->id,
        ])->assertRedirect();
        $this->assertSame('reported', $review->refresh()->status);
        $this->assertSame($other->id, $review->reports()->firstOrFail()->user_id);

        $this->actingAs($moderator)->post('/admin/avis/'.$review->id.'/moderer', [
            'decision' => 'hide',
        ])->assertRedirect();
        $this->assertSame('hidden', $review->refresh()->status);
        $this->actingAs($customer)->delete('/compte/avis/'.$review->id)->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);

        $this->actingAs($moderator)->post('/admin/avis/'.$review->id.'/moderer', [
            'decision' => 'restore',
        ])->assertRedirect();
        $this->assertSame('published', $review->refresh()->status);

        $this->actingAs($customer)->delete('/compte/avis/'.$review->id)->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_checkout_shaped_orders_use_the_commerce_state_machine(): void
    {
        [$vendorA] = $this->vendor('Alpha');
        [$vendorB] = $this->vendor('Beta');
        $customer = User::factory()->withRole(UserRole::Customer)->create();
        $other = User::factory()->withRole(UserRole::Customer)->create();
        $order = $this->checkoutOrder($customer, $vendorA, 'CHK-1');
        $foreign = $this->checkoutOrder($other, $vendorB, 'CHK-2');

        $this->assertSame('confirmed', $order->status);
        $this->assertSame('unpaid', $order->payment_status);

        $this->actingAs($customer)->put('/vendeur/commandes/'.$order->id, ['status' => 'preparing'])->assertForbidden();
        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$foreign->id, ['status' => 'preparing'])->assertForbidden();
        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$order->id, ['status' => 'cancelled'])->assertForbidden();
        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$order->id, ['status' => 'shipped'])->assertSessionHasErrors('status');
        $this->assertSame('confirmed', $order->refresh()->status);

        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$order->id, ['status' => 'preparing'])->assertRedirect();
        $this->actingAs($vendorA)->put('/vendeur/commandes/'.$order->id, ['status' => 'ready'])->assertRedirect();

        $support = User::factory()->withRole(UserRole::SupportAgent)->create();
        $this->actingAs($support)->put('/admin/commandes/'.$order->id, ['status' => 'cancelled'])->assertForbidden();

        $manager = User::factory()->withRole(UserRole::OrderManager)->create();
        $this->actingAs($manager)->put('/admin/commandes/'.$order->id, [
            'payment_status' => 'paid',
        ])->assertRedirect();
        $this->assertSame('paid', $order->refresh()->payment_status);
        $this->assertSame('ready', $order->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.update', 'subject_id' => $order->id]);

        $this->actingAs($other)->get('/compte/commandes/'.$order->id)->assertForbidden();
    }

    public function test_storefront_and_role_directory_stay_readable(): void
    {
        $this->get('/')->assertOk();
        $this->get('/recherche')->assertOk();
        $this->get('/categories')->assertOk();
        $this->get('/boutiques')->assertOk();
        $this->get('/panier')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();

        $admin = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $catalog = User::factory()->withRole(UserRole::CatalogManager)->create();

        $this->actingAs($catalog)->get('/admin/roles')->assertForbidden();
        $this->actingAs($catalog)->get('/admin/permissions')->assertForbidden();

        $this->actingAs($admin)->get('/admin/roles')
            ->assertOk()
            ->assertSee('super_admin')
            ->assertSee((string) count(PermissionMatrix::all()))
            ->assertSee(__('ui.access.roles_intro'))
            ->assertSee($admin->name)
            ->assertDontSee('name="permissions[]"', false);

        $this->actingAs($admin)->get('/admin/permissions')
            ->assertOk()
            ->assertSee('finance.refunds')
            ->assertSee('delivery.manage')
            ->assertSee($admin->name);
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

    private function product(Shop $shop, string $name): Product
    {
        return Product::query()->create([
            'vendor_id' => $shop->vendor_id,
            'shop_id' => $shop->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'sku' => 'SKU-'.Str::upper(Str::random(6)),
            'status' => 'published',
            'price_minor' => 2000,
            'currency' => 'CDF',
            'stock' => 3,
        ]);
    }

    private function deliveredOrder(User $customer, User $vendor, string $number, ?Product $product = null): Order
    {
        $shop = Shop::query()->where('vendor_id', $vendor->vendor_id)->firstOrFail();
        $product ??= $this->product($shop, 'Article '.$number);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'number' => $number,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'total_minor' => 2000,
            'currency' => 'CDF',
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->vendor_id,
            'shop_id' => $shop->id,
            'quantity' => 1,
            'unit_price_minor' => 2000,
            'line_total_minor' => 2000,
        ]);
        Payment::query()->create([
            'order_id' => $order->id,
            'amount_minor' => 2000,
            'currency' => 'CDF',
            'status' => 'successful',
        ]);

        return $order->refresh();
    }

    private function checkoutOrder(User $customer, User $vendor, string $number): Order
    {
        $shop = Shop::query()->where('vendor_id', $vendor->vendor_id)->firstOrFail();
        $product = $this->product($shop, 'Article '.$number);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'number' => $number,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'total_minor' => 2000,
            'currency' => 'CDF',
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->vendor_id,
            'shop_id' => $shop->id,
            'quantity' => 1,
            'unit_price_minor' => 2000,
            'line_total_minor' => 2000,
        ]);

        return $order;
    }
}
