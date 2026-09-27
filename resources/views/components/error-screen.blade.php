@props([
    'code',
    'title',
    'body',
])

<x-layouts.base :title="$title">
    <main id="contenu" class="flex flex-1 flex-col items-center justify-center px-4 py-16 text-center">
        <x-brand-logo size="lg" :href="route('home')" />
        <p class="mt-8 text-sm font-semibold uppercase tracking-wide text-twende-red">{{ $code }}</p>
        <h1 class="mt-2 text-3xl font-bold">{{ $title }}</h1>
        <p class="mt-3 max-w-md text-sm leading-relaxed text-twende-muted">{{ $body }}</p>
        <div class="mt-6">
            <x-button :href="route('home')">{{ __('ui.errors.home') }}</x-button>
        </div>
    </main>
</x-layouts.base>
