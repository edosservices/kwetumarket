<x-layouts.dashboard :title="__('commerce.follows')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.follows') }}</h1>
    @if ($shops->isEmpty())
        <div class="mt-6"><x-empty-state :title="__('commerce.follows_empty_title')" :description="__('commerce.follows_empty_body')"><x-slot:action><x-button :href="route('shops.index')">{{ __('ui.nav.shops') }}</x-button></x-slot:action></x-empty-state></div>
    @else
        <ul class="mt-6 space-y-3">
            @foreach ($shops as $shop)
                <li class="flex items-center justify-between gap-3 rounded-2xl border border-twende-line p-4 dark:border-white/10">
                    <a href="{{ route('shops.show', $shop) }}" class="font-semibold">{{ $shop->name }}</a>
                    <form method="POST" action="{{ route('follows.destroy', $shop) }}">@csrf @method('DELETE')<button class="text-sm font-semibold text-twende-red">{{ __('commerce.unfollow') }}</button></form>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.dashboard>
