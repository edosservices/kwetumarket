<x-layouts.guest :title="__('ui.auth.verify_title')">
    <h1 class="text-2xl font-bold">{{ __('ui.auth.verify_title') }}</h1>
    <p class="mt-2 text-sm leading-relaxed text-twende-muted">{{ __('ui.auth.verify_body') }}</p>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-6">
            <x-alert variant="success">{{ __('ui.auth.verify_sent') }}</x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <x-button type="submit">{{ __('ui.auth.verify_resend') }}</x-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm font-semibold text-twende-muted hover:text-twende-red">{{ __('ui.nav.logout') }}</button>
    </form>
</x-layouts.guest>
