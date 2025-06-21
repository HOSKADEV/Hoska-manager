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
            });
        </script>
    @endpush

</x-dashboard>
