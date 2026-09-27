<x-layouts.dashboard :title="__('ui.nav.vendor')">
    <h1 class="text-xl font-bold sm:text-2xl">{{ __('ui.nav.vendor') }}</h1>
    <p class="mt-1 max-w-2xl text-sm text-twende-muted">{{ __('ui.dashboard.vendor_intro') }}</p>
    <div class="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
        <x-kpi :label="__('commerce.orders')" :value="$orderCount" :href="route('vendor.orders.index')" />
        <x-kpi :label="__('ui.catalog.products_title')" :value="$productCount" :href="route('vendor.products.index')" />
        <x-kpi :label="__('ui.dashboard.clients')" :value="$clientCount" :href="route('vendor.customers')" />
        <x-kpi :label="__('ui.dashboard.low_stock')" :value="$lowStock" :href="route('vendor.inventory.index')" />
    </div>
    <section class="mt-4 rounded-lg border border-twende-line bg-white p-4 dark:border-white/10 dark:bg-twende-night-card">
        <h2 class="text-sm font-bold">{{ __('ui.dashboard.revenue') }}</h2>
        @if ($revenue->isEmpty())
            <p class="mt-3 text-sm text-twende-muted">{{ __('ui.dashboard.no_sales') }}</p>
        @else
            <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ($revenue as $row)
                    <li class="rounded-md bg-twende-light px-3 py-2 dark:bg-white/5">
                        <p class="text-xs text-twende-muted">{{ $row->currency }}</p>
                        <p class="text-lg font-bold">{{ \App\Support\Money::format((int) $row->gross, $row->currency) }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
    @if ($series->isNotEmpty())
        @php $max = max(1, $series->max('count')); @endphp
        <section class="mt-4 rounded-lg border border-twende-line bg-white p-4 dark:border-white/10 dark:bg-twende-night-card">
            <h2 class="text-sm font-bold">{{ __('commerce.orders') }}</h2>
            <div class="mt-4 flex h-28 items-end gap-2">
                @foreach ($series as $bar)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-1">
                        <div class="flex h-20 w-full items-end">
                            <div class="w-full rounded-t bg-twende-green" style="height: {{ max(4, (int) round(($bar['count'] / $max) * 80)) }}px"></div>
                        </div>
                        <span class="truncate text-[10px] text-twende-muted">{{ $bar['label'] }}</span>
                        <span class="text-[10px] font-semibold">{{ $bar['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="rounded-lg border border-twende-line bg-white p-4 dark:border-white/10 dark:bg-twende-night-card">
            <h2 class="text-sm font-bold">{{ __('ui.dashboard.recent_sales') }}</h2>
            @if ($recent->isEmpty())
                <p class="mt-3 text-sm text-twende-muted">{{ __('ui.dashboard.no_sales') }}</p>
            @else
                <ul class="mt-3 divide-y divide-twende-line text-sm dark:divide-white/10">
                    @foreach ($recent as $order)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <a href="{{ route('vendor.orders.index') }}" class="font-semibold text-twende-red">{{ $order->number }}</a>
                            <span>{{ \App\Support\Money::format((int) $order->total, $order->currency) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
        <section class="rounded-lg border border-twende-line bg-white p-4 dark:border-white/10 dark:bg-twende-night-card">
            <h2 class="text-sm font-bold">{{ __('ui.home.popular') }}</h2>
            @if ($popular->isEmpty())
                <p class="mt-3 text-sm text-twende-muted">{{ __('ui.dashboard.no_popular') }}</p>
            @else
                <ul class="mt-3 divide-y divide-twende-line text-sm dark:divide-white/10">
                    @foreach ($popular as $product)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <span class="min-w-0 truncate font-medium">{{ $product->name }}</span>
                            <span class="shrink-0 text-twende-muted">{{ (int) $product->units_sold }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.dashboard>
