@props([
    'title' => null,
    'description' => null,
])

<x-layouts.workspace area="customer" :title="$title" :description="$description">
    {{ $slot }}
</x-layouts.workspace>
