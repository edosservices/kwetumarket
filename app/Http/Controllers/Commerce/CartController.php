<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Commerce\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(CartService $carts): View
    {
        $quote = $carts->quote();

        return view('pages.commerce.cart', [
            'quote' => $quote,
            'cart' => $carts->current(false),
        ]);
    }

    public function store(Request $request, CartService $carts): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $product = Product::query()->with('shop')->findOrFail($data['product_id']);
        $carts->add($product, $data['product_variant_id'] ?? null, (int) ($data['quantity'] ?? 1));

        return redirect()
            ->route('cart.show')
            ->with('success', __('commerce.added'));
    }

    public function update(Request $request, CartItem $item, CartService $carts): RedirectResponse
    {
        $this->owns($item);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        $carts->setQuantity($item, (int) $data['quantity']);

        return back()->with('success', __('commerce.updated'));
    }

    public function destroy(CartItem $item, CartService $carts): RedirectResponse
    {
        $this->owns($item);
        $carts->remove($item);

        return back()->with('success', __('commerce.removed'));
    }

    public function clear(CartService $carts): RedirectResponse
    {
        $carts->clear();

        return back()->with('success', __('commerce.cleared'));
    }

    public function coupon(Request $request, CartService $carts): RedirectResponse
    {
        $data = $request->validate([
            'coupon' => ['required', 'string', 'max:32'],
        ]);
        $carts->applyCoupon($data['coupon']);

        return back()->with('success', __('commerce.coupon_applied'));
    }

    private function owns(CartItem $item): void
    {
        $cart = app(CartService::class)->current(false);
        abort_unless($cart && (int) $item->cart_id === (int) $cart->id, 404);
    }
}
