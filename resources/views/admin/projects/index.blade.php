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

            .badge-employee {
                background-color: #20c997;
                /* أخضر فاتح */
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
        <h1 class="h3 text-gray-800">All Projects</h1>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    <!-- Project Summary Cards -->
    <div class="row mb-4">

        <!-- Total Amount by Currency -->
        <div class="col-xl-12 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center mb-2">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Amount by Currency
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row">
                        @php
                            $currencySymbols = ['USD' => '$', 'EUR' => '€', 'DZD' => 'DZ'];
                        @endphp
                        @foreach($totalsByCurrency as $currency => $amount)
                            <div class="col-md-4 text-center">
                                <div class="h6 font-weight-bold text-gray-800">
                                    {{ $currencySymbols[$currency] ?? '' }} {{ number_format($amount, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Projects Count -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Projects
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $projectCount }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Amount in DZD -->
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center mb-2">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Amount Converted to DZD
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800 text-center">
                        {{ number_format($totalInDZD, 2) }} DZ
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Projects Table -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.projects.index') }}" class="mb-4 w-75" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="month" class="form-label fw-bold text-secondary">📅 Filter by Month</label>
                        <select name="month" id="month" class="form-select select2"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>
                                📆 All Months
                            </option>
                            @foreach ($availableMonths as $month)
                                <option value="{{ $month['value'] }}" {{ $selectedMonth === $month['value'] ? 'selected' : '' }}>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            {{-- <th>Description</th> --}}
                            <th>TotalAmount</th>
                            <th>Start Date</th>
                            <th>Duration</th>
                            <th>Delivery Date</th>
                            <th>Remaining Days</th>
                            <th>Remaining Amount</th>
                            {{-- <th>Attachments</th>
                            <th>Client Name</th>
                            <th>Employee Name</th> --}}
                            {{-- <th>User Name</th> --}}
                            {{-- <th>Created At</th>
                            <th>Updated At</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            {{-- <th>Description</th> --}}
                            <th>Total Amount</th>
                            <th>Start Date</th>
                            <th>Duration</th>
                            <th>Delivery Date</th>
                            <th>Remaining Days</th>
                            <th>Remaining Amount</th>
                            {{-- <th>Attachments</th>
                            <th>Client Name</th>
                            <th>Employee Name</th> --}}
                            {{-- <th>User Name</th> --}}
                            {{-- <th>Created At</th>
                            <th>Updated At</th> --}}
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr class="{{ $project->row_class }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $project->name }}</td>

                                @php
                                    $currencySymbols = [
                                        'USD' => '$',
                                        'EUR' => '€',
                                        'DZD' => 'DZ',
                                    ];
                                    $top = floor($project->remaining_days);
                                    $down = ceil($project->remaining_days);
                                @endphp
                                <td>
                                    {{ $currencySymbols[$project->currency] ?? '' }}
                                    {{ number_format($project->total_amount, 2) }}
                                </td>
                                <td>{{ $project->start_date ?? '-' }}</td>
                                <td>{{ $project->duration_days ? $project->duration_days . ' days' : '-' }}</td>
                                <td>{{ $project->delivery_date ?? '-' }}</td>
                                <td>
                                    @if (!is_null($project->remaining_days))
                                        @if ($project->remaining_days < 0)
                                            <span class="badge badge-danger">Overdue {{ abs($top) }}
                                                day(s)</span>
                                        @elseif ($project->remaining_days >= 0 && $project->remaining_days <= 1)
                                            <span class="badge badge-warning">Due Today</span>
                                        @else
                                            <span class="badge badge-success">{{ $top }} day(s)</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $currencySymbols[$project->currency] ?? '' }} {{ number_format($project->remaining_amount, 2) }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.projects.edit', $project->id) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class='fas fa-edit'></i>
                                    </a>
                                    <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST"
                                        style="display: inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?!')" type="submit"
                                            class="btn btn-sm btn-danger">
                                            <i class='fas fa-trash'></i>
                                        </button>
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
        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('js')
        <!-- Page level plugins -->
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- Page level custom scripts -->
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>

        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function () {
                $('#month').select2({
                    placeholder: "📆 Select an option",
                    allowClear: true,
                    width: '100%'
                });

                // استمع لأي تغيير في الفلاتر وأرسل الفورم
                $('#month').on('change', function () {
                    $(this).closest('form').submit();
                });
            });
        </script>
    @endpush
</x-dashboard>
