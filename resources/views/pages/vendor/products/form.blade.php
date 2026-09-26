<x-layouts.dashboard :title="$product->exists ? $product->name : __('ui.catalog.product_create')">
    @if (session('status'))
        <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $product->exists ? $product->name : __('ui.catalog.product_create') }}</h1>
        <x-button :href="$cancel" variant="outline" size="sm">{{ __('ui.catalog.back') }}</x-button>
    </div>
    <form method="POST" action="{{ $action }}" class="grid max-w-3xl gap-4">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif
        <x-select name="shop_id" :label="__('ui.catalog.shop')" :selected="old('shop_id', $product->shop_id)" :options="$shops->all()" />
        <x-select name="category_id" :label="__('ui.catalog.category')" :selected="old('category_id', $product->category_id)" :options="$categories->all()" />
        <x-select name="brand_id" :label="__('ui.catalog.brand')" :selected="old('brand_id', $product->brand_id)" :options="['' => __('ui.catalog.none')] + $brands->all()" />
        <x-input name="name" :label="__('ui.catalog.name')" :value="old('name', $product->name)" />
        <x-input name="slug" :label="__('ui.catalog.slug')" :value="old('slug', $product->slug)" :hint="__('ui.catalog.slug_hint')" />
        <x-input name="sku" :label="__('ui.catalog.sku')" :value="old('sku', $product->sku)" />
        <x-textarea name="description" :label="__('ui.catalog.description')" :value="old('description', $product->description)" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-input name="price" :label="__('ui.catalog.price')" :value="old('price', $product->exists ? \App\Support\Money::toInput((int) $product->price) : '')" inputmode="decimal" />
            <x-input name="compare_at_price" :label="__('ui.catalog.compare_price')" :value="old('compare_at_price', $product->compare_at_price ? \App\Support\Money::toInput((int) $product->compare_at_price) : '')" inputmode="decimal" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-select name="currency" :label="__('ui.catalog.currency')" :selected="old('currency', $product->currency ?? 'CDF')" :options="array_combine(config('twende.currencies'), config('twende.currencies'))" />
            <x-select name="condition" :label="__('ui.catalog.condition')" :selected="old('condition', $product->condition?->value ?? 'new')" :options="['new' => __('ui.catalog.conditions.new'), 'used' => __('ui.catalog.conditions.used'), 'refurbished' => __('ui.catalog.conditions.refurbished')]" />
        </div>
        <x-select name="status" :label="__('ui.catalog.status')" :selected="old('status', $product->status?->value ?? 'draft')" :options="$moderate
            ? ['draft' => __('ui.catalog.product_statuses.draft'), 'pending' => __('ui.catalog.product_statuses.pending'), 'published' => __('ui.catalog.product_statuses.published'), 'rejected' => __('ui.catalog.product_statuses.rejected'), 'archived' => __('ui.catalog.product_statuses.archived')]
            : ['draft' => __('ui.catalog.product_statuses.draft'), 'pending' => __('ui.catalog.product_statuses.pending'), 'archived' => __('ui.catalog.product_statuses.archived')]" />
        <x-input name="weight" type="number" :label="__('ui.catalog.weight')" :value="old('weight', $product->weight)" min="0" />
        @unless ($product->exists)
            <x-input name="initial_stock" type="number" :label="__('ui.catalog.initial_stock')" :value="old('initial_stock', 0)" min="0" />
        @endunless
        <x-button type="submit">{{ __('ui.catalog.save') }}</x-button>
    </form>

    @if ($product->exists && ! $moderate)
        <section class="mt-10 max-w-3xl">
            <h2 class="text-lg font-semibold">{{ __('ui.catalog.images') }}</h2>
            <form method="POST" action="{{ route('vendor.products.images.store', $product) }}" enctype="multipart/form-data" class="mt-4 grid gap-3">
                @csrf
                <x-input name="image" type="file" :label="__('ui.catalog.image')" accept="image/jpeg,image/png,image/webp,image/gif" />
                <x-input name="alt_text" :label="__('ui.catalog.alt')" :value="old('alt_text')" />
                <x-button type="submit" variant="secondary" size="sm">{{ __('ui.catalog.upload') }}</x-button>
            </form>
            <ul class="mt-4 space-y-3">
                @foreach ($product->images as $image)
                    <li class="flex items-center gap-3 rounded-2xl border border-twende-line p-3 dark:border-white/10">
                        <img src="{{ $image->url() }}" alt="" class="h-16 w-16 rounded-xl object-contain">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm">{{ $image->alt_text }}</p>
                            @if ($image->is_primary)
                                <x-badge variant="green">{{ __('ui.catalog.primary') }}</x-badge>
                            @endif
                        </div>
                        @unless ($image->is_primary)
                            <form method="POST" action="{{ route('vendor.products.images.primary', [$product, $image]) }}">
                                @csrf
                                @method('PUT')
                                <x-button type="submit" variant="outline" size="sm">{{ __('ui.catalog.make_primary') }}</x-button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('vendor.products.images.destroy', [$product, $image]) }}">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">{{ __('ui.catalog.delete') }}</x-button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="mt-10 max-w-3xl">
            <h2 class="text-lg font-semibold">{{ __('ui.catalog.variants') }}</h2>
            <form method="POST" action="{{ route('vendor.products.variants.store', $product) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <x-input name="name" :label="__('ui.catalog.name')" :value="old('name')" />
                <x-input name="sku" :label="__('ui.catalog.sku')" :value="old('sku')" />
                <x-input name="price" :label="__('ui.catalog.price')" :value="old('price')" inputmode="decimal" />
                <x-input name="stock" type="number" :label="__('ui.catalog.initial_stock')" :value="old('stock', 0)" min="0" />
                <x-input name="attributes[color]" :label="__('ui.catalog.attr_color')" :value="old('attributes.color')" />
                <x-input name="attributes[size]" :label="__('ui.catalog.attr_size')" :value="old('attributes.size')" />
                <x-input name="attributes[capacity]" :label="__('ui.catalog.attr_capacity')" :value="old('attributes.capacity')" />
                <x-input name="attributes[model]" :label="__('ui.catalog.attr_model')" :value="old('attributes.model')" />
                <x-select name="status" :label="__('ui.catalog.status')" selected="active" :options="['active' => __('ui.catalog.active'), 'inactive' => __('ui.catalog.inactive')]" />
                <div class="sm:col-span-2">
                    <x-button type="submit" variant="secondary" size="sm">{{ __('ui.catalog.variant_add') }}</x-button>
                </div>
            </form>
            <ul class="mt-4 divide-y divide-twende-line rounded-2xl border border-twende-line dark:divide-white/10 dark:border-white/10">
                @forelse ($product->variants as $variant)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                        <span class="font-medium">{{ $variant->name }}</span>
                        <span class="text-twende-muted">{{ $variant->sku }}</span>
                        <span>{{ $variant->stock }}</span>
                        <form method="POST" action="{{ route('vendor.products.variants.destroy', [$product, $variant]) }}">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="outline" size="sm">{{ __('ui.catalog.delete') }}</x-button>
                        </form>
                    </li>
                @empty
                    <li class="px-4 py-3 text-sm text-twende-muted">{{ __('ui.catalog.no_variants') }}</li>
                @endforelse
            </ul>
        </section>
    @endif
</x-layouts.dashboard>
