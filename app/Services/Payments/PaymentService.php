<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Commerce\CheckoutService;
use App\Services\Commerce\ReferralService;
use App\Services\Commerce\WalletService;
use App\Services\Payments\Gateways\CodGateway;
use App\Services\Payments\Gateways\HostedGateway;
use App\Services\Payments\Gateways\SandboxGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentService
{
    public function __construct(
        private WalletService $wallets,
        private ReferralService $referrals,
    ) {}

    public function settle(Order $order, string $method): bool
    {
        if (! app(PaymentCatalog::class)->allows($method)) {
            throw ValidationException::withMessages(['payment_method' => __('commerce.payment_invalid')]);
        }

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'provider' => $method,
            'status' => 'pending',
            'amount' => $order->total,
            'reference' => null,
            'meta' => ['live_capture' => false],
        ]);
        $payment->setRelation('order', $order);

        $result = $this->gateway($method)->authorize($payment);
        $payment->update([
            'status' => $result->status,
            'reference' => $result->reference,
            'meta' => $result->meta,
        ]);

        $order->update([
            'payment_status' => $result->status === 'paid' ? 'paid' : ($result->status === 'pending' ? 'pending' : 'unpaid'),
        ]);

        if ($result->status === 'paid') {
            $this->capture($order->fresh());
        }

        return $result->status === 'paid';
    }

    public function acceptWebhook(string $provider, string $body, ?string $signature): void
    {
        $secret = (string) config('twende.payments.webhook_secret');
        $expected = $secret === '' ? '' : hash_hmac('sha256', $body, $secret);

        if ($secret === '' || ! is_string($signature) || ! hash_equals($expected, $signature)) {
            throw new HttpException(401, 'Invalid payment signature.');
        }

        $payload = json_decode($body, true);

        if (! is_array($payload)) {
            throw ValidationException::withMessages(['payload' => __('experience.payment_payload')]);
        }

        $reference = (string) ($payload['reference'] ?? '');
        $status = (string) ($payload['status'] ?? '');
        $currency = (string) ($payload['currency'] ?? '');
        $amount = $payload['amount'] ?? null;

        if (is_int($amount)) {
            $minor = $amount;
        } elseif (is_string($amount) && ctype_digit($amount)) {
            $minor = (int) $amount;
        } else {
            $minor = null;
        }

        $normalized = match ($status) {
            'paid', 'payment.success', 'success' => 'paid',
            'failed', 'payment.failed' => 'failed',
            'cancelled', 'payment.cancelled' => 'cancelled',
            'refunded', 'payment.refunded' => 'refunded',
            'pending', 'payment.pending', 'payment.created', 'created' => 'pending',
            default => '',
        };

        if ($reference === '' || $minor === null || $normalized === '') {
            throw ValidationException::withMessages(['payload' => __('experience.payment_payload')]);
        }

        $eventId = (string) ($payload['idempotency'] ?? $payload['event_id'] ?? '');

        DB::transaction(function () use ($provider, $reference, $normalized, $currency, $minor, $eventId): void {
            $payment = Payment::query()->where('reference', $reference)->lockForUpdate()->first();

            if (! $payment || $payment->provider !== $provider) {
                throw ValidationException::withMessages(['reference' => __('experience.payment_unknown')]);
            }

            $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();

            if ($minor !== (int) $payment->amount || $minor !== (int) $order->total || $currency !== $order->currency) {
                throw ValidationException::withMessages(['amount' => __('experience.payment_amount')]);
            }

            $meta = $payment->meta ?? [];
            $seen = $meta['events'] ?? [];

            if ($eventId !== '' && in_array($eventId, $seen, true)) {
                return;
            }

            if ($eventId !== '') {
                $seen[] = $eventId;
                $meta['events'] = $seen;
            }

            if ($payment->status === 'paid' && $normalized === 'paid') {
                return;
            }

            if ($payment->status === 'refunded' || ($order->wallet_credited && $normalized === 'paid')) {
                return;
            }

            if ($normalized === 'pending') {
                $payment->update(['status' => 'pending', 'meta' => $meta]);
                $order->update(['payment_status' => 'pending']);

                return;
            }

            if (in_array($normalized, ['failed', 'cancelled'], true)) {
                if ($payment->status === 'paid') {
                    return;
                }

                $payment->update(['status' => $normalized, 'meta' => $meta]);
                $order->update(['payment_status' => $normalized === 'cancelled' ? 'unpaid' : 'failed']);
                app(CheckoutService::class)->releaseCommittedStock($order->fresh(), $order->user);

                return;
            }

            if ($normalized === 'refunded') {
                if ($payment->status !== 'paid') {
                    throw ValidationException::withMessages(['status' => __('operations.refund_needs_payment')]);
                }

                $order->refunds()->firstOrCreate(
                    ['payment_id' => $payment->id],
                    [
                        'user_id' => $order->user_id,
                        'amount' => $payment->amount,
                        'status' => 'pending',
                        'reason' => 'payment.refunded',
                    ],
                );
                app(CheckoutService::class)->approveRefund($order->fresh(), $order->user, 'payment.refunded');

                return;
            }

            $payment->update(['status' => 'paid', 'meta' => array_merge($meta, ['live_capture' => false, 'webhook' => true])]);
            $order->update(['payment_status' => 'paid']);
            app(CheckoutService::class)->commitStock($order->fresh(), $order->user);
            $this->capture($order->fresh());
        });
    }

    private function capture(Order $order): void
    {
        $order = $order->fresh();

        if ($order->wallet_credited || $order->payment_status !== 'paid') {
            return;
        }

        $this->wallets->creditVendors($order);
        $order->update(['wallet_credited' => true]);
        $this->referrals->rewardFirstPaidOrder($order->user, $order->fresh());
    }

    private function gateway(string $method): PaymentGateway
    {
        return match ($method) {
            'sandbox' => new SandboxGateway,
            'cod' => new CodGateway,
            'mpesa', 'airtel', 'orange', 'afrimoney', 'card' => new HostedGateway($method),
            default => throw ValidationException::withMessages(['payment_method' => __('commerce.payment_invalid')]),
        };
    }
}
