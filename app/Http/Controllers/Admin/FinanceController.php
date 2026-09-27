<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payout;
use App\Models\ReturnRequest;
use App\Services\Workflow\ReturnWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FinanceController extends Controller
{
    public function refund(Request $request, ReturnWorkflow $returns): RedirectResponse
    {
        $data = $request->validate([
            'return_request_id' => ['required', 'integer', 'exists:return_requests,id'],
            'amount_minor' => ['required', 'integer', 'min:1'],
        ]);

        $returnRequest = ReturnRequest::query()->findOrFail($data['return_request_id']);
        $returns->refund($returnRequest, $request->user(), (int) $data['amount_minor']);

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
