<x-dashboard title="Show TimeSheet">

<div class="container">
    <h2>Timesheet Summary</h2>
    <p><strong>Employee:</strong> {{ $employee->name }}</p>
    <p><strong>Timesheet Date:</strong> {{ $timesheet->work_date->format('Y-m-d') }}</p>
    <p><strong>Total Hours Worked (Timesheet):</strong> {{ $timesheet->hours_worked }} hours</p>

    <hr>

    <h4>All Tasks for This Employee:</h4>

    @if($tasks->isEmpty())
        <p>No tasks found for this employee.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
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
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->project?->name ?? '-' }}</td>
                        <td>{{ $task->start_time ? $task->start_time->format('H:i') : '-' }}</td>
                        <td>{{ $task->end_time ? $task->end_time->format('H:i') : '-' }}</td>
                        <td>{{ number_format($duration, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="table-secondary">
                    <td colspan="5" class="text-end"><strong>Total Task Hours (All Time):</strong></td>
                    <td><strong>{{ number_format($totalHours, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    @endif
</div>

</x-dashboard>
