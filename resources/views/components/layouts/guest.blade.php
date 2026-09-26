@props([
    'title' => null,
    'description' => null,
])

<x-layouts.base :title="$title" :description="$description">
    <main id="contenu" class="grid min-h-screen lg:grid-cols-2">
        <section class="relative hidden flex-col justify-between overflow-hidden bg-twende-light p-10 lg:flex dark:bg-twende-night-card">
            <div class="pointer-events-none absolute -left-16 -top-16 h-56 w-56 rounded-full bg-twende-red/10"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-10 h-64 w-64 rounded-full bg-twende-green/15"></div>
            <x-brand-logo size="lg" :href="route('home')" />
            <div class="relative max-w-md">
                <p class="text-sm font-semibold uppercase tracking-wide text-twende-green">{{ config('twende.name') }}</p>
                <h1 class="mt-3 text-4xl font-bold leading-tight text-twende-dark dark:text-white">{{ __('ui.auth.panel_title') }}</h1>
                <p class="mt-4 text-base leading-relaxed text-twende-muted">{{ __('ui.auth.panel_body') }}</p>
            </div>
            <p class="relative text-sm text-twende-muted">{{ __('ui.footer.tagline') }}</p>
        </section>
        <section class="flex flex-col justify-center px-4 py-8 sm:px-10">
            <div class="mb-8 lg:hidden">
                <x-brand-logo size="md" :href="route('home')" />
            </div>
            <div class="mx-auto w-full max-w-md">
                {{ $slot }}
            </div>
        </section>
    </main>
</x-layouts.base>
