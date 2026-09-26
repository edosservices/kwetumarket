<x-layouts.dashboard :title="__('ui.nav.admin')">
    <x-brand-logo size="sm" class="mb-6" />
    <h1 class="text-2xl font-bold">{{ __('ui.nav.admin') }}</h1>
    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ __('ui.dashboard.admin_intro') }}</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            __('ui.catalog.admin_links.categories') => route('admin.categories.index'),
            __('ui.catalog.admin_links.brands') => route('admin.brands.index'),
            __('ui.catalog.admin_links.vendors') => route('admin.vendors.index'),
            __('ui.catalog.admin_links.shops') => route('admin.shops.index'),
            __('ui.catalog.admin_links.products') => route('admin.products.index'),
            __('ui.catalog.admin_links.inventory') => route('admin.inventory.index'),
            __('commerce.users') => route('admin.users.index'),
            __('commerce.orders') => route('admin.orders.index'),
            __('commerce.delivery') => route('admin.deliveries.index'),
            __('commerce.withdraw') => route('admin.withdrawals.index'),
            __('commerce.dispute') => route('admin.disputes.index'),
            __('commerce.refund') => route('admin.refunds.index'),
            __('commerce.ads') => route('admin.ads.index'),
            __('commerce.certification') => route('admin.certifications.index'),
            __('commerce.analytics') => route('admin.analytics'),
            __('commerce.settings') => route('admin.settings'),
        ] as $label => $href)
            <a href="{{ $href }}" class="rounded-2xl border border-twende-line p-5 hover:border-twende-red dark:border-white/10">
                <h2 class="font-semibold">{{ $label }}</h2>
            </a>
        @endforeach
    </div>
</x-layouts.dashboard>
