@php
    $currencySymbols = [
        'USD' => '$',
        'EUR' => '€',
        'DZD' => 'DZ',
    ];
    $currency = old('project_id')
        ? optional(\App\Models\Project::find(old('project_id')))->currency
        : ($invoice->project->currency ?? null);
    $currencySymbol = $currency && isset($currencySymbols[$currency]) ? $currencySymbols[$currency] : '';
@endphp

<div class="mb-3">
    <x-form.input label="Invoice Number" name="invoice_number" placeholder="Enter Invoice Number"
        :oldval="old('invoice_number', $invoice->invoice_number ?? '')" />
</div>

<div class="mb-3">
    <x-form.select label="Project" name="project_id" id="project_id" placeholder='Select Project' :options="$projects"
        :oldval="old('project_id', $invoice->project_id ?? '')" />
</div>

<div id="project-stats" class="alert alert-info d-none">
    <p>💼 <strong>Project Total:</strong> <span id="project-total"></span> <span class="project-currency-symbol"></span>
    </p>
    <p>💰 <strong>Paid:</strong> <span id="project-paid"></span> <span class="project-currency-symbol"></span></p>
    <p>📉 <strong>Remaining:</strong> <span id="project-remaining"></span> <span class="project-currency-symbol"></span>
    </p>
</div>

<div class="mb-3">
    <x-form.input label="Amount" name="amount" id="invoice-amount" placeholder="Enter Invoice Amount"
        :oldval="old('amount', $invoice->amount ?? '')" />
</div>

<div id="invoice-calculation" class="alert alert-warning d-none">
    <p>📊 This is <strong><span id="amount-percentage"></span>%</strong> of project total.</p>
    <p>🧾 Remaining after this invoice: <strong><span id="remaining-after"></span> <span
                class="project-currency-symbol"></span></strong></p>
</div>

<div class="mb-3">
    <x-form.input type="datetime-local" label="Invoice Date" name="invoice_date" placeholder="Enter Invoice Date"
        :oldval="old('invoice_date', $invoice->invoice_date ?? '')" />
</div>

<div class="mb-3">
    <x-form.input type="datetime-local" label="Due Date" name="due_date" placeholder="Enter Due Date"
        :oldval="old('due_date', $invoice->due_date ?? '')" />
</div>

{{-- @php
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
</div> --}}


<input type="hidden" name="is_paid" value="0" />

{{-- <div class="mb-3">
    <x-form.select label="Wallet" name="wallet_id" placeholder="Select Wallet" :options="$wallets"
        :oldval="old('wallet_id', $invoice->wallet_id ?? '')" />
</div> --}}

@push('js')
    <script>
        const currencySymbols = {
            'USD': '$',
            'EUR': '€',
            'DZD': 'DZ'
        };

        let currencySymbol = @json($currencySymbol);
        let financialData = { total: 0, paid: 0, remaining: 0 };

        function updateCurrencySymbols() {
            document.querySelectorAll('.project-currency-symbol').forEach(el => {
                el.textContent = currencySymbol;
            });
        }

        function updateCalculations() {
            const amount = parseFloat(document.getElementById('invoice-amount')?.value || 0);
            if (isNaN(amount) || financialData.total === 0) {
                document.getElementById('invoice-calculation').classList.add('d-none');
                return;
            }

            const percent = ((amount / financialData.total) * 100).toFixed(2);
            const newRemaining = (financialData.remaining - amount).toFixed(2);

            document.getElementById('amount-percentage').textContent = percent;
            document.getElementById('remaining-after').textContent = newRemaining;
            document.getElementById('invoice-calculation').classList.remove('d-none');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const projectSelect = document.getElementById('project_id');
            const amountInput = document.getElementById('invoice-amount');

            updateCurrencySymbols();

            if (projectSelect && projectSelect.value) {
                // تحميل بيانات المشروع الحالي من السيرفر حتى بعد الرجوع من الخطأ
                fetch(`/admin/projects/${projectSelect.value}/financials`)
                    .then(res => res.json())
                    .then(data => {
                        financialData = data;

                        document.getElementById('project-total').textContent = data.total;
                        document.getElementById('project-paid').textContent = data.paid;
                        document.getElementById('project-remaining').textContent = data.remaining;

                        if (data.currency && currencySymbols[data.currency]) {
                            currencySymbol = currencySymbols[data.currency];
                        } else {
                            currencySymbol = '';
                        }

                        updateCurrencySymbols();
                        document.getElementById('project-stats').classList.remove('d-none');
                        updateCalculations();
                    }).catch(error => {
                        console.error('Error fetching initial project financials:', error);
                    });
            }

            if (projectSelect) {
                projectSelect.addEventListener('change', function () {
                    const projectId = this.value;
                    if (!projectId) return;

                    fetch(`/admin/projects/${projectId}/financials`)
                        .then(res => res.json())
                        .then(data => {
                            financialData = data;

                            document.getElementById('project-total').textContent = data.total;
                            document.getElementById('project-paid').textContent = data.paid;
                            document.getElementById('project-remaining').textContent = data.remaining;

                            if (data.currency && currencySymbols[data.currency]) {
                                currencySymbol = currencySymbols[data.currency];
                            } else {
                                currencySymbol = '';
                            }

                            updateCurrencySymbols();
                            document.getElementById('project-stats').classList.remove('d-none');
                            updateCalculations();
                        }).catch(error => {
                            console.error('Error fetching project financials:', error);
                        });
                });
            }

            if (amountInput) {
                amountInput.addEventListener('input', updateCalculations);
                updateCalculations();
            }
        });
    </script>
@endpush
