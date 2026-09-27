<?php

namespace App\Services\Workflow;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AccountNotice;
use App\Policies\OrderPolicy;
use Illuminate\Validation\ValidationException;

class OrderWorkflow
{
    /**
     * Status => next status => permission required.
     *
     * @var array<string, array<string, string>>
     */
    public const TRANSITIONS = [
        'pending' => [
            'paid' => 'orders.edit',
            'cancelled' => 'orders.cancel',
        ],
        'paid' => [
            'processing' => 'orders.process',
            'cancelled' => 'orders.cancel',
        ],
        'processing' => [
            'shipped' => 'orders.process',
            'cancelled' => 'orders.cancel',
        ],
        'shipped' => [
            'delivered' => 'orders.process',
        ],
    ];

    /**
     * @return array<string, string>
     */
    public function options(Order $order, User $actor): array
    {
        $options = [];

        foreach (self::TRANSITIONS[$order->status] ?? [] as $status => $permission) {
            if ($actor->can($permission) && app(OrderPolicy::class)->view($actor, $order)) {
                $options[$status] = $permission;
            }
        }

        return $options;
    }

    public function transition(Order $order, User $actor, string $to): void
    {
        $permission = self::TRANSITIONS[$order->status][$to] ?? null;

        if ($permission === null) {
            throw ValidationException::withMessages([
                'status' => __('ui.workflow.invalid_transition'),
            ]);
        }

        abort_unless($actor->can($permission), 403);
        abort_unless(app(OrderPolicy::class)->view($actor, $order), 403);

        $from = $order->status;
        $order->status = $to;
        $order->save();

        $action = match ($to) {
            'cancelled' => 'order.cancel',
            'processing', 'shipped', 'delivered' => 'order.process',
            default => 'order.update',
        };

        AuditLog::record($actor, $action, $order, [
            'from' => $from,
            'to' => $to,
            'module' => 'orders',
        ]);

        $order->customer?->notify(new AccountNotice(
            __('ui.notifications.order_title'),
            __('ui.notifications.order_body', ['number' => $order->number, 'status' => $to]),
        ));
    }
}
