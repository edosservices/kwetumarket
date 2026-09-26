<x-layouts.dashboard :title="$shop->name">
    <h1 class="mb-6 text-2xl font-bold">{{ $shop->name }}</h1>
    <form method="POST" action="{{ route('admin.shops.update', $shop) }}" enctype="multipart/form-data" class="grid max-w-3xl gap-4">
        @csrf
        @method('PUT')
        <x-input name="name" :label="__('ui.catalog.name')" :value="old('name', $shop->name)" />
        <x-input name="slug" :label="__('ui.catalog.slug')" :value="old('slug', $shop->slug)" />
        <x-textarea name="description" :label="__('ui.catalog.description')" :value="old('description', $shop->description)" />
        <x-input name="location" :label="__('ui.catalog.location')" :value="old('location', $shop->location)" />
        <x-input name="phone" :label="__('ui.catalog.phone')" :value="old('phone', $shop->phone)" />
        <x-input name="email" type="email" :label="__('ui.catalog.email')" :value="old('email', $shop->email)" />
        <x-input name="logo" type="file" :label="__('ui.catalog.logo')" accept="image/jpeg,image/png,image/webp,image/gif" />
        <x-input name="cover_image" type="file" :label="__('ui.catalog.cover')" accept="image/jpeg,image/png,image/webp,image/gif" />
        <x-select name="status" :label="__('ui.catalog.status')" :selected="old('status', $shop->status->value)" :options="[
            'pending' => __('ui.catalog.shop_statuses.pending'),
            'active' => __('ui.catalog.shop_statuses.active'),
            'suspended' => __('ui.catalog.shop_statuses.suspended'),
            'closed' => __('ui.catalog.shop_statuses.closed'),
        ]" />
        <div class="flex gap-2">
            <x-button type="submit">{{ __('ui.catalog.save') }}</x-button>
            <x-button :href="route('admin.shops.index')" variant="outline">{{ __('ui.catalog.back') }}</x-button>
        </div>
    </form>
</x-layouts.dashboard>
