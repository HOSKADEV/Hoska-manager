<div class="mb-3">
    <x-form.input label="Name" name="name" placeholder="Enter Employee Name" :oldval="$employee->name" />
</div>

<div class="mb-3">
    <x-form.input label="Phone" name="phone" placeholder="Enter Employee Phone" :oldval="$employee->phone" />
</div>
<div class="mb-3">
    <x-form.input label="Email" name="email" placeholder="Enter Employee Email" :oldval="$employee->email" />
</div>
<div class="mb-3">
    <x-form.input label="Address" name="address" placeholder="Enter Employee Address" :oldval="$employee->address" />
</div>


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

<div class="mb-3 col-md-12">
    <label for="payment_type" class="form-label">Payment Type</label>
    <select name="payment_type" id="payment_type" class="form-control">
        <option value="" disabled {{ $selectedValue == '' ? 'selected' : '' }}>Select Payment Type</option>
        @foreach($paymentOptions as $value => $label)
            <option value="{{ $value }}" {{ $selectedValue == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>


<div class="mb-3 col-md-12">
    <x-form.select label="User" name="user_id" placeholder='Select User' :options="$users"
        :oldval="$employee->user_id" />
</div>
