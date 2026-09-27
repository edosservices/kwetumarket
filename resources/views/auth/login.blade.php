<x-layouts.guest :title="__('ui.auth.login_title')">
    <h1 class="text-2xl font-bold">{{ __('ui.auth.login_title') }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ __('ui.auth.panel_body') }}</p>

    @if ($errors->any())
        <div class="mt-6">
            <x-alert variant="error">{{ $errors->first() }}</x-alert>
        </div>
    @endif

    @if (session('status'))
        <div class="mt-6">
            <x-alert variant="success">{{ session('status') }}</x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
        @csrf
        <x-input name="email" type="email" :label="__('ui.auth.email')" :value="old('email')" autocomplete="username" required autofocus />
        <x-input name="password" type="password" :label="__('ui.auth.password')" autocomplete="current-password" required />
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="rounded border-twende-line text-twende-red focus:ring-twende-red">
            {{ __('ui.auth.remember') }}
        </label>
        <x-button type="submit" class="w-full">{{ __('ui.auth.submit_login') }}</x-button>
    </form>

    <div class="mt-6 flex flex-col gap-2 text-sm">
        <a href="{{ route('password.request') }}" class="font-medium text-twende-red">{{ __('ui.auth.forgot') }}</a>
        <p>{{ __('ui.auth.no_account') }} <a href="{{ route('register') }}" class="font-semibold text-twende-green">{{ __('ui.nav.register') }}</a></p>
    </div>
</x-layouts.guest>
