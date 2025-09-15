<x-dashboard title="Project Tasks">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tasks for {{ $project->name }}</h1>
        <a href="{{ route('admin.team-manager-projects.show', $project->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Project
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Tasks</h6>
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
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('admin.team-manager-projects.tasks', $project->id) }}" class="btn btn-secondary ml-2">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tasks</h6>
        </div>
        <div class="card-body">
            @if($tasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Employee</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Duration (Hours)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>{{ $task->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('Y-m-d H:i') : 'N/A' }}</td>
                                    <td>{{ $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('Y-m-d H:i') : 'N/A' }}</td>
                                    <td>{{ number_format($task->duration_in_hours, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'info' : 'warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>No tasks found with the selected filters.</p>
            @endif
        </div>
    </div>
</x-dashboard>
