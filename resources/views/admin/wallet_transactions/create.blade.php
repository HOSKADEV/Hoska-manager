<x-dashboard title="Add New Wallet Transaction">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add New Transaction</h1>
        <a href="{{ route('admin.wallet-transactions.index') }}" class="btn btn-info">
            <i class="fas fa-arrow-left"></i> All Transactions
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.wallet-transactions.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="type" class="form-label">Transaction Type</label>
                    @if(auth()->user()->is_accountant == 1)
                        <!-- Accountant can only create expense transactions -->
                        <input type="hidden" name="type" value="expense" id="type">
                        <div class="form-control">
                            <span class="badge bg-danger text-white">Expense</span>
                        </div>
                    @else
                        <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                            <option value="" selected disabled>Select Type</option>
                            @foreach(['expense', 'income', 'transfer', 'withdraw', 'funding','sallary'] as $type)
                                <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @endif
                </div>

                @php
                    $currencySymbols = [
                        'USD' => '$',
                        'EUR' => '€',
                        'DZD' => 'DZ',
                    ];
                @endphp
                <div class="mb-3">
                    <label for="wallet_id" class="form-label">Wallet</label>
                    @if(auth()->user()->is_accountant == 1)
                        <!-- Accountant can only use Cash wallet -->
                        @php
                            $cashWallet = $wallets->first(function($wallet) {
                                return strtolower($wallet->name) === 'cash';
                            });
                        @endphp
                        @if($cashWallet)
                            <input type="hidden" name="wallet_id" value="{{ $cashWallet->id }}" id="wallet_id">
                            <div class="form-control">
                                {{ $cashWallet->name }} ({{ $currencySymbols[$cashWallet->currency] ?? '' }})
                            </div>
                            <div class="mb-2 text-muted" id="wallet-balance"
                                style="display: block; font-weight: 600 !important; color: #1e7e34 !important; margin-top: 5px !important;">
                                Balance: <span id="wallet-balance-value">{{ $currencySymbols[$cashWallet->currency] ?? '' }} {{ number_format($cashWallet->balance, 2) }}</span>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No Cash wallet found. Please contact the administrator.
                            </div>
                        @endif
                    @else
                        <select name="wallet_id" id="wallet_id"
                            class="form-control @error('wallet_id') is-invalid @enderror" required>
                            <option value="" selected disabled>Select Wallet</option>
                            @foreach ($wallets as $wallet)
                                <option value="{{ $wallet->id }}" {{ old('wallet_id') == $wallet->id ? 'selected' : '' }}>
                                    {{ $wallet->name }} ({{ $currencySymbols[$wallet->currency] ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="mb-2 text-muted" id="wallet-balance"
                            style="display: none; font-weight: 600 !important; color: #1e7e34 !important; margin-top: 5px !important;">
                            Balance: <span id="wallet-balance-value"></span>
                        </div>
                        @error('wallet_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @endif
                </div>

                <div class="mb-3" id="related-wallet-div" style="display: none;">
                    <label for="related_wallet_id" class="form-label">Related Wallet (For Transfer)</label>
                    <select name="related_wallet_id" id="related_wallet_id"
                        class="form-control @error('related_wallet_id') is-invalid @enderror">
                        <option value="" selected disabled>Select Related Wallet</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}" {{ old('related_wallet_id') == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->name }} ({{ $currencySymbols[$wallet->currency] ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    <div class="mb-2 text-muted" id="related-wallet-balance"
                        style="display: none; font-weight: 600 !important; color: #155724 !important; margin-top: 5px !important;">
                        Balance: <span id="related-wallet-balance-value"></span>
                    </div>
                    @error('related_wallet_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="exchange-rate-div" style="display: none;">
                    <label for="exchange_rate" class="form-label">Exchange Rate (For Transfer)</label>
                    <input type="text" step="0.000001" min="0" name="exchange_rate" id="exchange_rate"
                        value="{{ old('exchange_rate') }}"
                        class="form-control @error('exchange_rate') is-invalid @enderror"
                        placeholder="Enter exchange rate manually">
                    @error('exchange_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="text" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}"
                        class="form-control @error('amount') is-invalid @enderror" placeholder="Enter amount" required>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2 text-success" id="converted-amount" style="display: none;">
                    Converted Amount: <span id="converted-amount-value"></span>
                </div>

                <div class="mb-3">
                    <label for="transaction_date" class="form-label">Transaction Date</label>
                    <input type="datetime-local" name="transaction_date" id="transaction_date"
                        value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                        class="form-control @error('transaction_date') is-invalid @enderror" required>
                    @error('transaction_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description (Optional)</label>
                    <textarea name="description" id="description"
                        class="form-control @error('description') is-invalid @enderror" rows="3"
                        placeholder="Enter description">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Transaction</button>
            </form>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const typeSelect = document.getElementById('type');
                const walletSelect = document.getElementById('wallet_id');
                const relatedWalletSelect = document.getElementById('related_wallet_id');
                const amountInput = document.getElementById('amount');
                const exchangeRateInput = document.getElementById('exchange_rate');
                const exchangeRateLabel = document.querySelector('label[for="exchange_rate"]');

                const balanceDiv = document.getElementById('wallet-balance');
                const balanceSpan = document.getElementById('wallet-balance-value');
                const relatedBalanceDiv = document.getElementById('related-wallet-balance');
                const relatedBalanceSpan = document.getElementById('related-wallet-balance-value');

                const convertedAmountDiv = document.getElementById('converted-amount');
                const convertedAmountSpan = document.getElementById('converted-amount-value');

                let currentBalance = 0;
                let fromCurrency = '';
                let toCurrency = '';

                const currencySymbols = {
                    'USD': '$',
                    'EUR': '€',
                    'DZD': 'DZ'
                };

                function formatBalance(balance, currency) {
                    const symbol = currencySymbols[currency] || currency;
                    return `${symbol} ${parseFloat(balance).toFixed(2)}`;
                }

                function toggleFields() {
                    const relatedWalletDiv = document.getElementById('related-wallet-div');
                    const exchangeRateDiv = document.getElementById('exchange-rate-div');

                    // Accountant users can't see transfer options
                    const isAccountant = {{ auth()->user()->is_accountant == 1 ? 'true' : 'false' }};

                    if (isAccountant) {
                        // Hide transfer-related fields for accountants
                        relatedWalletDiv.style.display = 'none';
                        exchangeRateDiv.style.display = 'none';
                        document.getElementById('related_wallet_id').value = '';
                        document.getElementById('exchange_rate').value = '';
                        exchangeRateInput.required = false;
                        exchangeRateInput.disabled = false;
                        exchangeRateLabel.textContent = 'Exchange Rate (For Transfer)';
                        relatedBalanceDiv.style.display = 'none';
                        convertedAmountDiv.style.display = 'none';
                    } else if (typeSelect.value === 'transfer') {
                        relatedWalletDiv.style.display = 'block';
                        exchangeRateDiv.style.display = 'block';
                    } else {
                        relatedWalletDiv.style.display = 'none';
                        exchangeRateDiv.style.display = 'none';
                        document.getElementById('related_wallet_id').value = '';
                        document.getElementById('exchange_rate').value = '';
                        exchangeRateInput.required = false;
                        exchangeRateInput.disabled = false;
                        exchangeRateLabel.textContent = 'Exchange Rate (For Transfer)';
                        relatedBalanceDiv.style.display = 'none';
                        convertedAmountDiv.style.display = 'none';
                    }
                }

                function fetchWalletInfo(walletId, callback) {
                    if (!walletId) {
                        callback({ balance: 0, currency: '' });
                        return;
                    }

                    fetch(`/admin/wallets/${walletId}/balance`)
                        .then(res => res.json())
                        .then(data => callback(data));
                }

                function updateBalanceDisplay(data) {
                    currentBalance = parseFloat(data.balance);
                    fromCurrency = data.currency;
                    balanceSpan.textContent = formatBalance(currentBalance, fromCurrency);
                    balanceDiv.style.display = 'block';
                    updateExchangeLabel();
                }

                function updateExchangeLabel() {
                    if (typeSelect.value === 'transfer' && fromCurrency && toCurrency) {
                        exchangeRateLabel.textContent = `Exchange Rate (From ${fromCurrency} to ${toCurrency})`;

                        if (fromCurrency === toCurrency) {
                            exchangeRateInput.required = false;
                            exchangeRateInput.value = 1;
                            exchangeRateInput.disabled = true;
                        } else {
                            exchangeRateInput.required = true;
                            exchangeRateInput.disabled = false;
                            if (!exchangeRateInput.value || exchangeRateInput.value == 1) {
                                exchangeRateInput.value = '';
                            }
                        }
                    } else {
                        exchangeRateLabel.textContent = 'Exchange Rate (For Transfer)';
                        exchangeRateInput.required = false;
                        exchangeRateInput.disabled = false;
                    }

                    updateConvertedAmount();
                }

                function updateConvertedAmount() {
                    const amount = parseFloat(amountInput.value);
                    const exchangeRate = parseFloat(exchangeRateInput.value);

                    if (
                        typeSelect.value === 'transfer' &&
                        !isNaN(amount) &&
                        !isNaN(exchangeRate) &&
                        toCurrency
                    ) {
                        const converted = (amount * exchangeRate).toFixed(2);
                        const symbol = currencySymbols[toCurrency] || toCurrency;
                        convertedAmountSpan.textContent = `${symbol} ${converted}`;
                        convertedAmountDiv.style.display = 'block';
                    } else {
                        convertedAmountDiv.style.display = 'none';
                    }
                }

                amountInput.addEventListener('input', function () {
                    const amount = parseFloat(amountInput.value);
                    const type = typeSelect.value;

                    if (['expense', 'withdraw', 'transfer'].includes(type)) {
                        if (amount > currentBalance) {
                            amountInput.setCustomValidity('Insufficient balance in selected wallet.');
                        } else {
                            amountInput.setCustomValidity('');
                        }
                    } else {
                        amountInput.setCustomValidity('');
                    }

                    updateConvertedAmount();
                });

                exchangeRateInput.addEventListener('input', updateConvertedAmount);

                relatedWalletSelect.addEventListener('change', function () {
                    fetchWalletInfo(relatedWalletSelect.value, function (data) {
                        toCurrency = data.currency;
                        updateExchangeLabel();
                        relatedBalanceSpan.textContent = formatBalance(data.balance, toCurrency);
                        relatedBalanceDiv.style.display = 'block';
                        updateConvertedAmount();
                    });
                });

                const isAccountant = {{ auth()->user()->is_accountant == 1 ? 'true' : 'false' }};

                if (!isAccountant) {
                    typeSelect.addEventListener('change', function () {
                        toggleFields();
                        updateExchangeLabel();
                        amountInput.dispatchEvent(new Event('input'));
                    });
                }

                walletSelect.addEventListener('change', function () {
                    fetchWalletInfo(walletSelect.value, function (data) {
                        fromCurrency = data.currency;
                        updateBalanceDisplay(data);
                    });
                });

                // For accountant users, pre-load the cash wallet balance
                @if(auth()->user()->is_accountant == 1)
                    @if(isset($cashWallet))
                        currentBalance = {{ $cashWallet->balance }};
                        fromCurrency = '{{ $cashWallet->currency }}';
                        balanceSpan.textContent = formatBalance(currentBalance, fromCurrency);
                    @endif
                @endif

                toggleFields();

                // Load initial wallet balance if preselected
                if (walletSelect.value) {
                    fetchWalletInfo(walletSelect.value, updateBalanceDisplay);
                }
                if (relatedWalletSelect.value) {
                    fetchWalletInfo(relatedWalletSelect.value, function (data) {
                        toCurrency = data.currency;
                        relatedBalanceSpan.textContent = formatBalance(data.balance, toCurrency);
                        relatedBalanceDiv.style.display = 'block';
                        updateExchangeLabel();
                    });
                }
            });
        </script>
    @endpush

</x-dashboard>
