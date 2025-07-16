@if(auth()->user()->type === 'admin')
    <div class="mb-3">
        <x-form.select3 label="Employee" name="employee_id"  id="employee_id" placeholder='Select Employee' :options="$employees"
            :oldval="$task->employee_id" required />
    </div>

    <div class="mb-3">
        <x-form.select3 label="Project" name="project_id"  id="project_id"  placeholder='Select Project' :options="$projects"
            :oldval="$task->project_id" required />
    </div>
@elseif(auth()->user()->employee)
    <input type="hidden" name="employee_id" value="{{ auth()->user()->employee->id }}">

    <div class="mb-3">
        <x-form.select3 label="Project" name="project_id" placeholder='Select Project' :options="$projects"
            :oldval="$task->project_id" required />
    </div>
@else
    <div class="alert alert-warning">
        No employee linked to your account. Please contact admin.
    </div>
@endif

<div class="mb-3">
    <x-form.input label="Title" name="title" placeholder="Enter Task Title" :oldval="$task->title ?? '' " required />
</div>

<div class="mb-3">
    <x-form.area label="Description" name="description" placeholder="Enter Task Description"
        :oldval="$task->description" />
</div>

{{-- @if(!isset($task) || !$task->exists) --}}
{{-- عند الإنشاء: الحالة مخفية وقيمتها completed --}}
{{-- <input type="hidden" name="status" value="completed">
@else --}}
{{-- عند التعديل: عرض select الحالة --}}
{{-- @php
$stateOptions = [
'pending' => 'Pending',
'in_progress' => 'In Progress',
'completed' => 'Completed',
];
$selectedValue = old('status', $task->status ?? '');
@endphp --}}

{{-- <div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select name="status" id="status" class="form-control">
        <option value="" disabled {{ $selectedValue=='' ? 'selected' : '' }}>Select Status</option>
        @foreach($stateOptions as $value => $label)
        <option value="{{ $value }}" {{ $selectedValue==$value ? 'selected' : '' }}>
            {{ $label }}
        </option>
        @endforeach
    </select>
</div>
@endif --}}

{{-- <div class="mb-3">
    <x-form.input type="datetime-local" label="Due Date" name="due_date" placeholder="Enter Task Due Date"
        :oldval="$task->due_date" />
</div> --}}

{{-- @if(isset($task) && $task->exists) --}}
<div class="mb-3">
    <x-form.input type="datetime-local" label="Start Time" name="start_time" placeholder="Enter Task Start Time"
        :oldval="$task->start_time ? $task->start_time->format('Y-m-d\TH:i') : '' " required />
</div>
{{-- @endif --}}

<div class="mb-3">
    <x-form.input type="datetime-local" label="End Time" name="end_time" placeholder="Enter Task End Time"
        :oldval="$task->end_time ? $task->end_time->format('Y-m-d\TH:i') : ''" required />
</div>

@if(isset($task) && $task->exists)
    @php
        $totalMinutes = (int) round($task->duration_in_hours * 60);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $durationFormatted = $hours . 'h ' . $minutes . 'm';
    @endphp

    <div class="mb-3">
        <x-form.input label="Duration (Hours)" name="duration_in_hours" :oldval="$task->duration_in_hours" readonly />
    </div>

    <div class="mb-3">
        <x-form.input label="Duration (Formatted)" name="duration_formatted" :oldval="$durationFormatted" readonly />
    </div>
@endif

{{-- <div class="mb-3">
    <x-form.input label="Budget Amount" name="budget_amount" placeholder="Enter Task Budget Amount"
        :oldval="$task->budget_amount" />
</div> --}}
{{--
<div class="mb-3 col-md-12">
    <x-form.select label="Employee" name="employee_id" placeholder='Select Employee' :options="$employees"
        :oldval="$task->employee_id" />
</div> --}}

{{-- @if(auth()->user()->type === 'admin')
<div class="mb-3">
    <x-form.select label="Employee" name="employee_id" placeholder='Select Employee' :options="$employees"
        :oldval="$task->employee_id" />
</div>
@else
<div class="mb-3 col-md-12">
    <input type="hidden" name="employee_id" value="{{ auth()->user()->employee->id }}">
</div>
@endif --}}


{{--
<div class="mb-3">
    <x-form.select label="Project" name="project_id" placeholder='Select Project' :options="$projects"
        :oldval="$task->project_id" />
</div> --}}

{{-- @if (isset($task) && $task->exists)
<div class="mb-3 col-md-12">
    <x-form.input label="Project" name="project_name" :oldval="$task->employee->projects->first()->name ?? 'N/A'"
        readonly />
</div>
@endif --}}

{{-- <div class="mb-3 col-md-12">
    <x-form.select label="Project" name="project_id" :oldval="$task->project_id ?? 'N/A'" :options="$projects"
        placeholder="Select Project" />
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const employeeSelect = document.getElementById('employee_id');
            const projectSelect = document.getElementById('project_id');

            if (employeeSelect) {
                employeeSelect.addEventListener('change', function () {
                    const employeeId = this.value;
                    if (!employeeId) {
                        projectSelect.innerHTML = '<option value="">Select Project</option>';
                        return;
                    }

                    fetch(`/admin/employees/${employeeId}/projects`)
                        .then(response => response.json())
                        .then(projects => {
                            // أفرغ الخيارات الحالية
                            projectSelect.innerHTML = '<option value="">Select Project</option>';

                            // أضف المشاريع الجديدة
                            projects.forEach(project => {
                                const option = document.createElement('option');
                                option.value = project.id;
                                option.textContent = project.name;
                                projectSelect.appendChild(option);
                            });

                            // إذا عندك قيمة قديمة، اختارها
                            @if(old('project_id') || isset($task->project_id))
                                const oldProjectId = "{{ old('project_id', $task->project_id ?? '') }}";
                                if (oldProjectId) {
                                    projectSelect.value = oldProjectId;
                                }
                            @endif
                            })
                        .catch(err => console.error('Failed to load projects:', err));
                });

                // Trigger change event تلقائيًا لو فيه قيمة محددة مسبقًا
                if (employeeSelect.value) {
                    employeeSelect.dispatchEvent(new Event('change'));
                }
            }
        });
    </script>

@endpush
