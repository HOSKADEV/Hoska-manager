<div class="mb-3">
    <x-form.input label="Title" name="title" placeholder="Enter Task Title" :oldval="$task->title ?? '' " />
</div>

<div class="mb-3">
    <x-form.area label="Description" name="description" placeholder="Enter Task Description"
        :oldval="$task->description" />
</div>

@php

    $stateOptions = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];

    $selectedValue = old('status', $task->status ?? '');
@endphp

<div class="mb-3 col-md-12">
    <label for="status" class="form-label">Status</label>
    <select name="status" id="status" class="form-control">
        <option value="" disabled {{ $selectedValue == '' ? 'selected' : '' }}>Select Status</option>
        @foreach($stateOptions as $value => $label)
            <option value="{{ $value }}" {{ $selectedValue == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

{{-- <div class="mb-3">
    <x-form.input type="datetime-local" label="Due Date" name="due_date" placeholder="Enter Task Due Date"
        :oldval="$task->due_date" />
</div> --}}

@if(isset($task) && $task->exists)
    {{-- تعديل --}}
    <x-form.input type="datetime-local" label="Start Time" name="start_time" placeholder="Enter Task Start Time"
        :oldval="$task->start_time->format('Y-m-d\TH:i')" />
    {{-- @else
    إنشاء
    <x-form.input type="datetime-local" label="Start Time" name="start_time" placeholder="Enter Task Start Time"
        :oldval="now()->format('Y-m-d\TH:i')" /> --}}
@endif

<div class="mb-3">
    <x-form.input type="datetime-local" label="End Time" name="end_time" placeholder="Enter Task End Time"
        :oldval="$task->end_time" />
</div>

@if(isset($task) && $task->exists)
    <div class="mb-3 col-md-12">
        <x-form.input label="Duration (Hours)" name="duration_in_hours" :oldval="$task->duration_in_hours" readonly />
    </div>
@endif

{{-- <div class="mb-3">
    <x-form.input label="Budget Amount" name="budget_amount" placeholder="Enter Task Budget Amount"
        :oldval="$task->budget_amount" />
</div> --}}

<div class="mb-3 col-md-12">
    <x-form.select label="Employee" name="employee_id" placeholder='Select Employee' :options="$employees"
        :oldval="$task->employee_id" />
</div>

@if (isset($task) && $task->exists)
    <div class="mb-3 col-md-12">
        <x-form.input label="Project" name="project_name" :oldval="$task->employee->projects->first()->name ?? 'N/A'"
            readonly />
    </div>
@endif

{{-- <div class="mb-3 col-md-12">
    <x-form.select-multiple label="Employees" name="employee_name" :oldval="$task->project->employee->name ?? 'N/A'"
        readonly multiple="true" placeholder="Select Employees" />
</div> --}}


@push('js')
    <script>
        function calculateDuration() {
            const start = document.querySelector('[name="start_time"]').value;
            const end = document.querySelector('[name="end_time"]').value;

            if (start && end) {
                const startTime = new Date(start);
                const endTime = new Date(end);

                if (!isNaN(startTime.getTime()) && !isNaN(endTime.getTime())) {
                    const diffMs = endTime - startTime;
                    const diffHours = diffMs / (1000 * 60 * 60);
                    const rounded = Math.round(diffHours * 100) / 100;
                    document.querySelector('[name="duration_in_hours"]').value = rounded >= 0 ? rounded : 0;
                } else {
                    document.querySelector('[name="duration_in_hours"]').value = '';
                }
            } else {
                document.querySelector('[name="duration_in_hours"]').value = '';
            }
        }

        // Event listeners for real-time update
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('[name="start_time"]').addEventListener('input', calculateDuration);
            document.querySelector('[name="end_time"]').addEventListener('input', calculateDuration);

            // Initial calculation if values exist
            calculateDuration();
        });
    </script>
@endpush
