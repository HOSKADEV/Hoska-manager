<x-dashboard title="Contract Details">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Contract Details</h1>
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h5 class="text-primary">Contract Information</h5>
                    <table class="table table-bordered">
                        {{-- <tr>
                            <th width="30%">Name</th>
                            <td>{{ $contract->name }}</td>
                        </tr> --}}
                        <tr>
                            <th>Type</th>
                            <td>
                                <span class="badge {{ $contract->type === 'employee' ? 'bg-success text-white' : 'bg-primary text-white' }}">
                                    {{ ucfirst($contract->type) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Related To</th>
                            <td>
                                @if($contract->type === 'employee' && $contract->contractable)
                                    {{ $contract->contractable->name }}
                                @elseif($contract->type === 'project' && $contract->contractable)
                                    {{ $contract->contractable->name }}
                                @else
                                    <span class="text-muted">Not available</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>URL</th>
                            <td>
                                <a href="{{ $contract->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> Visit Contract
                                </a>
                            </td>
                        </tr>
                        {{-- <tr>
                            <th>Created By</th>
                            <td>{{ $contract->user->name }}</td>
                        </tr> --}}
                        <tr>
                            <th>Created At</th>
                            <td>{{ $contract->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-dashboard>
