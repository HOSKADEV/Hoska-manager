<x-dashboard title="Project Details - {{ $project->name }}">

    @push('css')
        <style>
            /* لون الأيقونة والمسافة بينها وبين الرابط */
            .project-links li i.fas.fa-link {
                color: #0d6efd;
                margin-right: 8px;
                font-size: 1.1em;
            }

            .project-links li a {
                font-weight: 500;
                transition: color 0.3s ease;
            }

            .project-links li a:hover {
                color: #0a58ca;
                text-decoration: underline;
            }

            .project-links li span.text-muted {
                font-style: italic;
                font-size: 0.9em;
                margin-left: 10px;
                color: #6c757d;
            }

            .project-links li {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px 12px;
            }

            /* تنسيق قائمة تفاصيل الموظفين */
            .employee-hours-details {
                position: relative;
                cursor: pointer;
                margin-left: 8px;
                display: inline-block;
            }

            .employee-hours-details .details-dropdown {
                display: none;
                position: absolute;
                top: 24px;
                left: 0;
                background: white;
                border: 1px solid #ccc;
                padding: 10px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
                z-index: 1000;
                min-width: 220px;
                font-size: 0.9em;
                border-radius: 4px;
            }

            .employee-hours-details .details-dropdown ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .employee-hours-details .details-dropdown li {
                margin-bottom: 8px;
                line-height: 1.2;
            }

            .employee-hours-details .details-dropdown hr {
                margin: 6px 0;
                border: 0;
                border-top: 1px solid #eee;
            }
        </style>
    @endpush

    @php
        // احسب مجموع التطويرات
        $developmentsTotal = $developments->sum('amount') ?? 0;
        $isEmployee = auth()->user()->type === 'employee';
        $currencySymbols = ['USD' => '$', 'EUR' => '€', 'DZD' => 'DZ'];

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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Project Details</h1>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>

    @if(!$isEmployee)
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
                            {{ number_format($project->total_paid_amount_project_with_developments, 2) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger h-100">
                    <div class="card-body">
                        <h5 class="card-title">Remaining Amount</h5>
                        <p class="card-text fs-4">{{ $currencySymbols[$project->currency] ?? '' }}
                            {{ number_format($project->total_amount_project_with_developments - $project->total_paid_amount_project_with_developments, 2) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                @if ($project->delivered_at)
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <h5 class="card-title mb-0">Delivered</h5>
                        </div>
                    </div>
                @else
                    <div class="card text-white bg-info h-100">
                        <div class="card-body">
                            <h5 class="card-title">Remaining Days</h5>
                            <p class="card-text fs-4"><span class="{{ $remainingClass }}">{{ $remainingText }}</span></p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row gy-4 align-items-stretch">
        <div class="col-lg-7">
            <div class="card mb-5 shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong></p>
                    <p>{{ $project->name ?? '-' }}</p>
                    <hr>
                    <p><strong>Description:</strong></p>
                    <p>{{ $project->description ?? '-' }}</p>
                    <hr>
                    <p><strong>Start Date:</strong> {{ $project->start_date ?? '-' }}</p>
                    <p><strong>Duration:</strong>
                        {{ $project->duration_days ? $project->duration_days . ' day(s)' : '-' }}</p>
                    <p><strong>Delivery Date:</strong> {{ $project->delivery_date ?? '-' }}</p>
                    </span>
                    </p>
                    <hr>

                    {{-- @if((bool) $project->is_manual)
                        <p><strong>Manual Hours Spent:</strong> {{ number_format($project->manual_hours_spent ?? 0, 2) }}
                            hour(s)</p>
                        <p><strong>Manual Cost:</strong> {{ number_format($project->manual_cost ?? 0, 2) }}
                            {{ $currencySymbols[$project->currency] ?? '' }}
                        </p>
                        <p><strong>Is Manual:</strong> نعم</p>
                    @else --}}
                        {{-- <p><strong>Is Manual:</strong> لا</p>
                    @endif --}}

                    <hr>

                    @if(!$isEmployee)
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
                    @endif

                    <p><strong>Project Links:</strong></p>
                    @if($project->links->isNotEmpty())
                        <ul class="list-group list-group-flush project-links">
                            @foreach($project->links as $link)
                                <li>
                                    <i class="fas fa-link"></i>
                                    <a href="{{ $link->url }}" target="_blank">{{ $link->url }}</a>
                                    @if($link->label)
                                        <span class="text-muted">({{ $link->label }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No links available.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5 d-flex flex-column">
            @if(!$isEmployee)
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
                                @if($developments->isNotEmpty())
                                    <tr>
                                        <th>Total Amount Development</th>
                                        @if($developments->isEmpty())
                                            <td colspan="7" class="text-center text-muted">No developments available</td>
                                        @else
                                            <td colspan="{{ count($developments) }}">
                                                @foreach ( $developments as $development )
                                                    {{ $currencySymbols[$development->currency] ?? '' }}
                                                    {{ number_format($developmentsTotal, 2) }}
                                                @endforeach
                                            </td>
                                        @endif
                                    </tr>
                                @endif
                                <tr>
                                    <th>Paid Amount</th>
                                    <td class="text-success">{{ $currencySymbols[$project->currency] ?? '' }}
                                        {{ number_format($project->total_paid_amount_project_with_developments, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Remaining Amount</th>
                                    <td class="text-danger">{{ $currencySymbols[$project->currency] ?? '' }}
                                        {{ number_format($project->total_amount_project_with_developments - $project->total_paid_amount_project_with_developments, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Total Worked Hours</th>
                                    <td>
                                        {{-- @if($project->is_manual)
                                            @php
                                                $manualHours = $project->manual_hours_spent ?? 0;
                                                $wholeHours = floor($manualHours);
                                                $minutes = round(($manualHours - $wholeHours) * 60);
                                            @endphp
                                            {{ $wholeHours }}h {{ $minutes }}m
                                        @else --}}
                                            @php
                                                $wholeHours = floor($totalHours ?? 0);
                                                $minutes = round((($totalHours ?? 0) - $wholeHours) * 60);
                                            @endphp
                                            {{ $wholeHours }}h {{ $minutes }}m

                                            <button class="btn btn-sm btn-light border toggle-employee-details ml-3" type="button"
                                            aria-expanded="false" title="Show details">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                        {{-- @endif --}}
                                    </td>

                                </tr>

                                {{-- @if(!$project->is_manual) --}}
                                    <tr id="employeeDetailsRow" class="employee-details-row" style="display: none;">
                                        <td colspan="2" class="p-3 bg-light">
                                            <ul class="list-unstyled mb-0">
                                                @foreach($hoursByEmployee as $empId => $hours)
                                                    @php
                                                        $employee = $employees[$empId];
                                                        $cost = $costsByEmployee[$empId] ?? 0;
                                                        $currencySymbol = $currencySymbols[$employee->currency ?? $project->currency] ?? '';
                                                    @endphp
                                                    <li class="mb-2">
                                                        <span class="text-primary fw-semibold">Name:</span>
                                                        <strong>{{ $employee->name }}</strong><br>

                                                        @php
                                                        $wholeHours = floor($hours);
                                                        $minutes = round(($hours - $wholeHours) * 60);
                                                        @endphp
                                                        <span class="text-primary fw-semibold">Hours:</span>
                                                        {{ $wholeHours }}h {{ $minutes }}m<br>

                                                        <span class="text-primary fw-semibold">Cost:</span>
                                                        {{ $currencySymbol }} {{ number_format($cost, 2) }}
                                                        <hr>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const toggleBtn = document.querySelector('.toggle-employee-details');
                                            const detailsRow = document.getElementById('employeeDetailsRow');
                                            const icon = toggleBtn.querySelector('i');

                                            toggleBtn.addEventListener('click', () => {
                                                const isVisible = detailsRow.style.display === 'table-row';
                                                if (isVisible) {
                                                    detailsRow.style.display = 'none';
                                                    toggleBtn.setAttribute('aria-expanded', 'false');
                                                    icon.classList.remove('fa-times');
                                                    icon.classList.add('fa-ellipsis-v');
                                                } else {
                                                    detailsRow.style.display = 'table-row';
                                                    toggleBtn.setAttribute('aria-expanded', 'true');
                                                    icon.classList.remove('fa-ellipsis-v');
                                                    icon.classList.add('fa-times');
                                                }
                                            });
                                        });
                                    </script>
                                {{-- @endif --}}

                                <tr>
                                    <th>Total Cost in DZD</th>
                                    <td>
                                        {{-- @if($project->is_manual)
                                            {{ number_format($project->manual_cost ?? 0, 2) }} DZ
                                        @else --}}
                                            {{ number_format($totalCostDZD ?? 0, 2) }} DZ
                                        {{-- @endif --}}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

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

            @if(auth()->user()->type === 'admin' &&$marketer &&$marketerCommissionPercent)
                <div class="card shadow-sm mt-4 h-100">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Marketer Info</h5>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <p>
                                <strong>Name:</strong>
                                <span class="badge bg-success p-2 me-2 mb-2 text-white">
                                    {{ $marketer->name }}
                                </span>
                            </p>
                            <p><strong>Email:</strong> {{ $marketer->email }}</p>
                            <p><strong>Commission:</strong> {{ $marketerCommissionPercent }}%</p>
                            <p>
                                <strong>Commission Amount:</strong>
                                {{ $currencySymbols[$project->currency] ?? '' }}
                                {{ number_format($marketerCommissionAmount, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
    @if($developments->isNotEmpty())
        <div class="row gy-4 align-items-stretch mt-4">
            <div class="col-lg-12">
                <div class="card mb-5 shadow-sm h-100">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Developments Information</h5>
                    </div>
                    <div class="card-body">
                        @if($developments->isNotEmpty())
                            <table class="table table-bordered table-striped mb-0" style="font-size: 0.9rem;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        @unless ($isEmployee)
                                        <th>Total Amount</th>
                                        <th>Paid Amount</th>
                                        @endunless
                                        <th>Project Name</th>
                                        <th>Start Date</th>
                                        <th>Duration (Days)</th>
                                        <th>Delivery Date</th>
                                        <th>Remaining Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($developments as $index => $development)
                                        @php
                                            $currencySymbols = ['USD' => '$', 'EUR' => '€', 'DZD' => 'DZ'];
                                            $currency = $development->currency ?? 'DZD';  // العملة من التطوير نفسه
                                            $remainingDays = $development->remaining_days;
                                            $days = floor($remainingDays);
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $development->description ?? '-' }}</td>
                                            @unless ($isEmployee)

                                            <td>{{ $currencySymbols[$currency] ?? '' }}{{ number_format($development->amount, 2) }}</td>
                                            <td>{{ $currencySymbols[$currency] ?? '' }}{{ number_format($development->paid_amount, 2) }}</td>
                                            @endunless
                                            <td>{{ $development->project->name ?? '-' }}</td>
                                            <td>{{ $development->start_date ? \Carbon\Carbon::parse($development->start_date)->format('Y-m-d') : '-' }}</td>
                                            <td>{{ $development->duration_days ?? '-' }}</td>
                                            <td>{{ $development->delivery_date ? \Carbon\Carbon::parse($development->delivery_date)->format('Y-m-d') : '-' }}</td>
                                            <td>

                                                @if ($development->delivered_at)
                                                    <span class="badge badge-primary">Delivered</span>
                                                @elseif (!is_null($remainingDays))
                                                    @if($days < 0)
                                                        <span class="badge bg-danger">Overdue {{ abs($days) }} day(s)</span>
                                                    @elseif($days == 0)
                                                        <span class="badge bg-warning text-dark">Due Today</span>
                                                    @else
                                                        <span class="badge bg-success">{{ $days }} day(s)</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted text-center">No developments found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Tasks Section -->
    <!-- Customer Satisfaction Section -->
    <div class="row gy-4 align-items-stretch mt-4">
        <div class="col-lg-12">
            <div class="card mb-5 shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Customer Satisfaction</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Satisfaction Components:</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>جودة التسليم</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $project->delivery_quality ?? 0 }}%;" aria-valuenow="{{ $project->delivery_quality ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $project->delivery_quality ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>سرعة الاستجابة</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $project->response_speed ?? 0 }}%;" aria-valuenow="{{ $project->response_speed ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $project->response_speed ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>مستوى الدعم</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $project->support_level ?? 0 }}%;" aria-valuenow="{{ $project->support_level ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $project->support_level ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>تحقيق التوقعات</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $project->expectations_met ?? 0 }}%;" aria-valuenow="{{ $project->expectations_met ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $project->expectations_met ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>نية الاستمرار</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $project->continuation_intent ?? 0 }}%;" aria-valuenow="{{ $project->continuation_intent ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $project->continuation_intent ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                            <h6 class="mb-3">Final Customer Satisfaction Score:</h6>
                            <div class="circular-progress mb-3" style="position: relative; width: 180px; height: 180px;">
                                <svg viewBox="0 0 36 36" class="circular-progress-bar" style="width: 100%; height: 100%;">
                                    <path class="circular-progress-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="3"/>
                                    <path class="circular-progress-fill" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#28a745" stroke-width="3" stroke-dasharray="{{ ($project->final_satisfaction_score ?? 0) }}, 100"/>
                                </svg>
                                <div class="circular-progress-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.5rem; font-weight: bold;">
                                            {{ $project->final_satisfaction_score ?? 0 }}%
                                        </div>
                            </div>
                            <p class="text-center">رضاء العملاء النهائي = (جودة التسليم + سرعة الاستجابة + مستوى الدعم + تحقيق التوقعات + نية الاستمرار)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Section -->
    <div class="row gy-4 align-items-stretch mt-4">
        <div class="col-lg-12">
            <div class="card mb-5 shadow-sm h-100">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Our Tasks Information</h5>
                    <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#addTaskModal">
                        <i class="fas fa-plus"></i> Add New Task
                    </button>
                </div>
                <div class="card-body">
                    @if($project->ourTasks->isNotEmpty())
                        <table class="table table-bordered table-striped mb-0" style="font-size: 0.9rem;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Cost</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->ourTasks as $index => $task)
                                    @php
                                        $currencySymbols = [
                                            'USD' => '$',
                                            'EUR' => '€',
                                            'DZD' => 'DZ'
                                        ];
                                        // Duration is now displayed as text
                                        $totalMinutes = (int) round($task->duration * 60);
                                        $hours = intdiv($totalMinutes, 60);
                                        $minutes = $totalMinutes % 60;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $task->title }}</td>
                                        <td>{{ $task->description ?? '-' }}</td>
                                        <td>{{ $hours }}h {{ $minutes }}m</td>
                                        <td>{{ $currencySymbols[$project->currency] ?? '' }}{{ number_format($task->cost, 2) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-task-btn"
                                                data-id="{{ $task->id }}"
                                                data-title="{{ $task->title }}"
                                                data-description="{{ $task->description }}"
                                                data-duration="{{ $task->duration ?? '' }}"
                                                data-cost="{{ $task->cost }}"
                                                data-toggle="modal" data-target="#editTaskModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.our-tasks.destroy', $task->id) }}" method="POST" class="delete-task-form" style="display: inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center">No tasks found. Click "Add New Task" to create one.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.our-tasks.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="duration">Duration (hours)</label>
                            <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g. 100">
                        </div>

                        <div class="form-group">
                            <label for="cost">Cost</label>
                            <input type="number" class="form-control" id="cost" name="cost" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="#" method="POST" id="editTaskForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="project_id" value="{{ $project->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_title">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="edit_duration">Duration (hours)</label>
                            <input type="text" class="form-control" id="edit_duration" name="duration" placeholder="e.g. 100">
                        </div>

                        <div class="form-group">
                            <label for="edit_cost">Cost</label>
                            <input type="number" class="form-control" id="edit_cost" name="cost" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle edit task button click
                document.querySelectorAll('.edit-task-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const taskId = this.getAttribute('data-id');
                        const title = this.getAttribute('data-title');
                        const description = this.getAttribute('data-description');
                        const duration = this.getAttribute('data-duration');
                        const cost = this.getAttribute('data-cost');

                        // Populate form fields
                        document.getElementById('edit_title').value = title;
                        document.getElementById('edit_description').value = description;
                        document.getElementById('edit_duration').value = duration;
                        document.getElementById('edit_cost').value = cost;

                        // Update form action
                        const form = document.getElementById('editTaskForm');
                        const baseUrl = "{{ url('/admin/our-tasks/') }}";
                        form.action = baseUrl + '/' + taskId;
                    });
                });

                // Handle add task form submission with AJAX
                const addTaskForm = document.querySelector('#addTaskModal form');
                if (addTaskForm) {
                    addTaskForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        const url = this.action;

                        fetch(url, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').length ? $('meta[name="csrf-token"]').attr('content') : $('input[name="_token"]').val()
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Close modal
                            $('#addTaskModal').modal('hide');

                            // Reset form
                            this.reset();

                            // Show success message
                            // if (typeof flash !== 'undefined') {
                            //     flash().success('Task created successfully');
                            // } else {
                            //     alert('Task created successfully');
                            // }

                            // Reload page to show new task
                            window.location.reload();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error creating task. Please try again.');
                        });
                    });
                }

                // Handle edit task form submission with AJAX
                const editTaskForm = document.getElementById('editTaskForm');
                if (editTaskForm) {
                    editTaskForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        const url = this.action;

                        fetch(url, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').length ? $('meta[name="csrf-token"]').attr('content') : $('input[name="_token"]').val()
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Close modal
                            $('#editTaskModal').modal('hide');

                            // Show success message
                            // if (typeof flash !== 'undefined') {
                            //     flash().success('Task updated successfully');
                            // } else {
                            //     alert('Task updated successfully');
                            // }

                            // Reload page to show updated task
                            window.location.reload();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error updating task. Please try again.');
                        });
                    });
                }
            });
        </script>
    @endpush
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

    @push('js')
        <script>
            $(document).ready(function() {
                // Handle edit task button click
                $('.edit-task-btn').on('click', function() {
                    const taskId = $(this).data('id');
                    const title = $(this).data('title');
                    const description = $(this).data('description');
                    const duration = $(this).data('duration');
                    const cost = $(this).data('cost');

                    // Populate the edit form
                    $('#edit_title').val(title);
                    $('#edit_description').val(description);
                    $('#edit_duration').val(duration);
                    $('#edit_cost').val(cost);

                    // Update form action URL
                    const baseUrl = "{{ url('/admin/our-tasks/') }}";
                    $('#editTaskForm').attr('action', baseUrl + '/' + taskId);
                });

                // Handle delete task with AJAX
                $(document).on('submit', '.delete-task-form', function(e) {
                    e.preventDefault();

                    if (confirm('Are you sure you want to delete this task?')) {
                        const form = $(this);
                        const url = form.attr('action');

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: form.serialize(),
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').length ? $('meta[name="csrf-token"]').attr('content') : $('input[name="_token"]').val()
                            },
                            success: function(response) {
                                // Show success message
                                // if (typeof flash !== 'undefined') {
                                //     flash().success('Task deleted successfully');
                                // } else {
                                //     alert('Task deleted successfully');
                                // }

                                // Reload page to reflect deletion
                                window.location.reload();
                            },
                            error: function(xhr) {
                                let errorMessage = 'Error deleting task. Please try again.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                alert(errorMessage);
                            }
                        });
                    }
                });
            });
        </script>
    @endpush

</x-dashboard>
