<x-layouts.dashboard :title="__('commerce.delivery')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.delivery') }}</h1>
    <ul class="mt-6 space-y-4">
        @foreach ($deliveries as $delivery)
            <li class="rounded-2xl border border-twende-line p-4 text-sm dark:border-white/10">
                <p class="font-semibold">{{ $delivery->order->number }} · {{ __('commerce.delivery_statuses.'.$delivery->status) }} · {{ $delivery->agent?->name }}</p>
                @if (in_array($delivery->status, ['pending', 'assigned'], true))
                    <form method="POST" action="{{ route('admin.deliveries.assign', $delivery) }}" class="mt-2 flex flex-wrap gap-2">
                        @csrf
                        <select name="agent_id" class="h-10 rounded-xl border border-twende-line px-2 dark:border-white/15 dark:bg-twende-night">
                            @foreach ($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach
                        </select>
                        <button class="h-10 rounded-full bg-twende-green px-4 font-semibold text-white">{{ __('commerce.assign') }}</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</x-layouts.dashboard>
