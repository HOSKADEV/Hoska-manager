<x-dashboard title="Edit Contract">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Edit Contract</h1>
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.contracts.update', $contract->id) }}">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="employee" {{ $contract->type === 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="project" {{ $contract->type === 'project' ? 'selected' : '' }}>Project</option>
                        </select>
                        @error('type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- <div class="col-md-6">
                        <label for="name" class="form-label">Contract Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $contract->name) }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div> --}}
                    <div class="col-md-6" id="employee-field" style="{{ $contract->type === 'employee' ? '' : 'display: none;' }}">
                        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="contractable_id" id="employee_id" class="form-select select2" {{ $contract->type === 'employee' ? 'required' : '' }}>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $contract->type === 'employee' && $contract->contractable_id == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('contractable_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6" id="project-field" style="{{ $contract->type === 'project' ? '' : 'display: none;' }}">
                        <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                        <select name="contractable_id" id="project_id" class="form-select select2" {{ $contract->type === 'project' ? 'required' : '' }}>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ $contract->type === 'project' && $contract->contractable_id == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('contractable_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">

                    <div class="col-md-6">
                        <label for="url" class="form-label">Contract URL <span class="text-danger">*</span></label>
                        <input type="url" name="url" id="url" class="form-control" value="{{ old('url', $contract->url) }}" required>
                        @error('url')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Contract</button>
                </div>
            </form>
        </div>
    </div>

    @push('css')
        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('js')
        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('type');
                const employeeField = document.getElementById('employee-field');
                const projectField = document.getElementById('project-field');
                const employeeSelect = document.getElementById('employee_id');
                const projectSelect = document.getElementById('project_id');

                function toggleFields() {
                    const selectedType = typeSelect.value;

                    if (selectedType === 'employee') {
                        employeeField.style.display = 'block';
                        projectField.style.display = 'none';
                        employeeSelect.setAttribute('name', 'contractable_id');
                        projectSelect.setAttribute('name', '');
                        employeeSelect.setAttribute('required', 'required');
                        projectSelect.removeAttribute('required');
                    } else if (selectedType === 'project') {
                        employeeField.style.display = 'none';
                        projectField.style.display = 'block';
                        employeeSelect.setAttribute('name', '');
                        projectSelect.setAttribute('name', 'contractable_id');
                        employeeSelect.removeAttribute('required');
                        projectSelect.setAttribute('required', 'required');
                    } else {
                        employeeField.style.display = 'none';
                        projectField.style.display = 'none';
                        employeeSelect.setAttribute('name', '');
                        projectSelect.setAttribute('name', '');
                        employeeSelect.removeAttribute('required');
                        projectSelect.removeAttribute('required');
                    }

                }

                typeSelect.addEventListener('change', toggleFields);

                // Initialize fields visibility
                toggleFields();

                // Initialize Select2
                $('.select2').select2({
                    placeholder: "Select an option",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>
    @endpush
</x-dashboard>
