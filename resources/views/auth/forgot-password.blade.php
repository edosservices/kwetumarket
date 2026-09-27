<x-layouts.guest :title="__('ui.auth.forgot_title')">
    <h1 class="text-2xl font-bold">{{ __('ui.auth.forgot_title') }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ __('ui.auth.forgot_body') }}</p>

    @if (session('status'))
        <div class="mt-6">
            <x-alert variant="success">{{ session('status') }}</x-alert>
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6">
            <x-alert variant="error">{{ $errors->first() }}</x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <x-input name="email" type="email" :label="__('ui.auth.email')" :value="old('email')" autocomplete="email" required autofocus />
        <x-button type="submit" class="w-full">{{ __('ui.auth.submit_forgot') }}</x-button>
    </form>

    <p class="mt-6 text-sm"><a href="{{ route('login') }}" class="font-semibold text-twende-red">{{ __('ui.nav.login') }}</a></p>
</x-layouts.guest>
