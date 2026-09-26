<x-layouts.guest :title="__('ui.auth.register_title')">
    <h1 class="text-2xl font-bold">{{ __('ui.auth.register_title') }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ __('ui.auth.panel_body') }}</p>

    @if ($errors->any())
        <div class="mt-6">
            <x-alert variant="error">{{ $errors->first() }}</x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
        @csrf
        <x-input name="name" :label="__('ui.auth.name')" :value="old('name')" autocomplete="name" required autofocus />
        <x-input name="email" type="email" :label="__('ui.auth.email')" :value="old('email')" autocomplete="email" required />
        <x-input name="phone" type="tel" :label="__('ui.auth.phone')" :value="old('phone')" :hint="__('ui.auth.phone_hint')" autocomplete="tel" />
        <x-input name="referral" :label="__('commerce.referral_field')" :value="old('referral', request('parrain'))" autocomplete="off" />
        <x-input name="password" type="password" :label="__('ui.auth.password')" :hint="__('ui.auth.password_hint')" autocomplete="new-password" required />
        <x-input name="password_confirmation" type="password" :label="__('ui.auth.password_confirmation')" autocomplete="new-password" required />
        <x-button type="submit" class="w-full">{{ __('ui.auth.submit_register') }}</x-button>
    </form>

    <p class="mt-6 text-sm">{{ __('ui.auth.has_account') }} <a href="{{ route('login') }}" class="font-semibold text-twende-red">{{ __('ui.nav.login') }}</a></p>
</x-layouts.guest>
