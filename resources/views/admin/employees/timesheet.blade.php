
<x-dashboard title="Employee Timesheet">

    @push('css')
        <style>
            /* Responsive table wrapper */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
                color: #1a1a1a;
            }

            th,
            td {
                padding: 12px 15px;
                border: 1px solid #ddd;
                text-align: center;
                font-size: 16px;
            }

            th {
                background-color: #0d47a1;
                color: white;
                font-weight: 700;
            }

            /* إزالة اللون الافتراضي */
            tbody tr:nth-child(even) {
                background-color: transparent;
            }

            /* تلوين الصفوف بالتناوب */
            .row-color-0 {
                background-color: #ffffff;
                /* أبيض */
            }

            .row-color-1 {
                background-color: #f5f5dc;
                /* بيج فاتح */
            }

            /* صف المجموع النهائي */
            .total-row {
                background-color: #0d47a1 !important;
                /* بيج أغمق */
                font-weight: bold;
            }

            tbody tr:hover {
                background-color: #e3f2fd;
            }

            .summary-box {
                background-color: #e3f2fd;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 30px;
                font-size: 18px;
            }

            h2,
            h3 {
                color: #0d47a1;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            @media (max-width: 600px) {

                th,
                td {
                    font-size: 14px;
                    padding: 10px 8px;
                }

                .summary-box {
                    font-size: 16px;
                    padding: 15px;
                }
            }

            /* Print-specific styles */
            @media print {
                /* Hide everything except the print content */
                body * {
                    visibility: hidden;
                }

                /* Show the container and its contents */
                .container, .container * {
                    visibility: visible;
                }

                /* Position the container properly */
                .container {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    margin: 0;
                    padding: 15px;
                }

                /* Hide the print button */
                .btn {
                    display: none !important;
                }

                /* Hide the filter form */
                #filterForm {
                    display: none !important;
                }

                /* Ensure proper table formatting */
                .table-responsive {
                    overflow: visible !important;
                }

                /* Add some spacing for better print layout */
                .summary-box {
                    page-break-inside: avoid;
                    margin-bottom: 20px;
                }

                table {
                    page-break-inside: avoid;
                }

                /* Maintain colors when printing */
                th {
                    background-color: #0d47a1 !important;
                    color: white !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .total-row {
                    background-color: #0d47a1 !important;
                    color: white !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .row-color-1 {
                    background-color: #f5f5dc !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .summary-box {
                    background-color: #e3f2fd !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                h2, h3 {
                    color: #0d47a1 !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                /* Hide any copyright or footer information */
                footer, .copyright, .app-footer {
                    display: none !important;
                }
            }
        </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Employee Timesheet - {{ $employee->name }}</h2>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-info">
            <i class="fas fa-long-arrow-alt-left"></i> Back to Employees
        </a>
    </div>

    <div class="container py-4" style="max-width: 900px; margin: auto;">
        <div class="summary-box">
            <p><strong>Employee Name:</strong> {{ $employee->name }}</p>
            <p><strong>Timesheet Month:</strong>
                @if($monthFilter === 'all')
                    All Months
                @else
                    {{ \Carbon\Carbon::parse($monthFilter)->format('F Y') }}
                @endif
            </p>
            <p><strong>Total Hours Worked:</strong> {{ number_format($timesheet->hours_worked, 2) }} hours</p>
            <p><strong>Monthly Salary:</strong> {{ number_format($timesheet->month_salary, 2) }} {{ $employee->currency }}</p>
            <p><strong>RIP:</strong> {{ $employee->iban }}</p>
            <p><strong>Hour Rate:</strong>
                @if($monthFilter === 'all')
                    {{ number_format($employee->rate, 2) }}
                @else
                    {{ number_format($timesheet->rate, 2) }}
                @endif
            {{ $employee->currency }}</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Tasks for this Employee</h3>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Tasks
            </button>
        </div>

        @if($tasks->isEmpty())
            <p>No tasks found for this employee in the selected month.</p>
        @else
            <div class="d-flex align-items-center mb-3">
                {{-- فلتر حسب الشهر والمشروع --}}
                <form method="GET" action="{{ route('admin.employees.timesheet', $employee->id) }}" class="mb-4" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-5 me-2">
                            <label for="month" class="form-label fw-bold text-secondary">📅 Filter by Month</label>
                            <select name="month" id="month" class="form-select select2">
                                <option value="all" {{ $monthFilter == 'all' ? 'selected' : '' }}>All Months</option>
                                @foreach ($availableMonths as $month)
                                    <option value="{{ $month['value'] }}" {{ $monthFilter == $month['value'] ? 'selected' : '' }}>
                                        {{ $month['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="project_id" class="form-label fw-bold text-secondary">📅 Filter by Project</label>
                            <select name="project_id" id="project_id" class="form-select select2">
                                <option value="all" {{ $projectFilter == 'all' ? 'selected' : '' }}>All Projects</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" {{ $projectFilter == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Task Title</th>
                            <th>Project</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Duration (hours)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tasksByDate = $tasks->groupBy(fn($task) => $task->start_time ? $task->start_time->format('Y-m-d') : 'N/A');
                            $totalHours = 0;
                            $rowColors = ['row-color-0', 'row-color-1'];
                            $colorIndex = 0;
                        @endphp

                        @foreach ($tasksByDate as $date => $tasksForDate)
                            @php
                                $rowspan = $tasksForDate->count();
                                $rowClass = $rowColors[$colorIndex % count($rowColors)];
                                $colorIndex++;
                                $dateTotalHours = $tasksForDate->sum(fn($t) => $t->duration_in_hours ?? 0);
                                $totalHours += $dateTotalHours;
                            @endphp

                            @foreach ($tasksForDate as $index => $task)
                                <tr class="{{ $rowClass }}">
                                    @if ($index == 0)
                                        <td rowspan="{{ $rowspan }}">{{ $date }}</td>
                                    @endif
                                    <td style="text-align: left;">{{ $task->title }}</td>
                                    <td>{{ $task->project?->name ?? '-' }}</td>
                                    <td>{{ $task->start_time ? $task->start_time->format('H:i') : '-' }}</td>
                                    <td>{{ $task->end_time ? $task->end_time->format('H:i') : '-' }}</td>
                                    <td>{{ number_format($task->duration_in_hours ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                            

                            <tr class="{{ $rowClass }}" style="font-weight: bold;">
                                <td colspan="5" style="text-align: center;">Total Hours for {{ $date }}:</td>
                                <td>{{ number_format($dateTotalHours, 2) }}</td>
                            </tr>
                        @endforeach

                        <tr class="total-row bg-primary text-white">
                            <td colspan="5" style="text-align: center;">Total Task Hours:</td>
                            <td>{{ number_format($totalHours, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @push('css')
        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('js')
        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <!-- Initialize Select2 -->
        <script>
            $(document).ready(function () {
                $('#project_id').select2({
                    placeholder: "Select an option",
                    allowClear: true,
                    width: '100%'
                });

                $('#month').select2({
                    placeholder: "Select a month",
                    allowClear: true,
                    width: '100%'
                });

                // استمع لأي تغيير في الفلاتر وأرسل الفورم
                $('#project_id, #month').on('change', function () {
                    $(this).closest('form').submit();
                });
            });
        </script>
    @endpush

</x-dashboard>
