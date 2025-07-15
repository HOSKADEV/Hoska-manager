@props(['label' => '', 'name' => '', 'placeholder' => '', 'oldval' => '', 'type' => 'text', 'id' => '', 'attributes' => []])

<label for="{{ $name }}">
    {{ $label }}
    @if (str_contains($attributes, 'required')) <span class="text-danger">*</span> @endif
</label>

<input type="{{$type}}" class="form-control @error($name) is-invalid @enderror" id="{{ $id }}" name="{{ $name }}"
    value="{{ old($name, $oldval) }}" placeholder="{{ $placeholder }}" {{ $attributes }}>
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
