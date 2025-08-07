<x-dashboard title="All Wallets">
    @push('css')
        <style>
            .badge-currency {
                background-color: #20c997;
                color: white;
                padding: 0.3em 0.6em;
                font-size: 0.8rem;
                border-radius: 0.3rem;
            }
        </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Wallets</h1>
        <a href="{{ route('admin.wallets.create') }}" class="btn btn-info"><i class="fas fa-plus"></i> Add Wallet</a>
    </div>

    <!-- Wallets Summary Cards -->
    <div class="row mb-4">

        <!-- Total Balance USD -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total USD Balance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalsByCurrency['USD'] ?? 0, 2) }} $
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Balance EUR -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total EUR Balance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalsByCurrency['EUR'] ?? 0, 2) }} €
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Balance DZD -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total DZD Balance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalsByCurrency['DZD'] ?? 0, 2) }} DZ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Balance in DZD (All Wallets converted) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Balance in DZD
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalInDZD, 2) }} DZ
                            </div>
                        </div>
                        <div class="col-auto">


                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                        <!-- Button to open the modal -->
                        <button type="button" class="btn btn-sm btn-outline-warning mt-2" data-toggle="modal"
                            data-target="#exchangeRateModal">
                            <i class="fas fa-exchange-alt mr-1"></i> Enter Exchange Rate
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="exchangeRateModal" tabindex="-1" role="dialog" aria-labelledby="exchangeRateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="GET" action="{{ route('admin.wallets.index') }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exchangeRateModalLabel">Enter Exchange Rate</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="usd_rate">USD to DZD</label>
                            <input type="text" step="0.01" class="form-control" name="usd_rate" id="usd_rate"
                                value="{{ $usdRate }}">
                        </div>
                        <div class="form-group">
                            <label for="eur_rate">EUR to DZD</label>
                            <input type="text" step="0.01" class="form-control" name="eur_rate" id="eur_rate"
                                value="{{ $eurRate }}">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">تحويل</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Balance</th>
                            <th>Currency</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Balance</th>
                            <th>Currency</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($wallets as $wallet)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $wallet->name }}</td>
                                {{-- <td>${{ number_format($wallet->balance, 2) }}</td> --}}
                                @php
                                    $currencySymbols = [
                                        'USD' => '$',
                                        'EUR' => '€',
                                        'DZD' => 'DZ',
                                    ];
                                @endphp
                                <td>
                                    {{ $currencySymbols[$wallet->currency] ?? '' }} {{ number_format($wallet->balance, 2) }}
                                </td>
                                <td>
                                    <span class="badge badge-currency">{{ $wallet->currency }}</span>
                                </td>
                                <td>{{ $wallet->created_at->diffForHumans() }}</td>
                                <td>{{ $wallet->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.wallets.show', $wallet->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.wallets.edit', $wallet->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.wallets.destroy', $wallet->id) }}" method="POST"
                                        style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?')" type="submit"
                                            class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No Wallets Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('css')
        <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    @endpush

    @push('js')
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    @endpush
</x-dashboard>
