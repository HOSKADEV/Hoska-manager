<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Timesheets</h1>
        {{-- <a href="{{ route('admin.timesheets.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add
            New</a> --}}
    </div>

    <!-- Timesheet Summary Cards -->
    <div class="row mb-4">

        <!-- Total Hours Worked -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Hours Worked
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalHours, 2) }} hrs
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Salaries -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Monthly Salary
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalSalaries, 2) }} $
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paid Salaries -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Paid Salaries
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $paidCount }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unpaid Salaries -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Unpaid Salaries
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $unpaidCount }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                {{-- filter --}}
                <form method="GET" action="{{ route('admin.timesheets.index') }}" class="mb-4 w-75" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="month" class="form-label fw-bold text-secondary">📅 Filter by Month</label>
                            <select name="month" id="month" class="form-select select2">
                                <option value="all" {{ request('month', now()->format('Y-m')) === 'all' ? 'selected' : '' }}>
                                    📆 All Months</option>
                                @foreach ($availableMonths as $month)
                                    <option value="{{ $month['value'] }}" {{ request('month', now()->format('Y-m')) === $month['value'] ? 'selected' : '' }}>
                                        {{ $month['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- فلتر حالة الدفع --}}
                        <div class="col-md-4">
                            <label for="is_paid" class="form-label fw-bold text-secondary">💰 Filter by Payment
                                Status</label>
                            <select name="is_paid" id="is_paid" class="form-select select2">
                                <option value="all" {{ request('is_paid', '') === 'all' ? 'selected' : '' }}>All</option>
                                <option value="1" {{ request('is_paid') === '1' ? 'selected' : '' }}>Paid</option>
                                <option value="0" {{ request('is_paid') === '0' ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Add this anywhere suitable -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fa fa-file-excel"></i> Export Excel
                </button>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee Name</th>
                            <th>Duration (hours)</th>
                            <th>Monthly Salary</th> <!-- الأجر الشهري -->
                            <th>Payment Status</th> <!-- مدفوع / غير مدفوع -->
                            <th>Month</th>
                            {{-- <th>Project Name</th> --}}
                            {{-- <th>Created At</th>
                            <th>Updated At</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Employee Name</th>
                            <th>Duration (hours)</th>
                            <th>Monthly Salary</th> <!-- الأجر الشهري -->
                            <th>Payment Status</th> <!-- مدفوع / غير مدفوع -->
                            <th>Month</th>
                            {{-- <th>Project Name</th> --}}
                            {{-- <th>Created At</th>
                            <th>Updated At</th> --}}
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($timesheets as $timesheet)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $timesheet->employee->name ?? '_'}}</td>
                                <td>{{ $timesheet->hours_worked }}</td>
                                <td>{{ number_format($timesheet->month_salary, 2) }} $</td> <!-- الأجر الشهري -->
                                <td>
                                    @if($timesheet->is_paid)
                                        <span class="badge bg-success text-white">Paid</span>
                                    @else
                                        <span class="badge bg-danger text-white">Unpaid</span>
                                    @endif
                                </td>
                                <td>{{ $timesheet->work_date->format('Y-M') }}</td>
                                {{-- <td>{{ $task->start_time ? $task->start_time->format('Y-m-d') : '-' }}</td> --}}
                                {{-- <td>{{ $timesheet->project->name ?? '_'}}</td> --}}
                                {{-- <td>{{ $timesheet->created_at->diffForHumans() }}</td>
                                <td>{{ $timesheet->updated_at->diffForHumans() }}</td> --}}
                                <td>
                                    @if(!$timesheet->is_paid)
                                        <form id="pay-form-{{ $timesheet->id }}"
                                            action="{{ route('admin.timesheets.markPaid', $timesheet->id) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button" class="btn btn-success btn-sm"
                                                onclick="confirmPayment({{ $timesheet->id }})">
                                                <i class="fa fa-check-circle"></i> Mark as Paid
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-success d-none">Paid</span>
                                    @endif
                                    <a href="{{ route('admin.timesheets.show', $timesheet->id) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="d-none"></td>
                                <td colspan="8" class="text-center">No Data Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- <!-- Modal -->
            <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <form id="export-form" action="{{ route('timesheets.export') }}" method="GET">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select Columns to Export</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @php
                                $columns = ['id' => 'ID', 'employee_name' => 'Employee', 'project_name' => 'Project',
                                'date' => 'Date', 'hours' => 'Hours', 'is_paid' => 'Paid'];
                                @endphp

                                @foreach($columns as $key => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $key }}"
                                        id="col_{{ $key }}" checked>
                                    <label class="form-check-label" for="col_{{ $key }}">
                                        {{ $label }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Export</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div> --}}
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
        <!-- Initialize Select2 -->
        <script>
            $(document).ready(function () {
                $('#month, #is_paid').select2({
                    placeholder: "📆 Select an option",
                    allowClear: true,
                    width: '100%'
                });

                // استمع لأي تغيير في الفلاتر وأرسل الفورم
                $('#month, #is_paid').on('change', function () {
                    $(this).closest('form').submit();
                });
            });
        </script>

        <script>
            function confirmPayment(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to mark this timesheet as paid?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, mark as paid',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('pay-form-' + id).submit();
                    }
                });
            }
        </script>
    @endpush
</x-dashboard>
