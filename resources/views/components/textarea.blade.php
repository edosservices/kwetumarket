@props([
    'name',
    'label',
    'value' => null,
    'bag' => 'default',
    'hint' => null,
])

@php
    $error = $errors->getBag($bag)->first($name);
    $id = $attributes->get('id', $name);
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $id }}" class="text-sm font-medium text-twende-dark dark:text-white">{{ $label }}</label>
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class('min-h-28 w-full rounded-xl border border-twende-line bg-white px-3 py-2 text-sm text-twende-dark outline-none transition placeholder:text-twende-muted focus:border-twende-red focus:ring-2 focus:ring-twende-red/20 dark:border-white/15 dark:bg-twende-night dark:text-white') }}
    >{{ $value }}</textarea>
    @if ($hint)
        <p class="text-xs text-twende-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="text-sm font-medium text-twende-red">{{ $error }}</p>
    @endif
</div>
