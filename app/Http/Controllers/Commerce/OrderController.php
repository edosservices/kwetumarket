<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\Commerce\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('orders.view-own'), 403);

        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with('delivery')
            ->latest()
            ->paginate(12);

        return view('pages.commerce.orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id || $request->user()->can('orders.manage'), 403);
        $order->load(['items.shop', 'delivery.agent', 'events.user', 'payment', 'zone', 'disputes', 'refunds']);

        return view('pages.commerce.orders.show', ['order' => $order]);
    }

    public function cancel(Request $request, Order $order, CheckoutService $checkout): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $checkout->cancel($order, $request->user());

        return back()->with('success', __('commerce.cancelled'));
    }

    public function review(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ]);
        $item = $order->items()->where('product_id', $data['product_id'])->first();
        abort_unless($item && $order->status !== 'cancelled', 422);
        $product = Product::query()->findOrFail($data['product_id']);
        $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['order_id' => $order->id, 'rating' => $data['rating'], 'body' => $data['body']],
        );

        return back()->with('success', __('commerce.review_saved'));
    }

    public function dispute(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);

        if ($order->disputes()->where('status', 'open')->exists()) {
            return back()->with('error', __('commerce.dispute_open'));
        }

        $order->disputes()->create([
            'user_id' => $request->user()->id,
            'status' => 'open',
            'reason' => $data['reason'],
        ]);

        return back()->with('success', __('commerce.dispute_sent'));
    }

    public function refund(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);

        if (! in_array($order->status, ['delivered', 'shipped', 'confirmed', 'preparing'], true) || $order->status === 'refunded') {
            return back()->with('error', __('commerce.refund_closed'));
        }

        if ($order->refunds()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', __('commerce.refund_closed'));
        }

        $order->refunds()->create([
            'user_id' => $request->user()->id,
            'amount' => $order->total,
            'status' => 'pending',
            'reason' => $data['reason'],
        ]);

        return back()->with('success', __('commerce.refund_sent'));
    }
}
