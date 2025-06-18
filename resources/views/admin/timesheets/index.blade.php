<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Timesheets</h1>
        {{-- <a href="{{ route('admin.timesheets.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add
            New</a> --}}
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.timesheets.index') }}"
                class="mb-4 d-flex align-items-center gap-2">
                <label for="month" class="mb-0">Filter by Month:</label>
                <input type="month" name="month" id="month" value="{{ request('month') }}"
                    class="form-control w-auto" />
                <button type="submit" class="btn btn-primary mx-3">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request()->has('month'))
                    <a href="{{ route('admin.timesheets.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filter
                    </a>
                @endif
            </form>

            <div class="table-responsive mt-3">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            {{-- <th>Work Date</th> --}}
                            <th>Employee Name</th>
                            <th>Duration (hours)</th>
                            {{-- <th>Project Name</th> --}}
                            {{-- <th>Created At</th>
                            <th>Updated At</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            {{-- <th>Work Date</th> --}}
                            <th>Employee Name</th>
                            <th>Duration (hours)</th>
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
                                {{-- <td>{{ $timesheet->work_date->diffForHumans() }}</td> --}}
                                <td>{{ $timesheet->employee->name ?? '_'}}</td>
                                <td>{{ $timesheet->hours_worked }}</td>
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
    @endpush

    @push('js')
        <!-- Page level plugins -->
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- Page level custom scripts -->
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    @endpush
</x-dashboard>
