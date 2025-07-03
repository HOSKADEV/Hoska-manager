<x-dashboard title="Wallet Transactions">
    @push('css')
        <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
        <style>
            /* يمكن إضافة تنسيقات خاصة هنا */
            .badge-type {
                text-transform: capitalize;
                padding: 0.3em 0.6em;
                border-radius: 0.25rem;
                color: white;
                font-weight: 600;
            }

            .badge-expense {
                background-color: #dc3545;
                /* أحمر */
            }

            .badge-income {
                background-color: #28a745;
                /* أخضر */
            }

            .badge-transfer {
                background-color: #17a2b8;
                /* سماوي */
            }

            .badge-withdraw {
                background-color: #ffc107;
                /* أصفر */
                color: #212529;
            }

            .badge-funding {
                background-color: #6f42c1;
                /* بنفسجي */
            }
        </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Wallet Transactions</h1>
        <a href="{{ route('admin.wallet-transactions.create') }}" class="btn btn-info">Expense Transactions</a>
    </div>

    @php
        $typeLabels = [
            'expense' => 'Expense',
            'income' => 'Income',
            'transfer_in' => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            'withdraw' => 'Withdraw',
            'funding' => 'Funding',
        ];
        $typeClasses = [
            'expense' => 'expense',
            'income' => 'income',
            'transfer_in' => 'transfer',
            'transfer_out' => 'transfer',
            'withdraw' => 'withdraw',
            'funding' => 'funding',
        ];
    @endphp

    <form method="GET" class="form-inline mb-3" id="filterForm">
        <select name="wallet_id" class="form-control mr-2" onchange="document.getElementById('filterForm').submit()">
            <option value="">All Wallet</option>
            @foreach ($wallets as $wallet)
                <option value="{{ $wallet->id }}" {{ request('wallet_id') == $wallet->id ? 'selected' : '' }}>
                    {{ $wallet->name }}
                </option>
            @endforeach
        </select>

        <select name="type" class="form-control mr-2" onchange="document.getElementById('filterForm').submit()">
            <option value="">All Type</option>
            @foreach(array_keys($typeLabels) as $type)
                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                    {{ $typeLabels[$type] }}
                </option>
            @endforeach
        </select>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Wallet</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Exchange Rate</th> {{-- عمود سعر الصرف --}}
                            <th>Description</th>
                            <th>Related Wallet</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Wallet</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Exchange Rate</th>
                            <th>Description</th>
                            <th>Related Wallet</th>
                            <th>Date</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse($transactions as $txn)
                            <tr>
                                <td>{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}
                                </td>
                                <td>{{ $txn->wallet->name }}</td>
                                <td>
                                    <span class="badge badge-type badge-{{ $typeClasses[$txn->type] ?? 'income' }}">
                                        {{ $typeLabels[$txn->type] ?? ucfirst($txn->type) }}
                                    </span>
                                </td>
                                @php
                                    $currencySymbols = [
                                        'USD' => '$',
                                        'EUR' => '€',
                                        'DZD' => 'DA',
                                    ];
                                @endphp
                                <td>
                                    {{ $currencySymbols[$txn->wallet->currency] ?? '' }}{{ number_format($txn->amount, 2) }}
                                </td>
                                <td>
                                    {{-- عرض سعر الصرف فقط إذا كان من نوع تحويل --}}
                                    @if(in_array($txn->type, ['transfer_in', 'transfer_out']) && $txn->exchange_rate)
                                        {{ number_format($txn->exchange_rate, 6) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $txn->description ?? '-' }}</td>
                                <td>{{ $txn->relatedWallet ? $txn->relatedWallet->name : '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($txn->transaction_date)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $transactions->withQueryString()->links() }}
        </div>
    </div>

    @push('js')
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    @endpush
</x-dashboard>
