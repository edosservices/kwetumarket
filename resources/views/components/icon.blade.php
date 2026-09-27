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
        'pin' => ['M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z', 'M12 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z'],
        'home' => ['M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z'],
        'heart' => ['M12 20s-7-4.4-7-9a4 4 0 0 1 7-2 4 4 0 0 1 7 2c0 4.6-7 9-7 9z'],
        'bell' => ['M6 9a6 6 0 0 1 12 0c0 7 3 7 3 7H3s3 0 3-7', 'M10 19a2 2 0 0 0 4 0'],
        'truck' => ['M3 7h11v8H3z', 'M14 10h4l3 3v2h-7z', 'M7 18a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z', 'M18 18a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z'],
        'lock' => ['M8 11V8a4 4 0 0 1 8 0v3', 'M6 11h12v9H6z'],
        'shield' => ['M12 3l8 3v6c0 5-3.4 7.6-8 9-4.6-1.4-8-4-8-9V6l8-3z'],
        'return' => ['M4 12a8 8 0 1 0 2.2-5.5', 'M4 4v5h5'],
        'users' => ['M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6z', 'M16 11a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z', 'M3.5 19a5.5 5.5 0 0 1 11 0', 'M14 19a4.5 4.5 0 0 1 6.5-4'],
        'support' => ['M5 13v-1a7 7 0 0 1 14 0v1', 'M5 13h3v6H6a1 1 0 0 1-1-1v-5z', 'M16 13h3v5a1 1 0 0 1-1 1h-2v-6z'],
    ];
    $icon = $paths[$name] ?? $paths['grid'];
    $segments = is_array($icon) ? $icon : [$icon];
@endphp

<svg {{ $attributes->class('h-5 w-5') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @foreach ($segments as $segment)
        <path d="{{ $segment }}" />
    @endforeach
</svg>
