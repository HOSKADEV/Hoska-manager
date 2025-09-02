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
        @unless(Auth::user()->type === 'employee')
            <a href="{{ route('admin.projects.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
        @endunless
    </div>

    <!-- Project Summary Cards -->
    <div class="row mb-4">
        @if(auth()->user()->type !== 'employee')
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
                        <div class="row currency-initial">
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
        @endif

        <!-- Total Projects Count -->
        <div class="{{ auth()->user()->type === 'employee' ? 'col-xl-12' : 'col-xl-6 col-md-6' }} mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Projects
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="projectCount">
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

        @if(auth()->user()->type !== 'employee')
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800 text-center" id="totalInDZD">
                            {{ number_format($totalInDZD, 2) }} DZ
                        </div>
                        <!-- Button to open the modal -->
                        <button type="button" class="btn btn-sm btn-outline-warning mt-2" data-toggle="modal"
                            data-target="#exchangeRateModal">
                            <i class="fas fa-exchange-alt mr-1"></i> Enter Exchange Rate
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="exchangeRateModal" tabindex="-1" role="dialog"
                aria-labelledby="exchangeRateModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form method="GET" action="{{ route('admin.projects.index') }}">
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

        @endif
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

                    <div class="col-md-4">
                        <label for="status" class="form-label fw-bold text-secondary">📊 Filter by Status</label>
                        <select name="status" id="status" class="form-select select2"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>
                                📋 All Statuses
                            </option>
                            <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>
                                ⏳ In Progress
                            </option>
                            <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>
                                ✅ Completed
                            </option>
                            <option value="in_deadline" {{ $statusFilter === 'in_deadline' ? 'selected' : '' }}>
                                ⏰ In Deadline
                            </option>
                            <option value="after_deadline" {{ $statusFilter === 'after_deadline' ? 'selected' : '' }}>
                                ❌ After Deadline
                            </option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                {{-- # --}}
                                <div class="form-check" style="margin-bottom: 25px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </div>
                            </th>
                            <th>Name</th>
                            {{-- <th>Description</th> --}}
                            @unless(Auth::user()->type === 'employee')
                                <th>Total Amount</th>
                            @endunless
                            <th>Start Date</th>
                            <th>Duration</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th>Remaining Days</th>
                            @unless(Auth::user()->type === 'employee')
                                <th>Remaining Amount</th>
                                <th>Expenses</th>
                                <th>Profits</th>
                            @endunless
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
                            @unless(Auth::user()->type === 'employee')
                                <th>Total Amount</th>
                            @endunless
                            <th>Start Date</th>
                            <th>Duration</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th>Remaining Days</th>
                            @unless(Auth::user()->type === 'employee')
                                <th>Remaining Amount</th>
                                <th>Expenses</th>
                                <th>Profits</th>
                            @endunless
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
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="project-checkbox form-check-input" data-id="{{ $project->id }}" data-amount="{{ $project->total_amount }}" data-currency="{{ $project->currency }}">
                                    </div>
                                </td>
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
                                @php
                                    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'DZD' => 'DZ'];
                                @endphp
                                @unless(Auth::user()->type === 'employee')
                                    <td>
                                        {{ $currencySymbols[$project->currency] ?? '' }}
                                        {{ number_format($project->total_amount_project_with_developments, 2) }}
                                    </td>
                                @endunless
                                {{-- <td>
                                    {{ $currencySymbols[$project->currency] ?? '' }}
                                    {{ number_format($project->total_amount, 2) }}
                                </td> --}}
                                <td>{{ $project->start_date ?? '-' }}</td>
                                <td>{{ $project->duration_days ? $project->duration_days . ' days' : '-' }}</td>
                                <td>{{ $project->delivery_date ?? '-' }}</td>
                                <td>
                                    @if ($project->delivered_at)
                                        <span class="badge bg-success text-white">✅ Completed</span>
                                    @elseif (!is_null($project->remaining_days))
                                        @if ($project->remaining_days < 0)
                                            <span class="badge bg-danger text-white">❌ After Deadline</span>
                                        @elseif ($project->remaining_days >= 0 && $project->remaining_days <= 1)
                                            <span class="badge bg-warning text-dark">⏰ In Deadline</span>
                                        @else
                                            <span class="badge bg-info text-white">⏳ In Progress</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary text-white">📋 Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($project->delivered_at)
                                        <span class="badge badge-primary">Delivered</span>
                                    @elseif (!is_null($project->remaining_days))
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
                                @unless(Auth::user()->type === 'employee')
                                    <td>
                                        <span class="{{ ($project->total_amount_project_with_developments - $project->total_paid_amount_project_with_developments) == 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $currencySymbols[$project->currency] ?? '' }}
                                            {{ number_format($project->total_amount_project_with_developments - $project->total_paid_amount_project_with_developments, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $currencySymbols[$project->currency] ?? '' }}
                                        {{ number_format($project->total_expenses, 2) }}
                                    </td>
                                    <td>
                                        <span class="{{ ($project->total_paid_amount_project_with_developments - $project->total_expenses) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $currencySymbols[$project->currency] ?? '' }}
                                            {{ number_format($project->total_paid_amount_project_with_developments - $project->total_expenses, 2) }}
                                        </span>
                                    </td>
                                @endunless
                                <td>
                                    <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(Auth::user()->type !== 'employee')
                                        @if (is_null($project->delivered_at))
                                            <form action="{{ route('admin.projects.markDelivered', $project->id) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="return confirm('Mark as delivered?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif

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
                                    @endif
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
        <script src="{{ asset("assets/js/demo/datatables-demo.js?v=" . time()) }}"></script>

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

                // Store initial statistics values
                const initialStats = {
                    totalsByCurrency: {},
                    projectCount: 0,
                    totalInDZD: 0
                };

                // Initialize initial statistics from the rendered page
                @if(auth()->user()->type !== 'employee')
                    @foreach($totalsByCurrency as $currency => $amount)
                        initialStats.totalsByCurrency['{{ $currency }}'] = {{ $amount }};
                    @endforeach
                    initialStats.totalInDZD = {{ $totalInDZD }};
                @endif
                initialStats.projectCount = {{ $projectCount }};

                // Handle select all checkbox
                $('#selectAll').change(function() {
                    $('.project-checkbox').prop('checked', $(this).prop('checked'));
                    updateStatistics();
                });

                // Handle individual project checkboxes
                $('.project-checkbox').change(function() {
                    updateStatistics();

                    // Update select all checkbox state
                    const allCheckboxes = $('.project-checkbox');
                    const checkedCheckboxes = $('.project-checkbox:checked');

                    if (checkedCheckboxes.length === 0) {
                        $('#selectAll').prop('checked', false);
                        $('#selectAll').prop('indeterminate', false);
                    } else if (checkedCheckboxes.length === allCheckboxes.length) {
                        $('#selectAll').prop('checked', true);
                        $('#selectAll').prop('indeterminate', false);
                    } else {
                        $('#selectAll').prop('checked', false);
                        $('#selectAll').prop('indeterminate', true);
                    }
                });

                // Function to update statistics based on selected projects
                function updateStatistics() {
                    const selectedProjects = $('.project-checkbox:checked');

                    if (selectedProjects.length === 0) {
                        // No projects selected, show initial statistics
                        $('.currency-initial').show();
                        $('.currency-container').hide();
                        @if(auth()->user()->type !== 'employee')
                            $('#totalInDZD').text(numberFormat(initialStats.totalInDZD, 2) + ' DZ');
                        @endif
                        $('#projectCount').text(initialStats.projectCount);
                        return;
                    }

                    // Hide initial statistics when projects are selected
                    $('.currency-initial').hide();
                    $('.currency-container').show();

                    // Calculate statistics for selected projects
                    const selectedTotals = {};
                    let selectedTotalInDZD = 0;

                    selectedProjects.each(function() {
                        const amount = parseFloat($(this).data('amount'));
                        const currency = $(this).data('currency');

                        if (!selectedTotals[currency]) {
                            selectedTotals[currency] = 0;
                        }
                        selectedTotals[currency] += amount;
                    });

                    // Convert to DZD
                    @if(auth()->user()->type !== 'employee')
                        const usdRate = {{ $usdRate }};
                        const eurRate = {{ $eurRate }};

                        Object.keys(selectedTotals).forEach(currency => {
                            let rate = 1;
                            if (currency === 'USD') rate = usdRate;
                            if (currency === 'EUR') rate = eurRate;
                            selectedTotalInDZD += selectedTotals[currency] * rate;
                        });

                        // Update UI
                        updateCurrencyCards(selectedTotals);
                        $('#totalInDZD').text(numberFormat(selectedTotalInDZD, 2) + ' DZ');
                    @endif

                    $('#projectCount').text(selectedProjects.length);
                }

                // Helper function to update currency cards
                function updateCurrencyCards(totals) {
                    const currencySymbols = {'USD': '$', 'EUR': '€', 'DZD': 'DZ'};

                    // Clear existing currency displays
                    $('.currency-display').remove();

                    // Add new currency displays
                    const currencyContainer = $('.currency-container');
                    if (currencyContainer.length === 0) {
                        // Create container if it doesn't exist
                        const row = $('<div class="row currency-container"></div>');
                        $('.card.border-left-success .card-body .row').first().after(row);
                    }

                    Object.keys(totals).forEach(currency => {
                        const col = $(`
                            <div class="col-md-4 text-center currency-display">
                                <div class="h6 font-weight-bold text-gray-800">
                                    ${currencySymbols[currency] || ''} ${numberFormat(totals[currency], 2)}
                                </div>
                            </div>
                        `);
                        $('.currency-container').append(col);
                    });
                }

                // Helper function to format numbers
                function numberFormat(number, decimals) {
                    return number.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }
            });
        </script>

    @endpush
</x-dashboard>
