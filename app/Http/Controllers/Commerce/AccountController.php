<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function favorites(Request $request): View
    {
        abort_unless($request->user()->can('wishlist.manage'), 403);
        $products = Product::query()
            ->published()
            ->forCard()
            ->whereIn('products.id', $request->user()->favorites()->pluck('product_id'))
            ->paginate(12);

        return view('pages.commerce.favorites', ['products' => $products]);
    }

    public function favorite(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()->can('wishlist.manage'), 403);
        $request->user()->favorites()->firstOrCreate(['product_id' => $product->id]);

        return back()->with('success', __('commerce.favorite_added'));
    }

    public function unfavorite(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()->can('wishlist.manage'), 403);
        $request->user()->favorites()->where('product_id', $product->id)->delete();

        return back()->with('success', __('commerce.favorite_removed'));
    }

    public function follows(Request $request): View
    {
        $shops = Shop::query()
            ->whereIn('id', $request->user()->shopFollows()->pluck('shop_id'))
            ->orderBy('name')
            ->paginate(12);

        return view('pages.commerce.follows', ['shops' => $shops]);
    }

    public function follow(Request $request, Shop $shop): RedirectResponse
    {
        $request->user()->shopFollows()->firstOrCreate(['shop_id' => $shop->id]);

        return back()->with('success', __('commerce.followed'));
    }

    public function unfollow(Request $request, Shop $shop): RedirectResponse
    {
        $request->user()->shopFollows()->where('shop_id', $shop->id)->delete();

        return back()->with('success', __('commerce.unfollowed'));
    }

    public function notifications(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('pages.commerce.notifications', ['notifications' => $notifications]);
    }

    public function readNotifications(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', __('commerce.notifications_read'));
    }

    public function messages(Request $request): View
    {
        abort_unless($request->user()->can('messages.create') || $request->user()->can('shops.manage-own'), 403);
        $query = Conversation::query()->with(['shop', 'user', 'messages' => fn ($q) => $q->latest()->limit(1)])->latest();

        if ($request->user()->can('shops.manage') && $request->user()->isAdmin()) {
            // admin sees every conversation
        } elseif ($request->user()->vendorProfile) {
            $shopIds = $request->user()->vendorProfile->shops()->pluck('id');
            $query->where(function ($inner) use ($request, $shopIds): void {
                $inner->where('user_id', $request->user()->id)->orWhereIn('shop_id', $shopIds);
            });
        } else {
            $query->where('user_id', $request->user()->id);
        }

        return view('pages.commerce.messages.index', ['conversations' => $query->paginate(20)]);
    }

    public function showMessage(Request $request, Conversation $conversation): View
    {
        $this->authorizeConversation($request, $conversation);
        $conversation->load(['messages.user', 'shop', 'user']);
        $conversation->messages()->where('user_id', '!=', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return view('pages.commerce.messages.show', ['conversation' => $conversation]);
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);
        $shop = Shop::query()->findOrFail($data['shop_id']);
        $conversation = Conversation::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'shop_id' => $shop->id,
        ]);
        $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return redirect()->route('messages.show', $conversation)->with('success', __('commerce.message_sent'));
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($request, $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'min:1', 'max:2000']]);
        $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', __('commerce.message_sent'));
    }

    public function referral(Request $request): View
    {
        $user = $request->user();

        if (! $user->referral_code) {
            $user->forceFill(['referral_code' => $user::nextReferralCode()])->save();
        }

        return view('pages.commerce.referral', [
            'user' => $user->fresh(),
            'referrals' => $user->referralsMade()->with('referred:id,name')->latest()->get(),
        ]);
    }

    private function authorizeConversation(Request $request, Conversation $conversation): void
    {
        $user = $request->user();
        $ownsShop = $user->vendorProfile && $user->vendorProfile->shops()->whereKey($conversation->shop_id)->exists();
        abort_unless((int) $conversation->user_id === (int) $user->id || $ownsShop || $user->can('users.manage'), 403);
    }
}
