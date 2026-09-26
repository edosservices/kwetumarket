@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-full font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-twende-red focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:focus-visible:ring-offset-twende-night';
    $variants = [
        'primary' => 'bg-twende-red text-white hover:bg-twende-red-dark',
        'secondary' => 'bg-twende-green text-white hover:bg-twende-green-dark',
        'cart' => 'bg-twende-green-bright text-white hover:bg-twende-green',
        'outline' => 'border border-twende-line bg-white text-twende-dark hover:border-twende-red hover:text-twende-red dark:border-white/15 dark:bg-transparent dark:text-white',
        'ghost' => 'text-twende-dark hover:bg-twende-light dark:text-white dark:hover:bg-white/10',
        'danger' => 'bg-twende-red text-white hover:bg-twende-red-dark',
    ];
    $sizes = [
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-11 px-5 text-sm',
        'lg' => 'h-12 px-6 text-base',
    ];
    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
