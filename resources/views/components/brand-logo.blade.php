@props([
    'size' => 'md',
    'href' => null,
])

@php
    $heights = [
        'sm' => 'h-8 sm:h-10',
        'md' => 'h-10',
        'lg' => 'h-16',
        'xl' => 'h-24 sm:h-28',
    ];
    $height = $heights[$size] ?? $heights['md'];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class('inline-flex max-w-full shrink-0 items-center rounded-xl bg-white px-1.5 py-1') }}>
@else
    <span {{ $attributes->class('inline-flex max-w-full shrink-0 items-center rounded-xl bg-white px-1.5 py-1') }}>
@endif
        <img
            src="{{ asset(config('twende.logo')) }}"
            alt="{{ config('twende.name') }}"
            width="1774"
            height="887"
            class="{{ $height }} w-auto max-w-full object-contain"
            decoding="async"
        >
@if ($href)
    </a>
@else
    </span>
@endif
