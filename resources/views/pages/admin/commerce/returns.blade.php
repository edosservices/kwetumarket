<x-layouts.dashboard :title="__('operations.returns')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('operations.returns') }}</h1>
    <ul class="mt-6 space-y-4">
        @forelse ($returns as $return)
            <li class="rounded-2xl border border-twende-line p-4 text-sm dark:border-white/10">
                <p class="font-semibold">{{ $return->order?->number }} · {{ $return->status }} · {{ $return->payment?->reference }}</p>
                <p class="mt-1">{{ $return->reason }}</p>
                @if (in_array($return->status, ['requested', 'vendor_accepted'], true))
                    <form method="POST" action="{{ route('admin.returns.update', $return) }}" class="mt-3 flex flex-wrap gap-2">
                        @csrf
                        <input name="admin_note" class="h-11 rounded-full border border-twende-line px-4 dark:border-white/15 dark:bg-twende-night" placeholder="{{ __('operations.admin_note') }}">
                        <button name="decision" value="approved" class="h-11 rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('operations.accept') }}</button>
                        <button name="decision" value="rejected" class="h-11 rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('operations.reject') }}</button>
                    </form>
                @endif
            </li>
        @empty
            <li><x-empty-state :title="__('operations.returns')" /></li>
        @endforelse
    </ul>
</x-layouts.dashboard>
