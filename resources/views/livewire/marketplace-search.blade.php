@php
    $inputId = 'search-'.$this->getId();
    $compact = $variant === 'header';
@endphp

<form wire:submit="search" class="flex w-full items-center gap-2" role="search">
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
