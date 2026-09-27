@php
    $inputId = 'search-'.$this->getId();
    $compact = $variant === 'header';
    $hasSuggestions = $suggesting && (
        $suggestions['products']->isNotEmpty()
        || $suggestions['categories']->isNotEmpty()
        || $suggestions['vendors']->isNotEmpty()
    );
@endphp

<div class="relative w-full min-w-0" x-data="{ open: {{ $errors->has('image') ? 'true' : 'false' }}, suggest: false, lat: '', lng: '', located: false }">
    @if ($errors->has('image'))
        <x-alert variant="error" class="mb-3">{{ $errors->first('image') }}</x-alert>
    @endif

    <div class="flex w-full min-w-0 items-center gap-1.5">
        <form wire:submit="search" class="flex min-w-0 flex-1 items-stretch overflow-hidden rounded-lg border border-twende-line bg-white focus-within:ring-2 focus-within:ring-twende-red/30 dark:border-white/15 dark:bg-twende-night" role="search">
            <label class="sr-only" for="category-{{ $inputId }}">{{ __('ui.store.all_categories') }}</label>
            <select
                id="category-{{ $inputId }}"
                wire:model="category"
                class="{{ $compact ? 'h-10 max-w-[7.5rem] text-[11px] sm:max-w-[9.5rem] sm:text-xs' : 'h-11 max-w-[11rem] text-sm' }} hidden shrink-0 border-r border-twende-line bg-twende-light px-2 font-medium text-twende-dark outline-none sm:block dark:border-white/15 dark:bg-white/5 dark:text-white"
            >
                <option value="">{{ __('ui.store.all_categories') }}</option>
                @foreach ($roots as $root)
                    <option value="{{ $root->id }}">{{ $root->name }}</option>
                @endforeach
            </select>
            <label class="sr-only" for="{{ $inputId }}">{{ __('ui.nav.search') }}</label>
            <input
                id="{{ $inputId }}"
                type="search"
                wire:model.live.debounce.300ms="query"
                maxlength="120"
                placeholder="{{ $compact ? __('ui.store.search_query') : __('ui.store.search_wide') }}"
                x-on:focus="suggest = true"
                class="{{ $compact ? 'h-10' : 'h-11' }} min-w-0 flex-1 border-0 bg-transparent px-3 text-sm text-twende-dark outline-none dark:text-white"
            >
            <button type="submit" class="{{ $compact ? 'h-10 gap-1 px-2.5 text-xs sm:px-3' : 'h-11 gap-1.5 px-4 text-sm' }} inline-flex shrink-0 items-center justify-center bg-twende-red font-bold text-white hover:bg-twende-red-dark" aria-label="{{ __('ui.nav.search') }}">
                <span wire:loading.remove wire:target="search" class="inline-flex items-center gap-1">
                    <x-icon name="search" class="h-4 w-4" />
                    <span class="hidden sm:inline">{{ __('ui.nav.search') }}</span>
                </span>
                <span wire:loading wire:target="search"><x-loading class="h-4 w-4 text-white" /></span>
            </button>
        </form>
        <button type="button" class="{{ $compact ? 'h-10 w-9' : 'h-11 w-11' }} inline-flex shrink-0 items-center justify-center rounded-lg border border-twende-line bg-white text-twende-dark hover:border-twende-red hover:text-twende-red dark:border-white/15 dark:bg-twende-night dark:text-white" x-on:click="open = true" aria-haspopup="dialog" aria-label="{{ __('ui.smart.by_image') }}">
            <x-icon name="camera" class="h-4 w-4" />
        </button>
        <a href="{{ route('nearby') }}" class="{{ $compact ? 'hidden h-10 w-9 sm:inline-flex' : 'inline-flex h-11 w-11' }} shrink-0 items-center justify-center rounded-lg border border-twende-line bg-white text-twende-dark hover:border-twende-green hover:text-twende-green dark:border-white/15 dark:bg-twende-night dark:text-white" aria-label="{{ __('ui.smart.nearby') }}">
            <x-icon name="pin" class="h-4 w-4" />
        </a>
    </div>

    @if ($hasSuggestions)
        <div
            x-show="suggest"
            x-cloak
            x-on:click.outside="suggest = false"
            class="absolute left-0 right-0 top-full z-50 mt-1 max-h-80 overflow-y-auto rounded-lg border border-twende-line bg-white py-2 text-sm shadow-lg dark:border-white/10 dark:bg-twende-night-card"
        >
            <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-twende-muted">{{ __('ui.store.suggestions') }}</p>
            <a href="{{ route('search', ['q' => $query, 'category' => $category ?: null]) }}" class="block px-3 py-1.5 font-medium hover:bg-twende-light dark:hover:bg-white/5">{{ $query }}</a>
            @if ($suggestions['products']->isNotEmpty())
                <p class="px-3 pt-2 text-[11px] font-semibold uppercase tracking-wide text-twende-muted">{{ __('ui.store.suggest_products') }}</p>
                @foreach ($suggestions['products'] as $product)
                    <a href="{{ route('products.show', $product) }}" class="block truncate px-3 py-1.5 hover:bg-twende-light dark:hover:bg-white/5">{{ $product->name }}</a>
                @endforeach
            @endif
            @if ($suggestions['categories']->isNotEmpty())
                <p class="px-3 pt-2 text-[11px] font-semibold uppercase tracking-wide text-twende-muted">{{ __('ui.store.suggest_categories') }}</p>
                @foreach ($suggestions['categories'] as $categoryRow)
                    <a href="{{ route('categories.show', $categoryRow) }}" class="block truncate px-3 py-1.5 hover:bg-twende-light dark:hover:bg-white/5">{{ $categoryRow->name }}</a>
                @endforeach
            @endif
            @if ($suggestions['vendors']->isNotEmpty())
                <p class="px-3 pt-2 text-[11px] font-semibold uppercase tracking-wide text-twende-muted">{{ __('ui.store.suggest_vendors') }}</p>
                @foreach ($suggestions['vendors'] as $vendor)
                    @php $shop = $vendor->shops->first(); @endphp
                    <a href="{{ $shop ? route('shops.show', $shop) : route('shops.index') }}" class="block truncate px-3 py-1.5 hover:bg-twende-light dark:hover:bg-white/5">{{ $vendor->business_name ?: $vendor->user?->name }}</a>
                @endforeach
            @endif
        </div>
    @endif

    @unless ($compact)
        <div class="mt-2 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('nearby') }}" class="font-semibold text-twende-green hover:underline">{{ __('ui.smart.nearby') }}</a>
            <a href="{{ route('promotions') }}" class="font-semibold text-twende-red hover:underline">{{ __('ui.smart.promotions_link') }}</a>
        </div>
    @endunless

    <template x-teleport="body">
        <div x-show="open" x-cloak x-on:keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="image-search-title-{{ $inputId }}">
            <div class="absolute inset-0 bg-twende-dark/50" x-on:click="open = false"></div>
            <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl dark:bg-twende-night-card">
                <div class="flex items-start justify-between gap-4">
                    <h2 id="image-search-title-{{ $inputId }}" class="text-lg font-semibold">{{ __('ui.smart.image_title') }}</h2>
                    <button type="button" class="rounded-full p-1 text-twende-muted" x-on:click="open = false" aria-label="{{ __('ui.nav.close') }}">
                        <x-icon name="close" />
                    </button>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-twende-muted">{{ __('ui.smart.location_explainer') }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" class="inline-flex h-10 items-center rounded-full bg-twende-green px-4 text-sm font-semibold text-white" x-on:click="navigator.geolocation ? navigator.geolocation.getCurrentPosition((position) => { lat = position.coords.latitude.toFixed(4); lng = position.coords.longitude.toFixed(4); located = true; }, () => { located = false; }) : located = false">
                        {{ __('ui.smart.allow_location') }}
                    </button>
                    <button type="button" class="inline-flex h-10 items-center rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15" x-on:click="lat = ''; lng = ''; located = false">
                        {{ __('ui.smart.later') }}
                    </button>
                </div>
                <p class="mt-3 text-sm text-twende-green" x-show="located" x-cloak>{{ __('ui.smart.location_ready') }}</p>
                <form method="POST" action="{{ route('search.image.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-3">
                    @csrf
                    <input type="hidden" name="lat" x-bind:value="lat">
                    <input type="hidden" name="lng" x-bind:value="lng">
                    <input x-ref="photo" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" x-on:change="if ($event.target.files.length) $el.form.requestSubmit()">
                    <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-twende-green px-5 text-sm font-semibold text-white" x-on:click="$refs.photo.removeAttribute('capture'); $refs.photo.click()">
                        {{ __('experience.choose_image') }}
                    </button>
                    <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-full border border-twende-line px-5 text-sm font-semibold dark:border-white/15" x-on:click="$refs.photo.setAttribute('capture', 'environment'); $refs.photo.click()">
                        <x-icon name="camera" class="h-5 w-5" /> {{ __('experience.take_photo') }}
                    </button>
                </form>
            </div>
        </div>
    </template>
</div>
