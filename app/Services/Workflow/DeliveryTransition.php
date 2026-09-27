<?php

namespace App\Services\Workflow;

use App\Models\AuditLog;
use App\Models\Delivery;
use App\Models\User;
use App\Notifications\AccountNotice;
use Illuminate\Validation\ValidationException;

class DeliveryTransition
{
    /**
     * @var array<string, list<string>>
     */
    public const NEXT = [
        'pending' => ['accepted', 'declined'],
        'assigned' => ['accepted', 'declined'],
        'accepted' => ['picked_up'],
        'picked_up' => ['in_transit'],
        'in_transit' => ['delivered', 'failed'],
    ];

    /**
     * @return list<string>
     */
    public static function allowed(string $status): array
    {
        return self::NEXT[$status] ?? [];
    }

    public function advance(Delivery $delivery, User $actor, string $to): void
    {
        if (! in_array($to, self::allowed($delivery->status), true)) {
            throw ValidationException::withMessages([
                'status' => __('ui.workflow.invalid_transition'),
            ]);
        }

        $from = $delivery->status;
        $delivery->status = $to;
        $delivery->save();

        AuditLog::record($actor, 'delivery.update', $delivery, [
            'from' => $from,
            'to' => $to,
        ]);

        $delivery->loadMissing('order.customer', 'agent');
        $delivery->order?->customer?->notify(new AccountNotice(
            __('ui.notifications.delivery_title'),
            __('ui.notifications.delivery_body', ['number' => $delivery->order->number, 'status' => $to]),
        ));
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
        ]);

        $delivery->loadMissing('order');

        $agent->notify(new AccountNotice(
            __('ui.notifications.mission_title'),
            __('ui.notifications.mission_body', ['number' => $delivery->order?->number ?? $delivery->id]),
        ));
    }
}
