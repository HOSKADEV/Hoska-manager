<div class="mb-3">
    <x-form.input label="Name" name="name" placeholder="Enter Project Name" :oldval="$project->name" />
</div>

<div class="mb-3">
    <x-form.area label="Description" name="description" placeholder="Enter Project Description"
        :oldval="$project->description" />
</div>

<div class="mb-3">
    <x-form.input label="Total Amount" name="total_amount" placeholder="Enter Project Total Amount"
        :oldval="$project->total_amount" />
</div>

@php
    $oldFiles = optional($project->attachments)->map(fn($file) => 'storage/' . $file->file_path)->toArray();
@endphp

<x-form.file label="Attachments" name="attachment" :oldfiles="$oldFiles" can_delete="true" multiple="true" />



<div class="mb-3">
    <x-form.select label="User" name="user_id" placeholder='Select User' :options="$users"
        :oldval="$project->user_id" />
</div>

<div class="mb-3">
    <x-form.select label="Client" name="client_id" placeholder='Select Client' :options="$clients"
        :oldval="$project->client_id" />
</div>

<div class="mb-3">
    @php
        $selectedEmployees = optional($project)->employees ? $project->employees->pluck('id')->toArray() : [];

    @endphp

    <x-form.select-multiple label="Employees" name="employee_id" :options="$employees" :oldval="$selectedEmployees"  multiple="true" placeholder="Select Employees" />
</div>

