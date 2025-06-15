<div class="mb-3">
    <x-form.input type="datetime-local" label="Work Date" name="work_date" placeholder="Enter Timesheet Work Date"
        :oldval="$timesheet->work_date" />
</div>

@if (isset($timesheet) && $timesheet->exists)
    <div class="mb-3">
        <x-form.input label="Hours Worked" name="hours_worked" placeholder="Hours Worked will be calculated automatically"
            :oldval="$timesheet->hours_worked" readonly />
    </div>
@endif

<div class="mb-3 col-md-12">
    <x-form.select label="Employee" name="employee_id" placeholder='Select Employee' :options="$employees"
        :oldval="$timesheet->employee_id" />
</div>

@if (isset($timesheet) && $timesheet->exists)
    <div class="mb-3 col-md-12">
        <x-form.input label="Project" name="project_name" :oldval="$timesheet->employee->projects->first()->name ?? 'N/A'" readonly/>
    </div>
@endif
