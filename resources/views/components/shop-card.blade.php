@props([
    'name',
    'href' => '#',
    'description' => null,
    'location' => null,
])

<article {{ $attributes->class('flex h-full flex-col rounded-2xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card') }}>
    <a href="{{ $href }}" class="flex h-full flex-col gap-3">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-twende-green/10 text-twende-green">
            <x-icon name="shop" class="h-7 w-7" />
        </div>
        <h3 class="text-lg font-semibold">{{ $name }}</h3>
        @if ($location)
            <p class="text-sm text-twende-muted">{{ $location }}</p>
        @endif
        @if ($description)
            <p class="text-sm leading-relaxed text-twende-muted">{{ $description }}</p>
        @endif
    </a>
</article>
