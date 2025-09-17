<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->name }} - Project Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .project-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 5px;
        }
        .card {
            border: none;
            border-radius: 5px;
            box-shadow: 0.125rem 0.125rem 0.125rem 0.125rem rgba(0, 0, 0, 0.15);
            margin-bottom: 1.5rem;
            /* transition: transform 0.3s; */
        }
        .card:hover {
            /* transform: translateY(-5px); */
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
            font-weight: 600;
        }
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }
        .task-card {
            border-left: 4px solid #0d6efd;
        }
        .filter-section {
            background-color: white;
            border-radius: 5px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .project-links li {
            margin-bottom: 0.5rem;
        }
        .project-links a {
            color: #0d6efd;
            text-decoration: none;
        }
        .project-links a:hover {
            text-decoration: underline;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 5px;
            margin-right: 8px;
        }
        .status-active { background-color: #198754; }
        .status-warning { background-color: #ffc107; }
        .status-danger { background-color: #dc3545; }
        .status-completed { background-color: #0d6efd; }
    </style>
</head>
<body>
    <div class="project-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">{{ $project->name }}</h1>
                    <p class="lead mb-0">{{ $project->description }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    @if ($project->client)
                        <p class="mb-1"><strong>Client:</strong> {{ $project->client->name }}</p>
                    @endif
                    <div class="d-flex align-items-center justify-content-md-end mt-2">
                        <span class="{{ $remainingClass }}">{{ $remainingText }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Project Information Section -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Project Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Start Date:</strong> {{ $project->start_date ?? '-' }}</p>
                                <p><strong>Duration:</strong> {{ $project->duration_days ? $project->duration_days . ' day(s)' : '-' }}</p>
                                <p><strong>Delivery Date:</strong> {{ $project->delivery_date ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong>
                                    @if ($project->delivered_at)
                                        <span class="badge bg-primary"><span class="status-indicator status-completed"></span> Completed</span>
                                    @elseif (!is_null($remainingDays))
                                        @if ($remainingDays < 0)
                                            <span class="badge bg-danger"><span class="status-indicator status-danger"></span> After Deadline</span>
                                        @elseif ($remainingDays >= 0 && $remainingDays <= 1)
                                            <span class="badge bg-warning text-dark"><span class="status-indicator status-warning"></span> Due Today</span>
                                        @else
                                            <span class="badge bg-info"><span class="status-indicator status-active"></span> In Progress</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </p>
                                <p><strong>Total Work Hours:</strong> {{ number_format($totalHours, 2) }} hours</p>
                            </div>
                        </div>

                        @if($project->links->isNotEmpty())
                            <hr>
                            <h6 class="mb-3"><i class="fas fa-link me-2"></i> Project Links</h6>
                            <ul class="list-unstyled project-links">
                                @foreach($project->links as $link)
                                    <li>
                                        <i class="fas fa-external-link-alt me-2"></i>
                                        <a href="{{ $link->url }}" target="_blank">{{ $link->url }}</a>
                                        @if($link->label)
                                            <span class="text-muted">({{ $link->label }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i> Team Members</h5>
                    </div>
                    <div class="card-body">
                        @if($project->employees && $project->employees->isNotEmpty())
                            <div class="d-flex flex-wrap">
                                @foreach($project->employees as $employee)
                                    <span class="badge bg-success p-2 me-2 mb-2">{{ $employee->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No team members assigned.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i> Filter Tasks</h5>
            <form method="GET" action="{{ route('projects.client-view', $project->id) }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="employee_id" class="form-label">Filter by Employee</label>
                        <select name="employee_id" id="employee_id" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ $selectedEmployeeId === 'all' ? 'selected' : '' }}>All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $selectedEmployeeId == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tasks Section -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> Project Tasks</h5>
                <span class="badge bg-primary">{{ $tasks->count() }} Tasks</span>
            </div>
            <div class="card-body">
                @if($tasks->isNotEmpty())
                    <div class="row">
                        @foreach($tasks as $task)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card task-card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $task->title }}</h6>
                                        <p class="card-text text-muted small">{{ $task->description ?? 'No description' }}</p>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                @if($task->employee)
                                                    <span class="badge bg-info">{{ $task->employee->name }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="badge bg-secondary">
                                                    {{ number_format($task->duration_in_hours, 2) }}h
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <span class="badge bg-secondary">
                                                    {{ $task->start_time ? $task->start_time->format('D, Y/m/d H:i') : '-' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="badge bg-secondary">
                                                    {{ $task->end_time ? $task->end_time->format('D, Y/m/d H:i') : '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tasks found for this project.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Our Tasks Section -->
        @if($project->ourTasks->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i> Additional Tasks</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($project->ourTasks as $task)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card task-card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $task->title }}</h6>
                                        <p class="card-text text-muted small">{{ $task->description ?? 'No description' }}</p>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <span class="badge bg-secondary">
                                                    {{ number_format($task->duration, 2) }}h
                                                </span>
                                            </div>
                                            {{-- <div>
                                                <span class="badge bg-warning text-dark">
                                                    {{ $currencySymbols[$project->currency] ?? '' }}{{ number_format($task->cost, 2) }}
                                                </span>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="text-center mt-5 mb-4 text-muted">
            <p class="mb-0">Project information last updated: {{ $project->updated_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
