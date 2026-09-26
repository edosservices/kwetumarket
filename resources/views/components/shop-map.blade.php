@props([
    'markers' => [],
    'latitude' => null,
    'longitude' => null,
])

@php
    $points = collect($markers)->values();
@endphp

@if ($points->isNotEmpty() || ($latitude !== null && $longitude !== null))
    <section {{ $attributes->class('overflow-hidden rounded-3xl border border-twende-line dark:border-white/10') }}>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <h2 class="text-lg font-semibold">{{ __('ui.smart.map') }}</h2>
            <p class="text-xs text-twende-muted">OpenStreetMap</p>
        </div>
        <div
            class="h-72 w-full bg-twende-light dark:bg-twende-night"
            data-twende-map
            data-markers='@json($points)'
            data-user-lat="{{ $latitude }}"
            data-user-lng="{{ $longitude }}"
            data-user-label="{{ __('ui.smart.approximate_position') }}"
        ></div>
    </section>
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.querySelectorAll('[data-twende-map]').forEach((node) => {
                if (!window.L || node.dataset.ready) {
                    return;
                }
                node.dataset.ready = '1';
                const markers = JSON.parse(node.dataset.markers || '[]');
                const userLat = node.dataset.userLat ? Number(node.dataset.userLat) : null;
                const userLng = node.dataset.userLng ? Number(node.dataset.userLng) : null;
                const map = L.map(node, { scrollWheelZoom: false });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                const bounds = [];
                markers.forEach((marker) => {
                    const pin = L.marker([marker.lat, marker.lng]).addTo(map);
                    pin.bindPopup(marker.name);
                    bounds.push([marker.lat, marker.lng]);
                });
                if (userLat !== null && userLng !== null && !Number.isNaN(userLat) && !Number.isNaN(userLng)) {
                    L.circle([userLat, userLng], { radius: 180, color: '#0D9827', fillOpacity: 0.2 }).addTo(map).bindPopup(node.dataset.userLabel);
                    bounds.push([userLat, userLng]);
                }
                if (bounds.length === 1) {
                    map.setView(bounds[0], 14);
                } else if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [24, 24] });
                }
            });
        </script>
    @endonce
@endif
