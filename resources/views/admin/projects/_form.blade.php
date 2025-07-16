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
    <x-form.input label="Start Date" name="start_date" type="date" :oldval="old('start_date', optional($project->start_date)->format('Y-m-d'))" />
</div>

<div class="mb-3">
    <x-form.input label="Duration (Days)" name="duration_days" type="text" min="1" :oldval="old('duration_days', $project->duration_days)" />
</div>

<div class="mb-3">
    <x-form.input label="Delivery Date" name="delivery_date" type="date" :oldval="old('delivery_date', optional($project->delivery_date)->format('Y-m-d'))" readonly="true" />
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
@endpush
