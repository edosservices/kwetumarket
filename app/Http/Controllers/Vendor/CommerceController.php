<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\SubscriptionPlan;
use App\Models\VendorSubscription;
use App\Services\Commerce\ExchangeRateService;
use App\Services\Commerce\PointsService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommerceController extends Controller
{
    public function orders(Request $request): View
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);
        $orders = Order::query()
            ->whereHas('items.shop', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->with(['user:id,name', 'items' => fn ($query) => $query->whereHas('shop', fn ($shop) => $shop->where('vendor_id', $vendor->id))])
            ->latest()
            ->paginate(15);

        return view('pages.vendor.commerce.orders', ['orders' => $orders]);
    }

    public function prepare(Request $request, Order $order): RedirectResponse
    {
        $this->ownsOrder($request, $order);

        if ($order->status !== 'confirmed') {
            return back()->with('error', __('commerce.prepare_closed'));
        }

        $order->update(['status' => 'preparing']);
        $order->events()->create([
            'user_id' => $request->user()->id,
            'status' => 'preparing',
            'note' => __('commerce.preparing'),
        ]);

        return back()->with('success', __('commerce.preparing'));
    }

    public function wallet(Request $request): View
    {
        abort_unless($request->user()->can('wallet.view-own'), 403);
        $wallet = $request->user()->wallet()->with(['entries' => fn ($query) => $query->latest()->limit(30)])->first();

        return view('pages.vendor.commerce.wallet', [
            'wallet' => $wallet,
            'withdrawals' => $request->user()->withdrawals()->latest()->limit(20)->get(),
        ]);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('withdrawals.request'), 403);
        $data = $request->validate([
            'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $amount = Money::toMinor($data['amount']);
        $balance = (int) ($request->user()->wallet?->balance ?? 0);

        if ($amount < 1 || $amount > $balance) {
            return back()->with('error', __('commerce.wallet_short'))->withInput();
        }

        if ($request->user()->withdrawals()->where('status', 'pending')->exists()) {
            return back()->with('error', __('commerce.withdrawal_pending'));
        }

        $request->user()->withdrawals()->create([
            'amount' => $amount,
            'status' => 'pending',
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', __('commerce.withdrawal_sent'));
    }

    public function subscription(Request $request): View
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);

        $rates = app(ExchangeRateService::class);

        return view('pages.vendor.commerce.subscription', [
            'plans' => SubscriptionPlan::query()->where('is_active', true)->orderBy('price')->get(),
            'current' => $vendor->subscriptions()->with('plan')->latest()->first(),
            'rate' => $rates->latest(),
            'points' => app(PointsService::class)->balance($request->user()),
            'level' => app(PointsService::class)->level(app(PointsService::class)->balance($request->user())),
        ]);
    }

    public function subscribe(Request $request, ExchangeRateService $rates, PointsService $points): RedirectResponse
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);
        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'use_points' => ['nullable', 'boolean'],
        ]);
        $plan = SubscriptionPlan::query()->where('is_active', true)->findOrFail($data['plan_id']);
        $usd = (int) ($plan->price_usd_cents ?? 0);
        $rate = $usd > 0 ? $rates->latest() : null;
        $charge = $usd > 0 ? $rates->usdCentsToQuoteMinor($usd) : (int) $plan->price;

        if ($usd > 0 && ($charge === null || $rate === null)) {
            return back()->with('error', __('experience.rate_missing'));
        }

        $spent = 0;
        $covered = 0;

        if ($request->boolean('use_points') && $usd > 0) {
            $cover = $points->coverUsdCents($request->user(), $usd);
            $spent = $cover['points'];
            $covered = $cover['usd_cents'];
        }

        $remaining = max(0, $usd - $covered);
        $reference = 'SANDBOX-PLAN-'.$plan->id;

        if ($spent > 0 && $remaining === 0) {
            $reference = 'POINTS';
        } elseif ($spent > 0) {
            $reference = 'SANDBOX-POINTS-PARTIAL';
        }

        if ($remaining > 0 && $usd > 0 && config('twende.commerce.payment_driver') !== 'sandbox') {
            return back()->with('error', __('experience.points_partial_needs_payment'));
        }

        if ($spent > 0) {
            $points->redeem($request->user(), $spent, 'subscription:'.$plan->slug);
        }

        $vendor->subscriptions()->where('status', 'active')->update(['status' => 'replaced']);
        VendorSubscription::query()->create([
            'vendor_id' => $vendor->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->interval_days),
            'payment_reference' => $reference,
            'amount_minor' => $charge,
            'points_spent' => $spent,
            'rate_minor_per_unit' => $rate?->minor_per_unit,
            'rate_quoted_at' => $rate?->quoted_at,
        ]);

        return back()->with('success', $reference === 'POINTS' ? __('experience.subscribed_points') : __('commerce.subscribed'));
    }

    public function certification(Request $request): View
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);

        return view('pages.vendor.commerce.certification', [
            'requests' => $vendor->certifications()->latest()->get(),
        ]);
    }

    public function requestCertification(Request $request): RedirectResponse
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);
        $data = $request->validate(['note' => ['required', 'string', 'min:10', 'max:1000']]);

        if ($vendor->certifications()->where('status', 'pending')->exists()) {
            return back()->with('error', __('commerce.certification_pending'));
        }

        $vendor->certifications()->create([
            'status' => 'pending',
            'note' => $data['note'],
        ]);

        return back()->with('success', __('commerce.certification_sent'));
    }

    public function ads(Request $request): View
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);

        return view('pages.vendor.commerce.ads', [
            'ads' => $vendor->ads()->latest()->get(),
        ]);
    }

    public function storeAd(Request $request): RedirectResponse
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
        ]);
        AdCampaign::query()->create([
            'vendor_id' => $vendor->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'status' => 'pending',
        ]);

        return back()->with('success', __('commerce.ad_sent'));
    }

    public function promotions(Request $request): View
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor && $request->user()->can('promotions.manage-own'), 403);
        $promotions = Promotion::query()
            ->whereHas('product.shop', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->with('product:id,name,slug')
            ->latest()
            ->paginate(20);
        $products = \App\Models\Product::query()
            ->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.vendor.commerce.promotions', [
            'promotions' => $promotions,
            'products' => $products,
        ]);
    }

    public function storePromotion(Request $request): RedirectResponse
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'promotional_price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'ends_at' => ['required', 'date', 'after:now'],
        ]);
        $product = \App\Models\Product::query()
            ->whereKey($data['product_id'])
            ->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->firstOrFail();
        $price = Money::toMinor($data['promotional_price']);

        if ($price >= (int) $product->price) {
            return back()->with('error', __('commerce.promo_price'))->withInput();
        }

        Promotion::query()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'promotional_price' => $price,
                'starts_at' => now(),
                'ends_at' => $data['ends_at'],
                'is_active' => true,
            ],
        );

        return back()->with('success', __('commerce.promo_saved'));
    }

    public function analytics(Request $request): View
    {
        $vendor = $request->user()->vendorProfile;
        abort_unless($vendor, 403);
        $items = \App\Models\OrderItem::query()->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendor->id));

        return view('pages.vendor.commerce.analytics', [
            'orders' => Order::query()->whereHas('items.shop', fn ($query) => $query->where('vendor_id', $vendor->id))->count(),
            'gross' => (int) (clone $items)->sum('line_total'),
            'commission' => (int) (clone $items)->sum('commission'),
            'products' => \App\Models\Product::query()->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendor->id))->count(),
            'balance' => (int) ($request->user()->wallet?->balance ?? 0),
        ]);
    }

    private function ownsOrder(Request $request, Order $order): void
    {
        $vendorId = $request->user()->vendorProfile?->id;
        abort_unless($vendorId && $order->items()->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendorId))->exists(), 403);
    }
}
