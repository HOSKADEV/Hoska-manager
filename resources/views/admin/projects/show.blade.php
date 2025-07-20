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
        $isEmployee = auth()->user()->type === 'employee';
        $currencySymbols = ['USD' => '$', 'EUR' => '€', 'DZD' => 'DZ'];

        if ($project->is_manual) {
            // المشروع يدوي: المبلغ الكلي = المدفوع، والمتبقي صفر
            $paidAmount = $project->total_amount;
            $remainingAmount = 0;
        } else {
            $paidAmount = $project->payments->sum('amount');
            $remainingAmount = $project->total_amount - $paidAmount;
        }

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
                        <p class="card-text fs-4"><span class="{{ $remainingClass }}">{{ $remainingText }}</span></p>
                    </div>
                </div>
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
                    @php $status = $project->status_text; @endphp
                    <p><strong>Status:</strong>
                        <span class="badge
                            {{ $status == 'expired' ? 'bg-danger text-white' : '' }}
                            {{ $status == 'due_today' ? 'bg-warning text-dark' : '' }}
                            {{ $status == 'active' ? 'bg-success' : '' }} ">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </span>
                    </p>
                    <hr>

                    @if((bool) $project->is_manual)
                        <p><strong>Manual Hours Spent:</strong> {{ number_format($project->manual_hours_spent ?? 0, 2) }}
                            hour(s)</p>
                        <p><strong>Manual Cost:</strong> {{ number_format($project->manual_cost ?? 0, 2) }}
                            {{ $currencySymbols[$project->currency] ?? '' }}
                        </p>
                        <p><strong>Is Manual:</strong> نعم</p>
                    @else
                        <p><strong>Is Manual:</strong> لا</p>
                    @endif

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
                                    <td>
                                    @if($project->is_manual)
                                    @php
                                    $manualHours = $project->manual_hours_spent ?? 0;
                                    $wholeHours = floor($manualHours);
                                    $minutes = round(($manualHours - $wholeHours) * 60);
                                    @endphp
                                    {{ $wholeHours }}h {{ $minutes }}m
                                    @else
                                    @php
                                    $wholeHours = floor($totalHours ?? 0);
                                    $minutes = round((($totalHours ?? 0) - $wholeHours) * 60);
                                    @endphp
                                    {{ $wholeHours }}h {{ $minutes }}m

                                    <button class="btn btn-sm btn-light border toggle-employee-details ml-3" type="button"
                                    aria-expanded="false" title="Show details">
                                    <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    @endif
                                    </td>

                                </tr>

                                @if(!$project->is_manual)
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
                                @endif

                                <tr>
                                    <th>Total Cost in DZD</th>
                                    <td>
                                        @if($project->is_manual)
                                            {{ number_format($project->manual_cost ?? 0, 2) }} DZ
                                        @else
                                            {{ number_format($totalCostDZD ?? 0, 2) }} DZ
                                        @endif
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
