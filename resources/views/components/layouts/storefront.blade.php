@props([
    'title' => null,
    'description' => null,
])

<x-layouts.base :title="$title" :description="$description">
    <x-site-header />
    <main id="contenu" class="flex-1">
        {{ $slot }}
    </main>
    <x-site-footer />
</x-layouts.base>
