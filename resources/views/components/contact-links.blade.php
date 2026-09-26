@props([
    'links' => [],
])

@if ($links !== [])
    <div {{ $attributes->class('flex flex-wrap gap-2') }}>
        @foreach ($links as $link)
            <a
                href="{{ $link->href }}"
                @if ($link->external) target="_blank" rel="noopener noreferrer" @endif
                class="inline-flex h-10 items-center rounded-full border border-twende-line bg-white px-3 text-sm font-semibold text-twende-dark hover:border-twende-red hover:text-twende-red dark:border-white/15 dark:bg-transparent dark:text-white"
            >{{ $link->label }}</a>
        @endforeach
    </div>
@endif
