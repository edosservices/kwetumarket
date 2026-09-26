@props([
    'name',
    'price',
    'currency' => 'CDF',
    'href' => '#',
    'shop' => null,
    'image' => null,
    'badge' => null,
])

<article {{ $attributes->class('group flex h-full flex-col overflow-hidden rounded-2xl border border-twende-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-twende-night-card') }}>
    <a href="{{ $href }}" class="flex h-full flex-col">
        <div class="flex aspect-square items-center justify-center bg-twende-light dark:bg-white/5">
            @if ($image)
                <img src="{{ $image }}" alt="" class="h-full w-full object-contain">
            @else
                <x-icon name="bag" class="h-10 w-10 text-twende-green" />
            @endif
        </div>
        <div class="flex flex-1 flex-col gap-2 p-4">
            @if ($badge)
                <x-badge variant="red">{{ $badge }}</x-badge>
            @endif
            <h3 class="line-clamp-2 font-semibold text-twende-dark group-hover:text-twende-red dark:text-white">{{ $name }}</h3>
            @if ($shop)
                <p class="text-sm text-twende-muted">{{ $shop }}</p>
            @endif
            <p class="mt-auto text-base font-bold text-twende-red">{{ $price }} <span class="text-xs font-semibold">{{ $currency }}</span></p>
        </div>
    </a>
</article>
