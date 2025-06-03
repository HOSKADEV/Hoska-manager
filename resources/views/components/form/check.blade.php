@props(['label' => '', 'name' => '', 'oldval' => [], 'options' => []])

@if ($label)
    <label class="form-label">{{ $label }}</label>
@endif

@foreach ($options as $option)
    <label class="d-block"><input type="checkbox" name="{{ $name }}"  @checked(in_array($option->id, $oldval)) value="{{ $option->id }}"> {{ $option->name }}</label>
@endforeach
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
