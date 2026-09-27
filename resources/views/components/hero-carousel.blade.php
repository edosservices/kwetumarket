@props(['slides', 'compact' => false, 'welcome' => false])

@if ($slides->isNotEmpty())
    <div
        {{ $attributes->class([
            'relative overflow-hidden bg-twende-dark text-white',
            'min-h-56 rounded-3xl' => $compact,
            'min-h-[20rem] sm:min-h-[28rem] lg:min-h-[32rem]' => ! $compact,
        ]) }}
        x-data="{
            index: 0,
            count: {{ $slides->count() }},
            timer: null,
            startX: 0,
            reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            start() {
                if (this.reduced || this.count < 2) return;
                this.timer = setInterval(() => this.next(), 5000);
            },
            stop() { clearInterval(this.timer); },
            next() { this.index = (this.index + 1) % this.count; },
            prev() { this.index = (this.index - 1 + this.count) % this.count; },
        }"
        x-init="start()"
        x-on:mouseenter="stop()"
        x-on:mouseleave="start()"
        x-on:touchstart.passive="startX = $event.touches[0].clientX"
        x-on:touchend.passive="if ($event.changedTouches[0].clientX - startX > 40) prev(); if (startX - $event.changedTouches[0].clientX > 40) next();"
    >
        @foreach ($slides as $slide)
            <article class="absolute inset-0" x-show="index === {{ $loop->index }}" x-cloak>
                @if ($slide->imageUrl())
                    <img src="{{ $slide->imageUrl() }}" alt="" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full bg-gradient-to-br from-twende-red to-twende-green"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-twende-dark/85 via-twende-dark/45 to-twende-dark/10"></div>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-twende-dark/80 p-5 sm:p-8 {{ $welcome ? 'pt-28 sm:pt-36' : '' }}">
                    <h2 class="max-w-xl text-xl font-bold sm:text-3xl">{{ $slide->title }}</h2>
                    @if ($slide->subtitle)
                        <p class="mt-1 max-w-lg text-sm text-white/90">{{ $slide->subtitle }}</p>
                    @endif
                    @if ($slide->cta_label && $slide->cta_url)
                        <a href="{{ $slide->cta_url }}" class="mt-3 inline-flex h-10 w-fit items-center rounded-full bg-white px-4 text-sm font-semibold text-twende-dark">{{ $slide->cta_label }}</a>
                    @endif
                </div>
            </article>
        @endforeach

        @if ($welcome)
            <div class="relative z-10 flex max-w-2xl flex-col justify-start px-5 py-6 sm:px-10 sm:py-10 lg:px-14">
                <p class="text-sm font-semibold uppercase tracking-wide text-white/80">{{ __('ui.store.welcome') }}</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight sm:text-5xl">{{ __('ui.home.title') }}</h1>
                <p class="mt-3 text-lg font-semibold sm:text-2xl">{{ __('ui.store.slogan') }}</p>
                <p class="mt-2 max-w-xl text-sm text-white/90 sm:text-base">{{ __('ui.store.welcome_line') }}</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('products.index') }}" class="inline-flex h-11 items-center rounded-full bg-twende-red px-5 text-sm font-semibold text-white hover:bg-twende-red-dark">{{ __('commerce.buy_now') }}</a>
                    <a href="{{ route('sell') }}" class="inline-flex h-11 items-center rounded-full bg-white px-5 text-sm font-semibold text-twende-dark">{{ __('commerce.become_vendor') }}</a>
                </div>
            </div>
        @endif

        @if ($slides->count() > 1)
            <button type="button" class="absolute left-2 top-1/2 z-20 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-lg font-bold text-twende-dark shadow" x-on:click="prev()" aria-label="{{ __('ui.store.previous') }}">‹</button>
            <button type="button" class="absolute right-2 top-1/2 z-20 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-lg font-bold text-twende-dark shadow" x-on:click="next()" aria-label="{{ __('ui.store.next') }}">›</button>
            <div class="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 gap-2">
                @foreach ($slides as $slide)
                    <button type="button" class="h-2.5 w-2.5 rounded-full" x-bind:class="index === {{ $loop->index }} ? 'bg-white' : 'bg-white/40'" x-on:click="index = {{ $loop->index }}" aria-label="{{ $slide->title }}"></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
