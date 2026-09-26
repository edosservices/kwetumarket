@props([
    'href',
    'active' => false,
])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class('rounded-xl px-3 py-2 text-sm font-medium transition '.($active ? 'bg-twende-red text-white' : 'text-twende-dark hover:bg-twende-light dark:text-white dark:hover:bg-white/10')) }}
>
    {{ $slot }}
</a>
