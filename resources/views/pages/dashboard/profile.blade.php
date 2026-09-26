<x-layouts.dashboard :title="__('ui.dashboard.profile_title')">
    <h1 class="text-2xl font-bold">{{ __('ui.dashboard.profile_title') }}</h1>

    @if (session('status') === 'profile-information-updated')
        <div class="mt-6 max-w-xl">
            <x-alert variant="success">{{ __('ui.auth.profile_saved') }}</x-alert>
        </div>
    @elseif (session('status') === 'password-updated')
        <div class="mt-6 max-w-xl">
            <x-alert variant="success">{{ __('ui.auth.password_saved') }}</x-alert>
        </div>
    @elseif (session('status') === 'avatar-updated')
        <div class="mt-6 max-w-xl">
            <x-alert variant="success">{{ __('ui.smart.avatar_saved') }}</x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="mt-8 max-w-xl space-y-4">
        @csrf
        @if ($user->avatarUrl())
            <img src="{{ $user->avatarUrl() }}" alt="" class="h-16 w-16 rounded-full object-cover">
        @endif
        <x-input name="avatar" type="file" :label="__('ui.smart.avatar')" accept="image/jpeg,image/png,image/webp,image/gif" />
        <x-button type="submit" variant="outline">{{ __('ui.smart.save_avatar') }}</x-button>
    </form>

    <form method="POST" action="{{ route('user-profile-information.update') }}" class="mt-8 max-w-xl space-y-4">
        @csrf
        @method('PUT')
        <x-input name="name" :label="__('ui.auth.name')" :value="old('name', $user->name)" bag="updateProfileInformation" autocomplete="name" required />
        <x-input name="email" type="email" :label="__('ui.auth.email')" :value="old('email', $user->email)" bag="updateProfileInformation" autocomplete="email" required />
        <x-input name="phone" type="tel" :label="__('ui.auth.phone')" :value="old('phone', $user->phone)" :hint="__('ui.auth.phone_hint')" bag="updateProfileInformation" autocomplete="tel" />
        <x-input name="first_name" :label="__('ui.smart.first_name')" :value="old('first_name', $user->first_name)" bag="updateProfileInformation" />
        <x-input name="last_name" :label="__('ui.smart.last_name')" :value="old('last_name', $user->last_name)" bag="updateProfileInformation" />
        <x-input name="whatsapp" type="tel" :label="__('ui.smart.platforms.whatsapp')" :value="old('whatsapp', $user->whatsapp)" bag="updateProfileInformation" />
        <x-input name="country" :label="__('ui.smart.country')" :value="old('country', $user->country)" bag="updateProfileInformation" />
        <x-input name="province" :label="__('ui.smart.province')" :value="old('province', $user->province)" bag="updateProfileInformation" />
        <x-input name="city" :label="__('ui.smart.city')" :value="old('city', $user->city)" bag="updateProfileInformation" />
        <x-input name="commune" :label="__('ui.smart.commune')" :value="old('commune', $user->commune)" bag="updateProfileInformation" />
        <x-input name="quarter" :label="__('ui.smart.quarter')" :value="old('quarter', $user->quarter)" bag="updateProfileInformation" />
        <x-input name="address" :label="__('ui.smart.address')" :value="old('address', $user->address)" bag="updateProfileInformation" />
        <x-select
            name="locale"
            :label="__('ui.auth.locale')"
            :options="collect(config('twende.locales'))->mapWithKeys(fn ($locale) => [$locale => strtoupper($locale)])->all()"
            :selected="old('locale', $user->locale)"
            bag="updateProfileInformation"
        />
        <x-select
            name="currency"
            :label="__('ui.auth.currency')"
            :options="collect(config('twende.currencies'))->mapWithKeys(fn ($currency) => [$currency => $currency])->all()"
            :selected="old('currency', $user->currency)"
            bag="updateProfileInformation"
        />
        <x-button type="submit">{{ __('ui.auth.save_profile') }}</x-button>
    </form>

    <h2 class="mt-12 text-xl font-bold">{{ __('ui.dashboard.security_title') }}</h2>
    <form method="POST" action="{{ route('user-password.update') }}" class="mt-6 max-w-xl space-y-4">
        @csrf
        @method('PUT')
        <x-input name="current_password" type="password" :label="__('ui.auth.current_password')" bag="updatePassword" autocomplete="current-password" required />
        <x-input name="password" type="password" :label="__('ui.auth.password')" :hint="__('ui.auth.password_hint')" bag="updatePassword" autocomplete="new-password" required />
        <x-input name="password_confirmation" type="password" :label="__('ui.auth.password_confirmation')" bag="updatePassword" autocomplete="new-password" required />
        <x-button type="submit" variant="secondary">{{ __('ui.auth.save_password') }}</x-button>
    </form>
</x-layouts.dashboard>
