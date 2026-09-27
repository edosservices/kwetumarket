<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Refund;
use App\Notifications\AccountNotice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FinanceController extends Controller
{
    public function refund(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('finance.refunds'), 403);

        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'amount_minor' => ['required', 'integer', 'min:1'],
        ]);

        $order = Order::query()->findOrFail($data['order_id']);
        $this->authorize('view', $order);

        if ($data['amount_minor'] > (int) $order->total_minor) {
            throw ValidationException::withMessages([
                'amount_minor' => __('ui.workflow.amount_too_high'),
            ]);
        }

        $refund = Refund::query()->create([
            'order_id' => $order->id,
            'amount_minor' => $data['amount_minor'],
            'currency' => $order->currency,
            'status' => 'pending',
        ]);

        AuditLog::record($request->user(), 'refund.create', $refund, [
            'module' => 'finance',
            'order_id' => $order->id,
        ]);

        $order->customer?->notify(new AccountNotice(
            __('ui.notifications.refund_title'),
            __('ui.notifications.refund_body', ['number' => $order->number]),
        ));

        return back();
    }

    public function approvePayout(Request $request, Payout $payout): RedirectResponse
    {
        abort_unless($request->user()->can('finance.payouts'), 403);

        if ($payout->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => __('ui.workflow.invalid_transition'),
            ]);
        }

        $payout->forceFill(['status' => 'paid'])->save();

        AuditLog::record($request->user(), 'payout.approve', $payout, [
            'module' => 'finance',
            'vendor_id' => $payout->vendor_id,
        ]);

        return back();
    }
}
