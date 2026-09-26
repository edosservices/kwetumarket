<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
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

        if ($reference === '' || $minor === null || ! in_array($status, ['paid', 'failed', 'cancelled'], true)) {
            throw ValidationException::withMessages(['payload' => __('experience.payment_payload')]);
        }

        DB::transaction(function () use ($provider, $reference, $status, $currency, $minor): void {
            $payment = Payment::query()->where('reference', $reference)->lockForUpdate()->first();

            if (! $payment || $payment->provider !== $provider) {
                throw ValidationException::withMessages(['reference' => __('experience.payment_unknown')]);
            }

            $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();

            if ($minor !== (int) $payment->amount || $minor !== (int) $order->total || $currency !== $order->currency) {
                throw ValidationException::withMessages(['amount' => __('experience.payment_amount')]);
            }

            if ($payment->status === 'paid' || $order->wallet_credited) {
                return;
            }

            if ($status !== 'paid') {
                $payment->update(['status' => $status]);
                $order->update(['payment_status' => $status === 'cancelled' ? 'unpaid' : 'failed']);

                return;
            }

            $payment->update(['status' => 'paid', 'meta' => array_merge($payment->meta ?? [], ['live_capture' => false, 'webhook' => true])]);
            $order->update(['payment_status' => 'paid']);
            $this->capture($order);
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
