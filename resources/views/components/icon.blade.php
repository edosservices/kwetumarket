@props([
    'name',
])

@php
    $paths = [
        'menu' => 'M4 7h16M4 12h16M4 17h16',
        'close' => 'M6 6l12 12M18 6L6 18',
        'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
        'cart' => 'M6 6h15l-1.5 9h-12zM6 6L5 3H2M9 20a1 1 0 100-2 1 1 0 000 2zM18 20a1 1 0 100-2 1 1 0 000 2z',
        'user' => 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0',
        'sun' => 'M12 4v2M12 18v2M4 12H2M22 12h-2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M18.4 5.6L17 7M7 17l-1.4 1.4M12 16a4 4 0 100-8 4 4 0 000 8z',
        'moon' => 'M20 14.5A8 8 0 1110 4a6.5 6.5 0 0010 10.5z',
        'bag' => 'M6 8h12l-1 12H7L6 8zM9 8V7a3 3 0 016 0v1',
        'shop' => 'M4 10l8-6 8 6v9a1 1 0 01-1 1h-5v-6H10v6H5a1 1 0 01-1-1v-9z',
        'grid' => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
        'camera' => 'M4 8h3l2-2h6l2 2h3a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2v-8a2 2 0 012-2zM12 16.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z',
        'pin' => 'M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11zM12 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z',
    ];
    $d = $paths[$name] ?? $paths['grid'];
@endphp

<svg {{ $attributes->class('h-5 w-5') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="{{ $d }}" />
</svg>
