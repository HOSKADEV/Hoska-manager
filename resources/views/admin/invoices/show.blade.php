<x-dashboard title="Invoice Details">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Invoice Details - #{{ $invoice->invoice_number }}</h1>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-info"><i class="fas fa-long-arrow-alt-left"></i>All
            Invoices</a>
    </div>

    <div class="card shadow-sm mb-4" id="printable-area">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Project Financial Summary</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Total Amount</h6>
                        <p class="h5 text-primary">{{ number_format($totalAmount, 2) }}
                            {{ $invoice->project->currency }}
                        </p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Paid Amount</h6>
                        <p class="h5 text-success">{{ number_format($paidAmount, 2) }} {{ $invoice->project->currency }}
                        </p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Remaining Amount</h6>
                        <p class="h5 text-danger">{{ number_format($remainingAmount, 2) }}
                            {{ $invoice->project->currency }}
                        </p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <h6>Paid Percentage</h6>
                        <p class="h5 text-info">{{ $paidPercentage }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4" id="invoice-details">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Invoice Information</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <th>Invoice Number</th>
                        <td>{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <th>Project</th>
                        <td>{{ $invoice->project->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Client</th>
                        <td>{{ $invoice->client->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td>{{ number_format($invoice->amount, 2) }} {{ $invoice->project->currency }}</td>
                    </tr>
                    <tr>
                        <th>Is Paid</th>
                        <td>
                            @if($invoice->is_paid)
                                <span class="badge bg-success px-3 py-1 text-danger">Paid</span>
                            @else
                                <span class="badge bg-warning px-3 py-1 text-dark">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Invoice Date</th>
                        <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Due Date</th>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Wallet</th>
                        <td>{{ $invoice->wallet->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $invoice->updated_at->format('Y-m-d H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mb-5">
        <button class="btn btn-primary btn-lg" onclick="printInvoice()">
            <i class="fas fa-print"></i> Print Invoice
        </button>
    </div>

    @push('js')
        <script>
            function printInvoice() {
                let printContents = document.getElementById('printable-area').innerHTML + document.getElementById('invoice-details').innerHTML;
                let originalContents = document.body.innerHTML;

                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                window.location.reload();
            }
        </script>
    @endpush
</x-dashboard>
