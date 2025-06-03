<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Developments</h1>
        <a href="{{ route('admin.developments.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Rate</th>
                            <th>Project Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>description</th>
                            <th>amount</th>
                            <th>Project Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                            @forelse ($developments as $development)
                                <tr>
                                    <td>{{ $development->id }}</td>
                                    <td>{{ $development->description }}</td>
                                    <td>{{ $development->amount }}</td>
                                    <td>{{ $development->project->name ?? '_'}}</td>
                                    <td>{{ $development->created_at->diffForHumans() }}</td>
                                    <td>{{ $development->updated_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('admin.developments.edit', $development->id) }}" class="btn btn-sm btn-primary"><i class='fas fa-edit'></i></a>
                                        <form action="{{ route('admin.developments.destroy', $development->id) }}" method="POST" style="display: inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('Are you sure?!')" type="submit" class="btn btn-sm btn-danger"><i class='fas fa-trash'></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No Data Found</td>
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

