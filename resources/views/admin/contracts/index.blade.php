<x-dashboard title="Contracts Management">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Contracts</h1>
        <a href="{{ route('admin.contracts.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.contracts.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="type" class="form-label">Filter by Type</label>
                        <select name="type" id="type" class="form-control" onchange="this.form.submit()">
                            <option value="all" {{ $typeFilter === 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="employee" {{ $typeFilter === 'employee' ? 'selected' : '' }}>Employees</option>
                            <option value="project" {{ $typeFilter === 'project' ? 'selected' : '' }}>Projects</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            {{-- <th>Name</th> --}}
                            <th>Type</th>
                            <th>Related To</th>
                            <th>URL</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            {{-- <th>Name</th> --}}
                            <th>Type</th>
                            <th>Related To</th>
                            <th>URL</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($contracts as $contract)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                {{-- <td>{{ $contract->name }}</td> --}}
                                <td>
                                    <span class="badge {{ $contract->type === 'employee' ? 'bg-success text-white' : 'bg-primary text-white' }}">
                                        {{ ucfirst($contract->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($contract->type === 'employee' && $contract->contractable)
                                        {{ $contract->contractable->name }}
                                    @elseif($contract->type === 'project' && $contract->contractable)
                                        {{ $contract->contractable->name }}
                                    @else
                                        <span class="text-muted">Not available</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $contract->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> Visit
                                    </a>
                                </td>
                                <td>{{ $contract->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.contracts.show', $contract->id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.contracts.edit', $contract->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.contracts.destroy', $contract->id) }}" method="POST" style="display: inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?!')" type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
        <script src="{{ asset("assets/js/demo/datatables-demo.js?v=" . time()) }}"></script>
    @endpush
</x-dashboard>
