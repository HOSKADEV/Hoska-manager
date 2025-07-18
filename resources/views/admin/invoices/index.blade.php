<x-dashboard title="Main Dashboard">
    @push('css')
        <style>
            .badge-custom {
                display: inline-block;
                padding: 0.5em 0.8em;
                font-size: 0.9rem;
                font-weight: 600;
                color: #fff;
                border-radius: 0.5rem;
                white-space: nowrap;
                margin-right: 0.3em;
                margin-bottom: 0.2em;
            }

            .badge-client {
                background-color: #007bff;
                /* أزرق */
            }

            .badge-project {
                background-color: #c70cba;
                /* بنفسجي */
            }

            .badge-muted {
                background-color: #6c757d;
                /* رمادي */
            }

            .badge-paid {
                background-color: #28a745;
                /* أخضر Bootstrap */
                color: white;
                font-weight: 600;
                padding: 0.35em 0.7em;
                border-radius: 0.4rem;
                font-size: 0.85rem;
            }

            .badge-unpaid {
                background-color: #dc3545;
                /* أحمر Bootstrap */
                color: white;
                font-weight: 600;
                padding: 0.35em 0.7em;
                border-radius: 0.4rem;
                font-size: 0.85rem;
            }
        </style>
    @endpush
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Invoices</h1>
        <a href="{{ route('admin.invoices.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice Number</th>
                            <th>Amount</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Is Paid</th>
                            <th>Project Name</th>
                            <th>Client Name</th>
                            {{-- <th>Wallet</th>  --}}
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Invoice Number</th>
                            <th>Amount</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Is Paid</th>
                            <th>Project Name</th>
                            <th>Client Name</th>
                            {{-- <th>Wallet</th> --}}
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $invoice->invoice_number }}</td>
                                {{-- <td>{{ $invoice->amount }}</td> --}}
                                @php
                                    $currencySymbols = [
                                        'USD' => '$',
                                        'EUR' => '€',
                                        'DZD' => 'DZ',
                                    ];
                                @endphp
                                <td>
                                    {{ $currencySymbols[$invoice->project?->currency] ?? '' }}
                                    {{ number_format($invoice->amount, 2) }}
                                </td>
                                <td>{{ $invoice->invoice_date->diffForHumans() }}</td>
                                <td>{{ $invoice->due_date?->diffForHumans() ?? '_' }}</td>
                                <td>
                                    @if($invoice->is_paid)
                                        <span class="badge-paid">Paid</span>
                                    @else
                                        <span class="badge-unpaid">Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    @if($invoice->project)
                                        <span class="badge-custom badge-project">{{ $invoice->project->name }}</span>
                                    @else
                                        <span class="badge-custom badge-muted">_</span>
                                    @endif
                                </td>
                                <td>
                                    @if($invoice->project && $invoice->project->client)
                                        <span class="badge-custom badge-client">{{ $invoice->project->client->name }}</span>
                                    @else
                                        <span class="badge-custom badge-muted">N/A</span>
                                    @endif
                                </td>
                                {{-- <td>
                                    @if($invoice->wallet)
                                        <span class="badge-custom badge-muted">{{ $invoice->wallet->name }}</span>
                                    @else
                                        <span class="badge-custom badge-muted">N/A</span>
                                    @endif
                                </td> --}}
                                <td>{{ $invoice->created_at->diffForHumans() }}</td>
                                <td>{{ $invoice->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('admin.invoices.edit', $invoice->id) }}"
                                        class="btn btn-sm btn-primary" title="Edit">
                                        <i class='fas fa-edit'></i>
                                    </a>
                                    <form action="{{ route('admin.invoices.destroy', $invoice->id) }}" method="POST"
                                        style="display: inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?!')" type="submit"
                                            class="btn btn-sm btn-danger" title="Delete">
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="d-none"></td>
                                <td colspan="12" class="text-center">No Data Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('css')
        <!-- Custom styles for this page -->
        <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    @endpush

    @push('js')
        <!-- Page level plugins -->
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- Page level custom scripts -->
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    @endpush
</x-dashboard>
