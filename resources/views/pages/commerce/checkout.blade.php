<x-layouts.storefront :title="__('commerce.checkout')">
    <div class="mx-auto max-w-5xl px-4 py-8 pb-28">
        <x-flash />
        <ol class="mb-6 flex flex-wrap gap-2 text-xs font-semibold uppercase text-twende-muted">
            @foreach (['cart_title', 'address_title', 'zone', 'payment', 'tracking'] as $step)
                <li class="rounded-full bg-twende-light px-3 py-1 dark:bg-white/10">{{ __('commerce.'.$step) }}</li>
            @endforeach
        </ol>
        <h1 class="text-2xl font-bold">{{ __('commerce.checkout') }}</h1>
        @if ($quote->lines === [])
            <div class="mt-6">
                <x-empty-state :title="__('commerce.cart_empty_title')" :description="__('commerce.cart_empty_body')">
                    <x-slot:action><x-button :href="route('products.index')">{{ __('commerce.browse') }}</x-button></x-slot:action>
                </x-empty-state>
            </div>
        @else
            <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]" x-data="{ sending: false }" x-on:submit="sending = true">
                @csrf
                <div class="space-y-6">
                    <section class="rounded-3xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card">
                        <h2 class="font-semibold">{{ __('commerce.address_title') }}</h2>
                        @if ($addresses->isNotEmpty())
                            <label class="mt-3 block text-sm" for="address_id">{{ __('commerce.saved_address') }}</label>
                            <select id="address_id" name="address_id" class="mt-1 h-11 w-full rounded-xl border border-twende-line bg-white px-3 dark:border-white/15 dark:bg-twende-night">
                                <option value="">{{ __('commerce.address_title') }}</option>
                                @foreach ($addresses as $saved)
                                    <option value="{{ $saved->id }}">{{ $saved->label }} — {{ $saved->address }}, {{ $saved->city }}</option>
                                @endforeach
                            </select>
                        @endif
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <label class="text-sm sm:col-span-2">{{ __('commerce.phone') }}
                                <input name="phone" value="{{ old('phone', auth()->user()->phone) }}" required class="mt-1 h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                            </label>
                            <label class="text-sm">{{ __('commerce.city') }}
                                <input name="city" value="{{ old('city', auth()->user()->city) }}" required class="mt-1 h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                            </label>
                            <label class="text-sm sm:col-span-2">{{ __('commerce.address') }}
                                <input name="address" value="{{ old('address', auth()->user()->address) }}" required class="mt-1 h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                            </label>
                            <label class="text-sm sm:col-span-2">{{ __('commerce.notes') }}
                                <textarea name="notes" class="mt-1 min-h-20 w-full rounded-xl border border-twende-line px-3 py-2 dark:border-white/15 dark:bg-twende-night">{{ old('notes') }}</textarea>
                            </label>
                            <label class="flex items-center gap-2 text-sm sm:col-span-2">
                                <input type="checkbox" name="save_address" value="1" class="h-4 w-4"> {{ __('commerce.save_address') }}
                            </label>
                        </div>
                    </section>
                    <section class="rounded-3xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card">
                        <h2 class="font-semibold">{{ __('commerce.zone') }}</h2>
                        <div class="mt-3 space-y-2">
                            @foreach ($zones as $zone)
                                <label class="flex items-center justify-between gap-3 rounded-2xl border border-twende-line px-3 py-3 dark:border-white/10">
                                    <span class="flex items-center gap-2"><input type="radio" name="delivery_zone_id" value="{{ $zone->id }}" @checked(old('delivery_zone_id', $selectedZone) == $zone->id) required> {{ $zone->name }}</span>
                                    <span class="font-semibold">{{ $zone->formattedFee() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                    <section class="rounded-3xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card">
                        <h2 class="font-semibold">{{ __('commerce.payment') }}</h2>
                        <div class="mt-3 space-y-2">
                            <label class="flex items-start gap-2 rounded-2xl border border-twende-line px-3 py-3 dark:border-white/10">
                                <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod') required>
                                <span>{{ __('commerce.pay_cod') }}</span>
                            </label>
                            <label class="flex items-start gap-2 rounded-2xl border border-twende-line px-3 py-3 dark:border-white/10">
                                <input type="radio" name="payment_method" value="sandbox" @checked(old('payment_method') === 'sandbox')>
                                <span>
                                    {{ __('commerce.pay_sandbox') }}
                                    <span class="mt-1 block text-sm text-twende-muted">{{ __('commerce.sandbox_note') }}</span>
                                </span>
                            </label>
                        </div>
                        <label class="mt-4 block text-sm">{{ __('commerce.coupon') }}
                            <input name="coupon" value="{{ old('coupon') }}" class="mt-1 h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                        </label>
                    </section>
                </div>
                <aside class="h-fit rounded-3xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card">
                    <h2 class="font-semibold">{{ __('commerce.summary') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($quote->lines as $line)
                            <li class="flex justify-between gap-3"><span class="min-w-0 truncate">{{ $line->item->product->name }} × {{ $line->item->quantity }}</span><span>{{ \App\Support\Money::format($line->lineTotal, $quote->currency) }}</span></li>
                        @endforeach
                    </ul>
                    <div class="mt-4"><x-quote-summary :quote="$quote" /></div>
                    <button type="submit" class="mt-4 inline-flex h-12 w-full items-center justify-center rounded-full bg-twende-green-bright text-sm font-semibold text-white hover:bg-twende-green disabled:opacity-60" x-bind:disabled="sending || {{ $quote->blocked ? 'true' : 'false' }}">
                        <span x-show="!sending">{{ __('commerce.place') }}</span>
                        <span x-show="sending" x-cloak>{{ __('commerce.placing') }}</span>
                    </button>
                </aside>
            </form>
        @endif
    </div>
</x-layouts.storefront>
