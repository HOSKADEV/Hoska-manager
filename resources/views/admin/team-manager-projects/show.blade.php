<x-dashboard title="Project Details">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $project->name }}</h1>
        <a href="{{ route('admin.team-manager-projects.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Projects
        </a>
    </div>

    <!-- Project Details -->
    <div class="row">

        <!-- Project Info -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Project Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $project->name }}</p>
                    <p><strong>Client:</strong> {{ $project->client->name ?? 'N/A' }}</p>
                    <p><strong>Start Date:</strong> {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : 'N/A' }}</p>
                    <p><strong>Delivery Date:</strong> {{ $project->delivery_date ? \Carbon\Carbon::parse($project->delivery_date)->format('Y-m-d') : 'N/A' }}</p>
                    <p><strong>Status:</strong>
                        @if($project->delivered_at)
                            <span class="badge badge-primary">Delivered</span>
                        @elseif($project->remaining_days < 0)
                            <span class="badge badge-danger">Expired</span>
                        @elseif($project->remaining_days >= 0 && $project->remaining_days <= 1)
                            <span class="badge badge-warning">Due Today</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </p>
                    <p><strong>Description:</strong> {{ $project->description ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Team Members -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Team Members</h6>
                </div>
                <div class="card-body">
                    @if($project->employees->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        {{-- <th>Position</th> --}}
                                        {{-- <th>Rate</th>
                                        <th>Currency</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->employees as $employee)
                                        <tr>
                                            <td>{{ $employee->name }}</td>
                                            {{-- <td>{{ $employee->position ?? 'N/A' }}</td> --}}
                                            {{-- <td>{{ $employee->rate }}</td>
                                            <td>{{ $employee->currency ?? 'N/A' }}</td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>No team members assigned to this project.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Timesheets and Tasks -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs" id="projectTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="timesheets-tab" data-toggle="tab" href="#timesheets" role="tab" aria-controls="timesheets" aria-selected="true">
                        Timesheets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tasks-tab" data-toggle="tab" href="#tasks" role="tab" aria-controls="tasks" aria-selected="false">
                        Tasks
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="projectTabsContent">
                <!-- Timesheets Tab -->
                <div class="tab-pane fade show active" id="timesheets" role="tabpanel" aria-labelledby="timesheets-tab">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="m-0 font-weight-bold text-primary">Project Timesheets</h5>
                        <a href="{{ route('admin.team-manager-projects.timesheets', $project->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i> Filter Timesheets
                        </a>
                    </div>

                    @if($timesheets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Work Date</th>
                                        <th>Hours Worked</th>
                                        {{--<th>Salary</th> --}}
                                        {{-- <th>Status</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timesheets as $timesheet)
                                        <tr>
                                            <td>{{ $timesheet->employee->name ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($timesheet->work_date)->format('Y-m-d') }}</td>
                                            <td>{{ $timesheet->total_hours }}</td>
                                            {{-- <td>{{ $timesheet->month_salary }}</td>
                                            <td>
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
                        <p>No timesheets found for this project.</p>
                    @endif
                </div>

                <!-- Tasks Tab -->
                <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="m-0 font-weight-bold text-primary">Project Tasks</h5>
                        <a href="{{ route('admin.team-manager-projects.tasks', $project->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i> Filter Tasks
                        </a>
                    </div>

                    @if($project->tasks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
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
                                    @foreach($project->tasks as $task)
                                        <tr>
                                            <td>{{ $task->title }}</td>
                                            <td>{{ $task->employee->name ?? 'N/A' }}</td>
                                            <td>{{ $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('Y-m-d H:i') : 'N/A' }}</td>
                                            <td>{{ $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('Y-m-d H:i') : 'N/A' }}</td>
                                            <td>{{ number_format($task->duration_in_hours, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $task->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($task->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>No tasks found for this project.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-dashboard>
