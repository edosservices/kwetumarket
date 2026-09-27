<?php

namespace App\Services\Commerce;

use App\Enums\VariantStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\PlatformSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Catalog\DropshipService;
use App\Services\Catalog\OfferPricing;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function count(): int
    {
        $cart = $this->current(false);

        if (! $cart) {
            return 0;
        }

        return (int) $cart->items()->sum('quantity');
    }

    public function current(bool $create = true): ?Cart
    {
        $userId = auth()->id();
        $cartId = session('cart_id');

        if ($cartId) {
            $cart = Cart::query()->find($cartId);

            if ($cart && ($cart->user_id === null || (int) $cart->user_id === (int) $userId)) {
                if ($userId && $cart->user_id === null && ! Cart::query()->where('user_id', $userId)->whereKeyNot($cart->id)->exists()) {
                    $cart->update(['user_id' => $userId]);
                }

                return $cart;
            }
        }

        if ($userId) {
            $cart = Cart::query()->where('user_id', $userId)->first();

            if ($cart) {
                session(['cart_id' => $cart->id]);

                return $cart;
            }
        }

        if (! $create) {
            return null;
        }

        $cart = Cart::query()->create([
            'user_id' => $userId,
            'session_id' => session()->getId(),
            'currency' => (string) config('twende.currency.default', 'CDF'),
        ]);
        session(['cart_id' => $cart->id]);

        return $cart;
    }

    public function forUser(User $user): Cart
    {
        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['currency' => $user->currency ?: config('twende.currency.default', 'CDF')],
        );
        session(['cart_id' => $cart->id]);

        return $cart;
    }

    public function adopt(User $user): void
    {
        $sessionCart = session('cart_id')
            ? Cart::query()->with('items')->find(session('cart_id'))
            : null;
        $userCart = Cart::query()->where('user_id', $user->id)->first();

        if ($sessionCart && (int) $sessionCart->user_id === (int) $user->id) {
            return;
        }

        if (! $sessionCart || $sessionCart->user_id !== null) {
            if ($userCart) {
                session(['cart_id' => $userCart->id]);
            }

            return;
        }

        if (! $userCart) {
            $sessionCart->update([
                'user_id' => $user->id,
                'session_id' => session()->getId(),
            ]);
            session(['cart_id' => $sessionCart->id]);

            return;
        }

        foreach ($sessionCart->items as $item) {
            $this->mergeItem($userCart, $item);
        }

        $sessionCart->items()->delete();
        $sessionCart->delete();
        session(['cart_id' => $userCart->id]);
    }

    public function add(Product $product, ?int $variantId, int $quantity, ?User $owner = null): CartItem
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => __('commerce.qty_min')]);
        }

        if (! $product->isPubliclyVisible()) {
            throw ValidationException::withMessages(['product_id' => __('commerce.unavailable')]);
        }

        $variant = $this->resolveVariant($product, $variantId);
        $available = $this->available((int) $product->id, (int) ($variant?->id ?? 0));

        if ($available < 1) {
            throw ValidationException::withMessages(['quantity' => __('commerce.stock_short')]);
        }

        $cart = $owner ? $this->forUser($owner) : $this->current();
        $this->assertCurrency($cart, $product);

        $key = (int) ($variant?->id ?? 0);
        $item = $cart->items()->firstOrNew([
            'product_id' => $product->id,
            'variant_key' => $key,
        ]);
        $next = min($available, ($item->exists ? (int) $item->quantity : 0) + $quantity);

        if ($next < 1) {
            throw ValidationException::withMessages(['quantity' => __('commerce.stock_short')]);
        }

        $item->product_variant_id = $variant?->id;
        $item->quantity = $next;
        $item->save();

        return $item;
    }

    public function setQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => __('commerce.qty_min')]);
        }

        $available = $this->available((int) $item->product_id, (int) $item->variant_key);

        if ($quantity > $available) {
            throw ValidationException::withMessages(['quantity' => __('commerce.stock_short')]);
        }

        $item->update(['quantity' => $quantity]);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(?Cart $cart = null): void
    {
        $cart ??= $this->current(false);

        if ($cart) {
            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);
        }
    }

    public function applyCoupon(string $code): void
    {
        $cart = $this->current();
        $quote = $this->quote(null, null, false);
        $coupon = $this->coupon($code, $quote->subtotal, true);
        $cart->update(['coupon_code' => $coupon->code]);
    }

    public function quote(?int $zoneId = null, ?string $couponCode = null, bool $strictCoupon = false): CartQuote
    {
        $cart = $this->current(false);
        $currency = (string) config('twende.currency.default', 'CDF');
        $lines = [];
        $subtotal = 0;
        $commission = 0;
        $blocked = false;
        $percent = max(0, (int) config('twende.commerce.commission_percent'));

        if ($cart) {
            $cart->load([
                'items.product.activePromotion',
                'items.product.shop',
                'items.variant',
            ]);
            $currency = $cart->currency ?: $currency;

            foreach ($cart->items as $item) {
                $product = $item->product;

                if (! $product) {
                    $blocked = true;

                    continue;
                }

                $currency = $product->currency ?: $currency;
                $unit = $this->unitPrice($product, $item->variant);
                $lineTotal = $unit * (int) $item->quantity;
                $available = $this->available((int) $product->id, (int) $item->variant_key);
                $short = ! $product->isPubliclyVisible() || $available < (int) $item->quantity;
                $lineCommission = intdiv($lineTotal * $percent, 100);
                $blocked = $blocked || $short;
                $subtotal += $lineTotal;
                $commission += $lineCommission;
                $lines[] = new CartLine($item, $unit, $lineTotal, $available, $short, $lineCommission);
            }
        }

        $code = $couponCode ?? $cart?->coupon_code;
        $discount = 0;
        $warning = null;
        $applied = null;

        if (is_string($code) && trim($code) !== '') {
            $applied = $this->coupon($code, $subtotal, $strictCoupon);
            $discount = $applied?->discountFor($subtotal) ?? 0;

            if (! $applied) {
                $warning = __('commerce.coupon_invalid');
            }
        }

        $fee = 0;

        if ($zoneId) {
            $zone = DeliveryZone::query()->where('is_active', true)->find($zoneId);
            $fee = (int) ($zone->fee ?? 0);
            $threshold = PlatformSetting::integer('free_shipping_minor', (int) config('twende.commerce.free_shipping_minor'));

            if ($threshold > 0 && $subtotal >= $threshold) {
                $fee = 0;
            }
        }

        $taxable = max(0, $subtotal - $discount);
        $taxRate = max(0, (int) config('twende.commerce.tax_percent'));
        $tax = intdiv($taxable * $taxRate, 100);

        return new CartQuote(
            lines: $lines,
            subtotal: $subtotal,
            discount: $discount,
            deliveryFee: $fee,
            tax: $tax,
            total: $taxable + $fee + $tax,
            commission: $commission,
            couponCode: $applied?->code,
            blocked: $blocked,
            currency: $currency,
            couponWarning: $warning,
        );
    }

    public function available(int $productId, int $variantKey): int
    {
        $inventory = Inventory::query()
            ->where('product_id', $productId)
            ->when(
                $variantKey > 0,
                fn ($query) => $query->where('product_variant_id', $variantKey),
                fn ($query) => $query->whereNull('product_variant_id'),
            )
            ->first();

        $local = 0;
        $product = null;

        if ($inventory) {
            $local = $inventory->available();
        } elseif ($variantKey > 0) {
            $local = max(0, (int) ProductVariant::query()->whereKey($variantKey)->value('stock'));
        } else {
            $product = Product::query()
                ->withSum('inventories as stock_on_hand', 'quantity')
                ->withSum('inventories as stock_reserved', 'reserved')
                ->find($productId);
            $local = $product?->availableQuantity() ?? 0;
        }

        $product = $product ?? Product::query()->find($productId);

        if (! $product) {
            return $local;
        }

        return app(DropshipService::class)->saleAllowed($product, $local);
    }

    public function unitPrice(Product $product, ?ProductVariant $variant): int
    {
        if ($variant) {
            return $variant->salePrice();
        }

        return OfferPricing::forProduct($product)->finalPrice;
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        $hasVariants = $product->variants()->where('status', VariantStatus::Active)->exists();

        if ($hasVariants && ! $variantId) {
            throw ValidationException::withMessages(['product_variant_id' => __('commerce.variant_required')]);
        }

        if (! $variantId) {
            return null;
        }

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->whereKey($variantId)
            ->first();

        if (! $variant || $variant->status !== VariantStatus::Active) {
            throw ValidationException::withMessages(['product_variant_id' => __('commerce.variant_invalid')]);
        }

        return $variant;
    }

    private function assertCurrency(Cart $cart, Product $product): void
    {
        $existing = $cart->items()->with('product:id,currency')->first();

        if ($existing && $existing->product && $existing->product->currency !== $product->currency) {
            throw ValidationException::withMessages(['product_id' => __('commerce.currency_mismatch')]);
        }

        if ($cart->items()->doesntExist()) {
            $cart->update(['currency' => $product->currency]);
        }
    }

    private function mergeItem(Cart $cart, CartItem $item): void
    {
        $existing = $cart->items()
            ->where('product_id', $item->product_id)
            ->where('variant_key', $item->variant_key)
            ->first();
        $available = $this->available((int) $item->product_id, (int) $item->variant_key);
        $next = min($available, ($existing->quantity ?? 0) + (int) $item->quantity);

        if ($next < 1) {
            return;
        }

        if ($existing) {
            $existing->update(['quantity' => $next]);

            return;
        }

        $cart->items()->create([
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'variant_key' => $item->variant_key,
            'quantity' => $next,
        ]);
    }

    private function coupon(string $code, int $subtotal, bool $strict): ?Coupon
    {
        $coupon = Coupon::query()->whereRaw('upper(code) = ?', [strtoupper(trim($code))])->first();

        if (! $coupon || ! $coupon->accepts($subtotal)) {
            if ($strict) {
                throw ValidationException::withMessages(['coupon' => __('commerce.coupon_invalid')]);
            }

            return null;
        }

        return $coupon;
    }
}
