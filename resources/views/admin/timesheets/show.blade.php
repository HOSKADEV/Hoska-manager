<x-dashboard title="Timesheet Details">

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
        </style>
    @endpush

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Employee Timesheet</h2>
        <a href="{{ route('admin.timesheets.index') }}" class="btn btn-info">
            <i class="fas fa-long-arrow-alt-left"></i> All Timesheets
        </a>
    </div>

    <div class="container py-4" style="max-width: 900px; margin: auto;">
        <div class="summary-box">
            <p><strong>Employee Name:</strong> {{ $employee->name }}</p>
            <p><strong>Timesheet Date:</strong> {{ $timesheet->work_date->format('Y-m-d') }}</p>
            <p><strong>Total Hours Worked:</strong> {{ number_format($timesheet->hours_worked, 2) }} hours</p>
        </div>

        <h3>Tasks for this Employee</h3>

        @if($tasks->isEmpty())
            <p>No tasks found for this employee on this date.</p>
        @else
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

</x-dashboard>
