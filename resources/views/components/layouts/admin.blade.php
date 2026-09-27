@props([
    'title' => null,
    'description' => null,
])

<x-layouts.workspace area="admin" :title="$title" :description="$description">
    {{ $slot }}
</x-layouts.workspace>
