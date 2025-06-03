<div class="mb-3">
    <x-form.input label="Title" name="title" placeholder="Enter Task Title" :oldval="$task->title" />
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

<div class="mb-3">
    <x-form.input type="datetime-local" label="Due Date" name="due_date" placeholder="Enter Task Due Date" :oldval="$task->due_date" />
</div>

<div class="mb-3">
    <x-form.input label="Budget Amount" name="budget_amount" placeholder="Enter Task Budget Amount" :oldval="$task->budget_amount" />
</div>

<div class="mb-3 col-md-12">
    <x-form.select label="Project" name="project_id" placeholder='Select User' :options="$projects" :oldval="$task->project_id" />
</div>

<div class="mb-3 col-md-12">
    <x-form.select label="Employee" name="employee_id" placeholder='Select User' :options="$employees" :oldval="$task->employee_id" />
</div>
