<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Projects</h1>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Total Amount</th>
                            <th>Attachments</th>
                            <th>User Name</th>
                            <th>Client Name</th>
                            <th>Employee Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Total Amount</th>
                            <th>Attachments</th>
                            <th>User Name</th>
                            <th>Client Name</th>
                            <th>Employee Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr>
                                <td>{{ $project->id }}</td>
                                <td>{{ $project->name }}</td>
                                <td>{{ $project->description }}</td>
                                <td>{{ $project->total_amount }}</td>
                                {{-- <td>{{ $project->attachments->first()->file_name ?? '_'}}</td> --}}
                                <td>
                                    @php
                                        $x = 1
                                    @endphp
                                    @if($project->attachments->isNotEmpty())
                                        @foreach($project->attachments as $attachment)
                                            <div>
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                                    title="{{ basename($attachment->file_path) }}" download="{{ basename($attachment->file_path) }}">
                                                    {{-- {{ basename($attachment->file_path) }} --}}
                                                    Uploaded File{{ $x++ }}
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        _
                                    @endif
                                </td>

                                <td>{{ $project->user->name ?? '_'}}</td>
                                <td>{{ $project->client->name ?? '_'}}</td>
                                <td>
                                    @if($project->employees && $project->employees->isNotEmpty())
                                        {{ $project->employees->pluck('name')->join(', ') }}
                                    @else
                                        _
                                    @endif
                                </td>
                                <td>{{ $project->created_at->diffForHumans() }}</td>
                                <td>{{ $project->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.projects.edit', $project->id) }}"
                                        class="btn btn-sm btn-primary"><i class='fas fa-edit'></i></a>
                                    <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST"
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
                                <td colspan="10" class="text-center">No Data Found</td>
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
