@once
@if (session('success'))
    <x-alert variant="success" class="mb-4">{{ session('success') }}</x-alert>
@endif
@if (session('error'))
    <x-alert variant="error" class="mb-4">{{ session('error') }}</x-alert>
@endif
@if (isset($errors) && $errors->any())
    <x-alert variant="error" class="mb-4">{{ $errors->first() }}</x-alert>
@endif
@endonce
