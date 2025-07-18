<x-dashboard title="Project Details - {{ $project->name }}">

    @push('css')
        <style>
            /* لون الأيقونة والمسافة بينها وبين الرابط */
            .project-links li i.fas.fa-link {
                color: #0d6efd;
                /* أزرق بوتستراب */
                margin-right: 8px;
                font-size: 1.1em;
            }

            /* تنسيق الرابط */
            .project-links li a {
                font-weight: 500;
                transition: color 0.3s ease;
            }

            .project-links li a:hover {
                color: #0a58ca;
                text-decoration: underline;
            }

            /* تنسيق الوصف (label) */
            .project-links li span.text-muted {
                font-style: italic;
                font-size: 0.9em;
                margin-left: 10px;
                color: #6c757d;
                /* رمادي */
            }

            /* تباعد أفضل بين عناصر كل رابط */
            .project-links li {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px 12px;
            }
        </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Project Details</h1>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>

    @php
        $currencySymbols = ['USD' => '$', 'EUR' => '€', 'DZD' => 'DZ'];
        $paidAmount = $project->payments->sum('amount');
        $remainingAmount = $project->total_amount - $paidAmount;

        $remainingDays = null;
        $remainingText = "N/A";
        $remainingClass = "badge bg-secondary";

        if ($project->delivery_date) {
            $remainingDays = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($project->delivery_date), false);
            $remainingDays = floor($remainingDays);
            if ($remainingDays < 0) {
                $remainingText = "Overdue " . abs($remainingDays) . " day(s)";
                $remainingClass = "badge bg-danger";
            } elseif ($remainingDays == 0) {
                $remainingText = "Due Today";
                $remainingClass = "badge bg-warning text-dark";
            } else {
                $remainingText = $remainingDays . " day(s)";
                $remainingClass = "badge bg-success";
            }
        }
    @endphp

    {{-- Quick Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Amount</h5>
                    <p class="card-text fs-4">{{ $currencySymbols[$project->currency] ?? '' }}
                        {{ number_format($project->total_amount, 2) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h5 class="card-title">Paid Amount</h5>
                    <p class="card-text fs-4">{{ $currencySymbols[$project->currency] ?? '' }}
                        {{ number_format($paidAmount, 2) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger h-100">
                <div class="card-body">
                    <h5 class="card-title">Remaining Amount</h5>
                    <p class="card-text fs-4">{{ $currencySymbols[$project->currency] ?? '' }}
                        {{ number_format($remainingAmount, 2) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h5 class="card-title">Remaining Days</h5>
                    <p class="card-text fs-4">
                        <span class="{{ $remainingClass }}">{{ $remainingText }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Details --}}
    <div class="row gy-4 align-items-stretch">
        {{-- Left Column --}}
        <div class="col-lg-7">
            <div class="card mb-5 shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Description:</strong></p>
                    <p>{{ $project->description ?? '-' }}</p>
                    <hr>
                    <p><strong>Start Date:</strong> {{ $project->start_date ?? '-' }}</p>
                    <p><strong>Duration:</strong>
                        {{ $project->duration_days ? $project->duration_days . ' day(s)' : '-' }}</p>
                    <p><strong>Delivery Date:</strong> {{ $project->delivery_date ?? '-' }}</p>
                    @php
                        $status = $project->status_text;
                    @endphp
                    <p><strong class=" mr-2">Status:</strong>
                        <span class="badge
                        {{ $status == 'expired' ? 'bg-danger text-white' : '' }}
                        {{ $status == 'due_today' ? 'bg-warning text-dark' : '' }}
                        {{ $status == 'active' ? 'bg-success' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </span>
                    </p>
                    <hr>
                    <p><strong>Attachments:</strong></p>
                    @if($project->attachments->isNotEmpty())
                        <ul class="list-group list-group-flush">
                            @foreach($project->attachments as $attachment)
                                <li class="list-group-item">
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                        class="text-decoration-none">
                                        <i class="fas fa-file-alt me-2"></i> {{ basename($attachment->file_path) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No attachments available.</p>
                    @endif
                    <hr>
                    <p><strong>Project Links:</strong></p>
                    @if($project->links->isNotEmpty())
                        <ul class="list-group list-group-flush project-links">
                            @foreach($project->links as $link)
                                <li>
                                    <i class="fas fa-link"></i>
                                    <a href="{{ $link->url }}" target="_blank">{{ $link->url }}</a>
                                    @if( $link->label)
                                        <span class="text-muted">({{$link->label }})</span>
                                    @endif
                                    <hr>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No links available.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-5 d-flex flex-column">
            <div class="card mb-4 shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <tr>
                                <th>Total Amount</th>
                                <td>{{ $currencySymbols[$project->currency] ?? '' }}
                                    {{ number_format($project->total_amount, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <th>Paid Amount</th>
                                <td class="text-success">{{ $currencySymbols[$project->currency] ?? '' }}
                                    {{ number_format($paidAmount, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <th>Remaining Amount</th>
                                <td class="text-danger">{{ $currencySymbols[$project->currency] ?? '' }}
                                    {{ number_format($remainingAmount, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <th>Total Worked Hours</th>
                                <td>{{ number_format($totalHours ?? 0, 2) }} hour(s)</td>
                            </tr>
                            <tr>
                                <th>Total Cost in DZD</th>
                                <td>{{ number_format($totalCostDZD ?? 0, 2) }} DZ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm mt-4 h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Client & Employees</h5>
                </div>
                <div class="card-body">
                    <p><strong>Client:</strong></p>
                    @if($project->client)
                        <span class="badge bg-primary p-2 mb-3 text-white">{{ $project->client->name }}</span>
                    @else
                        <span class="text-muted mb-3 d-block">-</span>
                    @endif

                    <p><strong>Employees:</strong></p>
                    @if($project->employees && $project->employees->isNotEmpty())
                        <div>
                            @foreach($project->employees as $employee)
                                <span class="badge bg-success p-2 me-2 mb-2 text-white">{{ $employee->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No employees assigned.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 p-3 border rounded bg-white text-dark mb-3 small d-flex justify-content-between align-items-center flex-wrap"
        style="max-width: 100%;">
        <div class="mb-2 d-flex align-items-center">
            <i class="fas fa-clock mr-2"></i>
            <span><strong>Created At:</strong> {{ $project->created_at->format('d M Y, H:i') }}</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="fas fa-sync-alt mr-2"></i>
            <span><strong>Last Updated:</strong> {{ $project->updated_at->format('d M Y, H:i') }}</span>
        </div>
    </div>

</x-dashboard>
