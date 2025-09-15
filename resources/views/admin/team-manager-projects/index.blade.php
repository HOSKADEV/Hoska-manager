@extends('layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Projects</h1>
    </div>

    <!-- Projects Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Projects List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Client</th>
                            <th>Start Date</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th>Team Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr class="{{ $project->row_class }}">
                                <td>{{ $project->name }}</td>
                                <td>{{ $project->client->name ?? 'N/A' }}</td>
                                <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : 'N/A' }}</td>
                                <td>{{ $project->delivery_date ? \Carbon\Carbon::parse($project->delivery_date)->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    @if($project->delivered_at)
                                        <span class="badge badge-primary">Delivered</span>
                                    @elseif($project->remaining_days < 0)
                                        <span class="badge badge-danger">Expired</span>
                                    @elseif($project->remaining_days >= 0 && $project->remaining_days <= 1)
                                        <span class="badge badge-warning">Due Today</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td>{{ $project->employees->count() }}</td>
                                <td>
                                    <a href="{{ route('admin.team-manager-projects.show', $project->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
