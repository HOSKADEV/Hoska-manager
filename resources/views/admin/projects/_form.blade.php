<div class="mb-3">
    <x-form.input label="Name" name="name" placeholder="Enter Project Name" :oldval="$project->name" />
</div>

<div class="mb-3">
    <x-form.area label="Description" name="description" placeholder="Enter Project Description"
        :oldval="$project->description" />
</div>

<div class="mb-3">
    <x-form.input label="Total Amount" name="total_amount" placeholder="Enter Project Total Amount" :oldval="$project->total_amount" />
</div>

@php
    $firstAttachment = optional($project->attachments)->first();
    $oldImage = $firstAttachment ? asset('storage/' . $firstAttachment->file_path) : null;
@endphp

<x-form.file
    label="Attachment"
    name="attachment"
    :oldimage="$oldImage"
    can_delete="true"
/>


<div class="mb-3">
    <x-form.select label="User" name="user_id" placeholder='Select User' :options="$users"
        :oldval="$project->user_id" />
</div>

<div class="mb-3">
    <x-form.select label="Client" name="client_id" placeholder='Select Client' :options="$clients"
        :oldval="$project->client_id" />
</div>

<div class="mb-3">
    <x-form.select label="Employee" name="employee_id" placeholder='Select Employee' :options="$employees"
        :oldval="$project->employee_id" />
</div>

