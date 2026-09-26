@props(['slides', 'compact' => false])

@if ($slides->isNotEmpty())
    <div
        class="relative overflow-hidden rounded-3xl bg-twende-dark text-white {{ $compact ? 'min-h-56' : 'min-h-72' }}"
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
                    <img src="{{ $slide->imageUrl() }}" alt="" class="h-full w-full object-cover opacity-80">
                @else
                    <div class="h-full w-full bg-gradient-to-br from-twende-red to-twende-green"></div>
                @endif
                <div class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-twende-dark/80 p-6 sm:p-10">
                    <h2 class="max-w-xl text-2xl font-bold sm:text-4xl">{{ $slide->title }}</h2>
                    @if ($slide->subtitle)
                        <p class="mt-2 max-w-lg text-sm text-white/90 sm:text-base">{{ $slide->subtitle }}</p>
                    @endif
                    @if ($slide->cta_label && $slide->cta_url)
                        <a href="{{ $slide->cta_url }}" class="mt-4 inline-flex h-11 w-fit items-center rounded-full bg-white px-5 text-sm font-semibold text-twende-dark">{{ $slide->cta_label }}</a>
                    @endif
                </div>
            </article>
        @endforeach
        @if ($slides->count() > 1)
            <div class="absolute bottom-4 right-4 flex gap-2">
                @foreach ($slides as $slide)
                    <button type="button" class="h-2.5 w-2.5 rounded-full bg-white/70" x-bind:class="index === {{ $loop->index }} ? 'bg-white' : 'bg-white/40'" x-on:click="index = {{ $loop->index }}" aria-label="{{ $slide->title }}"></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
