<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Dispute;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(Request $request): View
    {
        $user = $request->user();

        return view('pages.dashboard.home', [
            'user' => $user,
            'orderCount' => $user->orders()->count(),
            'favoriteCount' => $user->favorites()->count(),
            'addressCount' => $user->addresses()->count(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function vendor(Request $request): View
    {
        $vendor = $request->user()->vendorProfile;
        $shopIds = $vendor ? $vendor->shops()->pluck('id') : collect();
        $orders = Order::query()->whereHas('items', fn ($query) => $query->whereIn('shop_id', $shopIds));
        $orderCount = (clone $orders)->count();
        $revenue = $shopIds->isEmpty()
            ? collect()
            : OrderItem::query()
                ->whereIn('order_items.shop_id', $shopIds)
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->groupBy('orders.currency')
                ->selectRaw('orders.currency as currency, sum(order_items.line_total) as gross')
                ->get();
        $recent = (clone $orders)->latest()->limit(5)->get(['id', 'number', 'total', 'currency', 'status', 'created_at']);
        $popular = Product::query()
            ->whereIn('shop_id', $shopIds)
            ->withSum('orderItems as units_sold', 'quantity')
            ->orderByRaw('coalesce(units_sold, 0) desc')
            ->limit(4)
            ->get()
            ->filter(fn (Product $product) => (int) $product->units_sold > 0)
            ->values();

        return view('pages.dashboard.vendor', [
            'user' => $request->user(),
            'orderCount' => $orderCount,
            'productCount' => Product::query()->whereIn('shop_id', $shopIds)->count(),
            'clientCount' => $shopIds->isEmpty() ? 0 : (int) Order::query()->whereHas('items', fn ($query) => $query->whereIn('shop_id', $shopIds))->distinct()->count('user_id'),
            'lowStock' => $shopIds->isEmpty() ? 0 : Inventory::query()->whereHas('product', fn ($query) => $query->whereIn('shop_id', $shopIds))->whereRaw('(quantity - reserved) <= ?', [(int) config('twende.nearby.low_stock', 3)])->count(),
            'revenue' => $revenue,
            'recent' => $recent,
            'popular' => $popular,
            'series' => $orderCount > 0 ? $this->monthlyOrders($orders) : collect(),
        ]);
    }

    public function delivery(Request $request): View
    {
        return view('pages.dashboard.delivery', [
            'user' => $request->user(),
        ]);
    }

    public function admin(Request $request): View
    {
        return view('pages.dashboard.admin', [
            'user' => $request->user(),
            'counts' => [
                'users' => User::query()->count(),
                'clients' => User::role('client')->count(),
                'vendors' => Vendor::query()->count(),
                'products' => Product::query()->count(),
                'orders' => Order::query()->count(),
                'payments' => Payment::query()->count(),
                'deliveries' => Delivery::query()->count(),
                'reviews' => Review::query()->count(),
                'disputes' => Dispute::query()->count(),
            ],
        ]);
    }

    public function profile(Request $request): View
    {
        $this->authorize('update', $request->user());

        return view('pages.dashboard.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * @param  Builder<Order>  $orders
     * @return Collection<int, array{label: string, count: int}>
     */
    private function monthlyOrders($orders): Collection
    {
        $start = now()->startOfMonth()->subMonths(5);
        $rows = (clone $orders)->where('created_at', '>=', $start)->get(['created_at']);

        return collect(range(0, 5))->map(function (int $offset) use ($start, $rows): array {
            $month = $start->copy()->addMonths($offset);

            return [
                'label' => $month->locale(app()->getLocale())->isoFormat('MMM'),
                'count' => $rows->filter(fn (Order $order) => $order->created_at->format('Y-m') === $month->format('Y-m'))->count(),
            ];
        });
    }
}
