<x-dashboard title="Main Dashboard">
    @push('css')
        <style>
            .badge-custom {
                display: inline-block;
                padding: 0.5em 0.8em;
                /* زيادة البادينغ */
                font-size: 0.9rem;
                /* تكبير حجم الخط */
                font-weight: 600;
                color: #fff;
                border-radius: 0.5rem;
                white-space: nowrap;
            }

            .badge-project {
                background-color: #6f42c1;
                /* بنفسجي */
            }

            .badge-employee {
                background-color: #20c997;
                /* أخضر فاتح */
            }

            .badge-muted {
                background-color: #6c757d;
                /* رمادي */
            }

            .badge-danger {
                background-color: #dc3545;
                /* أحمر */
            }
        </style>

    @endpush
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Tasks</h1>
        <a href="{{ route('admin.tasks.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            {{-- <th>Description</th> --}}
                            {{-- <th>Status</th> --}}
                            {{-- <th>Due Date</th> --}}
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration (hours)</th>
                            {{-- <th>Cost</th> --}}
                            <th>Budget Amount</th>
                            <th>Project ID</th>
                            <th>Employee ID</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            {{-- <th>Description</th> --}}
                            {{-- <th>Status</th> --}}
                            {{-- <th>Due Date</th> --}}
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration (hours)</th>
                            {{-- <th>Cost</th> --}}
                            <th>Budget Amount</th>
                            <th>Project ID</th>
                            <th>Employee ID</th>
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
                                {{-- <td class="col-md-3">{{ $task->description }}</td> --}}
                                {{-- <td>{{ $task->status }}</td> --}}
                                {{-- <td>{{ $task->due_date->diffForHumans() }}</td> --}}
                                <td>{{ $task->start_time->format('D, Y/m/d H:i A') }}</td>
                                <td>{{ $task->end_time ? $task->end_time->format('D, Y/m/d H:i A') : '-' }}</td>
                                <td>{{ $task->duration_in_hours }}</td>
                                {{-- <td>{{ number_format($task->cost, 2) }} $</td> --}}
                                @php
                                    $currencySymbols = [
                                        'USD' => '$',
                                        'EUR' => '€',
                                        'DZD' => 'DZ',
                                    ];
                                @endphp
                                <td>
                                    {{ $currencySymbols[$task->employee?->currency] ?? '' }} {{ number_format($task->cost, 2) }}
                                </td>

                                {{-- <td>{{ $task->budget_amount }}</td> --}}
                                <td>
                                    @if($task->project)
                                        <span class="badge-custom badge-project">
                                            {{ $task->project->name }}
                                        </span>
                                    @else
                                        <span class="badge-custom badge-muted">N/A</span>
                                    @endif
                                </td>

                                <td>
                                    @if($task->employee)
                                        <span class="badge-custom badge-employee">
                                            {{ $task->employee->name }}
                                        </span>
                                    @else
                                        <span class="badge-custom badge-danger">-</span>
                                    @endif
                                </td>

                                {{-- <td>{{ $task->employee->name ?? '-' }}</td> --}}
                                <td>{{ $task->created_at->format('Y/m/d H:i A') }}</td>
                                <td>{{ $task->updated_at->format('Y/m/d H:i A') }}</td>
                                <td class="col-md-4">
                                    <a href="{{ route('admin.tasks.show', $task->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.tasks.edit', $task->id) }}" class="btn btn-sm btn-primary"><i
                                            class='fas fa-edit'></i>
                                    </a>
                                    <form action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST"
                                        style="display: inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?!')" type="submit"
                                            class="btn btn-sm btn-danger"><i class='fas fa-trash'></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="d-none"></td>
                                <td colspan="15" class="text-center">No Data Found</td>
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
    @endpush

    @push('js')
        <!-- Page level plugins -->
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- Page level custom scripts -->
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    @endpush
</x-dashboard>
