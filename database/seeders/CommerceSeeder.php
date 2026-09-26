<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\ShopStatus;
use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\AdCampaign;
use App\Models\Review;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Shop;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCertification;
use App\Services\Commerce\CartService;
use App\Services\Commerce\CheckoutService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        DeliveryZone::query()->updateOrCreate(['name' => 'Retrait en boutique'], ['city' => 'Kinshasa', 'fee' => 0, 'is_active' => true]);
        DeliveryZone::query()->updateOrCreate(['name' => 'Livraison Kinshasa'], ['city' => 'Kinshasa', 'fee' => 500000, 'is_active' => true]);

        Coupon::query()->updateOrCreate(['code' => 'BIENVENUE'], [
            'type' => 'percent',
            'value' => 10,
            'min_subtotal' => 0,
            'is_active' => true,
            'usage_limit' => 1000,
        ]);

        SubscriptionPlan::query()->updateOrCreate(['slug' => 'decouverte'], ['name' => 'Découverte', 'price' => 0, 'interval_days' => 30, 'is_active' => true]);
        SubscriptionPlan::query()->updateOrCreate(['slug' => 'boutique'], ['name' => 'Boutique', 'price' => 0, 'price_usd_cents' => 300, 'interval_days' => 30, 'is_active' => true]);

        User::query()->whereNull('referral_code')->each(function (User $user): void {
            $user->forceFill(['referral_code' => User::nextReferralCode()])->save();
        });

        $categories = Category::query()->where('status', 'active')->pluck('id');
        $shops = Shop::query()->where('status', ShopStatus::Active)->get();

        while (Vendor::query()->count() < 20) {
            $index = Vendor::query()->count() + 1;
            $user = User::query()->updateOrCreate(
                ['email' => 'vendeur'.$index.'@twende.market'],
                [
                    'name' => 'Vendeur '.$index,
                    'phone' => '+24382000'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                    'password' => DatabaseSeeder::DEMO_PASSWORD,
                    'locale' => 'fr',
                    'currency' => 'CDF',
                    'email_verified_at' => now(),
                ],
            );
            $user->syncRoles([UserRole::Vendor->value]);
            Vendor::query()->updateOrCreate(['user_id' => $user->id], [
                'status' => VendorStatus::Active,
                'business_name' => 'Commerce '.$index,
                'manager_name' => $user->name,
            ]);
        }

        $vendors = Vendor::query()->where('status', VendorStatus::Active)->get();

        while (Shop::query()->count() < 10) {
            $index = Shop::query()->count() + 1;
            $vendor = $vendors[($index - 1) % $vendors->count()];
            Shop::query()->updateOrCreate(['slug' => 'boutique-'.$index], [
                'vendor_id' => $vendor->id,
                'name' => 'Boutique '.$index,
                'description' => 'Boutique de démonstration à Kinshasa.',
                'phone' => '+243830000'.$index,
                'email' => 'boutique'.$index.'@twende.market',
                'location' => 'Kinshasa',
                'status' => ShopStatus::Active,
                'country' => 'RD Congo',
                'province' => 'Kinshasa',
                'city' => 'Kinshasa',
                'commune' => 'Gombe',
                'latitude' => -4.31 + ($index / 1000),
                'longitude' => 15.31 + ($index / 1000),
                'publish_location' => true,
                'publish_address' => true,
            ]);
        }

        $shops = Shop::query()->where('status', ShopStatus::Active)->get();
        $names = ['Savon', 'Huile', 'Sucre', 'Farine', 'Lampe', 'Chargeur', 'Casque', 'Chemise', 'Pagne', 'Casserole', 'Ventilateur', 'Rallonge', 'Parfum', 'Cahier', 'Stylo'];

        while (Product::query()->count() < 100) {
            $index = Product::query()->count() + 1;
            $shop = $shops[$index % $shops->count()];
            $product = Product::query()->updateOrCreate(['sku' => 'DEMO-'.$index], [
                'shop_id' => $shop->id,
                'category_id' => $categories->random(),
                'name' => $names[$index % count($names)].' '.$index,
                'slug' => Str::slug($names[$index % count($names)].'-'.$index),
                'description' => 'Article de démonstration, stock réel, prix en francs congolais.',
                'price' => (1500 + ($index * 25)) * 100,
                'currency' => 'CDF',
                'status' => ProductStatus::Published,
                'condition' => 'new',
                'published_at' => now()->subDays($index % 30),
                'is_dropship' => $index % 17 === 0,
                'supplier_name' => $index % 17 === 0 ? 'Fournisseur démo' : null,
            ]);

            if ($product->inventories()->doesntExist()) {
                Inventory::query()->create([
                    'product_id' => $product->id,
                    'quantity' => 8 + ($index % 20),
                    'reserved' => 0,
                ]);
            }

            if ($index % 11 === 0 && $product->variants()->doesntExist()) {
                $variant = $product->variants()->create([
                    'sku' => 'DEMO-'.$index.'-S',
                    'name' => 'Petit',
                    'attributes' => ['size' => 'S'],
                    'price' => (int) $product->price,
                    'status' => 'active',
                ]);
                Inventory::query()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 6,
                    'reserved' => 0,
                ]);
            }
        }

        $patrick = Vendor::query()->whereHas('user', fn ($query) => $query->where('email', 'vendeur@twende.market'))->first();

        if ($patrick) {
            VendorCertification::query()->updateOrCreate(
                ['vendor_id' => $patrick->id, 'status' => 'approved'],
                ['note' => 'Pièces vérifiées pour la démonstration.', 'reviewed_at' => now()],
            );
            AdCampaign::query()->updateOrCreate(
                ['vendor_id' => $patrick->id, 'title' => 'Kinois Tech'],
                ['body' => 'Téléphones et accessoires disponibles à Gombe.', 'status' => 'approved', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDays(20)],
            );
        }

        $client = User::query()->where('email', 'client@twende.market')->first();
        $product = Product::query()->published()->whereDoesntHave('variants')->first();
        $zone = DeliveryZone::query()->where('name', 'Livraison Kinshasa')->first();

        if ($client && $product && $zone && $client->orders()->doesntExist()) {
            $carts = app(CartService::class);
            $checkout = app(CheckoutService::class);
            auth()->login($client);
            $carts->add($product, null, 1, $client);
            $paid = $checkout->place($client, [
                'phone' => $client->phone ?: '+243900000002',
                'city' => 'Kinshasa',
                'address' => 'Avenue de la Libération',
                'delivery_zone_id' => $zone->id,
                'payment_method' => 'sandbox',
            ]);
            Review::query()->create([
                'user_id' => $client->id,
                'product_id' => $product->id,
                'order_id' => $paid->id,
                'rating' => 5,
                'body' => 'Livraison claire et produit conforme à la fiche.',
            ]);

            $second = Product::query()->published()->whereDoesntHave('variants')->whereKeyNot($product->id)->first();

            if ($second) {
                $carts->add($second, null, 1, $client);
                $checkout->place($client, [
                    'phone' => $client->phone ?: '+243900000002',
                    'city' => 'Kinshasa',
                    'address' => 'Boulevard du 30 Juin',
                    'delivery_zone_id' => $zone->id,
                    'payment_method' => 'cod',
                ]);
            }

            auth()->logout();
        }

        if (User::role('delivery_agent')->count() < 3) {
            foreach ([5, 6] as $index) {
                $courier = User::query()->updateOrCreate(
                    ['email' => 'livreur'.$index.'@twende.market'],
                    [
                        'name' => 'Livreur '.$index,
                        'phone' => '+24384000000'.$index,
                        'password' => DatabaseSeeder::DEMO_PASSWORD,
                        'locale' => 'fr',
                        'currency' => 'CDF',
                        'email_verified_at' => now(),
                    ],
                );
                $courier->syncRoles([UserRole::DeliveryAgent->value]);
            }
        }
    }
}
