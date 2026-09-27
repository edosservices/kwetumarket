@props([
    'title' => null,
    'description' => null,
])

<x-layouts.workspace area="delivery" :title="$title" :description="$description">
    {{ $slot }}
</x-layouts.workspace>
