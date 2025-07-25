<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home"
            aria-selected="true">Personal Information</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact"
            aria-selected="false">Contact Information</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="payment-tab" data-toggle="tab" href="#payment" role="tab" aria-controls="payment"
            aria-selected="false">Payment Information</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="login"
            aria-selected="false">Login Information</a>
    </li>
</ul>

<div class="tab-content mt-3" id="myTabContent">

    <!-- Tab 1: المعلومات الشخصية -->
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <div class="mb-3">
            <x-form.input label="Name" name="name" placeholder="Enter Employee Name" :oldval="$employee->name" />
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
            <x-form.input label="Rate" name="rate" placeholder="Enter Employee Rate" :oldval="$employee->rate" />
        </div>

        @php
            $paymentOptions = [
                'hourly' => 'Hourly',
                'monthly' => 'Monthly',
                'per_project' => 'Per Project',
            ];
            $selectedValue = old('payment_type', $employee->payment_type ?? '');
        @endphp

        <div class="mb-3">
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

        <div class="mb-3">
            <x-form.select2 label="Currency" name="currency" :options="['EUR' => 'Euro', 'USD' => 'US Dollar', 'DZD' => 'Algerian Dinar']" :selected="$employee->currency ?? old('currency')" placeholder="Select currency" />
        </div>

        <!-- 🔻 New Payment Details Section -->
        <div class="row">
            <div class="mb-3 col-md-6">
                <x-form.input label="Account Name" name="account_name" placeholder="Enter Account Name"
                    :oldval="old('account_name', $employee->account_name ?? '')" />
            </div>

            <div class="mb-3 col-md-6">
                <x-form.input label="Account Number" name="account_number" placeholder="Enter Account Number"
                    :oldval="old('account_number', $employee->account_number ?? '')" />
            </div>

            <div class="mb-3 col-md-6">
                <x-form.input label="IBAN / RIB" name="iban" placeholder="Enter IBAN / RIB" :oldval="old('iban', $employee->iban ?? '')" />
            </div>

            <div class="mb-3 col-md-6">
                <x-form.input label="Bank Code" name="bank_code" placeholder="Enter Bank Code or SWIFT"
                    :oldval="old('bank_code', $employee->bank_code ?? '')" />
            </div>
        </div>
    </div>

    @php
        // فقط إذا كانت هذه عملية تعديل (موجود موظف ومرتبط بمستخدم من نوع employee)
        $user = ($employee->exists && $employee->user && $employee->user->type === 'employee') ? $employee->user : null;
    @endphp

    <!-- Tab 4: معلومات تسجيل الدخول -->
    <div class="tab-pane fade" id="login" role="tabpanel" aria-labelledby="login-tab">
        <x-form.input label="Username" name="user[name]" placeholder="Enter Username" :oldval="old('user.name', $user?->name ?? '')" />
        <x-form.input label="Email" name="user[email]" placeholder="Enter Login Email" :oldval="old('user.email', $user?->email ?? '')" />
        <div class="mb-3">
            <x-form.input label="Password" name="user[password]" type="password"
                placeholder="Enter Password (leave blank to keep current)" />
        </div>
        {{-- <div class="form-group mb-3">
            <input type="hidden" name="user[is_marketer]" value="0">

            <div class="form-check">
                <input class="form-check-input @error('user.is_marketer') is-invalid @enderror" type="checkbox"
                    name="user[is_marketer]" id="is_marketer" value="1" {{ old('user.is_marketer', $user?->is_marketer)
                ? 'checked' : '' }}>
                <label class="form-check-label" for="is_marketer">
                    🧑‍💼 Is this employee a marketer?
                </label>
                @error('user.is_marketer')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <small class="form-text text-muted">
                Tick this if the employee will manage their assigned clients and earn commissions.
            </small>
        </div>
        <div class="form-group mb-3">
            <input type="hidden" name="user[is_accountant]" value="0">

            <div class="form-check">
                <input class="form-check-input @error('user.is_accountant') is-invalid @enderror" type="checkbox"
                    name="user[is_accountant]" id="is_accountant" value="1" {{ old('user.is_accountant',
                    $user?->is_accountant) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_accountant">
                    👨‍💼 Is this employee an accountant?
                </label>
                @error('user.is_accountant')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <small class="form-text text-muted">
                Tick this if the employee will manage financial transactions and accounts.
            </small>
        </div> --}}

        <!-- بطاقة المسوق -->
        <div class="card mb-3 border-success">
            <div class="card-header bg-success text-white">
                🧑‍💼 Marketer Role
            </div>
            <div class="card-body">
                <input type="hidden" name="user[is_marketer]" value="0">

                <div class="form-check">
                    <input class="form-check-input @error('user.is_marketer') is-invalid @enderror" type="checkbox"
                        name="user[is_marketer]" id="is_marketer" value="1" {{ old('user.is_marketer', $user?->is_marketer) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_marketer">
                        Is this employee a marketer?
                    </label>
                    @error('user.is_marketer')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <small class="form-text text-muted">
                    Tick this if the employee will manage their assigned clients and earn commissions.
                </small>
            </div>
        </div>

        <!-- بطاقة المحاسب -->
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary text-white">
                👨‍💼 Accountant Role
            </div>
            <div class="card-body">
                <input type="hidden" name="user[is_accountant]" value="0">

                <div class="form-check">
                    <input class="form-check-input @error('user.is_accountant') is-invalid @enderror" type="checkbox"
                        name="user[is_accountant]" id="is_accountant" value="1" {{ old('user.is_accountant', $user?->is_accountant) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_accountant">
                        Is this employee an accountant?
                    </label>
                    @error('user.is_accountant')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <small class="form-text text-muted">
                    Tick this if the employee will manage financial transactions and accounts.
                </small>
            </div>
        </div>

    </div>


</div>
