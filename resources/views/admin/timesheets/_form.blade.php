<div class="mb-3">
    <x-form.input type="datetime-local" label="Work Date" name="work_date" placeholder="Enter Timesheet Work Date"
        :oldval="$timesheet->work_date" />
</div>

<div class="mb-3">
    <x-form.input label="Hours Worked" name="hours_worked" placeholder="Enter Timesheet Hours Worked" :oldval="$timesheet->hours_worked" />
</div>

<div class="mb-3 col-md-12">
    <x-form.select label="Employee" name="employee_id" placeholder='Select Employee' :options="$employees"
        :oldval="$timesheet->employee_id" />
</div>

<div class="mb-3 col-md-12">
    <x-form.select label="Project" name="project_id" placeholder='Select Project' :options="$projects"
        :oldval="$timesheet->project_id" />
</div>


