@props([
    'label',
    'value',
])

<article {{ $attributes->class('rounded-2xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card') }}>
    <p class="text-sm text-twende-muted">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold text-twende-dark dark:text-white">{{ $value }}</p>
</article>
