<x-dashboard title="Project Details - {{ $project->name }}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Project Details</h1>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>

    @php
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'DZD' => 'DZ',
        ];

        $remainingDays = null;
        $remainingText = "N/A";
        $remainingClass = "badge badge-secondary";

        if ($project->delivery_date) {
            $remainingDays = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($project->delivery_date), false);
            $remainingDays = floor($remainingDays); // تقريب للأسفل لإزالة الكسور

            if ($remainingDays < 0) {
                $remainingText = "Overdue " . abs($remainingDays) . " day(s)";
                $remainingClass = "badge badge-danger";
            } elseif ($remainingDays === 0) {
                $remainingText = "Due Today";
                $remainingClass = "badge badge-warning";
            } else {
                $remainingText = $remainingDays . " day(s)";
                $remainingClass = "badge badge-success";
            }
        }
    @endphp

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">{{ $project->name }}</h4>
        </div>
        <div class="card-body">
            <p><strong>Description:</strong></p>
            <p>{{ $project->description ?? '-' }}</p>

            <p><strong>Total Amount:</strong>
                {{ $currencySymbols[$project->currency] ?? '' }} {{ number_format($project->total_amount, 2) }}
            </p>

            <p><strong>Start Date:</strong> {{ $project->start_date ?? '-' }}</p>
            <p><strong>Duration:</strong> {{ $project->duration_days ? $project->duration_days . ' day(s)' : '-' }}</p>
            <p><strong>Delivery Date:</strong> {{ $project->delivery_date ?? '-' }}</p>
            <p><strong>Remaining:</strong> <span class="{{ $remainingClass }}">{{ $remainingText }}</span></p>

            <p><strong>Attachments:</strong></p>
            @if($project->attachments->isNotEmpty())
                <ul>
                    @foreach($project->attachments as $attachment)
                        <li>
                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                class="text-decoration-none">
                                {{ basename($attachment->file_path) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">No attachments available.</p>
            @endif

            <p><strong class="d-block mb-2">Client:</strong>
                @if($project->client)
                    <span class="badge badge-primary p-2 mb-2">{{ $project->client->name }}</span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </p>

            <p><strong>Employees:</strong></p>
            @if($project->employees && $project->employees->isNotEmpty())
                <div>
                    @foreach($project->employees as $employee)
                        <span class="badge badge-success p-2 mb-3">{{ $employee->name }}</span>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No employees assigned.</p>
            @endif

            <p><strong>Created At:</strong> {{ $project->created_at->format('d M Y, H:i') }}</p>
            <p><strong>Last Updated:</strong> {{ $project->updated_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

</x-dashboard>
