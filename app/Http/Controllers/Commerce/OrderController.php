<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\Catalog\MediaStorage;
use App\Services\Commerce\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $order->load(['items.shop', 'delivery.agent.courierProfile', 'events.user', 'payment', 'zone', 'disputes', 'refunds', 'returns']);

        return view('pages.commerce.orders.show', ['order' => $order]);
    }

    public function receipt(Request $request, Order $order): Response
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id || $request->user()->can('orders.manage'), 403);
        abort_unless($order->payment_status === 'paid', 404);
        $order->load(['items', 'payment']);
        $html = view('pages.commerce.orders.receipt', ['order' => $order])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$order->number.'.html"',
        ]);
    }

    public function cancel(Request $request, Order $order, CheckoutService $checkout): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $checkout->cancel($order, $request->user());

        return back()->with('success', __('commerce.cancelled'));
    }

    public function review(Request $request, Order $order, MediaStorage $media): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'photo' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp'],
        ]);
        $item = $order->items()->where('product_id', $data['product_id'])->first();
        abort_unless($item && $order->status === 'delivered', 422);
        $product = Product::query()->findOrFail($data['product_id']);
        $review = $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['order_id' => $order->id, 'rating' => $data['rating'], 'body' => $data['body']],
        );

        if ($request->hasFile('photo')) {
            $review->photos()->create([
                'disk' => $media->disk(),
                'path' => $media->store($request->file('photo'), 'reviews/'.$review->id),
            ]);
        }

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

        if (! in_array($order->status, ['delivered', 'shipped', 'confirmed', 'preparing', 'ready', 'returned'], true) || $order->status === 'refunded') {
            return back()->with('error', __('commerce.refund_closed'));
        }

        $payment = $order->payments()->where('status', 'paid')->first();

        if (! $payment || $order->refunds()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', $payment ? __('commerce.refund_closed') : __('operations.refund_needs_payment'));
        }

        $order->refunds()->create([
            'user_id' => $request->user()->id,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'status' => 'pending',
            'reason' => $data['reason'],
        ]);

        return back()->with('success', __('commerce.refund_sent'));
    }

    public function retry(Request $request, Order $order, CheckoutService $checkout): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $data = $request->validate([
            'payment_method' => ['required', 'string'],
        ]);
        $paid = $checkout->retry($order, $request->user(), $data['payment_method']);

        return back()->with($paid ? 'success' : 'error', $paid ? __('experience.paid_confirmed') : __('experience.payment_pending'));
    }
}
