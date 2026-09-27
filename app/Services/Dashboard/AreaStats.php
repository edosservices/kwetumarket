<?php

namespace App\Services\Dashboard;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Commission;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Shop;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wishlist;
use App\Support\Money;

class AreaStats
{
    /**
     * @return array<string, int|string>
     */
    public function admin(): array
    {
        $revenue = (int) Payment::query()->where('status', 'successful')->sum('amount_minor');
        $commissions = (int) Commission::query()->sum('amount_minor');

        return [
            'users' => User::query()->count(),
            'customers' => User::role(UserRole::Customer->value)->count(),
            'vendors' => Vendor::query()->count(),
            'shops' => Shop::query()->count(),
            'agents' => User::role(UserRole::DeliveryAgent->value)->count(),
            'products' => Product::query()->count(),
            'orders' => Order::query()->count(),
            'revenue' => $revenue,
            'revenue_label' => Money::format($revenue),
            'commissions' => $commissions,
            'commissions_label' => Money::format($commissions),
            'payments' => Payment::query()->count(),
            'refunds' => Refund::query()->count(),
            'pending_vendors' => Vendor::query()->where('status', 'pending')->count(),
            'pending_shops' => Shop::query()->where('status', 'pending')->count(),
            'pending_products' => Product::query()->where('status', 'pending')->count(),
            'pending_tickets' => SupportTicket::query()->where('status', 'open')->count(),
        ];
    }

    /**
     * @return array<string, int|string>
     */
    public function vendor(User $user): array
    {
        $vendorId = $user->vendorId();
        $products = Product::query()->where('vendor_id', $vendorId);
        $items = OrderItem::query()->where('vendor_id', $vendorId);
        $revenue = (int) (clone $items)->sum('line_total_minor');

        return [
            'products' => (clone $products)->count(),
            'orders' => Order::query()->whereHas('items', fn ($query) => $query->where('vendor_id', $vendorId))->count(),
            'revenue' => $revenue,
            'revenue_label' => Money::format($revenue),
            'stock' => (int) (clone $products)->sum('stock'),
            'low_stock' => (clone $products)->where('stock', '>', 0)->where('stock', '<=', 3)->count(),
            'out_of_stock' => (clone $products)->where('stock', 0)->count(),
            'payouts' => (int) Payout::query()->where('vendor_id', $vendorId)->where('status', 'paid')->sum('amount_minor'),
            'customers' => (int) Order::query()
                ->whereHas('items', fn ($query) => $query->where('vendor_id', $vendorId))
                ->distinct('user_id')
                ->count('user_id'),
        ];
    }

    /**
     * @return array<string, int|string>
     */
    public function delivery(User $user): array
    {
        $mine = Delivery::query()->where('agent_id', $user->id);
        $scope = $user->can('delivery.manage') ? Delivery::query() : $mine;
        $earnings = (int) (clone $mine)->where('status', 'delivered')->sum('fee_minor');

        return [
            'assigned' => (clone $mine)->count(),
            'active' => (clone $mine)->whereIn('status', ['accepted', 'picked_up', 'in_transit'])->count(),
            'delivered' => (clone $mine)->where('status', 'delivered')->count(),
            'failed' => (clone $mine)->where('status', 'failed')->count(),
            'earnings' => $earnings,
            'earnings_label' => Money::format($earnings),
            'fleet' => $user->can('delivery.manage') ? User::role(UserRole::DeliveryAgent->value)->count() : 0,
            'all_deliveries' => (clone $scope)->count(),
            'problems' => (clone $scope)->where('status', 'failed')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function customer(User $user): array
    {
        return [
            'orders' => $user->hasMany(Order::class)->count(),
            'addresses' => $user->hasMany(Address::class)->count(),
            'wishlist' => $user->hasMany(Wishlist::class)->count(),
            'reviews' => $user->hasMany(Review::class)->count(),
            'tickets' => SupportTicket::query()->where('user_id', $user->id)->count(),
            'notifications' => $user->notifications()->count(),
        ];
    }
}
