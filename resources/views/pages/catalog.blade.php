<x-layouts.storefront :title="$title" :description="$description">
    <section class="mx-auto max-w-3xl px-4 py-16">
        <h1 class="text-3xl font-bold">{{ $title }}</h1>
        <div class="mt-6">
            <x-empty-state :title="$description">
                <x-slot:action>
                    <x-button :href="route('home')" variant="outline">{{ __('ui.errors.home') }}</x-button>
                </x-slot:action>
            </x-empty-state>
        </div>
    </section>
</x-layouts.storefront>
