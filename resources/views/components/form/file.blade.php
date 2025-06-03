@props(['label' => '', 'name' => '', 'oldimage' => '', 'pathName' => '', 'can_delete' => false])

@if ($label)
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
@endif

<input type="file" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" {{ $attributes }}>
@if ($oldimage)
    <div class="position-relative d-inline-block">
        @if ($can_delete)
            <div id="del_site_image">X</div>
        @endif
        {{-- <img width="100" src="{{ asset($oldimage) }}" alt="{{ $oldimage }}" class="img-thumbnail mt-1"> --}}
        <a href="{{ asset($oldimage)  }}" target="_blank"
            title="file">{{ $oldimage}}</a>
    </div>
@endif
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror

@push('css')
    <style>
        #del_site_image {
            position: absolute;
            width: 20px;
            height: 20px;
            font-size: 12px;
            top: 0;
            right: 0;
            background: red;
            color: white;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>

@endpush
