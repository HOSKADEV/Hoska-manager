<div class="mb-3">
    <x-form.input label="Invoice Number" name="invoice_number" placeholder="Enter Invoice Invoice Number"
        :oldval="$invoice->invoice_number" />
</div>

@if (isset($invoice) && $invoice->exists)
    <div class="mb-3">
        <x-form.input label="Amount" name="amount" placeholder="Enter Invoice Amount"
            :oldval="$invoice->project->total_amount ?? ''" readonly />
    </div>
@endif

<div class="mb-3">
    <x-form.input type="datetime-local" label="Invoice Date" name="invoice_date" placeholder="Enter Invoice Date"
        :oldval="$invoice->invoice_date" />
</div>

<div class="mb-3">
    <x-form.input type="datetime-local" label="Due Date" name="due_date" placeholder="Enter Due Date"
        :oldval="$invoice->due_date" />
</div>


@php
    $selectedIsPaid = old('is_paid', $invoice->is_paid ?? '');
@endphp

<div class="mb-3">
    <label for="is_paid" class="form-label">Select Is Paid</label>
    <select class="form-control @error('is_paid') is-invalid @enderror mt-2" id="is_paid" name="is_paid">
        <option value="" disabled {{ $selectedIsPaid === '' ? 'selected' : '' }}>Select Is Paid</option>
        <option value="1" {{ $selectedIsPaid == 1 ? 'selected' : '' }}>Paid</option>
        <option value="0" {{ $selectedIsPaid == 0 ? 'selected' : '' }}>Unpaid</option>
    </select>
    @error('is_paid')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 col-md-12">
    <x-form.select label="Project" name="project_id" placeholder='Select Project' :options="$projects"
        :oldval="$invoice->project_id" />
</div>

@if (isset($invoice) && $invoice->exists)
    <div class="mb-3 col-md-12">
        <x-form.input label="Client" name="client_name" placeholder="Client Name"
            :oldval="$invoice->project->client->name ?? 'N/A'" readonly />
    </div>
@endif

