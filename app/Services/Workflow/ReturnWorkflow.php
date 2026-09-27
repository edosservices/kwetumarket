<?php

namespace App\Services\Workflow;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Notifications\AccountNotice;
use Illuminate\Validation\ValidationException;

class ReturnWorkflow
{
    public function request(Order $order, User $customer, string $reason): ReturnRequest
    {
        abort_unless((int) $order->user_id === (int) $customer->id, 403);

        $payment = $order->payments()->whereIn('status', ['successful', 'paid'])->first();

        if (! $payment || ! in_array($order->status, ['delivered', 'shipped', 'returned'], true)) {
            throw ValidationException::withMessages([
                'reason' => __('ui.workflow.return_needs_payment'),
            ]);
        }

        if ($order->returns()->whereIn('status', ['requested', 'vendor_accepted', 'approved', 'refunded'])->exists()) {
            throw ValidationException::withMessages([
                'reason' => __('ui.workflow.return_open'),
            ]);
        }

        return $order->returns()->create([
            'user_id' => $customer->id,
            'payment_id' => $payment->id,
            'reason' => $reason,
            'status' => 'requested',
        ]);
    }

    public function vendorDecide(ReturnRequest $returnRequest, User $actor, string $decision, ?string $note): void
    {
        $returnRequest->loadMissing('order');
        $owns = $actor->isVendorSide()
            && $returnRequest->order->items()->where('vendor_id', $actor->vendorId())->exists();

        abort_unless($owns, 403);

        if ($returnRequest->status !== 'requested') {
            throw ValidationException::withMessages([
                'decision' => __('ui.workflow.return_closed'),
            ]);
        }

        $returnRequest->update([
            'status' => $decision,
            'vendor_note' => $note,
        ]);

        if ($decision === 'vendor_accepted' && in_array($returnRequest->order->status, ['delivered', 'shipped'], true)) {
            $returnRequest->order->update(['status' => 'returned']);
        }
    }

    public function supervise(ReturnRequest $returnRequest, User $actor, string $decision, ?string $note): void
    {
        abort_unless($actor->can('orders.edit'), 403);

        if (! in_array($returnRequest->status, ['requested', 'vendor_accepted'], true)) {
            throw ValidationException::withMessages([
                'decision' => __('ui.workflow.return_closed'),
            ]);
        }

        $returnRequest->update([
            'status' => $decision,
            'admin_note' => $note,
        ]);
    }

    public function refund(ReturnRequest $returnRequest, User $actor, int $amountMinor): Refund
    {
        abort_unless($actor->can('finance.refunds'), 403);

        $returnRequest->loadMissing('order.customer', 'user');
        $order = $returnRequest->order;

        if ($returnRequest->status !== 'approved') {
            throw ValidationException::withMessages([
                'return_request_id' => __('ui.workflow.return_closed'),
            ]);
        }

        if ($amountMinor > (int) $order->total_minor) {
            throw ValidationException::withMessages([
                'amount_minor' => __('ui.workflow.amount_too_high'),
            ]);
        }

        if ($returnRequest->refunds()->exists()) {
            throw ValidationException::withMessages([
                'return_request_id' => __('ui.workflow.return_closed'),
            ]);
        }

        $refund = Refund::query()->create([
            'order_id' => $order->id,
            'return_request_id' => $returnRequest->id,
            'payment_id' => $returnRequest->payment_id,
            'amount_minor' => $amountMinor,
            'currency' => $order->currency,
            'status' => 'pending',
        ]);

        $returnRequest->update(['status' => 'refunded']);
        $order->update(['status' => 'refunded', 'payment_status' => 'refunded']);
        $order->payments()->whereIn('status', ['successful', 'paid'])->update(['status' => 'refunded']);

        AuditLog::record($actor, 'refund.create', $refund, [
            'module' => 'finance',
            'order_id' => $order->id,
            'return_request_id' => $returnRequest->id,
        ]);

        $order->customer?->notify(new AccountNotice(
            __('ui.notifications.refund_title'),
            __('ui.notifications.refund_body', ['number' => $order->number]),
        ));

        return $refund;
    }
}
