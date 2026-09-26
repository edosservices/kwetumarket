<x-layouts.dashboard :title="__('ui.catalog.brands')">
    <h1 class="mb-6 text-2xl font-bold">{{ $brand->exists ? $brand->name : __('ui.catalog.create') }}</h1>
    <form method="POST" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" enctype="multipart/form-data" class="grid max-w-3xl gap-4">
        @csrf
        @if ($brand->exists) @method('PUT') @endif
        <x-input name="name" :label="__('ui.catalog.name')" :value="old('name', $brand->name)" />
        <x-input name="slug" :label="__('ui.catalog.slug')" :value="old('slug', $brand->slug)" />
        <x-textarea name="description" :label="__('ui.catalog.description')" :value="old('description', $brand->description)" />
        <x-input name="logo" type="file" :label="__('ui.catalog.logo')" accept="image/jpeg,image/png,image/webp,image/gif" />
        <x-select name="status" :label="__('ui.catalog.status')" :selected="old('status', $brand->status?->value ?? 'active')" :options="['active' => __('ui.catalog.active'), 'inactive' => __('ui.catalog.inactive')]" />
        <div class="flex gap-2">
            <x-button type="submit">{{ __('ui.catalog.save') }}</x-button>
            <x-button :href="route('admin.brands.index')" variant="outline">{{ __('ui.catalog.back') }}</x-button>
        </div>
    </form>
    @if ($brand->exists)
        <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="mt-6">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger" size="sm">{{ __('ui.catalog.delete') }}</x-button>
        </form>
    @endif
</x-layouts.dashboard>
