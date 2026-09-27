<?php

namespace App\Services\Workflow;

use App\Models\AuditLog;
use App\Models\CourierProfile;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AccountNotice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Single delivery path. Status names match the twende-final courier workflow:
 * pending/assigned → accepted → departed → en_route → arrived → delivered.
 * Wallet credit stays in twende-final's commerce service and is not reimplemented here.
 */
class DeliveryTransition
{
    /**
     * @var array<string, list<string>>
     */
    public const NEXT = [
        'pending' => ['accepted'],
        'assigned' => ['accepted'],
        'accepted' => ['departed'],
        'departed' => ['en_route'],
        'en_route' => ['arrived'],
        'arrived' => ['delivered'],
    ];

    /**
     * @return list<string>
     */
    public static function allowed(string $status): array
    {
        return self::NEXT[$status] ?? [];
    }

    public function advance(Delivery $delivery, User $actor, string $to, ?int $etaMinutes = null): void
    {
        DB::transaction(function () use ($delivery, $actor, $to, $etaMinutes): void {
            $delivery = Delivery::query()->whereKey($delivery->id)->lockForUpdate()->firstOrFail();

            if (! in_array($to, self::allowed($delivery->status), true)) {
                throw ValidationException::withMessages([
                    'status' => __('ui.workflow.invalid_transition'),
                ]);
            }

            $manager = $actor->can('delivery.manage');

            if ($to === 'accepted') {
                if ($delivery->agent_id && (int) $delivery->agent_id !== (int) $actor->id && ! $manager) {
                    throw ValidationException::withMessages([
                        'status' => __('ui.workflow.not_your_delivery'),
                    ]);
                }

                $profile = CourierProfile::query()->where('user_id', $delivery->agent_id ?: $actor->id)->first();

                if ($profile && ! $profile->canAcceptJobs() && ! $manager) {
                    throw ValidationException::withMessages([
                        'status' => __('ui.workflow.courier_unavailable'),
                    ]);
                }
            } elseif ((int) $delivery->agent_id !== (int) $actor->id && ! $manager) {
                throw ValidationException::withMessages([
                    'status' => __('ui.workflow.not_your_delivery'),
                ]);
            }

            $from = $delivery->status;
            $delivery->status = $to;

            if ($to === 'en_route' && $etaMinutes) {
                $delivery->eta_at = now()->addMinutes($etaMinutes);
            }

            $delivery->save();

            $profile = CourierProfile::query()->where('user_id', $delivery->agent_id)->first();

            if ($profile && $to === 'accepted') {
                $profile->update(['availability' => CourierProfile::ON_DELIVERY]);
            }

            if ($profile && $to === 'delivered') {
                $profile->update(['availability' => CourierProfile::AVAILABLE]);
            }

            $order = Order::query()->whereKey($delivery->order_id)->lockForUpdate()->first();

            if ($order) {
                $order->status = match ($to) {
                    'departed', 'en_route', 'arrived' => 'shipped',
                    'delivered' => 'delivered',
                    default => $order->status,
                };

                if ($to === 'delivered' && $order->payment_status !== 'paid') {
                    $order->payment_status = 'paid';
                    $order->payments()->whereIn('status', ['pending', 'unpaid'])->update([
                        'status' => 'successful',
                    ]);
                }

                $order->save();
            }

            AuditLog::record($actor, 'delivery.update', $delivery, [
                'from' => $from,
                'to' => $to,
                'module' => 'delivery',
            ]);

            $delivery->loadMissing('order.customer');
            $delivery->order?->customer?->notify(new AccountNotice(
                __('ui.notifications.delivery_title'),
                __('ui.notifications.delivery_body', ['number' => $delivery->order->number, 'status' => $to]),
            ));
        });
    }

    public function assign(Delivery $delivery, User $actor, User $agent): void
    {
        if (! in_array($delivery->status, ['pending', 'assigned'], true)) {
            throw ValidationException::withMessages([
                'status' => __('ui.workflow.invalid_transition'),
            ]);
        }

        $delivery->forceFill([
            'agent_id' => $agent->id,
            'status' => 'assigned',
        ])->save();

        AuditLog::record($actor, 'delivery.assign', $delivery, [
            'agent_id' => $agent->id,
            'module' => 'delivery',
        ]);

        $delivery->loadMissing('order');

        $agent->notify(new AccountNotice(
            __('ui.notifications.mission_title'),
            __('ui.notifications.mission_body', ['number' => $delivery->order?->number ?? $delivery->id]),
        ));
    }
}
