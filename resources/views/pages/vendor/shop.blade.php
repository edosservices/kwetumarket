<x-layouts.dashboard :title="__('ui.catalog.my_shop')">
    @if (session('status'))
        <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    <h1 class="text-2xl font-bold">{{ __('ui.catalog.my_shop') }}</h1>
    @if (! $vendor)
        <p class="mt-4 text-sm text-twende-muted">{{ __('ui.catalog.admin_uses_shops') }}</p>
    @else
        @if ($vendor->status->value !== 'active')
            <x-alert class="mt-4">{{ __('ui.catalog.vendor_status_notice', ['status' => __('ui.catalog.vendor_statuses.'.$vendor->status->value)]) }}</x-alert>
        @endif
        <form method="POST" action="{{ route('vendor.shop.update') }}" enctype="multipart/form-data" class="mt-6 grid max-w-3xl gap-4">
            @csrf
            @method('PUT')
            <x-input name="name" :label="__('ui.catalog.name')" :value="old('name', $shop?->name)" />
            <x-input name="slug" :label="__('ui.catalog.slug')" :value="old('slug', $shop?->slug)" />
            <x-textarea name="description" :label="__('ui.catalog.description')" :value="old('description', $shop?->description)" />
            <x-input name="location" :label="__('ui.catalog.location')" :value="old('location', $shop?->location)" />
            <x-input name="phone" :label="__('ui.catalog.phone')" :value="old('phone', $shop?->phone)" />
            <x-input name="email" type="email" :label="__('ui.catalog.email')" :value="old('email', $shop?->email)" />
            <x-input name="logo" type="file" :label="__('ui.catalog.logo')" accept="image/jpeg,image/png,image/webp,image/gif" />
            <x-input name="cover_image" type="file" :label="__('ui.catalog.cover')" accept="image/jpeg,image/png,image/webp,image/gif" />
            <input type="hidden" name="status" value="{{ old('status', $shop?->status?->value ?? 'pending') }}">
            <x-button type="submit">{{ __('ui.catalog.save') }}</x-button>
        </form>
    @endif
</x-layouts.dashboard>
