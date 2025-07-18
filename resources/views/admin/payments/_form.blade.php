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

<div id="invoice-info" class="alert alert-info d-none">
    <p>👤 Client: <strong id="client-name"></strong></p>
    <p>📁 Project: <strong id="project-name"></strong></p>
    <p>💵 Amount: <strong id="invoice-amount"></strong> <span id="invoice-currency"></span></p>
</div>

<div class="mb-3" id="exchange-rate-group">
    <x-form.input type="text" step="0.0001" min="0" label="Exchange Rate" name="exchange_rate"
        placeholder="Enter exchange rate" :oldval="old('exchange_rate', $payment->exchange_rate ?? '')" />
</div>

@if (isset($payment) && $payment->exists)
    <div class="mb-3">
        <x-form.input label="Amount" name="amount" placeholder="Enter Payment amount" :oldval="$payment->invoice->amount ?? ''" readonly />
    </div>
@endif

<div class="mb-3">
    <x-form.select3 label="Wallet" name="wallet_id" placeholder="Select Wallet" :options="$wallets"
        :oldval="old('wallet_id', $payment->wallet_id ?? '')" />
</div>


<div class="mb-3">
    <x-form.input type="datetime-local" label="Payment Date" name="payment_date" placeholder="Enter Payment Date"
        :oldval="$payment->payment_date" />
</div>

<div class="mb-3">
    <x-form.area label="Note" name="note" placeholder="Enter Payment Note" :oldval="$payment->note" />
</div>

@push('js')
    <script>
        const wallets = @json($walletsFull); // قائمة المحافظ مع خصائصها بما فيها العملة
        const invoices = @json($invoices->load(['project', 'client'])); // الفواتير مع المشاريع والعميل فقط

        const currencySymbols = {
            'USD': '$',
            'EUR': '€',
            'DZD': 'DZ'
        };

        const invoiceSelect = document.getElementById('invoice_id');
        const walletSelect = document.getElementById('wallet_id');
        const exchangeRateGroup = document.getElementById('exchange-rate-group');

        function updateExchangeRateVisibility() {
            const selectedInvoiceId = invoiceSelect.value;
            const selectedWalletId = walletSelect.value;

            const invoice = invoices.find(inv => inv.id == selectedInvoiceId);
            const selectedWallet = wallets.find(w => w.id == selectedWalletId);

            const label = exchangeRateGroup.querySelector('label');

            if (!invoice || !invoice.project || !selectedWallet) {
                exchangeRateGroup.style.display = 'none';
                if (label) label.textContent = 'Exchange Rate';
                return;
            }

            const projectCurrency = invoice.project.currency;
            const walletCurrency = selectedWallet.currency;

            if (!walletCurrency || walletCurrency === projectCurrency) {
                exchangeRateGroup.style.display = 'none';
                if (label) label.textContent = 'Exchange Rate';
            } else {
                exchangeRateGroup.style.display = 'block';
                const fromSymbol = currencySymbols[projectCurrency] || projectCurrency;
                const toSymbol = currencySymbols[walletCurrency] || walletCurrency;
                if (label) {
                    label.textContent = `Exchange Rate (${fromSymbol} → ${toSymbol})`;
                }
            }
        }

        // حدث عند تغيير الفاتورة أو المحفظة
        invoiceSelect.addEventListener('change', updateExchangeRateVisibility);
        walletSelect.addEventListener('change', updateExchangeRateVisibility);

        // عند تحميل الصفحة نفذ الوظيفة
        document.addEventListener('DOMContentLoaded', () => {
            updateExchangeRateVisibility();

            if (invoiceSelect.value) {
                invoiceSelect.dispatchEvent(new Event('change'));
            }
        });

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const currencySymbols = {
                'USD': '$',
                'EUR': '€',
                'DZD': 'DZ'
            };

            const selectInvoice = document.getElementById('invoice_id');

            if (selectInvoice) {
                selectInvoice.addEventListener('change', function () {
                    const invoiceId = this.value;
                    if (!invoiceId) return;

                    fetch(`/admin/invoices/${invoiceId}/info`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('client-name').textContent = data.client_name;
                            document.getElementById('project-name').textContent = data.project_name;
                            document.getElementById('invoice-amount').textContent = data.amount;

                            const currencySymbol = currencySymbols[data.currency] || data.currency;
                            document.getElementById('invoice-currency').textContent = currencySymbol;

                            document.getElementById('invoice-info').classList.remove('d-none');
                        })
                        .catch(err => {
                            console.error("Failed to load invoice info:", err);
                            document.getElementById('invoice-info').classList.add('d-none');
                        });
                });

                if (selectInvoice.value) {
                    selectInvoice.dispatchEvent(new Event('change'));
                }
            }
        });
    </script>

@endpush
