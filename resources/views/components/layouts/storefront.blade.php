@props([
    'title' => null,
    'description' => null,
])

<x-layouts.base :title="$title" :description="$description">
    <div class="flex w-full min-w-0 flex-1 flex-col">
        <x-site-header />
        <main id="contenu" class="min-w-0 flex-1 bg-twende-light dark:bg-twende-night">
            <div class="mx-auto max-w-[100rem] px-3 pt-3 sm:px-4">
                <x-flash />
            </div>
            {{ $slot }}
        </main>
        <x-site-footer />
    </div>
    <x-mobile-nav />
    <x-help-panel />
</x-layouts.base>
