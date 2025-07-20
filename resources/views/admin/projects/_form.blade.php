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

<div class="mb-3">
    <x-form.select2 label="Currency" name="currency" :options="['EUR' => 'Euro', 'USD' => 'US Dollar', 'DZD' => 'Algerian Dinar']" :selected="$project->currency ?? old('currency')" placeholder="Select currency" />
</div>

<div class="mb-3">
    @php
        $oldFiles = optional($project->attachments)->map(fn($file) => 'storage/' . $file->file_path)->toArray();
    @endphp

    <x-form.file label="Attachments" name="attachment" :oldfiles="$oldFiles" can_delete="true" multiple="true" />
</div>

<div class="mb-3">
    <x-form.select label="Client" name="client_id" placeholder='Select Client' :options="$clients"
        :oldval="$project->client_id" />
</div>

<div class="mb-3">
    @php
        $selectedEmployees = optional($project)->employees ? $project->employees->pluck('id')->toArray() : [];

    @endphp

    <x-form.select-multiple label="Employees" name="employee_id" :options="$employees" :oldval="$selectedEmployees"
        multiple="true" placeholder="Select Employees" />
</div>

<div class="mb-3">
    <x-form.input label="Start Date" name="start_date" type="date" :oldval="old('start_date', $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '')" />
</div>

<div class="mb-3">
    <x-form.input label="Duration (Days)" name="duration_days" type="text" min="1" :oldval="old('duration_days', $project->duration_days)" />
</div>

<div class="mb-3">
    <x-form.input label="Delivery Date" name="delivery_date" type="date" :oldval="old('delivery_date', $project->delivery_date ? \Carbon\Carbon::parse($project->delivery_date)->format('Y-m-d') : '')"
        readonly="true" />
</div>

<div class="mb-3">
    <label class="form-label">Project Links</label>

    <div id="project-links">
        @php
            $links = old('links', isset($project) ? $project->links : []);
        @endphp

        @forelse($links as $i => $link)
            <div class="link-group d-flex gap-2 mb-2">
                <input type="hidden" name="links[existing][{{ is_object($link) ? $link->id : $i }}][id]"
                    value="{{ is_object($link) ? $link->id : $link['id'] ?? '' }}">
                <input type="url" name="links[existing][{{ is_object($link) ? $link->id : $i }}][url]"
                    class="form-control mr-2" placeholder="Link URL"
                    value="{{ old("links.existing.$i.url", is_object($link) ? $link->url : $link['url'] ?? '') }}" />
                <input type="text" name="links[existing][{{ is_object($link) ? $link->id : $i }}][label]"
                    class="form-control mr-2" placeholder="Label (optional)"
                    value="{{ old("links.existing.$i.label", is_object($link) ? $link->label : $link['label'] ?? '') }}" />
                <button type="button" class="btn btn-danger btn-sm remove-link">✕</button>
            </div>
        @empty
            <div class="link-group d-flex gap-2 mb-2">
                <input type="url" name="links[new][0][url]" class="form-control mr-2" placeholder="Link URL" />
                <input type="text" name="links[new][0][label]" class="form-control mr-2" placeholder="Label (optional)" />
                <button type="button" class="btn btn-danger btn-sm remove-link">✕</button>
            </div>
        @endforelse
    </div>

    <button type="button" class="btn btn-sm btn-primary mt-2" id="add-link">+ Add Link</button>
</div>


<div id="manualFields">
    <div class="mb-3">
        <x-form.input label="Manual Hours Spent" name="manual_hours_spent" type="number" step="0.1" min="0"
            :oldval="old('manual_hours_spent', $project->manual_hours_spent)" placeholder="Enter manual hours spent" />
    </div>

    <div class="mb-3">
        <x-form.input label="Manual Cost" name="manual_cost" type="number" step="0.01" min="0"
            :oldval="old('manual_cost', $project->manual_cost)" placeholder="Enter manual cost" />
    </div>
</div>

<div class="form-check mb-3">
    <input type="checkbox" id="is_manual" name="is_manual" value="1" {{ old('is_manual', $project->is_manual) ? 'checked' : '' }}>

    <label class="form-check-label" for="is_manual">Is Manual</label>
</div>


@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startInput = document.querySelector('input[name="start_date"]');
            const durationInput = document.querySelector('input[name="duration_days"]');
            const deliveryInput = document.querySelector('input[name="delivery_date"]');

            function calculateDelivery() {
                const startDate = startInput.value;
                const duration = parseInt(durationInput.value);

                if (startDate && duration > 0) {
                    const date = new Date(startDate);
                    date.setDate(date.getDate() + duration);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    deliveryInput.value = `${year}-${month}-${day}`;
                } else {
                    deliveryInput.value = '';
                }
            }

            startInput.addEventListener('input', calculateDelivery);
            durationInput.addEventListener('input', calculateDelivery);

            calculateDelivery(); // Initial run
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let linkIndex = 0;

            document.getElementById('add-link').addEventListener('click', function () {
                const group = document.createElement('div');
                group.classList.add('link-group', 'd-flex', 'gap-2', 'mb-2');
                group.innerHTML = `
                                <input type="url" name="links[new][${linkIndex}][url]" class="form-control mr-2" placeholder="Link URL" />
                                <input type="text" name="links[new][${linkIndex}][label]" class="form-control mr-2" placeholder="Label (optional)" />
                                <button type="button" class="btn btn-danger btn-sm remove-link">✕</button>
                            `;
                document.getElementById('project-links').appendChild(group);
                linkIndex++;
            });

            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-link')) {
                    e.target.closest('.link-group').remove();
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isManualCheckbox = document.getElementById('is_manual');
            const manualFields = document.getElementById('manualFields');

            function toggleManualFields() {
                if (isManualCheckbox.checked) {
                    manualFields.style.display = 'block';
                } else {
                    manualFields.style.display = 'none';
                }
            }

            // initial toggle on page load
            toggleManualFields();

            // toggle on checkbox change
            isManualCheckbox.addEventListener('change', toggleManualFields);
        });
    </script>
@endpush
