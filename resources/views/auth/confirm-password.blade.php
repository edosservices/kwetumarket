<x-layouts.guest :title="__('ui.auth.confirm_title')">
    <h1 class="text-2xl font-bold">{{ __('ui.auth.confirm_title') }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ __('ui.auth.confirm_body') }}</p>

    @if ($errors->any())
        <div class="mt-6">
            <x-alert variant="error">{{ $errors->first() }}</x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-6 space-y-4">
        @csrf
        <x-input name="password" type="password" :label="__('ui.auth.password')" autocomplete="current-password" required autofocus />
        <x-button type="submit" class="w-full">{{ __('ui.auth.submit_confirm') }}</x-button>
    </form>
</x-layouts.guest>
