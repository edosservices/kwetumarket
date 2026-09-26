<x-layouts.dashboard :title="__('experience.hero_title')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('experience.hero_title') }}</h1>
    <form method="POST" action="{{ route('admin.hero.store') }}" enctype="multipart/form-data" class="mt-6 grid max-w-xl gap-3">
        @csrf
        <label class="text-sm">Placement
            <select name="placement" class="mt-1 h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                <option value="home">home</option>
                <option value="auth">auth</option>
            </select>
        </label>
        <x-input name="title" :label="__('ui.catalog.name')" :value="old('title')" />
        <x-input name="subtitle" label="Subtitle" :value="old('subtitle')" />
        <x-input name="cta_label" label="CTA" :value="old('cta_label')" />
        <x-input name="cta_url" label="URL" :value="old('cta_url')" />
        <x-input name="sort_order" type="number" label="Ordre" :value="old('sort_order', 0)" />
        <x-input name="image" type="file" :label="__('ui.catalog.image')" accept="image/jpeg,image/png,image/webp" />
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Actif</label>
        <button class="h-11 w-fit rounded-full bg-twende-red px-5 text-sm font-semibold text-white">{{ __('experience.slide_create') }}</button>
    </form>
    <ul class="mt-8 space-y-3">
        @foreach ($slides as $slide)
            <li class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-twende-line p-4 dark:border-white/10">
                <div>
                    <p class="font-semibold">{{ $slide->title }}</p>
                    <p class="text-sm text-twende-muted">{{ $slide->placement }} · {{ $slide->is_active ? 'actif' : 'inactif' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.hero.destroy', $slide) }}">
                    @csrf
                    @method('DELETE')
                    <button class="h-10 rounded-full border border-twende-line px-4 text-sm dark:border-white/15">{{ __('ui.catalog.delete') }}</button>
                </form>
            </li>
        @endforeach
    </ul>
</x-layouts.dashboard>
