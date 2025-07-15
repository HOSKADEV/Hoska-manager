@props(['label' => '', 'name' => '', 'placeholder' => '', 'oldval' => '', 'options' => []])

@if ($label)
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @if ($attributes->has('required')) <span class="text-danger">*</span> @endif
@endif

<select name="{{ $name }}" id="{{ $name }}" class="form-control @error($name) is-invalid @enderror">
    @if($placeholder)
        <option value="" disabled {{ old($name, $oldval) === '' ? 'selected' : '' }}>{{ $placeholder }}</option>
    @endif
    @foreach($options as $id => $label)
        <option value="{{ $id }}" {{ $id == old($name, $oldval) ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
</select>

@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
