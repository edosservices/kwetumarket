<x-layouts.guest :title="__('ui.auth.reset_title')">
    <h1 class="text-2xl font-bold">{{ __('ui.auth.reset_title') }}</h1>

    @if ($errors->any())
        <div class="mt-6">
            <x-alert variant="error">{{ $errors->first() }}</x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-input name="email" type="email" :label="__('ui.auth.email')" :value="old('email', $request->email)" autocomplete="email" required />
        <x-input name="password" type="password" :label="__('ui.auth.password')" :hint="__('ui.auth.password_hint')" autocomplete="new-password" required />
        <x-input name="password_confirmation" type="password" :label="__('ui.auth.password_confirmation')" autocomplete="new-password" required />
        <x-button type="submit" class="w-full">{{ __('ui.auth.submit_reset') }}</x-button>
    </form>
</x-layouts.guest>
