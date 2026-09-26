@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'bag' => 'default',
])

@php
    $error = $errors->getBag($bag)->first($name);
    $id = $attributes->get('id', $name);
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $id }}" class="text-sm font-medium">{{ $label }}</label>
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class('h-11 w-full rounded-xl border border-twende-line bg-white px-3 text-sm text-twende-dark outline-none focus:border-twende-red focus:ring-2 focus:ring-twende-red/20 dark:border-white/15 dark:bg-twende-night dark:text-white') }}
    >
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($error)
        <p id="{{ $id }}-error" class="text-sm font-medium text-twende-red">{{ $error }}</p>
    @endif
</div>
