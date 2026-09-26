<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\CheckoutRequest;
use App\Models\DeliveryZone;
use App\Services\Commerce\CartService;
use App\Services\Commerce\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(CartService $carts): View
    {
        $zones = DeliveryZone::query()->where('is_active', true)->orderBy('fee')->get();
        $zoneId = request()->integer('zone') ?: $zones->first()?->id;
        $quote = $carts->quote($zoneId ? (int) $zoneId : null);

        return view('pages.commerce.checkout', [
            'quote' => $quote,
            'zones' => $zones,
            'selectedZone' => $zoneId,
            'addresses' => auth()->user()->addresses()->latest()->get(),
        ]);
    }

    public function store(CheckoutRequest $request, CartService $carts, CheckoutService $checkout): RedirectResponse
    {
        if ($request->filled('coupon')) {
            $carts->applyCoupon((string) $request->input('coupon'));
        }

        $order = $checkout->place($request->user(), $request->validated());

        return redirect()
            ->route('orders.show', $order)
            ->with('success', __('commerce.order_created', ['number' => $order->number]));
    }
}
