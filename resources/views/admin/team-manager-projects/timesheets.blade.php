<x-dashboard title="Project Timesheets">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Timesheets for {{ $project->name }}</h1>
        <a href="{{ route('admin.team-manager-projects.show', $project->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Project
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Timesheets</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select name="employee_id" id="employee_id" class="form-control">
                            <option value="all" {{ $employeeFilter === 'all' ? 'selected' : '' }}>All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $employeeFilter == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="month" class="form-label">Month</label>
                        <input type="month" name="month" id="month" class="form-control" value="{{ $monthFilter }}">
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('admin.team-manager-projects.timesheets', $project->id) }}" class="btn btn-secondary ml-2">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Timesheets Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Timesheets</h6>
        </div>
        <div class="card-body">
            @if($timesheets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Work Date</th>
                                <th>Hours Worked</th>
                                {{-- <th>Month Salary</th> --}}
                                {{-- <th>Status</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timesheets as $timesheet)
                                <tr>
                                    <td>{{ $timesheet->employee->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($timesheet->work_date)->format('Y-m-d') }}</td>
                                    <td>{{ $timesheet->hours_worked }}</td>
                                    {{-- <td>{{ $timesheet->month_salary }}</td> --}}
                                    {{-- <td>
                                        @if($timesheet->is_paid)
                                            <span class="badge badge-success">Paid</span>
                                        @else
                                            <span class="badge badge-warning">Unpaid</span>
                                        @endif
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>No timesheets found with the selected filters.</p>
            @endif
        </div>
    </div>

</x-dashboard>
