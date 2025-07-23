{{-- @php
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
    @if(isset($invoice) && $invoice->exists)
    <!-- عرض الحقل في حالة التعديل -->
    <x-form.input label="Invoice Number" name="invoice_number" placeholder="Enter Invoice Number"
        :oldval="old('invoice_number', $invoice->invoice_number ?? '')" />
    @endif
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
        <option value="" disabled {{ $selectedIsPaid==='' ? 'selected' : '' }}>Select Is Paid</option>
        <option value="1" {{ $selectedIsPaid==1 ? 'selected' : '' }}>Paid</option>
        <option value="0" {{ $selectedIsPaid==0 ? 'selected' : '' }}>Unpaid</option>
    </select>
    @error('is_paid')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


<input type="hidden" name="is_paid" value="0" />--}}

{{-- <div class="mb-3">
    <x-form.select label="Wallet" name="wallet_id" placeholder="Select Wallet" :options="$wallets"
        :oldval="old('wallet_id', $invoice->wallet_id ?? '')" />
</div> --}}

{{-- @push('js')
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
@endpush --}}

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

{{-- رقم الفاتورة (في حالة التعديل فقط) --}}
<div class="mb-3">
    @if(isset($invoice) && $invoice->exists)
        <x-form.input label="Invoice Number" name="invoice_number" placeholder="Enter Invoice Number"
            :oldval="old('invoice_number', $invoice->invoice_number ?? '')" />
    @endif
</div>
<div class="mb-4">
    <label class="font-weight-bold d-block mb-2" style="font-size: 1.1rem;">Select Input Type:</label>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="selection_type" id="select_project" value="project"
        {{ old('selection_type', '') == 'project' ? 'checked' : '' }}>
        <label class="form-check-label" for="select_project" style="cursor: pointer;">
        <span class="badge badge-primary px-3 py-2">Project</span>
        </label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="selection_type" id="select_development" value="development"
        {{ old('selection_type', '') == 'development' ? 'checked' : '' }}>
        <label class="form-check-label" for="select_development" style="cursor: pointer;">
        <span class="badge badge-success px-3 py-2">Development</span>
        </label>
    </div>
</div>


{{-- اختيار المشروع --}}
<div class="mb-3" id="project-container">
    <x-form.select label="🔧 Project" name="project_id" id="project_id" placeholder='Select Project'
        :options="$projects" :oldval="old('project_id', $invoice->project_id ?? '')" />
</div>

{{-- إحصائيات المشروع --}}
<div id="project-stats" class="alert alert-info d-none">
    <p>💼 <strong>Project Total:</strong> <span id="project-total"></span> <span class="project-currency-symbol"></span>
    </p>
    <p>💰 <strong>Paid:</strong> <span id="project-paid"></span> <span class="project-currency-symbol"></span></p>
    <p>📉 <strong>Remaining:</strong> <span id="project-remaining"></span> <span class="project-currency-symbol"></span>
    </p>
</div>

{{-- اختيار التطويرات --}}
<div class="mb-3" id="development_id-container">
    <x-form.select3 label="🧩Development" name="development_id" id="development_id"
        placeholder="Select a Development" :options="$developmentsOptions" :oldval="old('development_id', $invoice->development_id ?? '')" />
</div>

{{-- إحصائيات التطوير --}}
<div id="development-stats" class="alert alert-warning d-none">
    <p>💼 <strong>Development Total:</strong> <span id="development-total"></span> <span
            class="development-currency-symbol"></span></p>
    <p>💸 <strong>Paid:</strong> <span id="development-paid"></span> <span class="development-currency-symbol"></span>
    </p>
    <p>📉 <strong>Remaining:</strong> <span id="development-remaining"></span> <span
            class="development-currency-symbol"></span></p>
</div>

{{-- المبلغ --}}
<div class="mb-3">
    <x-form.input label="Amount" name="amount" id="invoice-amount" placeholder="Enter Invoice Amount"
        :oldval="old('amount', $invoice->amount ?? '')" />
</div>

{{-- النسبة والخصم --}}
<div id="invoice-calculation" class="alert alert-warning d-none">
    <p>📊 This is <strong><span id="amount-percentage"></span>%</strong> of project total.</p>
    <p>🧾 Remaining after this invoice: <strong><span id="remaining-after"></span> <span
                class="project-currency-symbol"></span></strong></p>
</div>

{{-- التواريخ --}}
<div class="mb-3">
    <x-form.input type="datetime-local" label="Invoice Date" name="invoice_date" :oldval="old('invoice_date', $invoice->invoice_date ?? '')" />
</div>

<div class="mb-3">
    <x-form.input type="datetime-local" label="Due Date" name="due_date" :oldval="old('due_date', $invoice->due_date ?? '')" />
</div>

{{-- حالة الدفع --}}
<input type="hidden" name="is_paid" value="0" />

@push('js')
    {{-- <script>
        const currencySymbols = {
            'USD': '$',
            'EUR': '€',
            'DZD': 'DZ'
        };

        let currencySymbol = '';
        let financialData = { total: 0, paid: 0, remaining: 0 };

        function updateCurrencySymbols() {
            document.querySelectorAll('.project-currency-symbol').forEach(el => {
                el.textContent = currencySymbol;
            });
            document.querySelectorAll('.development-currency-symbol').forEach(el => {
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

        function toggleInputsVisibility() {
            const projectRadio = document.getElementById('select_project');
            const developmentRadio = document.getElementById('select_development');

            const projectContainer = document.getElementById('project-container');
            const developmentsContainer = document.getElementById('development_id-container');

            if (projectRadio.checked) {
                projectContainer.style.display = 'block';
                developmentsContainer.style.display = 'none';
            } else if (developmentRadio.checked) {
                projectContainer.style.display = 'none';
                developmentsContainer.style.display = 'block';
            } else {
                projectContainer.style.display = 'none';
                developmentsContainer.style.display = 'none';
            }
        }

        function fetchProjectFinancials(projectId) {
            if (!projectId) return;

            fetch(`/admin/projects/${projectId}/financials`)
                .then(res => res.json())
                .then(data => {
                    financialData = data;

                    document.getElementById('project-total').textContent = data.total;
                    document.getElementById('project-paid').textContent = data.paid;
                    document.getElementById('project-remaining').textContent = data.remaining;

                    currencySymbol = currencySymbols[data.currency] || '';

                    updateCurrencySymbols();
                    document.getElementById('project-stats').classList.remove('d-none');
                    document.getElementById('development-stats').classList.add('d-none');
                    document.getElementById('invoice-calculation').classList.remove('d-none');

                    updateCalculations();
                })
                .catch(error => {
                    console.error('Error fetching project financials:', error);
                });
        }

        function fetchDevelopmentDetails(devId) {
            if (!devId) return;

            fetch(`/admin/developments/${devId}/details`)
                .then(res => res.json())
                .then(data => {
                    // تحديث بيانات العملة
                    currencySymbol = currencySymbols[data.currency] || '';

                    updateCurrencySymbols();

                    // تحديث البيانات المعروضة
                    document.getElementById('development-total').textContent = data.development_total || '0.00';
                    document.getElementById('development-paid').textContent = data.development_paid || '0.00';
                    document.getElementById('development-remaining').textContent = data.development_remaining || '0.00';

                    // إظهار معلومات التطوير وإخفاء المشروع
                    document.getElementById('development-stats').classList.remove('d-none');
                    document.getElementById('project-stats').classList.add('d-none');
                    document.getElementById('invoice-calculation').classList.remove('d-none');

                    // تحديث البيانات المالية العامة
                    financialData = {
                        total: parseFloat((data.development_total || '0').replace(/,/g, '')),
                        paid: parseFloat((data.development_paid || '0').replace(/,/g, '')),
                        remaining: parseFloat((data.development_remaining || '0').replace(/,/g, '')),
                    };

                    updateCalculations();
                })
                .catch(error => {
                    console.error('Error fetching development details:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const projectSelect = document.getElementById('project_id');
            const developmentSelect = document.getElementById('development_id');
            const amountInput = document.getElementById('invoice-amount');
            const projectRadio = document.getElementById('select_project');
            const developmentRadio = document.getElementById('select_development');

            updateCurrencySymbols();

            // اختيار الراديو تلقائيًا إذا فيه قيمة موجودة
            if (projectSelect && projectSelect.value) {
                projectRadio.checked = true;
                toggleInputsVisibility();
                fetchProjectFinancials(projectSelect.value);
            } else if (developmentSelect && developmentSelect.value) {
                developmentRadio.checked = true;
                toggleInputsVisibility();
                fetchDevelopmentDetails(developmentSelect.value);
            } else {
                toggleInputsVisibility();
            }

            projectRadio.addEventListener('change', () => {
                toggleInputsVisibility();
                if (developmentSelect) developmentSelect.value = '';
                document.getElementById('development-stats').classList.add('d-none');
                document.getElementById('invoice-calculation').classList.add('d-none');

                if (projectSelect && projectSelect.value) {
                    fetchProjectFinancials(projectSelect.value);
                }
            });

            developmentRadio.addEventListener('change', () => {
                toggleInputsVisibility();
                if (projectSelect) projectSelect.value = '';
                document.getElementById('project-stats').classList.add('d-none');
                document.getElementById('invoice-calculation').classList.add('d-none');

                if (developmentSelect && developmentSelect.value) {
                    fetchDevelopmentDetails(developmentSelect.value);
                }
            });

            if (projectSelect) {
                projectSelect.addEventListener('change', function () {
                    if (projectRadio.checked) {
                        fetchProjectFinancials(this.value);
                    }
                });
            }

            if (developmentSelect) {
                developmentSelect.addEventListener('change', function () {
                    if (developmentRadio.checked) {
                        fetchDevelopmentDetails(this.value);
                    }
                });
            }

            if (amountInput) {
                amountInput.addEventListener('input', updateCalculations);
                updateCalculations();
            }
        });
    </script> --}}

{{-- <script>
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'DZD': 'DZ'
    };

    let currencySymbol = '';
    let financialData = { total: 0, paid: 0, remaining: 0 };

    function updateCurrencySymbols() {
        document.querySelectorAll('.project-currency-symbol').forEach(el => {
            el.textContent = currencySymbol;
        });
        document.querySelectorAll('.development-currency-symbol').forEach(el => {
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

    function toggleInputsVisibility() {
        const projectRadio = document.getElementById('select_project');
        const developmentRadio = document.getElementById('select_development');

        const projectContainer = document.getElementById('project-container');
        const developmentsContainer = document.getElementById('development_id-container');

        if (projectRadio.checked) {
            projectContainer.style.display = 'block';
            developmentsContainer.style.display = 'none';
        } else if (developmentRadio.checked) {
            projectContainer.style.display = 'none';
            developmentsContainer.style.display = 'block';
        } else {
            projectContainer.style.display = 'none';
            developmentsContainer.style.display = 'none';
        }
    }

    function fetchProjectFinancials(projectId) {
        if (!projectId) return;

        fetch(`/admin/projects/${projectId}/financials`)
            .then(res => res.json())
            .then(data => {
                financialData = data;

                document.getElementById('project-total').textContent = data.total;
                document.getElementById('project-paid').textContent = data.paid;
                document.getElementById('project-remaining').textContent = data.remaining;

                currencySymbol = currencySymbols[data.currency] || '';

                updateCurrencySymbols();
                document.getElementById('project-stats').classList.remove('d-none');
                document.getElementById('development-stats').classList.add('d-none');
                document.getElementById('invoice-calculation').classList.remove('d-none');

                updateCalculations();
            })
            .catch(error => {
                console.error('Error fetching project financials:', error);
            });
    }

    function fetchDevelopmentDetails(devId) {
        if (!devId) return;

        fetch(`/admin/developments/${devId}/details`)
            .then(res => res.json())
            .then(data => {
                currencySymbol = currencySymbols[data.currency] || '';

                updateCurrencySymbols();

                document.getElementById('development-total').textContent = data.development_total || '0.00';
                document.getElementById('development-paid').textContent = data.development_paid || '0.00';
                document.getElementById('development-remaining').textContent = data.development_remaining || '0.00';

                document.getElementById('development-stats').classList.remove('d-none');
                document.getElementById('project-stats').classList.add('d-none');
                document.getElementById('invoice-calculation').classList.remove('d-none');

                financialData = {
                    total: parseFloat((data.development_total || '0').replace(/,/g, '')),
                    paid: parseFloat((data.development_paid || '0').replace(/,/g, '')),
                    remaining: parseFloat((data.development_remaining || '0').replace(/,/g, '')),
                };

                updateCalculations();
            })
            .catch(error => {
                console.error('Error fetching development details:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const projectSelect = document.getElementById('project_id');
        const developmentSelect = document.getElementById('development_id');
        const amountInput = document.getElementById('invoice-amount');
        const projectRadio = document.getElementById('select_project');
        const developmentRadio = document.getElementById('select_development');

        updateCurrencySymbols();

        // عرض الخيار المحدد تلقائياً عند التحميل (مهم للـ edit)
        if (projectSelect && projectSelect.value) {
            projectRadio.checked = true;
            toggleInputsVisibility();
            fetchProjectFinancials(projectSelect.value);
        } else if (developmentSelect && developmentSelect.value) {
            developmentRadio.checked = true;
            toggleInputsVisibility();
            fetchDevelopmentDetails(developmentSelect.value);
        } else {
            toggleInputsVisibility();
        }

        // دعم إعادة تحديث العرض حسب الراديو المحدد (للتأكد من الحالة بعد تحميل الصفحة)
        if (projectRadio.checked) {
            toggleInputsVisibility();
            if (projectSelect && projectSelect.value) {
                fetchProjectFinancials(projectSelect.value);
            }
        } else if (developmentRadio.checked) {
            toggleInputsVisibility();
            if (developmentSelect && developmentSelect.value) {
                fetchDevelopmentDetails(developmentSelect.value);
            }
        }

        projectRadio.addEventListener('change', () => {
            toggleInputsVisibility();
            if (developmentSelect) developmentSelect.value = '';
            document.getElementById('development-stats').classList.add('d-none');
            document.getElementById('invoice-calculation').classList.add('d-none');

            if (projectSelect && projectSelect.value) {
                fetchProjectFinancials(projectSelect.value);
            }
        });

        developmentRadio.addEventListener('change', () => {
            toggleInputsVisibility();
            if (projectSelect) projectSelect.value = '';
            document.getElementById('project-stats').classList.add('d-none');
            document.getElementById('invoice-calculation').classList.add('d-none');

            if (developmentSelect && developmentSelect.value) {
                fetchDevelopmentDetails(developmentSelect.value);
            }
        });

        if (projectSelect) {
            projectSelect.addEventListener('change', function () {
                if (projectRadio.checked) {
                    fetchProjectFinancials(this.value);
                }
            });
        }

        if (developmentSelect) {
            developmentSelect.addEventListener('change', function () {
                if (developmentRadio.checked) {
                    fetchDevelopmentDetails(this.value);
                }
            });
        }

        if (amountInput) {
            amountInput.addEventListener('input', updateCalculations);
            updateCalculations();
        }
    });
</script> --}}

<script>
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'DZD': 'DZ'
    };

    let currencySymbol = '';
    let financialData = { total: 0, paid: 0, remaining: 0 };

    function updateCurrencySymbols() {
        document.querySelectorAll('.project-currency-symbol').forEach(el => {
            el.textContent = currencySymbol;
        });
        document.querySelectorAll('.development-currency-symbol').forEach(el => {
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

    function toggleInputsVisibility() {
        const projectRadio = document.getElementById('select_project');
        const developmentRadio = document.getElementById('select_development');

        const projectContainer = document.getElementById('project-container');
        const developmentsContainer = document.getElementById('development_id-container');

        if (projectRadio.checked) {
            projectContainer.style.display = 'block';
            developmentsContainer.style.display = 'none';
        } else if (developmentRadio.checked) {
            projectContainer.style.display = 'none';
            developmentsContainer.style.display = 'block';
        } else {
            projectContainer.style.display = 'none';
            developmentsContainer.style.display = 'none';
        }
    }

    function fetchProjectFinancials(projectId) {
        if (!projectId) return;

        fetch(`/admin/projects/${projectId}/financials`)
            .then(res => res.json())
            .then(data => {
                financialData = data;

                document.getElementById('project-total').textContent = data.total;
                document.getElementById('project-paid').textContent = data.paid;
                document.getElementById('project-remaining').textContent = data.remaining;

                currencySymbol = currencySymbols[data.currency] || '';

                updateCurrencySymbols();
                document.getElementById('project-stats').classList.remove('d-none');
                document.getElementById('development-stats').classList.add('d-none');
                document.getElementById('invoice-calculation').classList.remove('d-none');

                updateCalculations();
            })
            .catch(error => {
                console.error('Error fetching project financials:', error);
            });
    }

    function fetchDevelopmentDetails(devId) {
        if (!devId) return;

        fetch(`/admin/developments/${devId}/details`)
            .then(res => res.json())
            .then(data => {
                currencySymbol = currencySymbols[data.currency] || '';

                updateCurrencySymbols();

                document.getElementById('development-total').textContent = data.development_total || '0.00';
                document.getElementById('development-paid').textContent = data.development_paid || '0.00';
                document.getElementById('development-remaining').textContent = data.development_remaining || '0.00';

                document.getElementById('development-stats').classList.remove('d-none');
                document.getElementById('project-stats').classList.add('d-none');
                document.getElementById('invoice-calculation').classList.remove('d-none');

                financialData = {
                    total: parseFloat((data.development_total || '0').replace(/,/g, '')),
                    paid: parseFloat((data.development_paid || '0').replace(/,/g, '')),
                    remaining: parseFloat((data.development_remaining || '0').replace(/,/g, '')),
                };

                updateCalculations();
            })
            .catch(error => {
                console.error('Error fetching development details:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const projectSelect = document.getElementById('project_id');
        const developmentSelect = document.getElementById('development_id');
        const amountInput = document.getElementById('invoice-amount');
        const projectRadio = document.getElementById('select_project');
        const developmentRadio = document.getElementById('select_development');

        updateCurrencySymbols();

        // عرض الخيار المحدد تلقائياً عند التحميل (مهم للـ edit)
        if (developmentSelect && developmentSelect.value) {
            developmentRadio.checked = true;
            toggleInputsVisibility();
            fetchDevelopmentDetails(developmentSelect.value);
        } else if (projectSelect && projectSelect.value) {
            projectRadio.checked = true;
            toggleInputsVisibility();
            fetchProjectFinancials(projectSelect.value);
        } else {
            toggleInputsVisibility();
        }

        projectRadio.addEventListener('change', () => {
            toggleInputsVisibility();
            if (developmentSelect) developmentSelect.value = '';
            document.getElementById('development-stats').classList.add('d-none');
            document.getElementById('invoice-calculation').classList.add('d-none');

            if (projectSelect && projectSelect.value) {
                fetchProjectFinancials(projectSelect.value);
            }
        });

        developmentRadio.addEventListener('change', () => {
            toggleInputsVisibility();
            if (projectSelect) projectSelect.value = '';
            document.getElementById('project-stats').classList.add('d-none');
            document.getElementById('invoice-calculation').classList.add('d-none');

            if (developmentSelect && developmentSelect.value) {
                fetchDevelopmentDetails(developmentSelect.value);
            }
        });

        if (projectSelect) {
            projectSelect.addEventListener('change', function () {
                if (projectRadio.checked) {
                    fetchProjectFinancials(this.value);
                }
            });
        }

        if (developmentSelect) {
            developmentSelect.addEventListener('change', function () {
                if (developmentRadio.checked) {
                    fetchDevelopmentDetails(this.value);
                }
            });
        }

        if (amountInput) {
            amountInput.addEventListener('input', updateCalculations);
            updateCalculations();
        }
    });
</script>


@endpush
