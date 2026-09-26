<?php

namespace App\Services\Commerce;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Notifications\CommerceNotice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryWorkflow
{
    public function __construct(
        private WalletService $wallets,
        private ReferralService $referrals,
    ) {}

    public function assign(Delivery $delivery, User $agent, User $actor): void
    {
        if (! $agent->hasRole('delivery_agent') && ! $agent->hasRole('admin')) {
            throw ValidationException::withMessages(['agent_id' => __('commerce.agent_invalid')]);
        }

        if (! in_array($delivery->status, ['pending', 'assigned'], true)) {
            throw ValidationException::withMessages(['status' => __('commerce.delivery_step')]);
        }

        $delivery->update([
            'agent_id' => $agent->id,
            'status' => 'assigned',
        ]);
        $delivery->order->events()->create([
            'user_id' => $actor->id,
            'status' => 'assigned',
            'note' => $agent->name,
        ]);
        $agent->notify(new CommerceNotice(
            __('commerce.notice_assigned_title'),
            __('commerce.notice_assigned_body', ['number' => $delivery->order->number]),
            route('delivery.jobs'),
        ));
    }

    public function advance(Delivery $delivery, User $actor, string $to, ?int $etaMinutes = null): void
    {
        DB::transaction(function () use ($delivery, $actor, $to, $etaMinutes): void {
            $delivery = Delivery::query()->whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            $map = [
                'pending' => ['accepted'],
                'assigned' => ['accepted'],
                'accepted' => ['departed'],
                'departed' => ['en_route'],
                'en_route' => ['arrived'],
                'arrived' => ['delivered'],
            ];

            if (! in_array($to, $map[$delivery->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => __('commerce.delivery_step')]);
            }

            $admin = $actor->can('deliveries.manage');

            if ($to === 'accepted') {
                if ($delivery->agent_id && (int) $delivery->agent_id !== (int) $actor->id && ! $admin) {
                    throw ValidationException::withMessages(['status' => __('commerce.not_your_delivery')]);
                }

                if (! $delivery->agent_id) {
                    $delivery->agent_id = $actor->id;
                }
            } elseif ((int) $delivery->agent_id !== (int) $actor->id && ! $admin) {
                throw ValidationException::withMessages(['status' => __('commerce.not_your_delivery')]);
            }

            $delivery->status = $to;

            if ($to === 'en_route' && $etaMinutes) {
                $delivery->eta_at = now()->addMinutes($etaMinutes);
            }

            $delivery->save();

            $order = Order::query()->whereKey($delivery->order_id)->lockForUpdate()->firstOrFail();
            $order->status = match ($to) {
                'departed', 'en_route', 'arrived' => 'shipped',
                'delivered' => 'delivered',
                default => $order->status,
            };

            if ($to === 'delivered') {
                if ($order->payment_status === 'unpaid') {
                    $order->payment_status = 'paid';
                    $order->payments()->where('status', 'unpaid')->update([
                        'status' => 'paid',
                        'reference' => 'COD-'.$order->number,
                    ]);
                }

                if (! $order->wallet_credited) {
                    $order->setRelation('delivery', $delivery);
                    $this->wallets->creditVendors($order);
                    $this->wallets->creditCourier($order);
                    $order->wallet_credited = true;
                    $this->referrals->rewardFirstPaidOrder($order->user, $order);
                }
            }

            $order->save();
            $order->events()->create([
                'user_id' => $actor->id,
                'status' => $to,
                'note' => $delivery->eta_at ? __('commerce.eta', ['time' => $delivery->eta_at->timezone(config('app.timezone'))->format('d/m H:i')]) : null,
            ]);
            $order->user->notify(new CommerceNotice(
                __('commerce.notice_delivery_title'),
                __('commerce.delivery_statuses.'.$to).' · '.$order->number,
                route('orders.show', $order),
            ));
        });
    }
}
