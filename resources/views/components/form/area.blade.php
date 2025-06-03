@props(['label' => '', 'name' => '', 'placeholder' => '', 'oldval' => '', 'tiny' => false])

@if ($label)
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
@endif

<textarea type="text" class="form-control {{ $tiny ? 'tinyeditor' : '' }} @error($name) is-invalid @enderror"
    id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}" rows="5">
    {{ old($name, $oldval) }}
</textarea>
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror

@if ($tiny)
    @push('js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.0/tinymce.min.js" referrerpolicy="origin"></script>

        <script>
            tinymce.init({
                selector: '.tinyeditor',
                plugins: [
                    'a11ychecker', 'accordion', 'advlist', 'anchor', 'autolink', 'autosave',
                    'charmap', 'code', 'codesample', 'directionality', 'emoticons', 'exportpdf',
                    'exportword', 'fullscreen', 'help', 'image', 'importcss', 'importword',
                    'insertdatetime', 'link', 'lists', 'markdown', 'math', 'media', 'nonbreaking',
                    'pagebreak', 'preview', 'quickbars', 'save', 'searchreplace', 'table',
                    'visualblocks', 'visualchars', 'wordcount'
                ],
                toolbar: 'undo redo | accordion accordionremove | ' +
                    'importword exportword exportpdf | math | ' +
                    'blocks fontfamily fontsize | bold italic underline strikethrough | ' +
                    'align numlist bullist | link image | table media | ' +
                    'lineheight outdent indent | forecolor backcolor removeformat | ' +
                    'charmap emoticons | code fullscreen preview | save print | ' +
                    'pagebreak anchor codesample | ltr rtl',
                menubar: 'file edit view insert format tools table help'
            });
        </script>
    @endpush
@endif
