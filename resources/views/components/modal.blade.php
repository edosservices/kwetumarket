@props([
    'name',
    'title',
])

<div
    x-data="{ open: false }"
    x-on:open-modal.window="open = $event.detail === '{{ $name }}'"
    x-on:keydown.escape.window="open = false"
    x-cloak
>
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-{{ $name }}-title"
    >
        <div class="absolute inset-0 bg-twende-dark/50" x-on:click="open = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-twende-night-card">
            <div class="flex items-start justify-between gap-4">
                <h2 id="modal-{{ $name }}-title" class="text-lg font-semibold">{{ $title }}</h2>
                <button type="button" class="rounded-full p-1 text-twende-muted" x-on:click="open = false" aria-label="{{ __('ui.nav.close') }}">
                    <x-icon name="close" class="h-5 w-5" />
                </button>
            </div>
            <div class="mt-4">{{ $slot }}</div>
        </div>
    </div>
</div>
