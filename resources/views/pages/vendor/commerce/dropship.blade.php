<x-layouts.dashboard :title="__('experience.dropship')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('experience.dropship') }}</h1>
    <p class="mt-2 max-w-xl text-sm text-twende-muted">{{ __('commerce.dropship_help') }}</p>
    <form method="POST" action="{{ route('vendor.dropship.offer') }}" enctype="multipart/form-data" class="mt-6 grid max-w-3xl gap-3 md:grid-cols-2">
        @csrf
        <h2 class="font-semibold md:col-span-2">{{ __('operations.my_offers') }}</h2>
        <x-input name="name" :label="__('ui.catalog.name')" required />
        <x-input name="sku" :label="__('ui.catalog.sku')" required />
        <x-textarea name="description" :label="__('ui.catalog.description')" class="md:col-span-2" />
        <x-input name="price" :label="__('experience.supplier_price')" required />
        <x-input name="stock" type="number" :label="__('ui.catalog.stock')" value="0" min="0" required />
        <x-input name="lead_days" type="number" :label="__('operations.lead_days')" value="3" min="0" required />
        <x-input name="moq" type="number" :label="__('operations.moq')" value="1" min="1" required />
        <label class="text-sm">{{ __('ui.catalog.images') }} <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-1 block"></label>
        <button class="h-11 w-fit rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('operations.save') }}</button>
    </form>
    @if ($mine->isNotEmpty())
        <div class="mt-8 grid gap-4">
            @foreach ($mine as $offer)
                <form method="POST" action="{{ route('vendor.dropship.sync', $offer) }}" class="grid gap-3 rounded-2xl border border-twende-line p-4 dark:border-white/10 md:grid-cols-3">
                    @csrf
                    @method('PUT')
                    <p class="font-semibold md:col-span-3">{{ $offer->name }} · {{ $offer->sku }}</p>
                    <x-input name="price" :label="__('experience.supplier_price')" :value="\App\Support\Money::toInput((int) $offer->price)" />
                    <x-input name="stock" type="number" :label="__('ui.catalog.stock')" :value="$offer->stock" min="0" />
                    <x-select name="is_active" :label="__('ui.catalog.status')" :selected="$offer->is_active ? '1' : '0'" :options="['1' => __('ui.catalog.active'), '0' => __('ui.catalog.inactive')]" />
                    <button class="h-11 w-fit rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('operations.save') }}</button>
                </form>
            @endforeach
        </div>
    @endif
    <div class="mt-8 grid gap-4">
        <h2 class="font-semibold">{{ __('operations.supplier_catalog') }}</h2>
        @forelse ($offers as $offer)
            <form method="POST" action="{{ route('vendor.dropship.import', $offer) }}" class="grid gap-3 rounded-2xl border border-twende-line p-4 dark:border-white/10 md:grid-cols-2">
                @csrf
                <p class="font-semibold md:col-span-2">{{ $offer->name }} · {{ $offer->vendor?->business_name }} · {{ __('ui.catalog.stock') }} {{ $offer->stock }} · {{ __('operations.moq') }} {{ $offer->moq }}</p>
                <x-select name="shop_id" :label="__('ui.catalog.shop')" :options="$shops->all()" />
                <x-input name="price" :label="__('experience.reseller_price')" required />
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="stock_sync" value="1" checked> {{ __('operations.sync') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="publish" value="1"> {{ __('operations.publish') }}</label>
                <button class="h-10 w-fit rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('operations.import_offer') }}</button>
            </form>
        @empty
            <p class="text-sm text-twende-muted">{{ __('operations.supplier_catalog') }}</p>
        @endforelse
    </div>
    <div class="mt-6 grid gap-4">
        @forelse ($products as $product)
            <form method="POST" action="{{ route('vendor.dropship.update', $product) }}" class="grid gap-3 rounded-2xl border border-twende-line p-4 dark:border-white/10 md:grid-cols-2">
                @csrf
                @method('PUT')
                <h2 class="font-semibold md:col-span-2">{{ $product->name }}</h2>
                <x-input name="supplier_name" :label="__('commerce.supplier')" :value="old('supplier_name', $product->supplier_name)" />
                <x-input name="supplier_sku" :label="__('ui.catalog.sku')" :value="old('supplier_sku', $product->supplier_sku)" />
                <x-input name="supplier_price" :label="__('experience.supplier_price')" :value="old('supplier_price', $product->supplier_price ? \App\Support\Money::toInput((int) $product->supplier_price) : '')" inputmode="decimal" />
                <x-input name="price" :label="__('experience.reseller_price')" :value="old('price', \App\Support\Money::toInput((int) $product->price))" inputmode="decimal" />
                @if ($product->supplier_price)
                    <p class="text-sm md:col-span-2">{{ __('experience.margin') }} : {{ \App\Support\Money::format((int) $product->price - (int) $product->supplier_price, $product->currency) }}</p>
                @endif
                <button class="h-10 w-fit rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('ui.catalog.save') }}</button>
            </form>
        @empty
            <x-empty-state :title="__('ui.home.popular_empty')" />
        @endforelse
    </div>
</x-layouts.dashboard>
