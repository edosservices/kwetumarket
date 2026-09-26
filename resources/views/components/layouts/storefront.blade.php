@props([
    'title' => null,
    'description' => null,
])

<x-layouts.base :title="$title" :description="$description">
    <x-site-header />
    <div class="flex flex-1 flex-col pb-24 lg:pb-0">
        <main id="contenu" class="flex-1">
            <div class="mx-auto max-w-7xl px-4 pt-4">
                <x-flash />
            </div>
            {{ $slot }}
        </main>
        <x-site-footer />
    </div>
    <x-mobile-nav />
</x-layouts.base>
