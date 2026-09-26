@php
    $vendor = isset($vendor) ? $vendor : ($shop?->vendor);
    $social = function (string $platform) use ($vendor) {
        $link = $vendor?->socialLinks?->first(fn ($item) => $item->platform->value === $platform);

        return old($platform, $link?->url ?: ($link?->username ? '@'.$link->username : ''));
    };
@endphp

<x-input name="business_name" :label="__('ui.smart.business_name')" :value="old('business_name', $vendor?->business_name)" />
<x-input name="manager_name" :label="__('ui.smart.manager_name')" :value="old('manager_name', $vendor?->manager_name)" />
<x-input name="whatsapp" :label="__('ui.smart.platforms.whatsapp')" :value="$social('whatsapp')" />
<x-input name="instagram" :label="__('ui.smart.platforms.instagram')" :value="$social('instagram')" />
<x-input name="tiktok" :label="__('ui.smart.platforms.tiktok')" :value="$social('tiktok')" />
<x-input name="facebook" :label="__('ui.smart.platforms.facebook')" :value="$social('facebook')" />
<x-input name="website" :label="__('ui.smart.platforms.website')" :value="$social('website')" />
<x-input name="country" :label="__('ui.smart.country')" :value="old('country', $shop?->country)" />
<x-input name="province" :label="__('ui.smart.province')" :value="old('province', $shop?->province)" />
<x-input name="city" :label="__('ui.smart.city')" :value="old('city', $shop?->city)" />
<x-input name="commune" :label="__('ui.smart.commune')" :value="old('commune', $shop?->commune)" />
<x-input name="quarter" :label="__('ui.smart.quarter')" :value="old('quarter', $shop?->quarter)" />
<x-input name="address_line" :label="__('ui.smart.address')" :value="old('address_line', $shop?->address_line)" />
<x-textarea name="opening_hours" :label="__('ui.smart.hours')" :value="old('opening_hours', $shop?->opening_hours)" />
<div class="grid gap-3 sm:grid-cols-2">
    <x-input name="latitude" :label="__('ui.smart.latitude')" :value="old('latitude', $shop?->latitude)" />
    <x-input name="longitude" :label="__('ui.smart.longitude')" :value="old('longitude', $shop?->longitude)" />
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="hidden" name="publish_location" value="0">
    <input type="checkbox" name="publish_location" value="1" class="h-4 w-4 accent-twende-green" @checked(old('publish_location', $shop?->publish_location ?? true))>
    {{ __('ui.smart.publish_location') }}
</label>
<label class="flex items-center gap-2 text-sm">
    <input type="hidden" name="publish_address" value="0">
    <input type="checkbox" name="publish_address" value="1" class="h-4 w-4 accent-twende-green" @checked(old('publish_address', $shop?->publish_address ?? true))>
    {{ __('ui.smart.publish_address') }}
</label>
<div class="rounded-2xl border border-twende-line p-3 dark:border-white/10">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm font-medium">{{ __('ui.smart.pick_on_map') }}</p>
        <button type="button" id="shop-use-position" class="inline-flex h-9 items-center rounded-full bg-twende-green px-3 text-sm font-semibold text-white">{{ __('ui.smart.use_my_position') }}</button>
    </div>
    <div id="shop-location-map" class="mt-3 h-64 overflow-hidden rounded-2xl bg-twende-light dark:bg-twende-night"></div>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.L) {
            return;
        }
        const latInput = document.querySelector('[name="latitude"]');
        const lngInput = document.querySelector('[name="longitude"]');
        const startLat = latInput.value ? Number(latInput.value) : -4.321;
        const startLng = lngInput.value ? Number(lngInput.value) : 15.312;
        const map = L.map('shop-location-map').setView([startLat, startLng], latInput.value ? 15 : 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(map);
        let marker = latInput.value ? L.marker([startLat, startLng]).addTo(map) : null;
        const place = (lat, lng) => {
            latInput.value = Number(lat).toFixed(7);
            lngInput.value = Number(lng).toFixed(7);
            if (!marker) {
                marker = L.marker([lat, lng]).addTo(map);
            } else {
                marker.setLatLng([lat, lng]);
            }
        };
        map.on('click', (event) => place(event.latlng.lat, event.latlng.lng));
        document.getElementById('shop-use-position')?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                return;
            }
            navigator.geolocation.getCurrentPosition((position) => {
                place(position.coords.latitude, position.coords.longitude);
                map.setView([position.coords.latitude, position.coords.longitude], 16);
            });
        });
    });
</script>
