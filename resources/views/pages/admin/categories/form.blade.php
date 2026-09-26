<x-layouts.dashboard :title="__('ui.catalog.categories_title')">
    <h1 class="mb-6 text-2xl font-bold">{{ $category->exists ? $category->name : __('ui.catalog.create') }}</h1>
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="grid max-w-3xl gap-4">
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif
        <x-select name="parent_id" :label="__('ui.catalog.parent')" :selected="old('parent_id', $category->parent_id)" :options="['' => __('ui.catalog.none')] + $parents->all()" />
        <x-input name="name" :label="__('ui.catalog.name')" :value="old('name', $category->name)" />
        <x-input name="slug" :label="__('ui.catalog.slug')" :value="old('slug', $category->slug)" />
        <x-textarea name="description" :label="__('ui.catalog.description')" :value="old('description', $category->description)" />
        <x-input name="image" type="file" :label="__('ui.catalog.image')" accept="image/jpeg,image/png,image/webp,image/gif" />
        <x-input name="sort_order" type="number" :label="__('ui.catalog.sort_order')" :value="old('sort_order', $category->sort_order ?? 0)" min="0" />
        <x-select name="status" :label="__('ui.catalog.status')" :selected="old('status', $category->status?->value ?? 'active')" :options="['active' => __('ui.catalog.active'), 'inactive' => __('ui.catalog.inactive')]" />
        <div class="flex gap-2">
            <x-button type="submit">{{ __('ui.catalog.save') }}</x-button>
            <x-button :href="route('admin.categories.index')" variant="outline">{{ __('ui.catalog.back') }}</x-button>
        </div>
    </form>
    @if ($category->exists)
        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="mt-6">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger" size="sm">{{ __('ui.catalog.delete') }}</x-button>
        </form>
    @endif
</x-layouts.dashboard>
