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
            }

            .badge-project {
                background-color: #6f42c1;
            }

            .badge-employee {
                background-color: #20c997;
            }

            .badge-muted {
                background-color: #6c757d;
            }

            .badge-danger {
                background-color: #dc3545;
            }
        </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Tasks</h1>
        <div class="">
            <button id="exportSelectedBtn" type="button" class="btn btn-success mr-2" style="display: none;" onclick="exportSelected()">
                <i class="fas fa-file-excel"></i> Export Selected
            </button>
            <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#importExcelModal">
                <i class="fas fa-file-upload"></i> Import Tasks from Excel
            </button>
            <a href="{{ route('admin.tasks.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog" aria-labelledby="importExcelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <form action="{{ route('admin.tasks.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="importExcelModalLabel">Import Tasks from Excel</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="excel_file">Select Excel File</label>
                            <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx"
                                required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload"></i> Upload File
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <!-- Team Hours Summary Cards -->
    <div class="row mb-4">
        <!-- Today's Hours -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Hours Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalTodayHours, 2) }} hrs
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Week's Hours -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Hours This Week
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalWeekHours, 2) }} hrs
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month's Hours -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Hours This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalMonthHours, 2) }} hrs
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Year's Hours -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Hours This Year
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalYearHours, 2) }} hrs
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            @if (Auth::user()->type !== 'employee')
                <div class="d-flex justify-content-between align-items-center mb-3">
                    {{-- فلتر حسب المشروع --}}
                    <form method="GET" action="{{ route('admin.tasks.index') }}" class="mb-4" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label for="project_id" class="form-label fw-bold text-secondary">📅 Filter by Project</label>
                                <select name="project_id" id="project_id" class="form-select select2">
                                    <option value="all" {{ request('project_id') == 'all' ? 'selected' : '' }}>All Projects</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project['id'] }}" {{ request('project_id') == $project['id'] ? 'selected' : '' }}>
                                            {{ $project['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- فلتر حالة الدفع --}}
                            <div class="col-md-3">
                                <label for="employee_id" class="form-label fw-bold text-secondary">💼 Filter by Employee</label>
                                <select name="employee_id" id="employee_id" class="form-select select2">
                                    <option value="all" {{ request('employee_id') == 'all' ? 'selected' : '' }}>All Employees</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee['id'] }}" {{ request('employee_id') == $employee['id'] ? 'selected' : '' }}>
                                            {{ $employee['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Start Date Filter --}}
                            <div class="col-md-3">
                                <label for="start_date" class="form-label fw-bold text-secondary">📅 Start Date</label>
                                <input type="date"
                                    name="start_date"
                                    id="start_date"
                                    class="form-control"
                                    value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
                                    placeholder="Select start date">
                            </div>

                            {{-- End Date Filter --}}
                            <div class="col-md-3">
                                <label for="end_date" class="form-label fw-bold text-secondary">📅 End Date</label>
                                <input type="date"
                                    name="end_date"
                                    id="end_date"
                                    class="form-control"
                                    value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}"
                                    placeholder="Select end date">
                            </div>

                        </div>
                    </form>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-check" style="margin-bottom: 25px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </div>
                            </th>
                            <th>#</th>
                            <th>Title</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration (hours)</th>
                            <th>Budget Amount</th>
                            <th>Project Name</th>
                            @if(Auth::user()->type !== 'employee')
                                <th>Employee Name</th>
                            @endif
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>
                                <div class="form-check" style="margin-bottom: 25px;">
                                    <input type="checkbox" id="selectAllFooter" class="form-check-input">
                                </div>
                            </th>
                            <th>#</th>
                            <th>Title</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration (hours)</th>
                            <th>Budget Amount</th>
                            <th>Project Name</th>
                            @if(Auth::user()->type !== 'employee')
                                <th>Employee Name</th>
                            @endif
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="task-checkbox form-check-input" data-id="{{ $task->id }}">
                                </div>
                                </td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $task->title }}</td>
                                <td>{{ $task->start_time->format('D, Y/m/d H:i A') }}</td>
                                <td>{{ $task->end_time ? $task->end_time->format('D, Y/m/d H:i A') : '-' }}</td>
                                @php
                                    $totalMinutes = (int) round($task->duration_in_hours * 60);
                                    $hours = intdiv($totalMinutes, 60);
                                    $minutes = $totalMinutes % 60;
                                @endphp
                                <td>
                                    <span class="font-weight-bold text-primary">
                                        {{ $hours }}h {{ $minutes }}m
                                    </span>
                                    <div class="text-muted small">
                                        ({{ number_format($task->duration_in_hours, 2) }} hours)
                                    </div>
                                </td>
                                @php
                                    $currencySymbols = [
                                        'USD' => '$',
                                        'EUR' => '€',
                                        'DZD' => 'DZ',
                                    ];
                                @endphp
                                <td>
                                    {{ $currencySymbols[$task->employee?->currency] ?? '' }}
                                    {{ number_format($task->cost, 2) }}
                                </td>
                                <td>
                                    @if($task->project)
                                        <span class="badge-custom badge-project">
                                            {{ $task->project->name }}
                                        </span>
                                    @else
                                        <span class="badge-custom badge-muted">N/A</span>
                                    @endif
                                </td>
                                @if(Auth::user()->type !== 'employee')
                                    <td>
                                        @if($task->employee)
                                            <span class="badge-custom badge-employee">
                                                {{ $task->employee->name }}
                                            </span>
                                        @else
                                            <span class="badge-custom badge-danger">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td>{{ $task->created_at->format('Y/m/d H:i A') }}</td>
                                <td>{{ $task->updated_at->format('Y/m/d H:i A') }}</td>
                                <td>
                                    <a href="{{ route('admin.tasks.show', $task->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST"
                                        style="display: inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?!')" type="submit"
                                            class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->type !== 'employee' ? 12 : 11 }}" class="text-center">No Data
                                    Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('css')
        <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('js')
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset("assets/js/demo/datatables-demo.js?v=" . time()) }}"></script>
        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <!-- Initialize Select2 -->
        <script>
            $(document).ready(function () {
                $('#employee_id, #project_id').select2({
                    placeholder: "Select an option",
                    allowClear: true,
                    width: '100%'
                });

                // استمع لأي تغيير في الفلاتر وأرسل الفورم
                $('#employee_id, #project_id, #start_date, #end_date').on('change', function () {
                    $(this).closest('form').submit();
                });

                // Handle select all checkboxes
                $('#selectAll, #selectAllFooter').on('change', function() {
                    $('.task-checkbox').prop('checked', $(this).prop('checked'));
                    toggleExportButton();
                });

                // Handle individual checkbox changes
                $('.task-checkbox').on('change', function() {
                    toggleExportButton();

                    // Update select all checkboxes if all individual checkboxes are checked
                    var allChecked = $('.task-checkbox').length === $('.task-checkbox:checked').length;
                    $('#selectAll, #selectAllFooter').prop('checked', allChecked);
                });
            });

            // Function to toggle export button visibility
            function toggleExportButton() {
                if ($('.task-checkbox:checked').length > 0) {
                    $('#exportSelectedBtn').show();
                } else {
                    $('#exportSelectedBtn').hide();
                }
            }

            // Function to export selected tasks
            function exportSelected() {
                var selectedIds = [];
                $('.task-checkbox:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length === 0) {
                    alert('Please select at least one task to export');
                    return;
                }

                // Create a form and submit it to export the selected tasks
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.tasks.export-selected") }}';

                // Add CSRF token
                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add selected IDs
                var idsInput = document.createElement('input');
                idsInput.type = 'hidden';
                idsInput.name = 'selected_ids';
                idsInput.value = JSON.stringify(selectedIds);
                form.appendChild(idsInput);

                // Submit the form
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        </script>

    @endpush
</x-dashboard>
