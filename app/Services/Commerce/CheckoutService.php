<?php

namespace App\Services\Commerce;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Delivery;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\CommerceNotice;
use App\Services\Catalog\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private CartService $carts,
        private StockService $stock,
        private WalletService $wallets,
        private ReferralService $referrals,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function place(User $user, array $input): Order
    {
        return DB::transaction(function () use ($user, $input): Order {
            $cart = $this->carts->current(false);

            if (! $cart) {
                throw ValidationException::withMessages(['cart' => __('commerce.cart_empty')]);
            }

            $cart = \App\Models\Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            session(['cart_id' => $cart->id]);

            $zone = DeliveryZone::query()->where('is_active', true)->whereKey($input['delivery_zone_id'] ?? 0)->first();

            if (! $zone) {
                throw ValidationException::withMessages(['delivery_zone_id' => __('commerce.zone_invalid')]);
            }

            $method = (string) ($input['payment_method'] ?? '');

            if (! in_array($method, config('twende.commerce.methods'), true)) {
                throw ValidationException::withMessages(['payment_method' => __('commerce.payment_invalid')]);
            }

            $quote = $this->carts->quote($zone->id, $cart->coupon_code, true);

            if ($quote->lines === []) {
                throw ValidationException::withMessages(['cart' => __('commerce.cart_empty')]);
            }

            if ($quote->blocked) {
                throw ValidationException::withMessages(['stock' => __('commerce.stock_short')]);
            }

            $address = $this->address($user, $input);
            $order = Order::query()->create([
                'number' => 'TMP-'.str()->uuid(),
                'user_id' => $user->id,
                'status' => 'confirmed',
                'currency' => $quote->currency,
                'subtotal' => $quote->subtotal,
                'discount' => $quote->discount,
                'delivery_fee' => $quote->deliveryFee,
                'tax' => $quote->tax,
                'commission_total' => $quote->commission,
                'total' => $quote->total,
                'coupon_code' => $quote->couponCode,
                'payment_method' => $method,
                'payment_status' => $method === 'sandbox' ? 'paid' : 'unpaid',
                'delivery_zone_id' => $zone->id,
                'phone' => $address['phone'],
                'country' => $address['country'],
                'province' => $address['province'],
                'city' => $address['city'],
                'commune' => $address['commune'],
                'quarter' => $address['quarter'],
                'address' => $address['address'],
                'notes' => $input['notes'] ?? null,
                'wallet_credited' => false,
            ]);
            $order->update([
                'number' => 'TM-'.$order->created_at->format('Ymd').'-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($quote->lines as $line) {
                $product = $line->item->product;
                $variant = $line->item->variant;

                try {
                    $this->stock->record($product, $variant, StockMovementType::Sale, $line->item->quantity, $user, $order->number, 'Commande');
                } catch (InsufficientStockException) {
                    throw ValidationException::withMessages(['stock' => __('commerce.stock_short')]);
                }

                $order->items()->create([
                    'shop_id' => $product->shop_id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'name' => $product->name,
                    'variant_name' => $variant?->name,
                    'quantity' => $line->item->quantity,
                    'unit_price' => $line->unitPrice,
                    'line_total' => $line->lineTotal,
                    'commission' => $line->commission,
                ]);
            }

            if ($quote->couponCode) {
                $coupon = Coupon::query()->where('code', $quote->couponCode)->lockForUpdate()->first();

                if (! $coupon || ! $coupon->accepts($quote->subtotal)) {
                    throw ValidationException::withMessages(['coupon' => __('commerce.coupon_invalid')]);
                }

                $coupon->increment('used_count');
            }

            Delivery::query()->create([
                'order_id' => $order->id,
                'status' => 'pending',
            ]);

            Payment::query()->create([
                'order_id' => $order->id,
                'provider' => $method,
                'status' => $method === 'sandbox' ? 'paid' : 'unpaid',
                'amount' => $quote->total,
                'reference' => $method === 'sandbox' ? 'SANDBOX-'.$order->number : null,
                'meta' => [
                    'live_capture' => false,
                    'driver' => config('twende.commerce.payment_driver'),
                ],
            ]);

            $order->events()->create([
                'user_id' => $user->id,
                'status' => 'confirmed',
                'note' => $method === 'sandbox' ? __('commerce.paid_sandbox') : __('commerce.placed_cod'),
            ]);

            if ($method === 'sandbox') {
                $this->wallets->creditVendors($order);
                $order->update(['wallet_credited' => true]);
                $this->referrals->rewardFirstPaidOrder($user, $order->fresh());
            }

            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);

            if (! empty($input['save_address'])) {
                $this->rememberAddress($user, $address);
            }

            $user->notify(new CommerceNotice(
                __('commerce.notice_order_title'),
                __('commerce.notice_order_body', ['number' => $order->number]),
                route('orders.show', $order),
            ));

            return $order->fresh(['items', 'delivery', 'payment']);
        });
    }

    public function cancel(Order $order, User $actor): void
    {
        DB::transaction(function () use ($order, $actor): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $delivery = $order->delivery()->lockForUpdate()->first();

            if (! $delivery || ! in_array($delivery->status, ['pending', 'assigned', 'accepted'], true)) {
                throw ValidationException::withMessages(['order' => __('commerce.cancel_closed')]);
            }

            if (! in_array($order->status, ['confirmed', 'preparing'], true)) {
                throw ValidationException::withMessages(['order' => __('commerce.cancel_closed')]);
            }

            $order->load('items.product', 'items.variant');
            $this->restoreStock($order, $actor, StockMovementType::Cancellation);

            if ($order->wallet_credited) {
                $this->wallets->reverseOrder($order);
            }

            $order->update([
                'status' => 'cancelled',
                'wallet_credited' => false,
                'payment_status' => $order->payment_status === 'paid' ? 'refunded' : $order->payment_status,
            ]);
            $delivery->update(['status' => 'cancelled']);
            $order->payment()?->where('status', 'paid')->update(['status' => 'refunded']);
            $order->events()->create([
                'user_id' => $actor->id,
                'status' => 'cancelled',
                'note' => __('commerce.cancelled'),
            ]);
            $order->user->notify(new CommerceNotice(
                __('commerce.notice_cancel_title'),
                __('commerce.notice_cancel_body', ['number' => $order->number]),
                route('orders.show', $order),
            ));
        });
    }

    public function approveRefund(Order $order, User $actor, string $decision): void
    {
        DB::transaction(function () use ($order, $actor, $decision): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $refund = $order->refunds()->where('status', 'pending')->lockForUpdate()->first();

            if (! $refund || $order->status === 'refunded') {
                throw ValidationException::withMessages(['refund' => __('commerce.refund_closed')]);
            }

            $order->load('items.product', 'items.variant', 'delivery');
            $this->restoreStock($order, $actor, StockMovementType::Return);

            if ($order->wallet_credited) {
                $this->wallets->reverseOrder($order);
            }

            $refund->update([
                'status' => 'approved',
                'decision' => $decision,
                'reviewed_by' => $actor->id,
            ]);
            $order->update([
                'status' => 'refunded',
                'payment_status' => 'refunded',
                'wallet_credited' => false,
            ]);
            $order->payment()?->update(['status' => 'refunded']);
            $order->events()->create([
                'user_id' => $actor->id,
                'status' => 'refunded',
                'note' => $decision,
            ]);
        });
    }

    public function restoreStock(Order $order, User $actor, StockMovementType $type): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product) {
                Log::warning('Produit manquant pendant le retour de stock.', ['order_item_id' => $item->id]);

                continue;
            }

            if ($item->product_variant_id && $item->variant === null) {
                Log::warning('Variante manquante pendant le retour de stock.', ['order_item_id' => $item->id]);
            }

            $this->stock->record($product, $item->variant, $type, (int) $item->quantity, $actor, $order->number, $type->value);
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{phone: string, country: ?string, province: ?string, city: string, commune: ?string, quarter: ?string, address: string}
     */
    private function address(User $user, array $input): array
    {
        if (! empty($input['address_id'])) {
            $saved = Address::query()->where('user_id', $user->id)->whereKey($input['address_id'])->first();

            if ($saved) {
                return [
                    'phone' => $saved->phone,
                    'country' => $saved->country,
                    'province' => $saved->province,
                    'city' => $saved->city,
                    'commune' => $saved->commune,
                    'quarter' => $saved->quarter,
                    'address' => $saved->address,
                ];
            }
        }

        return [
            'phone' => (string) $input['phone'],
            'country' => $input['country'] ?? null,
            'province' => $input['province'] ?? null,
            'city' => (string) $input['city'],
            'commune' => $input['commune'] ?? null,
            'quarter' => $input['quarter'] ?? null,
            'address' => (string) $input['address'],
        ];
    }

    /**
     * @param  array{phone: string, country: ?string, province: ?string, city: string, commune: ?string, quarter: ?string, address: string}  $address
     */
    private function rememberAddress(User $user, array $address): void
    {
        $exists = Address::query()
            ->where('user_id', $user->id)
            ->where('phone', $address['phone'])
            ->where('address', $address['address'])
            ->exists();

        if ($exists) {
            return;
        }

        if (! Address::query()->where('user_id', $user->id)->exists()) {
            $address['is_default'] = true;
        }

        $user->addresses()->create([
            'label' => 'Livraison',
            ...$address,
        ]);
    }
}
