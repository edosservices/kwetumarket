<x-layouts.dashboard :title="__('commerce.subscription')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.subscription') }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ __('commerce.sandbox_note') }}</p>
    @if ($current)
        <p class="mt-4 font-semibold">{{ $current->plan->name }} · {{ $current->status }} · {{ $current->ends_at->format('d/m/Y') }}</p>
    @endif
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ($plans as $plan)
            <form method="POST" action="{{ route('vendor.subscription.store') }}" class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <h2 class="font-semibold">{{ $plan->name }}</h2>
                <p class="mt-2">{{ \App\Support\Money::format((int) $plan->price) }} / {{ $plan->interval_days }} j</p>
                <button class="mt-4 h-10 rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('commerce.subscription') }}</button>
            </form>
        @endforeach
    </div>
</x-layouts.dashboard>
