@props([
    'name',
    'href' => '#',
    'count' => null,
])

<a href="{{ $href }}" {{ $attributes->class('flex min-w-40 flex-col justify-between rounded-2xl border border-twende-line bg-white p-4 transition hover:border-twende-green dark:border-white/10 dark:bg-twende-night-card') }}>
    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-twende-red/10 text-twende-red">
        <x-icon name="grid" class="h-5 w-5" />
    </span>
    <span class="mt-6 block font-semibold">{{ $name }}</span>
    @if (! is_null($count))
        <span class="mt-1 block text-sm text-twende-muted">{{ $count }}</span>
    @endif
</a>
