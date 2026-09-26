<?php

namespace Tests\Feature\Commerce;

use App\Enums\UserRole;
use App\Models\CartItem;
use App\Models\Delivery;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Catalog\StockService;
use App\Enums\StockMovementType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_checkout_and_tracking_run_on_the_server(): void
    {
        $buyer = User::factory()->withRole(UserRole::Client)->create();
        $product = Product::factory()->published()->create(['price' => 1_000_000, 'currency' => 'CDF']);
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 4,
            'reserved' => 0,
        ]);
        $zone = DeliveryZone::query()->create([
            'name' => 'Kinshasa',
            'fee' => 200_000,
            'is_active' => true,
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect(route('cart.show'));

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('20 000,00');

        $item = CartItem::query()->firstOrFail();
        $this->patch(route('cart.items.update', $item), ['quantity' => 3])->assertRedirect();
        $this->assertSame(3, $item->fresh()->quantity);
        $this->patch(route('cart.items.update', $item), ['quantity' => 9])
            ->assertSessionHasErrors('quantity');

        $this->post('/login', [
            'email' => $buyer->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(3, CartItem::query()->whereHas('cart', fn ($query) => $query->where('user_id', $buyer->id))->value('quantity'));

        $this->actingAs($buyer)->post(route('checkout.store'), [
            'phone' => '+243810000111',
            'city' => 'Kinshasa',
            'address' => '10 Avenue du Commerce',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'sandbox',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(3_200_000, $order->total);
        $this->assertSame(300_000, $order->commission_total);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(1, Inventory::query()->where('product_id', $product->id)->first()->available());
        $this->assertSame(0, CartItem::query()->count());

        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee('aucun débit réel');

        $vendorUser = $product->shop->vendor->user;
        $this->assertSame(2_700_000, Wallet::query()->where('user_id', $vendorUser->id)->value('balance'));

        $this->actingAs($buyer)->get('/admin')->assertForbidden();
        $this->actingAs($vendorUser)->get(route('admin.settings'))->assertForbidden();
    }

    public function test_variant_stock_blocks_checkout_and_cod_pays_the_courier_on_delivery(): void
    {
        $buyer = User::factory()->withRole(UserRole::Client)->create();
        $courier = User::factory()->withRole(UserRole::DeliveryAgent)->create();
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        $product = Product::factory()->published()->create(['price' => 500_000]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 700_000,
            'stock' => 0,
        ]);
        app(StockService::class)->record($product, $variant, StockMovementType::Purchase, 1, null, 'seed', 'ouverture');
        $zone = DeliveryZone::query()->create(['name' => 'Course', 'fee' => 100_000, 'is_active' => true]);

        $this->actingAs($buyer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('product_variant_id');

        $this->actingAs($buyer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect();

        Inventory::query()->where('product_variant_id', $variant->id)->update(['quantity' => 0]);

        $this->actingAs($buyer)->post(route('checkout.store'), [
            'phone' => '+243810000222',
            'city' => 'Kinshasa',
            'address' => 'Marché',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'cod',
        ])->assertSessionHasErrors('stock');

        Inventory::query()->where('product_variant_id', $variant->id)->update(['quantity' => 1]);
        $variant->update(['stock' => 1]);

        $this->actingAs($buyer)->post(route('checkout.store'), [
            'phone' => '+243810000222',
            'city' => 'Kinshasa',
            'address' => 'Marché',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'cod',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(800_000, $order->total);
        $this->assertSame('unpaid', $order->payment_status);
        $delivery = Delivery::query()->where('order_id', $order->id)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.deliveries.assign', $delivery), [
            'agent_id' => $courier->id,
        ])->assertRedirect();

        $steps = [
            'accepted' => [],
            'departed' => [],
            'en_route' => ['eta_minutes' => 30],
            'arrived' => [],
            'delivered' => [],
        ];

        foreach ($steps as $status => $extra) {
            $this->actingAs($courier)->post(route('delivery.jobs.advance', $delivery), [
                'status' => $status,
                ...$extra,
            ])->assertRedirect();
        }

        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(100_000, Wallet::query()->where('user_id', $courier->id)->value('balance'));
        $this->actingAs($buyer)->get(route('orders.show', $order))->assertOk()->assertSee('Arrivée estimée');
    }

    public function test_api_cart_uses_explicit_status_codes(): void
    {
        $buyer = User::factory()->withRole(UserRole::Client)->create();
        $product = Product::factory()->published()->create();
        Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 2, 'reserved' => 0]);

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id])->assertUnauthorized();
        $this->actingAs($buyer, 'sanctum')->postJson('/api/v1/cart/items', [])->assertUnprocessable();
        $this->actingAs($buyer, 'sanctum')->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();
        $this->actingAs($buyer, 'sanctum')->getJson('/api/v1/cart')->assertOk()->assertJsonPath('data.lines.0.quantity', 1);
        $this->actingAs($buyer, 'sanctum')->getJson('/api/v1/admin/products')->assertForbidden();
        $this->getJson('/api/v1/products/inconnu')->assertNotFound();
    }
}
