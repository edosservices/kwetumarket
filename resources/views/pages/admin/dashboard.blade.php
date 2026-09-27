<x-layouts.admin :title="__('ui.areas.admin')">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ __('ui.areas.admin') }}</h1>
    <p class="mt-2 max-w-2xl text-sm text-twende-muted">{{ __('ui.dashboard.admin_intro') }}</p>
    @php
        $cards = [
            'users' => ['value' => $stats['users'], 'permission' => 'users.view'],
            'customers' => ['value' => $stats['customers'], 'permission' => 'analytics.customers'],
            'vendors' => ['value' => $stats['vendors'], 'permission' => 'vendors.view'],
            'shops' => ['value' => $stats['shops'], 'permission' => 'shops.view'],
            'agents' => ['value' => $stats['agents'], 'permission' => 'delivery.view'],
            'products' => ['value' => $stats['products'], 'permission' => 'products.view'],
            'orders' => ['value' => $stats['orders'], 'permission' => 'orders.view'],
            'revenue' => ['value' => $stats['revenue_label'], 'permission' => 'finance.view'],
            'commissions' => ['value' => $stats['commissions_label'], 'permission' => 'finance.commissions'],
            'payments' => ['value' => $stats['payments'], 'permission' => 'finance.view'],
            'refunds' => ['value' => $stats['refunds'], 'permission' => 'finance.refunds'],
            'pending_vendors' => ['value' => $stats['pending_vendors'], 'permission' => 'vendors.view'],
            'pending_shops' => ['value' => $stats['pending_shops'], 'permission' => 'shops.view'],
            'pending_products' => ['value' => $stats['pending_products'], 'permission' => 'products.view'],
            'pending_tickets' => ['value' => $stats['pending_tickets'], 'permission' => 'support.view'],
        ];
    @endphp
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $key => $card)
            @can($card['permission'])
                <x-stat-card :label="__('ui.stats.'.$key)" :value="$card['value']" />
            @endcan
        @endforeach
    </div>
</x-layouts.admin>
