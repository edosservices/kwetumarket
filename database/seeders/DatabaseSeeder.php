<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public const DEMO_PASSWORD = 'Twende-Demo-2026';

    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        if (app()->environment('production')) {
            return;
        }

        $this->demoUser('Admin Twende', 'admin@twende.market', '+243900000001', UserRole::SuperAdmin);
        $customer = $this->demoUser('Grâce Ilunga', 'client@twende.market', '+243900000002', UserRole::Customer);
        $seller = $this->demoUser('Patrick Mbuyi', 'vendeur@twende.market', '+243900000003', UserRole::Vendor);
        $courier = $this->demoUser('Sarah Ngalula', 'livreur@twende.market', '+243900000004', UserRole::DeliveryAgent);
        $this->demoUser('Chantal Kabila', 'catalogue@twende.market', '+243900000005', UserRole::CatalogManager);
        $this->demoUser('David Ilunga', 'dispatch@twende.market', '+243900000006', UserRole::DeliveryManager);
        $this->demoMarket($seller, $customer, $courier);
    }

    private function demoUser(string $name, string $email, string $phone, UserRole $role): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => self::DEMO_PASSWORD,
                'locale' => 'fr',
                'currency' => 'CDF',
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$role->value]);

        return $user;
    }

    private function demoMarket(User $seller, User $customer, User $courier): void
    {
        $vendor = Vendor::query()->updateOrCreate(
            ['user_id' => $seller->id],
            ['name' => 'Boutique Mbuyi', 'status' => 'active'],
        );
        $seller->forceFill(['vendor_id' => $vendor->id])->save();

        $shop = Shop::query()->updateOrCreate(
            ['slug' => 'boutique-mbuyi'],
            ['vendor_id' => $vendor->id, 'name' => 'Boutique Mbuyi', 'status' => 'active'],
        );

        $product = Product::query()->updateOrCreate(
            ['sku' => 'TWD-DEMO-RIZ'],
            [
                'vendor_id' => $vendor->id,
                'shop_id' => $shop->id,
                'name' => 'Riz parfumé 25 kg',
                'slug' => 'riz-parfume-25-kg',
                'status' => 'published',
                'price_minor' => 4500000,
                'currency' => 'CDF',
                'stock' => 12,
            ],
        );

        $order = Order::query()->updateOrCreate(
            ['number' => 'TWD-2026-000001'],
            [
                'user_id' => $customer->id,
                'status' => 'paid',
                'total_minor' => 4500000,
                'currency' => 'CDF',
            ],
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $product->id],
            [
                'vendor_id' => $vendor->id,
                'shop_id' => $shop->id,
                'quantity' => 1,
                'unit_price_minor' => 4500000,
                'line_total_minor' => 4500000,
            ],
        );

        Payment::query()->updateOrCreate(
            ['order_id' => $order->id],
            ['amount_minor' => 4500000, 'currency' => 'CDF', 'status' => 'successful'],
        );

        Delivery::query()->updateOrCreate(
            ['order_id' => $order->id],
            ['agent_id' => $courier->id, 'status' => 'accepted', 'fee_minor' => 150000, 'currency' => 'CDF'],
        );
    }
}
