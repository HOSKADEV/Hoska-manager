@props([
    'label' => '',
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => null,
])

<div class="mb-3">
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}
    >
        @if ($placeholder)
            <option value="" disabled {{ is_null($selected) ? 'selected' : '' }}>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $key => $option)
            <option value="{{ $key }}" {{ (string)$key === (string)old($name, $selected) ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
