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

    @if($employee)
    <div class="card mt-4">
        <div class="card-body">
            <h5>Payment Information</h5>
            {{-- <form action="{{ route('admin.profile') }}" method="POST" enctype="multipart/form-data"> --}}
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <x-form.input label="Account Name" name="account_name" placeholder="Enter Account Name" :oldval="$employee->account_name ?? ''" />
                </div>
                <div class="mb-3">
                    <x-form.input label="Account Number" name="account_number" placeholder="Enter Account Number" :oldval="$employee->account_number ?? ''" />
                </div>
                <div class="mb-3">
                    <label class="form-label">IBAN / RIB</label>
                    <input type="text" class="form-control" name="iban" id="ibanInput" placeholder="IBAN / RIB" value="{{ old('iban', $employee->iban ?? '') }}">
                    <div id="ibanValidationMessage" class="mt-2"></div>
                </div>
                <div class="mb-3">
                    <x-form.input label="Bank Code" name="bank_code" placeholder="Enter Bank Code" :oldval="$employee->bank_code ?? ''" />
                </div>
                @if (!$employee->is_iban_valid)
                    <div class="mb-3">
                        <button type="button" id="validateIban" class="btn btn-primary">Validate</button>
                    </div>
                @endif
                {{-- <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Payment Info</button> --}}
            {{-- </form> --}}
        </div>
    </div>
    @endif

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

                $('#validateIban').click(function() {
                    // Get the IBAN value using the ID we added to the input
                    const iban = $('#ibanInput').val();

                    // Check if IBAN has 20 characters
                    if (iban.length !== 20) {
                        $('#ibanValidationMessage').html('<div class="alert alert-danger">IBAN must be exactly 20 characters long.</div>');
                        return;
                    }

                    // Send jQuery request to validate IBAN
                    $.ajax({
                        url: '{{ route("admin.profile.validate-rip") }}',
                        method: 'POST',
                        data: {
                            rip: iban,
                            bank_code: bank_code,
                            account_name: account_name,
                            account_number: account_number,
                            _token: '{{ csrf_token() }}' // Include CSRF token for Laravel
                        },
                        success: function(response) {
                            $('#ibanValidationMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                            $('#validateIban').addClass('d-none');
                        },
                        error: function(xhr) {
                            let errorMessage = 'Error validating IBAN. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            $('#ibanValidationMessage').html('<div class="alert alert-danger">' + errorMessage + '</div>');
                        }
                    });
                });

            });
        </script>

    @endpush

</x-dashboard>
