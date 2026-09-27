@props([
    'paginator',
])

@if ($paginator->hasPages())
    <nav {{ $attributes->class('flex items-center justify-between gap-3') }} aria-label="{{ __('ui.pagination.label') }}">
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-10 items-center rounded-full px-4 text-sm text-twende-muted">{{ __('ui.pagination.previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-full border border-twende-line px-4 text-sm font-semibold hover:border-twende-red hover:text-twende-red">{{ __('ui.pagination.previous') }}</a>
        @endif
        <span class="text-sm text-twende-muted">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-full border border-twende-line px-4 text-sm font-semibold hover:border-twende-red hover:text-twende-red">{{ __('ui.pagination.next') }}</a>
        @else
            <span class="inline-flex h-10 items-center rounded-full px-4 text-sm text-twende-muted">{{ __('ui.pagination.next') }}</span>
        @endif
    </nav>
@endif
