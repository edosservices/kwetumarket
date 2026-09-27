<x-layouts.dashboard :title="__('operations.addresses')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('operations.addresses') }}</h1>
    <form method="POST" action="{{ route('addresses.store') }}" class="mt-6 grid max-w-xl gap-3">
        @csrf
        <x-input name="label" :label="__('operations.address_label')" value="Maison" required />
        <x-input name="phone" type="tel" :label="__('ui.auth.phone')" :value="old('phone', auth()->user()->phone)" required />
        <x-input name="country" :label="__('ui.smart.country')" :value="old('country', 'RD Congo')" />
        <x-input name="province" :label="__('ui.smart.province')" :value="old('province')" />
        <x-input name="city" :label="__('ui.smart.city')" :value="old('city')" required />
        <x-input name="commune" :label="__('ui.smart.commune')" :value="old('commune')" />
        <x-input name="quarter" :label="__('ui.smart.quarter')" :value="old('quarter')" />
        <x-input name="address" :label="__('ui.smart.address')" :value="old('address')" required />
        <x-input name="latitude" :label="__('operations.latitude')" :value="old('latitude')" inputmode="decimal" />
        <x-input name="longitude" :label="__('operations.longitude')" :value="old('longitude')" inputmode="decimal" />
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"> {{ __('operations.primary') }}</label>
        <button class="h-11 w-fit rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('operations.save') }}</button>
    </form>
    <ul class="mt-8 space-y-3">
        @forelse ($addresses as $address)
            <li class="rounded-2xl border border-twende-line p-4 text-sm dark:border-white/10">
                <p class="font-semibold">{{ $address->label }} @if($address->is_default) · {{ __('operations.primary') }} @endif</p>
                <p>{{ $address->address }}, {{ $address->quarter }} {{ $address->commune }} {{ $address->city }}</p>
                <p class="text-twende-muted">{{ $address->phone }}</p>
                <div class="mt-3 flex gap-3">
                    @unless ($address->is_default)
                        <form method="POST" action="{{ route('addresses.primary', $address) }}">@csrf<button class="font-semibold text-twende-green">{{ __('operations.make_primary') }}</button></form>
                    @endunless
                    <form method="POST" action="{{ route('addresses.destroy', $address) }}">@csrf @method('DELETE')<button class="font-semibold text-twende-red">{{ __('ui.catalog.delete') }}</button></form>
                </div>
            </li>
        @empty
            <li><x-empty-state :title="__('operations.addresses')" /></li>
        @endforelse
    </ul>
</x-layouts.dashboard>
