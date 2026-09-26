@props([
    'title' => null,
    'description' => null,
])

<x-layouts.base :title="$title" :description="$description">
    <main id="contenu" class="grid min-h-screen lg:grid-cols-2">
        <section class="relative flex flex-col justify-between overflow-hidden bg-twende-light p-4 sm:p-10 lg:min-h-screen dark:bg-twende-night-card">
            <x-brand-logo size="lg" :href="route('home')" />
            @php($authSlides = \App\Models\HeroSlide::query()->visible('auth')->get())
            @if ($authSlides->isNotEmpty())
                <div class="relative my-6">
                    <x-hero-carousel :slides="$authSlides" compact />
                </div>
            @else
                <div class="relative my-6 max-w-md">
                    <p class="text-sm font-semibold uppercase tracking-wide text-twende-green">{{ config('twende.name') }}</p>
                    <h1 class="mt-3 text-3xl font-bold leading-tight text-twende-dark lg:text-4xl dark:text-white">{{ __('ui.auth.panel_title') }}</h1>
                    <p class="mt-4 text-base leading-relaxed text-twende-muted">{{ __('ui.auth.panel_body') }}</p>
                </div>
            @endif
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
