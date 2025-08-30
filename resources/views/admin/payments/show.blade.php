<x-dashboard title="Payment Details">
    @php
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'DZD' => 'DZ',
            // أضف رموز عملات أخرى حسب الحاجة
        ];
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Payment Details - #{{ $payment->id }}</h1>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-info">
            <i class="fas fa-long-arrow-alt-left"></i> All Payments
        </a>
    </div>

    <div class="card shadow-sm mb-4" id="printable-area">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Payment Information</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <th>Amount</th>
                        <td>
                            @if($payment->invoice)
                                {{ $payment->invoice->currency }}
                            @endif
                            {{ number_format($payment->amount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Date</th>
                        <td>{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Note</th>
                        <td>{{ $payment->note ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Invoice Number</th>
                        <td>{{ $payment->invoice->invoice_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Wallet</th>
                        <td>
                            {{ $payment->invoice->wallet->name ?? '-' }}
                            @if(isset($payment->invoice->wallet->currency))
                                ({{ $payment->invoice->wallet->currency }})
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Exchange Rate</th>
                        <td>{{ $payment->exchange_rate ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Converted Amount (After Exchange)</th>
                        <td>
                            @if($payment->exchange_rate && $payment->amount)
                                {{ number_format($payment->amount * $payment->exchange_rate, 2) }}
                                ({{ $payment->wallet->currency }})
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $payment->updated_at->format('Y-m-d H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-primary btn-lg" onclick="printPayment()">
            <i class="fas fa-print"></i> Print Payment
        </button>
    </div>

    @push('js')
        <script>
            function printPayment() {
                let printContents = document.getElementById('printable-area').innerHTML;
                let originalContents = document.body.innerHTML;

                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                window.location.reload();
            }
        </script>
    @endpush
</x-dashboard>
