@php
    $inputId = 'search-'.$this->getId();
    $compact = $variant === 'header';
@endphp

<div class="w-full" x-data="{ open: {{ $errors->has('image') ? 'true' : 'false' }}, lat: '', lng: '', located: false }">
    @if ($errors->has('image'))
        <x-alert variant="error" class="mb-3">{{ $errors->first('image') }}</x-alert>
    @endif

    <div class="flex w-full items-center gap-2">
        <form wire:submit="search" class="flex min-w-0 flex-1 items-center gap-2" role="search">
            <label class="sr-only" for="{{ $inputId }}">{{ __('ui.nav.search') }}</label>
            <input
                id="{{ $inputId }}"
                type="search"
                wire:model="query"
                maxlength="120"
                placeholder="{{ __('ui.nav.search_placeholder') }}"
                class="{{ $compact ? 'h-10' : 'h-12' }} min-w-0 flex-1 rounded-full border border-twende-line bg-white px-4 text-sm text-twende-dark outline-none focus:border-twende-red focus:ring-2 focus:ring-twende-red/20 dark:border-white/15 dark:bg-twende-night dark:text-white"
            >
            <button type="submit" class="{{ $compact ? 'h-10 px-4 text-sm' : 'h-12 px-5' }} inline-flex shrink-0 items-center justify-center rounded-full bg-twende-red font-semibold text-white hover:bg-twende-red-dark">
                <span wire:loading.remove wire:target="search">{{ __('ui.nav.search') }}</span>
                <span wire:loading wire:target="search"><x-loading class="h-4 w-4 text-white" /></span>
            </button>
        </form>
        <button type="button" class="{{ $compact ? 'h-10 w-10' : 'h-12 w-12' }} inline-flex shrink-0 items-center justify-center rounded-full border border-twende-line bg-white text-twende-dark hover:border-twende-red hover:text-twende-red dark:border-white/15 dark:bg-twende-night dark:text-white" x-on:click="open = true" aria-haspopup="dialog" aria-label="{{ __('ui.smart.by_image') }}">
            <x-icon name="camera" />
        </button>
        <a href="{{ route('nearby') }}" class="{{ $compact ? 'h-10 w-10' : 'h-12 w-12' }} inline-flex shrink-0 items-center justify-center rounded-full border border-twende-line bg-white text-twende-dark hover:border-twende-green hover:text-twende-green dark:border-white/15 dark:bg-twende-night dark:text-white" aria-label="{{ __('ui.smart.nearby') }}">
            <x-icon name="pin" />
        </a>
    </div>
    <div wire:loading wire:target="search" class="mt-3 grid gap-2" aria-hidden="true">
        <div class="h-16 animate-pulse rounded-2xl bg-twende-light dark:bg-white/10"></div>
        <div class="h-16 animate-pulse rounded-2xl bg-twende-light dark:bg-white/10"></div>
    </div>

    @unless ($compact)
        <div class="mt-3 flex flex-wrap gap-3 text-sm">
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
                    <input x-ref="photo" type="file" name="image" accept="image/jpeg,image/png,image/webp" capture="environment" class="sr-only" x-on:change="if ($event.target.files.length) $el.form.requestSubmit()">
                    <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-twende-red px-5 text-sm font-semibold text-white" x-on:click="$refs.photo.setAttribute('capture', 'environment'); $refs.photo.click()">
                        <x-icon name="camera" class="h-5 w-5" /> {{ __('ui.smart.take_photo') }}
                    </button>
                    <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-full border border-twende-line px-5 text-sm font-semibold dark:border-white/15" x-on:click="$refs.photo.removeAttribute('capture'); $refs.photo.click()">
                        {{ __('ui.smart.choose_image') }}
                    </button>
                </form>
            </div>
        </div>
    </template>
</div>
