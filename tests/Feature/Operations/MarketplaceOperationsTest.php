<?php

namespace Tests\Feature\Operations;

use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Models\CourierProfile;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PriceChange;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SupplierOffer;
use App\Models\User;
use App\Services\Catalog\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MarketplaceOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_addresses_permissions_and_points_ledger(): void
    {
        $this->post('/register', [
            'first_name' => 'Amina',
            'last_name' => 'Kabila',
            'name' => 'Amina Kabila',
            'email' => 'amina@twende.test',
            'phone' => '+243810009901',
            'password' => 'Twende-Demo-2026',
            'password_confirmation' => 'Twende-Demo-2026',
        ])->assertRedirect();

        $user = User::query()->where('email', 'amina@twende.test')->firstOrFail();
        $this->assertSame('Amina', $user->first_name);
        $user->forceFill(['email_verified_at' => now()])->save();
        $other = User::factory()->withRole(UserRole::Client)->create();
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        $product = Product::factory()->published()->create();

        $this->actingAs($user)->post(route('addresses.store'), [
            'label' => 'Maison',
            'phone' => '+243810009901',
            'city' => 'Kinshasa',
            'address' => '12 Avenue',
            'latitude' => -4.3210987,
            'longitude' => 15.3123456,
        ])->assertRedirect();
        $this->actingAs($user)->post(route('addresses.store'), [
            'label' => 'Bureau',
            'phone' => '+243810009902',
            'city' => 'Kinshasa',
            'address' => '5 Marché',
            'is_default' => '1',
        ])->assertRedirect();

        $primary = $user->addresses()->where('is_default', true)->firstOrFail();
        $this->assertSame('Bureau', $primary->label);
        $this->actingAs($other)->delete(route('addresses.destroy', $user->addresses()->first()))->assertForbidden();
        $this->actingAs($user)->get(route('products.show', $product))->assertOk()->assertDontSee('-4.3210987');
        $this->assertTrue($admin->can('users.view'));
        $this->assertTrue($admin->can('refunds.manage'));
        $this->assertFalse($user->can('users.delete'));
        $this->assertFalse($user->can('payments.view'));
        $this->actingAs($user)->get(route('points.index'))->assertOk();
        $this->actingAs($user)->get(route('coupons.index'))->assertOk();
    }

    public function test_price_history_duplicate_and_image_limit(): void
    {
        $product = Product::factory()->published()->create(['price' => 100000, 'currency' => 'CDF']);
        $vendor = $product->shop->vendor->user;
        config(['twende.media.max_images' => 1]);

        $this->actingAs($vendor)->put(route('vendor.products.update', $product), [
            'shop_id' => $product->shop_id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => $product->description,
            'price' => '1500.00',
            'currency' => 'CDF',
            'status' => 'published',
            'condition' => 'new',
        ])->assertRedirect();

        $change = PriceChange::query()->firstOrFail();
        $this->assertSame(100000, $change->amount_before);
        $this->assertSame(150000, $change->amount_after);
        $this->actingAs($vendor)->post(route('vendor.products.images.store', $product), [
            'image' => UploadedFile::fake()->image('un.jpg'),
        ])->assertRedirect();
        $this->actingAs($vendor)->post(route('vendor.products.images.store', $product), [
            'image' => UploadedFile::fake()->image('deux.jpg'),
        ])->assertSessionHasErrors('image');
        $this->expectException(\LogicException::class);
        $change->delete();
    }

    public function test_duplicate_creates_a_draft_without_rewriting_history(): void
    {
        $product = Product::factory()->published()->create();
        $vendor = $product->shop->vendor->user;
        $this->actingAs($vendor)->post(route('vendor.products.duplicate', $product))->assertRedirect();
        $copy = Product::query()->whereKeyNot($product->id)->firstOrFail();
        $this->assertSame('draft', $copy->status->value);
        $this->actingAs($vendor)->put(route('vendor.products.update', $product).'/historique')->assertNotFound();
    }

    public function test_failed_payment_restores_stock_and_retry_captures_once(): void
    {
        config([
            'twende.payments.webhook_secret' => 'secret-test',
            'twende.payments.mpesa.enabled' => true,
            'twende.payments.mpesa.key' => 'key',
            'twende.payments.mpesa.secret' => 'secret',
        ]);
        $buyer = User::factory()->withRole(UserRole::Client)->create();
        $product = Product::factory()->published()->create(['price' => 500000, 'currency' => 'CDF']);
        Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 5, 'reserved' => 0]);
        $zone = DeliveryZone::query()->create(['name' => 'Gombe', 'fee' => 0, 'is_active' => true]);

        $this->actingAs($buyer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();
        $this->actingAs($buyer)->post(route('checkout.store'), [
            'phone' => '+243810009903',
            'city' => 'Kinshasa',
            'address' => 'Marché',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'mpesa',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame(4, Inventory::query()->where('product_id', $product->id)->first()->available());

        $payload = json_encode([
            'reference' => $order->payment->reference,
            'amount' => $order->total,
            'currency' => 'CDF',
            'status' => 'payment.failed',
            'idempotency' => 'fail-1',
        ], JSON_THROW_ON_ERROR);
        $this->call('POST', route('payments.webhook', 'mpesa'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TWENDE_SIGNATURE' => hash_hmac('sha256', $payload, 'secret-test'),
        ], $payload)->assertNoContent();

        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertFalse((bool) $order->fresh()->stock_committed);
        $this->assertSame(5, Inventory::query()->where('product_id', $product->id)->first()->available());

        $this->call('POST', route('payments.webhook', 'mpesa'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TWENDE_SIGNATURE' => hash_hmac('sha256', $payload, 'secret-test'),
        ], $payload)->assertNoContent();
        $this->assertSame(5, Inventory::query()->where('product_id', $product->id)->first()->available());

        $this->actingAs($buyer)->post(route('orders.retry', $order), [
            'payment_method' => 'sandbox',
        ])->assertRedirect();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(4, Inventory::query()->where('product_id', $product->id)->first()->available());
        $this->assertSame(1, $order->fresh()->payments()->where('status', 'paid')->count());
    }

    public function test_supplier_sync_blocks_a_sale_when_the_offer_is_gone(): void
    {
        $source = Product::factory()->published()->create();
        $seller = Product::factory()->published()->create();
        $offer = SupplierOffer::query()->create([
            'vendor_id' => $source->shop->vendor_id,
            'name' => 'Chargeur fournisseur',
            'sku' => 'SUP-CHARGE',
            'description' => 'Chargeur décrit par le fournisseur.',
            'price' => 200000,
            'currency' => 'CDF',
            'stock' => 4,
            'lead_days' => 2,
            'moq' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($seller->shop->vendor->user)->post(route('vendor.dropship.import', $offer), [
            'shop_id' => $seller->shop_id,
            'price' => '25.00',
            'stock_sync' => '1',
            'publish' => '1',
        ])->assertRedirect();

        $imported = Product::query()->where('supplier_offer_id', $offer->id)->firstOrFail();
        $this->assertSame('Chargeur décrit par le fournisseur.', $imported->description);
        $this->assertTrue($imported->stock_sync);
        $offer->update(['stock' => 0]);

        $this->actingAs(User::factory()->withRole(UserRole::Client)->create())->post(route('cart.items.store'), [
            'product_id' => $imported->id,
            'quantity' => 1,
        ])->assertSessionHasErrors();
    }

    public function test_full_order_path_from_variant_to_review(): void
    {
        $buyer = User::factory()->withRole(UserRole::Client)->create(['email_verified_at' => now()]);
        $courier = User::factory()->withRole(UserRole::DeliveryAgent)->create();
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        $product = Product::factory()->published()->create(['name' => 'Casque Ops', 'price' => 800000, 'currency' => 'CDF']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Noir / M',
            'color_name' => 'Noir',
            'color_hex' => '#111111',
            'size' => 'M',
            'price' => 900000,
            'stock' => 0,
        ]);
        app(StockService::class)->record($product, $variant, StockMovementType::Purchase, 3, null, 'seed', 'ouverture');
        $zone = DeliveryZone::query()->create(['name' => 'Lemba', 'fee' => 100000, 'is_active' => true]);

        $this->get(route('search', ['q' => 'Casque Ops']))->assertOk()->assertSee('Casque Ops');
        $this->get(route('products.show', $product))->assertOk()->assertSee('#111111');

        $this->actingAs($buyer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect();
        $this->actingAs($buyer)->post(route('checkout.store'), [
            'phone' => '+243810009904',
            'city' => 'Kinshasa',
            'address' => 'Quartier',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'sandbox',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame('paid', $order->payment_status);
        $vendor = $product->shop->vendor->user;
        $this->actingAs($vendor)->post(route('vendor.orders.prepare', $order))->assertRedirect();
        $this->actingAs($vendor)->post(route('vendor.orders.ready', $order))->assertRedirect();
        $this->assertSame('ready', $order->fresh()->status);

        $this->actingAs($courier)->post(route('delivery.profile'), [
            'availability' => 'offline',
            'vehicle_type' => 'Moto',
            'vehicle_plate' => 'KN-1234',
        ])->assertRedirect();
        $delivery = $order->delivery;
        $this->actingAs($admin)->post(route('admin.deliveries.assign', $delivery), ['agent_id' => $courier->id])->assertRedirect();
        $this->actingAs($courier)->post(route('delivery.jobs.advance', $delivery), ['status' => 'accepted'])->assertSessionHasErrors('status');
        $courier->courierProfile->update(['availability' => CourierProfile::AVAILABLE]);

        foreach (['accepted', 'departed', 'en_route', 'arrived', 'delivered'] as $status) {
            $this->actingAs($courier)->post(route('delivery.jobs.advance', $delivery), [
                'status' => $status,
                ...($status === 'en_route' ? ['eta_minutes' => 20] : []),
            ])->assertRedirect();
        }

        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertSame(CourierProfile::AVAILABLE, $courier->courierProfile->fresh()->availability);
        $this->actingAs($buyer)->post(route('orders.review', $order), [
            'product_id' => $product->id,
            'rating' => 5,
            'body' => 'Casque reçu en bon état, merci.',
        ])->assertRedirect();
        $review = $product->reviews()->firstOrFail();
        $this->actingAs($vendor)->post(route('vendor.reviews.reply', $review), [
            'vendor_reply' => 'Merci pour votre confiance.',
        ])->assertRedirect();
        $this->assertNotNull($review->fresh()->vendor_replied_at);

        $this->actingAs($buyer)->post(route('orders.return', $order), [
            'reason' => 'Le casque ne correspond pas à la taille choisie.',
            'evidence' => UploadedFile::fake()->image('preuve.jpg'),
        ])->assertRedirect();
        $this->assertNotNull($order->returns()->first()->payment_id);
        $unpaid = Order::query()->create([
            'number' => 'TM-UNPAID-1',
            'user_id' => $buyer->id,
            'status' => 'confirmed',
            'currency' => 'CDF',
            'subtotal' => 1000,
            'discount' => 0,
            'delivery_fee' => 0,
            'tax' => 0,
            'commission_total' => 0,
            'total' => 1000,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'phone' => '+243810009904',
            'city' => 'Kinshasa',
            'address' => 'Sans paiement',
        ]);
        $this->actingAs($buyer)->from(route('orders.show', $unpaid))->post(route('orders.return', $unpaid), [
            'reason' => 'Sans paiement confirmé cela doit échouer ici.',
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_csv_import_creates_drafts_and_rejects_excel(): void
    {
        $product = Product::factory()->create();
        $vendor = $product->shop->vendor->user;
        $csv = UploadedFile::fake()->createWithContent('catalogue.csv', "name,sku,description,price,stock\nLampe,LAMP-OPS,Lampe de bureau solide,25.00,2\n");

        $this->actingAs($vendor)->post(route('vendor.import.store'), [
            'shop_id' => $product->shop_id,
            'file' => $csv,
        ])->assertRedirect();
        $this->assertDatabaseHas('products', ['sku' => 'LAMP-OPS', 'status' => 'draft']);

        $this->actingAs($vendor)->post(route('vendor.import.store'), [
            'shop_id' => $product->shop_id,
            'file' => UploadedFile::fake()->create('catalogue.xlsx', 20),
        ])->assertSessionHasErrors('file');
    }
}
