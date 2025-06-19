<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Timesheets</h1>
        {{-- <a href="{{ route('admin.timesheets.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add
            New</a> --}}
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.timesheets.index') }}" class="mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="month" class="form-label fw-bold text-secondary">📅 Filter by Month</label>
                        <select name="month" id="month" class="form-select select2">
                            <option value="">📆 All Months</option>
                            @foreach ($availableMonths as $month)
                            <option value="{{ $month['value'] }}" {{ request('month')===$month['value'] ? 'selected'
                                : '' }}>
                                {{ $month['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        @if(request()->has('month'))
                        <a href="{{ route('admin.timesheets.index') }}" class="btn btn-outline-danger ms-2 px-4">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- <form method="GET" action="{{ route('admin.timesheets.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="month" class="form-label fw-bold text-secondary">📅 Filter by Month</label>
                        <select id="month" name="month" class="form-select">
                            <option value="">📆 All Months</option>
                            @foreach ($availableMonths as $month)
                                <option value="{{ $month['value'] }}" {{ request('month') === $month['value'] ? 'selected' : '' }}>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-success px-4 shadow-sm">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>

                        @if(request()->has('month'))
                            <a href="{{ route('admin.timesheets.index') }}" class="btn btn-outline-danger px-4 shadow-sm">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form> --}}



            <div class="table-responsive mt-3">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee Name</th>
                            <th>Duration (hours)</th>
                            <th>Date</th>
                            {{-- <th>Project Name</th> --}}
                            {{-- <th>Created At</th>
                            <th>Updated At</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Employee Name</th>
                            <th>Duration (hours)</th>
                            <th>Date</th>
                            {{-- <th>Project Name</th> --}}
                            {{-- <th>Created At</th>
                            <th>Updated At</th> --}}
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($timesheets as $timesheet)
                            <tr>
                                <td>{{ $timesheet->id }}</td>
                                <td>{{ $timesheet->employee->name ?? '_'}}</td>
                                <td>{{ $timesheet->hours_worked }}</td>
                                <td>{{ $timesheet->work_date->format('Y-M') }}</td>
                                {{-- <td>{{ $task->start_time ? $task->start_time->format('Y-m-d') : '-' }}</td> --}}
                                {{-- <td>{{ $timesheet->project->name ?? '_'}}</td> --}}
                                {{-- <td>{{ $timesheet->created_at->diffForHumans() }}</td>
                                <td>{{ $timesheet->updated_at->diffForHumans() }}</td> --}}
                                <td>
                                    <a href="{{ route('admin.timesheets.show', $timesheet->id) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No Data Found</td>
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
        <!-- Initialize Select2 -->
        <script>
            $(document).ready(function () {
                $('#month').select2({
                    placeholder: "📆 Select a Month",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>
    @endpush
</x-dashboard>
