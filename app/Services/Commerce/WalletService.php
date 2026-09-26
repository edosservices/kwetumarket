<?php

namespace App\Services\Commerce;

use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function credit(User $user, int $amount, string $type, ?string $reference = null, ?string $note = null): Wallet
    {
        if ($amount < 1) {
            throw new \InvalidArgumentException('Wallet credit must be positive.');
        }

        return $this->apply($user, $amount, $type, $reference, $note);
    }

    public function debit(User $user, int $amount, string $type, ?string $reference = null, ?string $note = null): Wallet
    {
        if ($amount < 1) {
            throw new \InvalidArgumentException('Wallet debit must be positive.');
        }

        return $this->apply($user, -$amount, $type, $reference, $note);
    }

    public function creditVendors(Order $order): void
    {
        $order->loadMissing('items.shop.vendor.user');

        foreach ($order->items->groupBy(fn ($item) => $item->shop->vendor->user_id) as $userId => $items) {
            $net = (int) $items->sum(fn ($item) => $item->line_total - $item->commission);

            if ($net < 1) {
                continue;
            }

            $user = User::query()->findOrFail($userId);
            $this->credit($user, $net, 'sale', $order->number, 'Vente '.$order->number);
        }
    }

    public function creditCourier(Order $order): void
    {
        $order->loadMissing('delivery.agent');
        $agent = $order->delivery?->agent;

        if ($agent === null || (int) $order->delivery_fee < 1) {
            return;
        }

        $this->credit($agent, (int) $order->delivery_fee, 'delivery', $order->number, 'Livraison '.$order->number);
    }

    public function reverseOrder(Order $order): void
    {
        $order->loadMissing('items.shop.vendor.user', 'delivery.agent');

        foreach ($order->items->groupBy(fn ($item) => $item->shop->vendor->user_id) as $userId => $items) {
            $net = (int) $items->sum(fn ($item) => $item->line_total - $item->commission);

            if ($net < 1) {
                continue;
            }

            $this->debit(User::query()->findOrFail($userId), $net, 'reversal', $order->number, 'Annulation '.$order->number);
        }

        $agent = $order->delivery?->agent;

        if ($agent && (int) $order->delivery_fee > 0 && $order->delivery?->status === 'delivered') {
            $this->debit($agent, (int) $order->delivery_fee, 'reversal', $order->number, 'Annulation livraison '.$order->number);
        }
    }

    private function apply(User $user, int $signedAmount, string $type, ?string $reference, ?string $note): Wallet
    {
        return DB::transaction(function () use ($user, $signedAmount, $type, $reference, $note): Wallet {
            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->first();

            if (! $wallet) {
                $wallet = Wallet::query()->create(['user_id' => $user->id, 'balance' => 0]);
                $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->first();
            }

            $next = (int) $wallet->balance + $signedAmount;

            if ($next < 0) {
                throw ValidationException::withMessages([
                    'amount' => __('commerce.wallet_short'),
                ]);
            }

            $wallet->update(['balance' => $next]);
            $wallet->entries()->create([
                'amount' => $signedAmount,
                'type' => $type,
                'reference' => $reference,
                'note' => $note,
            ]);

            return $wallet;
        });
    }
}
