<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ $user->name }}</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.profile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class='row'>
                    <div class='col-md-9'>
                        <div class='row'>
                            <div class='col-6'>
                                <div class="mb-3">
                                    <x-form.input label="Email" name="email" oldval="{{ $user->email }}" disabled />
                                </div>
                            </div>
                            <div class='col-6'>
                                <div class="mb-3">
                                    <x-form.input label="Username" name="name" oldval="{{ $user->name }}" disabled />
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <x-form.input label="Name" name="name" placeholder="Enter your Name"
                                oldval="{{ $user->name}}" />
                        </div>
                        <div class="mb-3">
                            @if($employee)
                                <x-form.input label="Phone" name="phone" placeholder="Enter Employee Phone"
                                    :oldval="$employee->contacts()->first()->phone ?? ''" />
                            @else
                                <x-form.input label="Phone" name="phone" placeholder="Enter Employee Phone" :oldval="''" />
                            @endif
                        </div>
                        @if($employee)
                            <div class="mb-3">
                                <label class="form-label">RIP</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="rip" id="ripInput" placeholder="RIP" value="{{ old('rip', $employee->iban ?? '') }}">
                                    @if (!$employee->is_iban_valid)
                                        <div class="input-group-append">
                                            <button type="button" id="validateRip" class="btn btn-primary">Validate RIP</button>
                                        </div>
                                    @endif
                                </div>
                                <div id="ripValidationMessage" class="mt-2"></div>
                            </div>
                        @endif
                        <div class="mb-3">
                            <x-form.input type='password' label="Password" name="password"
                                placeholder="Enter your Password" />
                        </div>
                        <div class="mb-3">
                            <x-form.input type='password' label="Confirm Password" name="password_confirmation"
                                placeholder="Enter Confirm Password" />
                        </div>
                    </div>

                    <div class='col-md-3'>
                        <div class="mb-3">
                            <label class="d-block" for="avatar">
                                <img class="img-thumbnail prev-img"
                                    style="width: 100%; height: 300px; object-fit: cover;"
                                    src="{{ asset($user->avatar ? $user->avatar : 'assets/img/undraw_profile.svg') }}"
                                    alt="img">

                            </label>
                            <div class="d-none">
                                <x-form.file label="Avatar" name="avatar" accept='.png,.jpg,.svg,.jpeg' />
                            </div>
                        </div>
                    </div>
                </div>

                <button class='btn btn-success'><i class="fas fa-save"></i> Save</button>
            </form>
        </div>
    </div>

    @push('js')
        <script>
            $(document).ready(function () {
                // Image Preview
                $('#avatar').change(function () {
                    let file = this.files[0];
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        $('.prev-img').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                });

                $('#validateRip').click(function() {
                    // Get the RIP value using the ID we added to the input
                    const rip = $('#ripInput').val();

                    // Check if RIP has 20 characters
                    if (rip.length !== 20) {
                        $('#ripValidationMessage').html('<div class="alert alert-danger">RIP must be exactly 20 characters long.</div>');
                        return;
                    }

                    // Send jQuery request to validate RIP
                    $.ajax({
                        url: '{{ route("admin.profile.validate-rip") }}',
                        method: 'POST',
                        data: {
                            rip: rip,
                            _token: '{{ csrf_token() }}' // Include CSRF token for Laravel
                        },
                        success: function(response) {
                            $('#ripValidationMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                            $('#validateRip').addClass('d-none');
                        },
                        error: function(xhr) {
                            let errorMessage = 'Error validating RIP. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            $('#ripValidationMessage').html('<div class="alert alert-danger">' + errorMessage + '</div>');
                        }
                    });
                });

            });
        </script>

    @endpush

</x-dashboard>
