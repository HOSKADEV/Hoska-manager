<x-dashboard title="Main Dashboard">
    @push('css')
        <style>
            .badge-custom {
                display: inline-block;
                padding: 0.4em 0.75em;
                font-size: 0.9rem;
                font-weight: 600;
                color: #fff;
                border-radius: 0.5rem;
                white-space: nowrap;
                margin-right: 0.3em;
                vertical-align: middle;
            }

            .badge-project {
                background-color: #28a745;
                /* أخضر */
            }

            .badge-muted {
                background-color: #6c757d;
                /* رمادي */
            }
        </style>
    @endpush
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
                            <th>#</th>
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
                            <th>#</th>
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
                                <td></td>
                                <td>{{ $development->description }}</td>
                                <td>{{ $development->amount }}</td>
                                <td>
                                    @if($development->project)
                                        <span class="badge-custom badge-project">{{ $development->project->name }}</span>
                                    @else
                                        <span class="badge-custom badge-muted">_</span>
                                    @endif
                                </td>
                                <td>{{ $development->created_at->diffForHumans() }}</td>
                                <td>{{ $development->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.developments.edit', $development->id) }}"
                                        class="btn btn-sm btn-primary"><i class='fas fa-edit'></i></a>
                                    <form action="{{ route('admin.developments.destroy', $development->id) }}" method="POST"
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
                <script>
            $(document).ready(function () {
                $('#dataTable').DataTable({
                    order: [[1, 'desc']],  // ترتيب حسب عمود العنوان أو أي عمود مناسب
                    columnDefs: [{
                        targets: 0,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    }]
                });
            });
        </script>
    @endpush
</x-dashboard>
