<x-layouts.base :title="__('experience.offline')">
    <main id="contenu" class="mx-auto flex max-w-lg flex-1 flex-col items-center justify-center px-4 py-16 text-center">
        <x-brand-logo size="md" :href="route('home')" />
        <h1 class="mt-6 text-2xl font-bold">{{ __('experience.offline') }}</h1>
        <p class="mt-3 text-sm text-twende-muted">{{ __('experience.offline_body') }}</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex h-11 items-center rounded-full bg-twende-red px-5 text-sm font-semibold text-white">{{ __('experience.home') }}</a>
    </main>
</x-layouts.base>
