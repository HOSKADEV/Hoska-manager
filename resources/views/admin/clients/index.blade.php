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
                vertical-align: middle;
            }

            .badge-user {
                background-color: #fd7e14;
                /* برتقالي */
            }

            .badge-muted {
                background-color: #6c757d;
                /* رمادي */
            }
        </style>
    @endpush
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Clients</h1>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    @if(auth()->user()->is_marketer)
        <div class="col-xl-12 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="w-100">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-3">
                                Total Commission (Converted)
                            </div>

                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="h6 font-weight-bold text-gray-800">
                                        $ {{ number_format($totalCommissionUSD, 2) }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="h6 font-weight-bold text-gray-800">
                                        € {{ number_format($totalCommissionEUR, 2) }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="h6 font-weight-bold text-gray-800">
                                        DZ {{ number_format($totalCommissionDZD, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pl-3 d-flex align-items-center">
                            <i class="fas fa-coins fa-3x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- DataTales Example -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Notes</th>
                            <th>User Name</th>
                            @if (auth()->user()->is_marketer)
                                <th>Commission %</th>
                                <th>Commission Value</th>
                            @else
                                <th class="d-none">Commission %</th>
                                <th class="d-none">Commission Value</th>
                            @endif
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Notes</th>
                            <th>User Name</th>
                            @if (auth()->user()->is_marketer)
                                <th>Commission %</th>
                                <th>Commission Value</th>
                            @else
                                <th class="d-none">Commission %</th>
                                <th class="d-none">Commission Value</th>
                            @endif
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->contacts->first()->phone ?? '-' }}</td>
                                <td>{{ $client->contacts->first()->email ?? '-' }}</td>
                                <td>{{ $client->contacts->first()->address ?? '-' }}</td>
                                <td>{{ $client->notes ?? '_' }}</td>
                                <td>
                                    <span class="badge-custom badge-user">{{ $client->user->name ?? 'Unknown' }}</span>
                                </td>
                                @if (auth()->user()->is_marketer)
                                    <td>{{ number_format($client->commissionPercent, 2) }}%</td>
                                    @php
                                        $currencySymbols = [
                                            'USD' => '$',
                                            'EUR' => '€',
                                            'DZD' => 'DZ',
                                        ];
                                    @endphp
                                    <td>{{ number_format($client->commissionValue, 2) }}
                                        {{ $currencySymbols[$client->currency] ?? '' }}
                                    </td>
                                @endif
                                <td>{{ $client->created_at->diffForHumans() }}</td>
                                <td>{{ $client->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.clients.edit', $client->id) }}"
                                        class="btn btn-sm btn-primary"><i class='fas fa-edit'></i></a>
                                    <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST"
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
                                <td colspan="10" class="text-center">No Data Found</td>
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
