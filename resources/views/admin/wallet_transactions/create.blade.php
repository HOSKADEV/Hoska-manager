<x-dashboard title="Add New Wallet Transaction">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add New Transaction</h1>
        <a href="{{ route('admin.wallet-transactions.index') }}" class="btn btn-info">
            <i class="fas fa-arrow-left"></i> All Transactions
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin.wallet-transactions.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="type" class="form-label">Transaction Type</label>
                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="" selected disabled>-- Select Type --</option>
                        @foreach(['expense', 'income', 'transfer', 'withdraw', 'funding'] as $type)
                            <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="wallet_id" class="form-label">Wallet</label>
                    <select name="wallet_id" id="wallet_id" class="form-control @error('wallet_id') is-invalid @enderror" required>
                        <option value="" selected disabled>-- Select Wallet --</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}" {{ old('wallet_id') == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->name }} ({{ $wallet->currency }})
                            </option>
                        @endforeach
                    </select>
                    @error('wallet_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="related-wallet-div" style="display: none;">
                    <label for="related_wallet_id" class="form-label">Related Wallet (For Transfer)</label>
                    <select name="related_wallet_id" id="related_wallet_id" class="form-control @error('related_wallet_id') is-invalid @enderror">
                        <option value="" selected disabled>-- Select Related Wallet --</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}" {{ old('related_wallet_id') == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->name }} ({{ $wallet->currency }})
                            </option>
                        @endforeach
                    </select>
                    @error('related_wallet_id')
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

                <div class="mb-3">
                    <label for="transaction_date" class="form-label">Transaction Date</label>
                    <input type="datetime-local" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                        class="form-control @error('transaction_date') is-invalid @enderror" required>
                    @error('transaction_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description (Optional)</label>
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description">{{ old('description') }}</textarea>
                </div>

                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Transaction</button>

            </form>

        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('type');
                const relatedWalletDiv = document.getElementById('related-wallet-div');

                function toggleRelatedWallet() {
                    if (typeSelect.value === 'transfer') {
                        relatedWalletDiv.style.display = 'block';
                    } else {
                        relatedWalletDiv.style.display = 'none';
                        document.getElementById('related_wallet_id').value = '';
                    }
                }

                typeSelect.addEventListener('change', toggleRelatedWallet);

                // Call once on load
                toggleRelatedWallet();
            });
        </script>
    @endpush

</x-dashboard>
