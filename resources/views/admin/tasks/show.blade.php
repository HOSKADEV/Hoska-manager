<x-dashboard title="Show Task Details">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Show Task Details</h1>
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-info"><i class="fas fa-long-arrow-alt-left"></i>All
            Tasks</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Task Details: <strong>{{ $task->title }}</strong></h4>
        </div>
        <div class="card-body fs-5">
            <p><strong>Task ID:</strong> {{ $task->id }}</p>
            <p><strong>Description:</strong> {{ $task->description }}</p>
            <p><strong>Status:</strong> <span class="badge bg-info text-dark">{{ $task->status }}</span></p>
            <p><strong>Start Date:</strong> {{ $task->start_time }}</p>
            <p><strong>End Date:</strong> {{ $task->end_time ?? '-' }}</p>
            <p><strong>Duration (Hours):</strong> {{ $task->duration_in_hours }}</p>
            <p><strong>Cost:</strong> <span class="text-success">{{ number_format($task->cost, 2) }} $</span></p>

            <hr class="my-4">

            <h5 class="text-primary">Assigned Employee:</h5>
            <p><strong>Name:</strong> {{ $task->employee->name ?? '-' }}</p>
            <p><strong>Projects:</strong>
                {{ $task->employee->projects->pluck('name')->implode(', ') ?? 'None' }}
            </p>

            <hr class="my-4">

            <h5 class="text-primary">Related Project (if any):</h5>
            <p><strong>Project Name:</strong> {{ $task->project->name ?? 'None' }}</p>
            <p><strong>Client:</strong> {{ $task->project->client->name ?? '-' }}</p>

            <hr class="my-4">

            <p><strong>Created At:</strong> {{ $task->created_at->format('Y-m-d H:i') }}</p>
            <p><strong>Last Updated:</strong> {{ $task->updated_at->format('Y-m-d H:i') }}</p>
        </div>
    </div>


</x-dashboard>
