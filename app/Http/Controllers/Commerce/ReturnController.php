<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\Catalog\MediaStorage;
use App\Services\Commerce\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function store(Request $request, Order $order, MediaStorage $media): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'evidence' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);
        $payment = $order->payments()->where('status', 'paid')->first();

        if (! $payment || ! in_array($order->status, ['delivered', 'shipped', 'returned'], true)) {
            return back()->with('error', __('operations.return_needs_payment'));
        }

        if ($order->returns()->whereIn('status', ['requested', 'vendor_accepted', 'approved', 'refunded'])->exists()) {
            return back()->with('error', __('operations.return_open'));
        }

        $path = null;
        $disk = null;

        if ($request->hasFile('evidence')) {
            $disk = $media->disk();
            $path = $media->store($request->file('evidence'), 'returns/'.$order->id);
        }

        $order->returns()->create([
            'user_id' => $request->user()->id,
            'payment_id' => $payment->id,
            'reason' => $data['reason'],
            'status' => 'requested',
            'evidence_disk' => $disk,
            'evidence_path' => $path,
        ]);
        $order->events()->create([
            'user_id' => $request->user()->id,
            'status' => 'returned',
            'note' => __('operations.return_requested'),
        ]);

        return back()->with('success', __('operations.return_requested'));
    }

    public function vendorDecide(Request $request, ReturnRequest $returnRequest): RedirectResponse
    {
        $vendorId = $request->user()->vendorProfile?->id;
        $returnRequest->load('order.items.shop');
        $owns = $vendorId && $returnRequest->order->items->contains(fn ($item) => (int) $item->shop->vendor_id === (int) $vendorId);
        abort_unless($owns, 403);
        $data = $request->validate([
            'decision' => ['required', 'in:vendor_accepted,vendor_rejected'],
            'vendor_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($returnRequest->status !== 'requested') {
            return back()->with('error', __('operations.return_closed'));
        }

        $returnRequest->update([
            'status' => $data['decision'],
            'vendor_note' => $data['vendor_note'] ?? null,
        ]);

        if ($data['decision'] === 'vendor_accepted') {
            $returnRequest->order->update(['status' => 'returned']);
        }

        return back()->with('success', __('operations.return_updated'));
    }

    public function adminIndex(Request $request): View
    {
        abort_unless($request->user()->can('refunds.manage'), 403);

        return view('pages.admin.commerce.returns', [
            'returns' => ReturnRequest::query()->with(['order:id,number', 'payment:id,status,reference', 'user:id,name'])->latest()->paginate(20),
        ]);
    }

    public function adminDecide(Request $request, ReturnRequest $returnRequest, CheckoutService $checkout): RedirectResponse
    {
        abort_unless($request->user()->can('refunds.manage'), 403);
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! in_array($returnRequest->status, ['requested', 'vendor_accepted'], true)) {
            return back()->with('error', __('operations.return_closed'));
        }

        if ($data['decision'] === 'rejected') {
            $returnRequest->update(['status' => 'rejected', 'admin_note' => $data['admin_note'] ?? null]);

            return back()->with('success', __('operations.return_updated'));
        }

        $payment = $returnRequest->payment;

        if (! $payment || $payment->status !== 'paid') {
            return back()->with('error', __('operations.refund_needs_payment'));
        }

        $returnRequest->order->refunds()->create([
            'user_id' => $returnRequest->user_id,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'status' => 'pending',
            'reason' => $returnRequest->reason,
        ]);
        $checkout->approveRefund($returnRequest->order, $request->user(), $data['admin_note'] ?: __('operations.return_refunded'));
        $returnRequest->update(['status' => 'refunded', 'admin_note' => $data['admin_note'] ?? null]);

        return back()->with('success', __('operations.return_refunded'));
    }
}
