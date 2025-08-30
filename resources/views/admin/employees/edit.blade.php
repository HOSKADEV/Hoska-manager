<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Edit  Employee</h1>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-info"><i class="fas fa-long-arrow-alt-left"></i>All Employees</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            <strong>The following fields are required:</strong>
            <ul class="mb-0">
                @foreach ($errors->keys() as $field)
                    <li>{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.employees._form')
                <button class='btn btn-success'><i class="fas fa-save"></i>Update</button>
            </form>
        </div>
    </div>

    @push('css')
        <!-- Custom styles for this page -->
        <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    @endpush

    @push('js')
        <!-- Page level plugins -->
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- Page level custom scripts -->
        <script src="{{ asset("assets/js/demo/datatables-demo.js?v=" . time()) }}"></script>
    @endpush
</x-dashboard>



{{-- <x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Edit  Employee</h1>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-info"><i class="fas fa-long-arrow-alt-left"></i>All Employees</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            <strong>The following fields are required:</strong>
            <ul class="mb-0">
                @foreach ($errors->keys() as $field)
                    <li>{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                            aria-controls="home" aria-selected="true">Personal Information</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                            aria-controls="contact" aria-selected="false">Contact Information</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="payment-tab" data-toggle="tab" href="#payment" role="tab"
                            aria-controls="payment" aria-selected="false">Payment Information</a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="myTabContent">

                    <!-- Tab 1: المعلومات الشخصية -->
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="mb-3">
                            <x-form.input label="Name" name="name" placeholder="Enter Employee Name"
                                :oldval="$employee->name" />
                        </div>
                    </div>

                    <!-- Tab 2: بيانات التواصل -->
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <div class="mb-3">
                            <x-form.input label="Phone" name="phone" placeholder="Enter Employee Phone"
                                :oldval="$employee->contacts->first()->phone ?? ''" />
                        </div>
                        <div class="mb-3">
                            <x-form.input label="Email" name="email" placeholder="Enter Employee Email"
                                :oldval="$employee->contacts->first()->email ?? ''" />
                        </div>
                        <div class="mb-3">
                            <x-form.input label="Address" name="address" placeholder="Enter Employee Address"
                                :oldval="$employee->contacts->first()->address ?? ''" />
                        </div>
                    </div>

                    <!-- Tab 3: بيانات الدفع -->
                    <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                        <div class="mb-3">
                            <x-form.input label="Rate" name="rate" placeholder="Enter Employee Rate"
                                :oldval="$employee->rate" />
                        </div>

                        @php
                            $paymentOptions = [
                                'hourly' => 'Hourly',
                                'monthly' => 'Monthly',
                                'per_project' => 'Per Project',
                            ];
                            $selectedValue = old('payment_type', $employee->payment_type ?? '');
                        @endphp

                        <div class="mb-3 col-md-12">
                            <label for="payment_type" class="form-label">Payment Type</label>
                            <select name="payment_type" id="payment_type" class="form-control">
                                <option value="" disabled {{ $selectedValue == '' ? 'selected' : '' }}>Select Payment Type
                                </option>
                                @foreach($paymentOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedValue == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 🔻 New Payment Details Section -->
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <x-form.input label="Account Name" name="account_name" placeholder="Enter Account Name"
                                    :oldval="old('account_name', $employee->account_name ?? '')" />
                            </div>

                            <div class="mb-3 col-md-6">
                                <x-form.input label="Account Number" name="account_number"
                                    placeholder="Enter Account Number" :oldval="old('account_number', $employee->account_number ?? '')" />
                            </div>

                            <div class="mb-3 col-md-6">
                                <x-form.input label="IBAN / RIB" name="iban" placeholder="Enter IBAN / RIB"
                                    :oldval="old('iban', $employee->iban ?? '')" />
                            </div>

                            <div class="mb-3 col-md-6">
                                <x-form.input label="Bank Code" name="bank_code" placeholder="Enter Bank Code or SWIFT"
                                    :oldval="old('bank_code', $employee->bank_code ?? '')" />
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-4">
                    <button class='btn btn-success'><i class="fas fa-save"></i>Update</button>
                </div>
            </form>

        </div>
    </div>

    @push('css')
        <!-- Custom styles for this page -->
        <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    @endpush

    @push('js')
        <!-- Page level plugins -->
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- Page level custom scripts -->
        <script src="{{ asset("assets/js/demo/datatables-demo.js?v=" . time()) }}"></script>
    @endpush
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const alert = document.getElementById('error-alert');
                if (alert) {
                    setTimeout(() => {
                        alert.remove(); // This will completely remove it from the DOM
                    }, 3000); // 3 seconds
                }
            });
        </script>
    @endpush
</x-dashboard> --}}


