@props(['label' => '', 'name' => '', 'placeholder' => '', 'oldval' => '', 'type' => 'text'])

@if ($label)
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
@endif

<input  type="{{$type}}" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $oldval) }}" placeholder="{{ $placeholder }}" {{ $attributes }}>
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
