<x-layouts.dashboard :title="__('ui.nav.vendor')">
    <x-brand-logo size="sm" class="mb-6" />
    <h1 class="text-2xl font-bold">{{ __('ui.nav.vendor') }}</h1>
    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ __('ui.dashboard.vendor_intro') }}</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach ([
            __('ui.dashboard.shop') => route('vendor.shop.edit'),
            __('ui.dashboard.products') => route('vendor.products.index'),
            __('ui.dashboard.stock') => route('vendor.inventory.index'),
            __('commerce.orders') => route('vendor.orders.index'),
            __('commerce.wallet') => route('vendor.wallet'),
            __('commerce.subscription') => route('vendor.subscription'),
            __('commerce.certification') => route('vendor.certification'),
            __('commerce.ads') => route('vendor.ads'),
            __('commerce.promotions') => route('vendor.promotions'),
            __('commerce.analytics') => route('vendor.analytics'),
            __('experience.dropship') => route('vendor.dropship.index'),
        ] as $label => $href)
            <a href="{{ $href }}" class="rounded-2xl border border-twende-line p-5 hover:border-twende-green dark:border-white/10">
                <h2 class="font-semibold">{{ $label }}</h2>
            </a>
        @endforeach
    </div>
</x-layouts.dashboard>
