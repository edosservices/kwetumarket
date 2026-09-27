@props(['label', 'value', 'href' => null])

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class('block rounded-lg border border-twende-line bg-white p-3 dark:border-white/10 dark:bg-twende-night-card') }}>
    <p class="text-xs font-medium text-twende-muted">{{ $label }}</p>
    <p class="mt-1 text-xl font-bold text-twende-dark dark:text-white">{{ $value }}</p>
</{{ $tag }}>
