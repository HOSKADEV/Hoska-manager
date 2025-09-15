<x-dashboard title="Timesheet Details">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Task Details for {{ $employee->name }} - {{ $month }}</h1>
        <a href="{{ route('admin.team-manager-projects.timesheets', $project->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Tasks Summary
        </a>
    </div>

    <!-- Timesheet Details Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Task Details</h6>
        </div>
        <div class="card-body">
            @if($tasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Task</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Hours Worked</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($task->start_time)->format('Y-m-d') }}</td>
                                    <td>{{ $task->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($task->start_time)->format('H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($task->end_time)->format('H:i') }}</td>
                                    <td>{{ number_format($task->duration_in_hours, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Total Hours</th>
                                <th>{{ number_format($tasks->sum('duration_in_hours'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p>No tasks found for the selected employee and month.</p>
            @endif
        </div>
    </div>

</x-dashboard>
