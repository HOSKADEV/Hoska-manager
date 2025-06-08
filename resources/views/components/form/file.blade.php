@props([
    'label' => '',
    'name' => '',
    'oldfiles' => [], // ← مصفوفة روابط الملفات القديمة
    'can_delete' => false,
    'multiple' => false,
])

@if ($label)
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
@endif

<input
    type="file"
    class="form-control @error($name) is-invalid @enderror"
    id="{{ $name }}"
    name="{{ $multiple ? $name . '[]' : $name }}"
    @if($multiple) multiple @endif
    {{ $attributes }}
>

{{-- عرض الملفات القديمة --}}
@if (!empty($oldfiles) && is_array($oldfiles))
    <div class="mt-3 d-flex flex-wrap gap-3">
        @foreach ($oldfiles as $index => $file)
            <div class="position-relative border p-2 rounded" style="min-width: 120px;">
                @php
                    $isImage = Str::endsWith(Str::lower($file), ['.jpg', '.jpeg', '.png', '.gif', '.webp']);
                @endphp

                @if ($can_delete)
                    <div class="del_file_btn" onclick="this.closest('div').remove()">X</div>
                @endif

                @if ($isImage)
                    <img src="{{ asset($file) }}"
                        alt="File {{ $index + 1 }}"
                        class="img-thumbnail"
                        style="width: 100px; height: 100px; object-fit: cover;">
                @else
                    <a href="{{ asset($file) }}" target="_blank" class="d-block text-truncate" style="max-width: 100px;">
                        📎 {{ basename($file) }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@endif

@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror

@push('css')
    <style>
        .del_file_btn {
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
            border-radius: 50%;
        }
    </style>
@endpush

@push('js')
    <script>
        document.querySelectorAll('.del_file_btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('div').remove();
            });
        });
    </script>
@endpush
{{-- End of file component --}}
