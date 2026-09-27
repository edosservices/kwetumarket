<?php

namespace App\Services\Workflow;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AccountNotice;
use App\Policies\OrderPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Staff transitions for the commerce order machine:
 * confirmed → preparing → ready, and cancellation while the courier has not departed.
 * Shipped and delivered are written by DeliveryTransition, not by a second status map.
 */
class OrderWorkflow
{
    /**
     * Status => next status => permission required.
     *
     * @var array<string, array<string, string>>
     */
    public const TRANSITIONS = [
        'confirmed' => [
            'preparing' => 'orders.process',
            'cancelled' => 'orders.cancel',
        ],
        'preparing' => [
            'ready' => 'orders.process',
            'cancelled' => 'orders.cancel',
        ],
        'ready' => [
            'cancelled' => 'orders.cancel',
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

        DB::transaction(function () use ($order, $actor, $to, $permission): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from = $order->status;

            if ($to === 'cancelled') {
                $delivery = $order->delivery()->lockForUpdate()->first();

                if ($delivery && ! in_array($delivery->status, ['pending', 'assigned', 'accepted'], true)) {
                    throw ValidationException::withMessages([
                        'status' => __('ui.workflow.invalid_transition'),
                    ]);
                }

                $delivery?->update(['status' => 'cancelled']);
            }

            $order->status = $to;
            $order->save();

            $action = match ($permission) {
                'orders.cancel' => 'order.cancel',
                'orders.process' => 'order.process',
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
        });
    }

    public function markPaid(Order $order, User $actor): void
    {
        abort_unless($actor->can('orders.edit'), 403);
        abort_unless(app(OrderPolicy::class)->view($actor, $order), 403);

        if (! in_array($order->status, ['confirmed', 'preparing', 'ready'], true) || $order->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'payment_status' => __('ui.workflow.invalid_transition'),
            ]);
        }

        $order->payment_status = 'paid';
        $order->save();
        $order->payments()->whereIn('status', ['pending', 'unpaid'])->update(['status' => 'successful']);

        AuditLog::record($actor, 'order.update', $order, [
            'payment_status' => 'paid',
            'module' => 'orders',
        ]);
    }
}
