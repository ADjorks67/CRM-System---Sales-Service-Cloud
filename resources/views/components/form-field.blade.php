@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'help' => null,
    'options' => null,
    'autocomplete' => null,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1']) }}>
    <label for="{{ $id }}" class="block text-sm font-medium text-text">
        {{ $label }}
        @if ($required)
            <span class="text-error" aria-hidden="true">*</span>
        @endif
    </label>

    @if (is_array($options))
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($required) required @endif
            @class([
                'min-h-11 w-full rounded border bg-card px-3 py-2 text-sm text-text',
                'border-error' => $hasError,
                'border-black/20' => ! $hasError,
            ])
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        >
            <option value="">Select…</option>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="4"
            @if ($required) required @endif
            @class([
                'min-h-11 w-full rounded border bg-card px-3 py-2 text-sm text-text',
                'border-error' => $hasError,
                'border-black/20' => ! $hasError,
            ])
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        >{{ old($name, $value) }}</textarea>
    @elseif ($type === 'checkbox')
        <label class="flex min-h-11 items-center gap-2 text-sm">
            <input
                id="{{ $id }}"
                type="checkbox"
                name="{{ $name }}"
                value="1"
                class="size-4 rounded border-black/20"
                @checked((bool) old($name, $value))
            >
            <span>{{ $slot->isEmpty() ? $label : $slot }}</span>
        </label>
    @else
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ $type === 'password' ? '' : old($name, $value) }}"
            @if ($required) required @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @class([
                'min-h-11 w-full rounded border bg-card px-3 py-2 text-sm text-text',
                'border-error' => $hasError,
                'border-black/20' => ! $hasError,
            ])
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        >
    @endif

    @if ($help)
        <p class="text-xs text-text/60">{{ $help }}</p>
    @endif

    @error($name)
        <p id="{{ $id }}-error" class="text-xs text-error" role="alert">{{ $message }}</p>
    @enderror
</div>
