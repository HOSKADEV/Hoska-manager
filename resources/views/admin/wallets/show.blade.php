<x-dashboard title="Wallet Details">
    @push('css')
        <style>
            .badge-currency {
                background-color: #20c997;
                color: white;
                padding: 0.25em 0.6em;
                font-size: 0.75rem;
                border-radius: 0.25rem;
                margin-left: 5px;
            }

            .table-secondary thead {
                background-color: #e9ecef;
            }

            tfoot tr {
                background-color: #f8f9fa;
                font-weight: bold;
            }
        </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">{{ $wallet->name }}</h1>
        <a href="{{ route('admin.wallets.index') }}" class="btn btn-info">
            <i class="fas fa-long-arrow-alt-left"></i> Back to Wallets
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>
                Balance:
                <span class="badge badge-success">
                    {{ number_format($wallet->balance, 2) }}
                    <span class="badge-currency">{{ $wallet->currency }}</span>
                </span>
            </h5>

            <p><strong>Currency:</strong> <span class="badge-currency">{{ $wallet->currency }}</span></p>
            <p><strong>Created:</strong> {{ $wallet->created_at->diffForHumans() }}</p>
            <p><strong>Updated:</strong> {{ $wallet->updated_at->diffForHumans() }}</p>
        </div>
    </div>

    <h4 class="mb-3">Recent Transactions</h4>
    <div class="table-responsive mb-3">
        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Related Wallet</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $txn)
                    <tr>
                        <td>{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}</td>
                        <td>{{ ucfirst($txn->type) }}</td>
                        <td>{{ number_format($txn->amount, 2) }}</td>
                        <td>{{ $txn->description ?? '-' }}</td>
                        <td>{{ $txn->relatedWallet?->name ?? '-' }}</td>
                        <td>{{ $txn->transaction_date->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No Transactions Found</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Total In:</strong> {{ number_format($totalIn, 2) }} <span
                            class="badge-currency">{{ $wallet->currency }}</span></td>
                    <td colspan="3"><strong>Total Out:</strong> {{ number_format($totalOut, 2) }} <span
                            class="badge-currency">{{ $wallet->currency }}</span></td>
                </tr>
            </tfoot>
        </table>
        {{ $transactions->withQueryString()->links() }}
    </div>

    <h4 class="mb-3">Payments Received (From Invoices Linked to this Wallet)</h4>
    <div class="table-responsive mb-3">
        <table class="table table-bordered table-striped">
            <thead class="table-secondary">
                <tr>
                    <th>#</th>
                    <th>Invoice Number</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                        <td>{{ $payment->invoice->invoice_number ?? '-' }}</td>
                        <td>{{ number_format($payment->amount * ($payment->exchange_rate ?? 1), 2) }}</td>
                        <td>{{ $payment->payment_date->format('Y-m-d H:i') }}</td>
                        <td>{{ $payment->note ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No Payments Found</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Total Payments</strong></td>
                    <td colspan="3">{{ number_format($totalPayments, 2) }} <span
                            class="badge-currency">{{ $wallet->currency }}</span></td>
                </tr>
            </tfoot>

        </table>
        {{ $payments->withQueryString()->links() }}
    </div>
</x-dashboard>
