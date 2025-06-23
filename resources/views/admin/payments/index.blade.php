<x-dashboard title="Main Dashboard">
    @push('css')
        <style>
            .badge-custom {
                display: inline-block;
                padding: 0.4em 0.75em;
                font-size: 0.9rem;
                font-weight: 600;
                color: #fff;
                border-radius: 0.5rem;
                white-space: nowrap;
                margin-right: 0.3em;
                vertical-align: middle;
            }

            .badge-invoice {
                background-color: #17a2b8;
                /* أزرق سماوي */
            }

            .badge-muted {
                background-color: #6c757d;
                /* رمادي */
            }
        </style>
    @endpush
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Payments</h1>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Note</th>
                            <th>Invoice Number</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Note</th>
                            <th>Invoice Number</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                @php
                                    $x = 0;
                                @endphp
                                <td>{{ $x += 1}}</td>
                                <td>{{ $payment->amount }}</td>
                                <td>{{ $payment->payment_date->diffForHumans() }}</td>
                                <td>{{ $payment->note }}</td>
                                <td>
                                    @if($payment->invoice && $payment->invoice->invoice_number)
                                        <span class="badge-custom badge-invoice">{{ $payment->invoice->invoice_number }}</span>
                                    @else
                                        <span class="badge-custom badge-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $payment->created_at->diffForHumans() }}</td>
                                <td>{{ $payment->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.payments.edit', $payment->id) }}"
                                        class="btn btn-sm btn-primary"><i class='fas fa-edit'></i></a>
                                    <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST"
                                        style="display: inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?!')" type="submit"
                                            class="btn btn-sm btn-danger"><i class='fas fa-trash'></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="d-none"></td>
                                <td colspan="11" class="text-center">No Data Found</td>
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
