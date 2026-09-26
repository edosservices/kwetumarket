<x-layouts.storefront :title="__('commerce.cart_title')">
    <div class="mx-auto max-w-7xl px-4 py-8">
        <x-flash />
        <h1 class="text-2xl font-bold sm:text-3xl">{{ __('commerce.cart_title') }}</h1>
        @if ($quote->lines === [])
            <div class="mt-8">
                <x-empty-state :title="__('commerce.cart_empty_title')" :description="__('commerce.cart_empty_body')">
                    <x-slot:icon><x-icon name="cart" class="mb-3 h-10 w-10" /></x-slot:icon>
                    <x-slot:action>
                        <x-button :href="route('products.index')">{{ __('commerce.browse') }}</x-button>
                    </x-slot:action>
                </x-empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="min-w-0 overflow-hidden rounded-3xl border border-twende-line bg-white dark:border-white/10 dark:bg-twende-night-card">
                    <div class="hidden grid-cols-[minmax(0,1fr)_8rem_8rem_8rem_auto] gap-3 border-b border-twende-line px-4 py-3 text-xs font-semibold uppercase text-twende-muted md:grid dark:border-white/10">
                        <span>{{ __('commerce.product') }}</span>
                        <span>{{ __('commerce.price') }}</span>
                        <span>{{ __('commerce.quantity') }}</span>
                        <span>{{ __('commerce.line_total') }}</span>
                        <span></span>
                    </div>
                    <ul class="divide-y divide-twende-line dark:divide-white/10">
                        @foreach ($quote->lines as $line)
                            <li class="grid gap-3 p-4 md:grid-cols-[minmax(0,1fr)_8rem_8rem_8rem_auto] md:items-center">
                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $line->item->product) }}" class="font-semibold hover:text-twende-red">{{ $line->item->product->name }}</a>
                                    @if ($line->item->variant)
                                        <p class="text-sm text-twende-muted">{{ $line->item->variant->name }}</p>
                                    @endif
                                    <p class="text-sm text-twende-muted">{{ $line->item->product->shop?->name }}</p>
                                    @if ($line->short)
                                        <p class="text-sm font-semibold text-twende-red">{{ __('commerce.stock_short') }}</p>
                                    @endif
                                </div>
                                <p>{{ \App\Support\Money::format($line->unitPrice, $quote->currency) }}</p>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('cart.items.update', $line->item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ max(1, $line->item->quantity - 1) }}">
                                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-twende-line font-semibold dark:border-white/15" @disabled($line->item->quantity <= 1) aria-label="−">−</button>
                                    </form>
                                    <span class="min-w-8 text-center font-semibold" aria-live="polite">{{ $line->item->quantity }}</span>
                                    <form method="POST" action="{{ route('cart.items.update', $line->item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $line->item->quantity + 1 }}">
                                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-twende-line font-semibold dark:border-white/15" @disabled($line->item->quantity >= $line->available) aria-label="+">+</button>
                                    </form>
                                </div>
                                <p class="font-semibold">{{ \App\Support\Money::format($line->lineTotal, $quote->currency) }}</p>
                                <form method="POST" action="{{ route('cart.items.destroy', $line->item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-twende-red">{{ __('commerce.remove') }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-twende-line p-4 dark:border-white/10">
                        <form method="POST" action="{{ route('cart.coupon') }}" class="flex min-w-0 flex-1 gap-2">
                            @csrf
                            <label class="sr-only" for="coupon">{{ __('commerce.coupon') }}</label>
                            <input id="coupon" name="coupon" value="{{ old('coupon', $cart?->coupon_code) }}" class="h-11 min-w-0 flex-1 rounded-full border border-twende-line px-4 dark:border-white/15 dark:bg-twende-night" placeholder="{{ __('commerce.coupon') }}">
                            <button type="submit" class="h-11 shrink-0 rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('commerce.apply') }}</button>
                        </form>
                        <form method="POST" action="{{ route('cart.clear') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-twende-red">{{ __('commerce.clear') }}</button>
                        </form>
                    </div>
                </div>
                <aside class="h-fit rounded-3xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card lg:sticky lg:top-24">
                    <h2 class="font-semibold">{{ __('commerce.summary') }}</h2>
                    <div class="mt-4">
                        <x-quote-summary :quote="$quote" />
                    </div>
                    @auth
                        @if ($quote->blocked)
                            <button type="button" disabled class="mt-4 inline-flex h-12 w-full cursor-not-allowed items-center justify-center rounded-full bg-twende-light text-sm font-semibold text-twende-muted">{{ __('commerce.checkout') }}</button>
                        @else
                            <x-button :href="route('checkout.create')" variant="cart" class="mt-4 w-full">{{ __('commerce.checkout') }}</x-button>
                        @endif
                    @else
                        <x-button :href="route('login')" class="mt-4 w-full">{{ __('commerce.login_to_checkout') }}</x-button>
                    @endauth
                </aside>
            </div>
        @endif
    </div>
</x-layouts.storefront>
