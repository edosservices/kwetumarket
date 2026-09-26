<x-layouts.dashboard :title="__('commerce.missions')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.missions') }}</h1>
    <p class="mt-2 text-sm">{{ __('commerce.earnings') }} : <strong>{{ \App\Support\Money::format($balance) }}</strong></p>
    @if ($jobs->isEmpty())
        <div class="mt-6"><x-empty-state :title="__('commerce.orders_empty_title')" /></div>
    @else
        <ul class="mt-6 space-y-4">
            @foreach ($jobs as $job)
                <li class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                    <p class="font-semibold">{{ $job->order->number }} · {{ __('commerce.delivery_statuses.'.$job->status) }}</p>
                    <p class="text-sm text-twende-muted">{{ $job->order->address }}, {{ $job->order->city }} · {{ $job->order->phone }}</p>
                    @php
                        $next = ['pending' => 'accepted', 'assigned' => 'accepted', 'accepted' => 'departed', 'departed' => 'en_route', 'en_route' => 'arrived', 'arrived' => 'delivered'][$job->status] ?? null;
                    @endphp
                    @if ($next)
                        <form method="POST" action="{{ route('delivery.jobs.advance', $job) }}" class="mt-3 flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="hidden" name="status" value="{{ $next }}">
                            @if ($next === 'en_route')
                                <label class="text-sm">{{ __('commerce.eta_minutes') }} <input type="number" name="eta_minutes" min="5" value="45" class="ml-2 h-10 w-24 rounded-xl border border-twende-line px-2 dark:border-white/15 dark:bg-twende-night"></label>
                            @endif
                            <button class="h-10 rounded-full bg-twende-green-bright px-4 text-sm font-semibold text-white">{{ __('commerce.delivery_statuses.'.$next) }}</button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.dashboard>
