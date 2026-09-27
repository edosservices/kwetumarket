@props([
    'name',
    'href' => '#',
    'count' => null,
    'image' => null,
])

<a href="{{ $href }}" {{ $attributes->class('twende-lift flex w-[6.5rem] shrink-0 flex-col items-center gap-1.5 rounded-lg border border-twende-line bg-white px-2 py-2.5 text-center transition hover:border-twende-green sm:w-[7.5rem] md:w-auto dark:border-white/10 dark:bg-twende-night-card') }}>
    <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-twende-light text-twende-green dark:bg-white/5">
        @if ($image)
            <img src="{{ $image }}" alt="" class="h-full w-full object-cover">
        @else
            <x-icon name="grid" class="h-5 w-5" />
        @endif
    </span>
    <span class="line-clamp-2 text-xs font-semibold leading-tight">{{ $name }}</span>
    @if (! is_null($count))
        <span class="sr-only">{{ $count }}</span>
    @endif
</a>
