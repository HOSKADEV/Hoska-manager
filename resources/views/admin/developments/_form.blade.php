<div class="mb-3">
    <x-form.select label="Project" name="project_id" placeholder='Select Project' :options="$projects"
        :oldval="$development->project_id" />
</div>

<div class="mb-3">
    <x-form.area label="Description" name="description" placeholder="Enter Client Description"
        :oldval="$development->description" />
</div>

<div class="mb-3">
    <x-form.input label="Amount" name="amount" placeholder="Enter Development Amount" :oldval="$development->amount" />
</div>

<div class="mb-3">
    <x-form.select2 label="Currency" name="currency" :options="[
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'DZD' => 'Algerian Dinar'
    ]" :selected="$project->currency ?? old('currency')" placeholder="Select currency" />
</div>

<div class="mb-3">
    <x-form.input label="Start Date" name="start_date" type="date" :oldval="old('start_date', $development->start_date ? \Carbon\Carbon::parse($development->start_date)->format('Y-m-d') : '')" />
</div>

<div class="mb-3">
    <x-form.input label="Duration (Days)" name="duration_days" type="number" min="1" :oldval="old('duration_days', $development->duration_days)" placeholder="Enter duration in days" />
</div>

<div class="mb-3">
    <x-form.input label="Delivery Date" name="delivery_date" type="date" :oldval="old('delivery_date', $development->delivery_date ? \Carbon\Carbon::parse($development->delivery_date)->format('Y-m-d') : '')"
        readonly="true" />
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

            calculateDelivery(); // initial calculation on page load
        });

    </script>

@endpush
