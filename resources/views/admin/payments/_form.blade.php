@if (isset($payment) && $payment->exists)
    <div class="mb-3">
        <x-form.input label="Amount" name="amount" placeholder="Enter Payment amount" :oldval="$payment->invoice->amount ?? ''" readonly />
    </div>
@endif


<div class="mb-3">
    <x-form.input type="datetime-local" label="Payment Date" name="payment_date" placeholder="Enter Payment Date"
        :oldval="$payment->payment_date" />
</div>

<div class="mb-3">
    <x-form.area label="Note" name="note" placeholder="Enter Payment Note" :oldval="$payment->note" />
</div>

<div class="mb-3">
    <label for="invoice_id" class="form-label">Invoice</label>
    <select name="invoice_id" id="invoice_id" class="form-control">
        <option value="">Select Invoice</option>
        @foreach ($invoices as $invoice)
            <option value="{{ $invoice->id }}" @if (old('invoice_id', $payment->invoice_id ?? '') == $invoice->id) selected
            @endif>
                {{ $invoice->invoice_number }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3" id="exchange-rate-group">
    <x-form.input type="text" step="0.0001" min="0" label="Exchange Rate" name="exchange_rate"
        placeholder="Enter exchange rate" :oldval="old('exchange_rate', $payment->exchange_rate ?? '')" />
</div>

@push('js')
    <script>
        const invoices = @json($invoices->load(['wallet', 'project']));

        const invoiceSelect = document.getElementById('invoice_id');
        const exchangeRateGroup = document.getElementById('exchange-rate-group');

        function updateExchangeRateVisibility() {
            const selectedInvoiceId = invoiceSelect.value;
            const invoice = invoices.find(inv => inv.id == selectedInvoiceId);

            if (!invoice || !invoice.wallet || !invoice.project) {
                exchangeRateGroup.style.display = 'none';
                return;
            }

            const walletCurrency = invoice.wallet.currency;
            const projectCurrency = invoice.project.currency;

            if (walletCurrency === projectCurrency) {
                exchangeRateGroup.style.display = 'none';
            } else {
                exchangeRateGroup.style.display = 'block';
            }
        }

        invoiceSelect.addEventListener('change', updateExchangeRateVisibility);

        document.addEventListener('DOMContentLoaded', updateExchangeRateVisibility);
    </script>
@endpush
