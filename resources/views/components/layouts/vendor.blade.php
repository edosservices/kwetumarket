@props([
    'title' => null,
    'description' => null,
])

<x-layouts.workspace area="vendor" :title="$title" :description="$description">
    {{ $slot }}
</x-layouts.workspace>
