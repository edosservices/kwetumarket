@props([
    'variant' => 'info',
])

@php
    $styles = [
        'success' => 'border-twende-green/30 bg-twende-green/10 text-twende-green-dark dark:text-twende-green-bright',
        'error' => 'border-twende-red/30 bg-twende-red/10 text-twende-red',
        'warning' => 'border-twende-red/20 bg-twende-light text-twende-dark dark:bg-white/5 dark:text-white',
        'info' => 'border-twende-line bg-twende-light text-twende-dark dark:border-white/10 dark:bg-white/5 dark:text-white',
    ];
@endphp

<div {{ $attributes->class('rounded-2xl border px-4 py-3 text-sm '.($styles[$variant] ?? $styles['info'])) }} role="status">
    {{ $slot }}
</div>
