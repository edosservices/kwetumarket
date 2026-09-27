@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class('flex flex-col items-center justify-center rounded-2xl border border-dashed border-twende-line bg-twende-light/70 px-6 py-10 text-center dark:border-white/15 dark:bg-white/5') }}>
    @isset($icon)
        <div class="text-twende-green">{{ $icon }}</div>
    @endisset
    <h3 class="text-base font-semibold text-twende-dark dark:text-white">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-md text-sm leading-relaxed text-twende-muted">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
