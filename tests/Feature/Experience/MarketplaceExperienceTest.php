<?php

namespace Tests\Feature\Experience;

use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Models\DeliveryZone;
use App\Models\ExchangeRate;
use App\Models\HeroSlide;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Models\PointsWallet;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Referral;
use App\Models\Shop;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Catalog\StockService;
use App\Services\Commerce\CartService;
use App\Services\Commerce\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_has_mobile_navigation_and_image_choice_before_camera(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(__('experience.home'))
            ->assertSee(__('experience.cart'))
            ->assertSeeInOrder([__('experience.choose_image'), __('experience.take_photo')]);
    }

    public function test_admin_hero_slide_appears_on_the_homepage(): void
    {
        Storage::fake('public');
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $this->actingAs($admin)->post(route('admin.hero.store'), [
            'placement' => 'home',
            'title' => 'Promo quartier',
            'subtitle' => 'Cette semaine',
            'cta_label' => 'Voir',
            'cta_url' => '/promotions',
            'sort_order' => 1,
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('slide.jpg', 800, 400),
        ])->assertRedirect();

        $this->assertDatabaseHas('hero_slides', ['title' => 'Promo quartier']);
        $this->get(route('home'))->assertOk()->assertSee('Promo quartier');
        $this->assertNotNull(HeroSlide::query()->first()->image);
    }

    public function test_color_and_size_use_the_selected_variant_stock(): void
    {
        $product = Product::factory()->published()->create(['price' => 100000]);
        $black = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Noir / L',
            'color_name' => 'Noir',
            'color_hex' => '#000000',
            'size' => 'L',
            'price' => 120000,
            'stock' => 0,
        ]);
        $white = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Blanc / L',
            'color_name' => 'Blanc',
            'color_hex' => '#FFFFFF',
            'size' => 'L',
            'price' => 90000,
            'promotional_price' => 80000,
            'stock' => 0,
        ]);
        app(StockService::class)->record($product, $white, StockMovementType::Purchase, 2, null, 'b', 'two');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Noir')
            ->assertSee('#000000')
            ->assertSee('Blanc');

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $black->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('quantity');

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $white->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.show'));

        $quote = app(CartService::class)->quote();
        $this->assertSame(80000, $quote->subtotal);
    }

    public function test_webhook_rejects_a_bad_signature_and_a_wrong_amount_then_captures_once(): void
    {
        config(['twende.payments.webhook_secret' => 'secret-test']);
        $product = Product::factory()->published()->create(['price' => 100000]);
        $buyer = User::factory()->withRole(UserRole::Client)->create();
        $order = Order::query()->create([
            'number' => 'TM-TEST-1',
            'user_id' => $buyer->id,
            'status' => 'confirmed',
            'currency' => 'CDF',
            'subtotal' => 100000,
            'discount' => 0,
            'delivery_fee' => 0,
            'tax' => 0,
            'commission_total' => 10000,
            'total' => 100000,
            'payment_method' => 'mpesa',
            'payment_status' => 'pending',
            'phone' => '+243810000222',
            'city' => 'Kinshasa',
            'address' => '1 rue',
        ]);
        $order->items()->create([
            'shop_id' => $product->shop_id,
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 1,
            'unit_price' => 100000,
            'line_total' => 100000,
            'commission' => 10000,
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'mpesa',
            'status' => 'pending',
            'amount' => 100000,
            'reference' => 'MPESA-1',
        ]);

        $body = json_encode([
            'reference' => 'MPESA-1',
            'amount' => 100000,
            'currency' => 'CDF',
            'status' => 'paid',
        ]);

        $this->call('POST', route('payments.webhook', 'mpesa'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TWENDE_SIGNATURE' => 'nope',
        ], $body)->assertStatus(401);

        $wrong = json_encode([
            'reference' => 'MPESA-1',
            'amount' => 1,
            'currency' => 'CDF',
            'status' => 'paid',
        ]);
        $this->call('POST', route('payments.webhook', 'mpesa'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TWENDE_SIGNATURE' => hash_hmac('sha256', $wrong, 'secret-test'),
        ], $wrong)->assertStatus(422);
        $this->assertSame('pending', $payment->fresh()->status);

        $this->call('POST', route('payments.webhook', 'mpesa'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TWENDE_SIGNATURE' => hash_hmac('sha256', $body, 'secret-test'),
        ], $body)->assertNoContent();
        $this->call('POST', route('payments.webhook', 'mpesa'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TWENDE_SIGNATURE' => hash_hmac('sha256', $body, 'secret-test'),
        ], $body)->assertNoContent();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(90000, Wallet::query()->where('user_id', $product->shop->vendor->user_id)->value('balance'));
    }

    public function test_enabled_gateway_stays_pending_until_the_webhook(): void
    {
        config([
            'twende.payments.mpesa.enabled' => true,
            'twende.payments.mpesa.key' => 'key',
            'twende.payments.mpesa.secret' => 'secret',
        ]);
        $buyer = User::factory()->withRole(UserRole::Client)->create();
        $product = Product::factory()->published()->create(['price' => 50000, 'currency' => 'CDF']);
        Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 2, 'reserved' => 0]);
        $zone = DeliveryZone::query()->create(['name' => 'Gombe', 'fee' => 1000, 'is_active' => true]);

        $this->actingAs($buyer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $this->actingAs($buyer)->get(route('checkout.create'))->assertOk()->assertSee('M-Pesa');
        $this->actingAs($buyer)->post(route('checkout.store'), [
            'phone' => '+243810000333',
            'city' => 'Kinshasa',
            'address' => '2 rue',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'mpesa',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame(0, (int) Wallet::query()->where('user_id', $product->shop->vendor->user_id)->value('balance'));
    }

    public function test_referral_points_require_a_verified_buyer_and_reverse_on_cancel(): void
    {
        $referrer = User::factory()->withRole(UserRole::Client)->create(['phone' => '+243811111111']);
        $unverified = User::factory()->withRole(UserRole::Client)->unverified()->create(['phone' => '+243822222222']);
        $blocked = Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $unverified->id,
            'code' => $referrer->referral_code,
        ]);
        $pending = Order::query()->create([
            'number' => 'TM-UNVERIFIED',
            'user_id' => $unverified->id,
            'status' => 'confirmed',
            'currency' => 'CDF',
            'subtotal' => 1000,
            'total' => 1000,
            'payment_method' => 'sandbox',
            'payment_status' => 'paid',
            'phone' => '+243822222222',
            'city' => 'Kinshasa',
            'address' => '3 rue',
        ]);
        app(\App\Services\Commerce\ReferralService::class)->rewardFirstPaidOrder($unverified, $pending);
        $this->assertSame(0, app(PointsService::class)->balance($referrer));
        $this->assertNull($blocked->fresh()->rewarded_at);

        $verified = User::factory()->withRole(UserRole::Client)->create(['phone' => '+243833333333']);
        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $verified->id,
            'code' => $referrer->referral_code,
        ]);
        $product = Product::factory()->published()->create(['price' => 100000, 'currency' => 'CDF']);
        Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 2, 'reserved' => 0]);
        $zone = DeliveryZone::query()->create(['name' => 'Lemba', 'fee' => 0, 'is_active' => true]);
        $this->actingAs($verified)->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($verified)->post(route('checkout.store'), [
            'phone' => '+243833333333',
            'city' => 'Kinshasa',
            'address' => '4 rue',
            'delivery_zone_id' => $zone->id,
            'payment_method' => 'sandbox',
        ])->assertRedirect();

        $this->assertSame(100, app(PointsService::class)->balance($referrer));
        $order = Order::query()->where('user_id', $verified->id)->firstOrFail();
        $this->actingAs($verified)->post(route('orders.cancel', $order))->assertRedirect();
        $this->assertSame(0, app(PointsService::class)->balance($referrer));
    }

    public function test_self_referral_does_not_attach(): void
    {
        $user = User::factory()->withRole(UserRole::Client)->create(['phone' => '+243844444444']);
        app(\App\Services\Commerce\ReferralService::class)->attach($user, $user->referral_code);
        $this->assertSame(0, Referral::query()->count());
    }

    public function test_points_can_pay_the_three_dollar_subscription_from_the_exchange_rate(): void
    {
        ExchangeRate::query()->create([
            'base' => 'USD',
            'quote' => 'CDF',
            'minor_per_unit' => 250000,
            'quoted_at' => now(),
        ]);
        $plan = SubscriptionPlan::query()->create([
            'name' => 'Boutique',
            'slug' => 'boutique-test',
            'price' => 0,
            'price_usd_cents' => 300,
            'interval_days' => 30,
            'is_active' => true,
        ]);
        $vendorUser = User::factory()->withRole(UserRole::Vendor)->create();
        $vendorUser->vendorProfile()->create(['status' => 'active']);
        app(PointsService::class)->award($vendorUser, 3000, 'test');

        $this->actingAs($vendorUser)->get(route('vendor.subscription'))
            ->assertOk()
            ->assertSee('3,00 USD')
            ->assertSee('7 500,00 CDF');

        $this->actingAs($vendorUser)->post(route('vendor.subscription.store'), [
            'plan_id' => $plan->id,
            'use_points' => 1,
        ])->assertRedirect();

        $this->assertSame(0, PointsWallet::query()->where('user_id', $vendorUser->id)->value('balance'));
        $this->assertDatabaseHas('vendor_subscriptions', [
            'vendor_id' => $vendorUser->vendorProfile->id,
            'payment_reference' => 'POINTS',
            'amount_minor' => 750000,
            'points_spent' => 3000,
        ]);
    }

    public function test_free_shipping_threshold_and_shop_hours(): void
    {
        PlatformSetting::put('free_shipping_minor', '100000');
        $product = Product::factory()->published()->create(['price' => 150000]);
        Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 1, 'reserved' => 0]);
        $zone = DeliveryZone::query()->create(['name' => 'Centre', 'fee' => 5000, 'is_active' => true]);
        $this->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1]);
        $quote = app(CartService::class)->quote($zone->id);
        $this->assertSame(0, $quote->deliveryFee);

        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Africa/Kinshasa'));
        $shop = Shop::factory()->create([
            'timezone' => 'Africa/Kinshasa',
            'weekly_hours' => ['mon' => ['08:00', '18:00']],
        ]);
        $this->assertTrue($shop->openState()['open']);
        Carbon::setTestNow(Carbon::parse('2026-09-28 19:00:00', 'Africa/Kinshasa'));
        $this->assertFalse($shop->fresh()->openState()['open']);
    }
}
