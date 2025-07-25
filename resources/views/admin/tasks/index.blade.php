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
        <a href="{{ route('admin.tasks.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
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
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
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
                                <td colspan="{{ Auth::user()->type !== 'employee' ? 11 : 10 }}" class="text-center">No Data
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
    @endpush

    @push('js')
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    @endpush
</x-dashboard>
