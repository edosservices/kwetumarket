@props([
    'variant' => 'neutral',
])

@php
    $styles = [
        'neutral' => 'bg-twende-light text-twende-dark dark:bg-white/10 dark:text-white',
        'red' => 'bg-twende-red/10 text-twende-red',
        'green' => 'bg-twende-green/10 text-twende-green-dark dark:text-twende-green-bright',
        'sponsored' => 'bg-twende-red text-white',
    ];
@endphp

<span {{ $attributes->class('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold '.($styles[$variant] ?? $styles['neutral'])) }}>
    {{ $slot }}
</span>
