@props(['label' => '', 'name' => '', 'placeholder' => '', 'oldval' => [], 'options' => [], 'multiple' => false])

@if ($label)
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
@endif

<select
    class="form-control @error($name) is-invalid @enderror mt-2"
    id="{{ $name }}"
    name="{{ $multiple ? $name . '[]' : $name }}"
    @if($multiple) multiple @endif
>
    @if ($placeholder && !$multiple)
        <option value="" disabled selected>{{ $placeholder }}</option>
    @endif

    @foreach ($options as $option)
        <option value="{{ $option->id }}"
            @if($multiple)
                {{ in_array($option->id, (array) $oldval) ? 'selected' : '' }}
            @else
                {{ $option->id == old($name, $oldval) ? 'selected' : '' }}
            @endif
        >
            {{ $option->name }}
        </option>
    @endforeach
</select>

@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror


@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#{{ $name }}').select2({
            placeholder: '{{ $placeholder }}',
            allowClear: true,
            width: '100%',
        });
    });
</script>
@endpush
