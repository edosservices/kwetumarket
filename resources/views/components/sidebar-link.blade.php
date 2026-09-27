@props([
    'href',
    'active' => false,
    'inverted' => false,
])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class('rounded-md px-3 py-1.5 text-sm font-medium transition '.($active ? 'bg-twende-red text-white' : ($inverted ? 'text-white/80 hover:bg-white/10' : 'text-twende-dark hover:bg-twende-light dark:text-white dark:hover:bg-white/10'))) }}
>
    {{ $slot }}
</a>
