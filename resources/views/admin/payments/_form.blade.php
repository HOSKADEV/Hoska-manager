
@if (isset($payment) && $payment->exists)
<div class="mb-3">
    <x-form.input label="Amount" name="amount" placeholder="Enter Payment amount"
        :oldval="$payment->invoice->amount ?? ''" readonly />
</div>
@endif


<div class="mb-3">
    <x-form.input type="datetime-local" label="Payment Date" name="payment_date" placeholder="Enter Payment Date" :oldval="$payment->payment_date" />
</div>

<div class="mb-3">
    <x-form.area label="Note" name="note" placeholder="Enter Payment Note"
        :oldval="$payment->note" />
</div>

<div class="mb-3 col-md-12">
    <label for="invoice_id" class="form-label">Invoice</label>
    <select name="invoice_id" id="invoice_id" class="form-control">
        <option value="">Select Invoice</option>
        @foreach ($invoices as $invoice)
            <option value="{{ $invoice->id }}"
                @if (old('invoice_id', $payment->invoice_id ?? '') == $invoice->id) selected @endif>
                {{ $invoice->invoice_number }}
            </option>
        @endforeach
    </select>
</div>

