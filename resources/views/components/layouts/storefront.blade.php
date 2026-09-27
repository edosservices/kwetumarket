@props([
    'title' => null,
    'description' => null,
])

<x-layouts.base :title="$title" :description="$description">
    <x-site-header />
    <div class="flex flex-1 flex-col pb-24 lg:pb-0">
        <main id="contenu" class="flex-1 bg-twende-light dark:bg-twende-night">
            <div class="mx-auto max-w-[100rem] px-3 pt-3 sm:px-4">
                <x-flash />
            </div>
            {{ $slot }}
        </main>
        <x-site-footer />
    </div>
    <x-mobile-nav />
</x-layouts.base>
