@props(['label' => '', 'name' => '', 'placeholder' => '', 'oldval' => '', 'options' => []])

@if ($label)
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
@endif

<select class="form-control @error($name) is-invalid @enderror mt-2" id="{{ $name }}" name="{{ $name }}">
    @if ($placeholder)
        <option value="" disabled selected>{{ $placeholder }}</option>
    @endif
    @foreach ($options as $option)
        <option value="{{ $option->id }}" @if ($option->id == old('invoice_id', $oldval)) selected @endif>{{ $option->name }}</option>
    @endforeach
</select>
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
