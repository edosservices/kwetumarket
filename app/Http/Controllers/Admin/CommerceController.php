<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\Delivery;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Models\VendorCertification;
use App\Models\WithdrawalRequest;
use App\Services\Commerce\CheckoutService;
use App\Services\Commerce\DeliveryWorkflow;
use App\Services\Commerce\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommerceController extends Controller
{
    public function orders(): View
    {
        return view('pages.admin.commerce.orders', [
            'orders' => Order::query()->with(['user:id,name', 'delivery'])->latest()->paginate(20),
        ]);
    }

    public function users(): View
    {
        return view('pages.admin.commerce.users', [
            'users' => User::query()->with('roles')->latest()->paginate(20),
        ]);
    }

    public function deliveries(): View
    {
        return view('pages.admin.commerce.deliveries', [
            'deliveries' => Delivery::query()->with(['order:id,number', 'agent:id,name'])->latest()->paginate(20),
            'agents' => User::role('delivery_agent')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function assign(Request $request, Delivery $delivery, DeliveryWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate(['agent_id' => ['required', 'integer', 'exists:users,id']]);
        $workflow->assign($delivery, User::query()->findOrFail($data['agent_id']), $request->user());

        return back()->with('success', __('commerce.assigned'));
    }

    public function withdrawals(): View
    {
        return view('pages.admin.commerce.withdrawals', [
            'withdrawals' => WithdrawalRequest::query()->with('user:id,name')->latest()->paginate(20),
        ]);
    }

    public function decideWithdrawal(Request $request, WithdrawalRequest $withdrawal, WalletService $wallets): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:paid,rejected'],
            'admin_note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', __('commerce.withdrawal_closed'));
        }

        if ($data['decision'] === 'paid') {
            $wallets->debit($withdrawal->user, (int) $withdrawal->amount, 'withdrawal', 'WD-'.$withdrawal->id, $data['admin_note'] ?? null);
        }

        $withdrawal->update([
            'status' => $data['decision'],
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', __('commerce.withdrawal_updated'));
    }

    public function disputes(): View
    {
        return view('pages.admin.commerce.disputes', [
            'disputes' => Dispute::query()->with(['order:id,number', 'user:id,name'])->latest()->paginate(20),
        ]);
    }

    public function resolveDispute(Request $request, Dispute $dispute): RedirectResponse
    {
        $data = $request->validate(['resolution' => ['required', 'string', 'min:5', 'max:2000']]);
        $dispute->update([
            'status' => 'resolved',
            'resolution' => $data['resolution'],
            'reviewed_by' => $request->user()->id,
        ]);

        return back()->with('success', __('commerce.dispute_resolved'));
    }

    public function refunds(): View
    {
        return view('pages.admin.commerce.refunds', [
            'refunds' => Refund::query()->with(['order:id,number,total,currency', 'user:id,name'])->latest()->paginate(20),
        ]);
    }

    public function decideRefund(Request $request, Refund $refund, CheckoutService $checkout): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($refund->status !== 'pending') {
            return back()->with('error', __('commerce.refund_closed'));
        }

        if ($data['decision'] === 'rejected') {
            $refund->update([
                'status' => 'rejected',
                'decision' => $data['note'],
                'reviewed_by' => $request->user()->id,
            ]);

            return back()->with('success', __('commerce.refund_updated'));
        }

        $checkout->approveRefund($refund->order, $request->user(), $data['note'] ?: __('commerce.refund_approved'));

        return back()->with('success', __('commerce.refund_updated'));
    }

    public function ads(): View
    {
        return view('pages.admin.commerce.ads', [
            'ads' => AdCampaign::query()->with('vendor.user:id,name')->latest()->paginate(20),
        ]);
    }

    public function decideAd(Request $request, AdCampaign $ad): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected']]);
        $ad->update([
            'status' => $data['decision'],
            'starts_at' => $data['decision'] === 'approved' ? now() : $ad->starts_at,
            'ends_at' => $data['decision'] === 'approved' ? now()->addDays(14) : $ad->ends_at,
        ]);

        return back()->with('success', __('commerce.ad_updated'));
    }

    public function certifications(): View
    {
        return view('pages.admin.commerce.certifications', [
            'requests' => VendorCertification::query()->with('vendor.user:id,name')->latest()->paginate(20),
        ]);
    }

    public function decideCertification(Request $request, VendorCertification $certification): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $certification->update([
            'status' => $data['decision'],
            'decision' => $data['note'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', __('commerce.certification_updated'));
    }

    public function analytics(): View
    {
        return view('pages.admin.commerce.analytics', [
            'orders' => Order::query()->count(),
            'revenue' => (int) Order::query()->where('payment_status', 'paid')->sum('total'),
            'openDisputes' => Dispute::query()->where('status', 'open')->count(),
            'users' => User::query()->count(),
            'products' => \App\Models\Product::query()->count(),
        ]);
    }

    public function settings(): View
    {
        return view('pages.admin.commerce.settings', [
            'settings' => [
                'currency' => config('twende.currency.default'),
                'tax_percent' => config('twende.commerce.tax_percent'),
                'commission_percent' => config('twende.commerce.commission_percent'),
                'payment_driver' => config('twende.commerce.payment_driver'),
                'sms_driver' => config('twende.sms.driver'),
                'search_driver' => config('twende.search.driver'),
                'vision_driver' => config('twende.vision.driver'),
            ],
        ]);
    }
}
