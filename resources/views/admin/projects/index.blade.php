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
                margin-right: 0.3em;
                margin-bottom: 0.2em;
            }

            .badge-client {
                background-color: #007bff;
                /* أزرق */
            }

            .badge-employee {
                background-color: #20c997;
                /* أخضر فاتح */
            }

            .badge-user {
                background-color: #fd7e14;
                /* برتقالي */
            }

            .badge-muted {
                background-color: #6c757d;
                /* رمادي */
            }
        </style>
    @endpush

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
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>TotalAmount</th>
                            <th>Attachments</th>
                            <th>Client Name</th>
                            <th>Employee Name</th>
                            <th>User Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Total Amount</th>
                            <th>Attachments</th>
                            <th>Client Name</th>
                            <th>Employee Name</th>
                            <th>User Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $project->name }}</td>
                                <td>{{ $project->description }}</td>
                                {{-- <td>{{ $project->total_amount }}</td> --}}
                                @php
                                    $currencySymbols = [
                                        'USD' => '$',
                                        'EUR' => '€',
                                        'DZD' => 'DZ',
                                    ];
                                @endphp
                                <td>
                                    {{ $currencySymbols[$project->currency] ?? '' }} {{ number_format( $project->total_amount, 2) }}
                                </td>
                                {{-- <td>{{ $project->attachments->first()->file_name ?? '_'}}</td> --}}
                                <td>
                                    @php
                                        $x = 1
                                    @endphp
                                    @if($project->attachments->isNotEmpty())
                                        @foreach($project->attachments as $attachment)
                                            <div>
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                                    title="{{ basename($attachment->file_path) }}">
                                                    {{-- {{ basename($attachment->file_path) }} --}}
                                                    View File{{ $x++ }}
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        _
                                    @endif
                                </td>
                                <td>
                                    @if($project->client)
                                        <span class="badge-custom badge-client">{{ $project->client->name }}</span>
                                    @else
                                        <span class="badge-custom badge-muted">_</span>
                                    @endif
                                </td>
                                <td>
                                    @if($project->employees && $project->employees->isNotEmpty())
                                        @foreach($project->employees as $employee)
                                            <span class="badge-custom badge-employee">{{ $employee->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge-custom badge-muted">_</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-custom badge-user">{{ auth()->user()->name }}</span>
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
                                <td class="d-none"></td>
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
