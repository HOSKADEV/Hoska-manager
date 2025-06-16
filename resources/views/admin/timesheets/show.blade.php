<x-dashboard title="Timesheet Details">

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

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
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

    <div class="container py-4" style="max-width: 900px; margin: auto;">
        <h2>Employee Timesheet</h2>

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
                        @php $totalHours = 0; @endphp
                        @foreach ($tasks as $task)
                            @php
                                $duration = $task->duration_in_hours ?? 0;
                                $totalHours += $duration;
                            @endphp
                            <tr>
                                <td>{{ $task->start_time ? $task->start_time->format('Y-m-d') : '-' }}</td>
                                <td style="text-align: left;">{{ $task->title }}</td>
                                <td>{{ $task->project?->name ?? '-' }}</td>
                                <td>{{ $task->start_time ? $task->start_time->format('H:i') : '-' }}</td>
                                <td>{{ $task->end_time ? $task->end_time->format('H:i') : '-' }}</td>
                                <td>{{ number_format($duration, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: bold; background-color: #bbdefb;">
                            <td colspan="5" style="text-align: center;">Total Task Hours:</td>
                            <td>{{ number_format($totalHours, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-dashboard>
