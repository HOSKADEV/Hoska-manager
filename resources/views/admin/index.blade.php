<x-dashboard title="Main Dashboard">
    <style>
        .bg {
            position: relative;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Adjust the opacity as needed */
        }

        .bg h1,
        .bg p {
            position: relative;
            z-index: 1;
            /* Ensure text is above the background */
        }

        .bg a {
            position: relative;
            z-index: 1;
            /* Ensure button is above the background */
        }

        .bg img {
            width: 100%;
            height: 50vh;
            object-fit: cover;
            margin: auto;
        }
    </style>

    @php
        $user = auth()->user();
    @endphp

    <!-- Content Row -->
    <div class="row">
        @if($user->type === 'admin')
            <!-- Admin cards -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Projects
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $totalProjects }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Clients
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $totalClients }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Earnings (Monthly)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $monthlyEarnings }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Task Completion
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            {{ $completionPercentage }}%
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                style="width: {{ $completionPercentage }}%"
                                                aria-valuenow="{{ $completionPercentage }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-1">
                                    {{ $completedTasks }} / {{ $totalTasks }} tasks completed
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($user->type === 'employee')
            @php
                $employee = $user->employee;
                $employeeProjectsCount = $employee->projects()->count();
                $employeeTasksCount = $employee->tasks()->count();
                $employeeCompletedTasksCount = $employee->tasks()->where('status', 'completed')->count();
                $employeeCompletionPercentage = $employeeTasksCount > 0 ? round(($employeeCompletedTasksCount / $employeeTasksCount) * 100) : 0;
            @endphp

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    My Projects
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $employeeProjectsCount }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بطاقة الأجر -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    My Monthly Salary
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $monthlyEarnings }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    My Task Completion
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            {{ $employeeCompletionPercentage }}%
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                style="width: {{ $employeeCompletionPercentage }}%"
                                                aria-valuenow="{{ $employeeCompletionPercentage }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-1">
                                    {{ $employeeCompletedTasksCount }} / {{ $employeeTasksCount }} tasks completed
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <div class="col-12">
                <div class="alert alert-warning">
                    Your user type does not have dashboard data to display.
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="bg"
                style="width: 100%; height: 70vh; object-fit: cover; margin: auto; background-image: url('{{ asset('assets/img/bg.jpg') }}'); background-color: rgba(0, 0, 0, 0.5);">

                <div class="text-center"
                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    <h1 class="text-white">Welcome to the Dashboard</h1>
                    <p class="text-white">This is a blank page template. You can customize it as per your requirements.
                    </p>
                    <a href="{{ route('admin.tasks.index') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i>
                        Go to Tasks</a>
                </div>
            </div>
        </div>
    </div>
</x-dashboard>
