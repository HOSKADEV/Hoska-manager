@props(['label' => '', 'name' => '', 'placeholder' => '', 'oldval' => '', 'options' => [], 'attributes' => ''])


@if ($label)
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @if ($attributes->has('required')) <span class="text-danger">*</span> @endif
@endif

<select class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" {{ $attributes }}>
    @if ($placeholder)
        <option value="" disabled selected>{{ $placeholder }}</option>
    @endif
    @foreach ($options as $option)
        <option value="{{ $option->id }}" @if ($option->id == old($name, $oldval)) selected @endif>{{ $option->name }}
        </option>
    @endforeach
</select>
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
