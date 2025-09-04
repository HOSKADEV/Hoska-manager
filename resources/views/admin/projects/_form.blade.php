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
    <x-form.select label="Client" name="client_id" data-original-client-id="{{ $project->client_id ?? '' }}"
        placeholder='Select Client' :options="$clients" :oldval="$project->client_id" id="client_id" />
</div>

<div id="marketer-fields" style="display:none;" data-has-marketer="{{ $project->marketer_id ? 'true' : 'false' }}">
    <div class="mb-3">
        <x-form.select label="Marketer" name="marketer_id" :options="$marketers" :oldval="$project->marketer_id"
            placeholder="Select marketer" />
    </div>

    <div class="mb-3">
        <label for="marketer_commission_percent" class="form-label">Marketer Commission Percent</label>
        <div class="input-group">
            <input type="text" step="0.01" min="0" max="100"
                class="form-control mr-1 @error('marketer_commission_percent') is-invalid @enderror"
                name="marketer_commission_percent" id="marketer_commission_percent"
                value="{{ old('marketer_commission_percent', $project->marketer_commission_percent) }}"
                placeholder="Enter commission percent" />
            <span class="input-group-text">%</span>
        </div>
        @error('marketer_commission_percent')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
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

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Customer Satisfaction Metrics</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="delivery_quality" class="form-label">جودة التسليم (0-100)</label>
                <div class="input-group">
                    <input type="range" class="form-range" id="delivery_quality" name="delivery_quality" style="width: 75%"
                           min="0" max="100" value="{{ old('delivery_quality', $project->delivery_quality ?? 0) }}"
                           oninput="document.getElementById('delivery_quality_value').textContent = this.value; updatePredictedScore()">
                    <span class="input-group-text" id="delivery_quality_value">{{ old('delivery_quality', $project->delivery_quality ?? 0) }}</span>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="response_speed" class="form-label">سرعة الاستجابة (0-100)</label>
                <div class="input-group">
                    <input type="range" class="form-range" id="response_speed" name="response_speed" style="width: 75%"
                           min="0" max="100" value="{{ old('response_speed', $project->response_speed ?? 0) }}"
                           oninput="document.getElementById('response_speed_value').textContent = this.value; updatePredictedScore()">
                    <span class="input-group-text" id="response_speed_value">{{ old('response_speed', $project->response_speed ?? 0) }}</span>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="support_level" class="form-label">مستوى الدعم (0-100)</label>
                <div class="input-group">
                    <input type="range" class="form-range" id="support_level" name="support_level" style="width: 75%"
                           min="0" max="100" value="{{ old('support_level', $project->support_level ?? 0) }}"
                           oninput="document.getElementById('support_level_value').textContent = this.value; updatePredictedScore()">
                    <span class="input-group-text" id="support_level_value">{{ old('support_level', $project->support_level ?? 0) }}</span>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="expectations_met" class="form-label">تحقيق التوقعات (0-100)</label>
                <div class="input-group">
                    <input type="range" class="form-range" id="expectations_met" name="expectations_met" style="width: 75%"
                           min="0" max="100" value="{{ old('expectations_met', $project->expectations_met ?? 0) }}"
                           oninput="document.getElementById('expectations_met_value').textContent = this.value; updatePredictedScore()">
                    <span class="input-group-text" id="expectations_met_value">{{ old('expectations_met', $project->expectations_met ?? 0) }}</span>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="continuation_intent" class="form-label">نية الاستمرار (0-100)</label>
                <div class="input-group">
                    <input type="range" class="form-range" id="continuation_intent" name="continuation_intent" style="width: 75%"
                           min="0" max="100" value="{{ old('continuation_intent', $project->continuation_intent ?? 0) }}"
                           oninput="document.getElementById('continuation_intent_value').textContent = this.value; updatePredictedScore()">
                    <span class="input-group-text" id="continuation_intent_value">{{ old('continuation_intent', $project->continuation_intent ?? 0) }}</span>
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <div class="alert alert-info">
                    <strong>رضاء العملاء النهائي = (جودة التسليم + سرعة الاستجابة + مستوى الدعم + تحقيق التوقعات + نية الاستمرار) / 5</strong>
                    <div class="mt-2">
                        <span>النتيجة المتوقعة: </span>
                        <span id="predicted_score" class="fw-bold">
                            {{ round((($project->delivery_quality ?? 0) + ($project->response_speed ?? 0) + ($project->support_level ?? 0) + ($project->expectations_met ?? 0) + ($project->continuation_intent ?? 0)) / 5) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- <div id="manualFields">
    <div class="mb-3">
        <x-form.input label="Manual Hours Spent" name="manual_hours_spent" type="text" step="0.1" min="0"
            :oldval="old('manual_hours_spent', $project->manual_hours_spent)" placeholder="Enter manual hours spent" />
    </div>

    <div class="mb-3">
        <x-form.input label="Manual Cost" name="manual_cost" type="text" step="0.01" min="0" :oldval="old('manual_cost', $project->manual_cost)" placeholder="Enter manual cost" />
    </div>
</div> --}}

{{-- <div class="form-check mb-3">
    <input type="checkbox" id="is_manual" name="is_manual" value="1" {{ old('is_manual', $project->is_manual) ? 'checked' : '' }}>

    <label class="form-check-label" for="is_manual">Is Manual</label>
</div> --}}


@push('js')
    <script>
        function updatePredictedScore() {
            const deliveryQuality = parseInt(document.getElementById('delivery_quality').value) || 0;
            const responseSpeed = parseInt(document.getElementById('response_speed').value) || 0;
            const supportLevel = parseInt(document.getElementById('support_level').value) || 0;
            const expectationsMet = parseInt(document.getElementById('expectations_met').value) || 0;
            const continuationIntent = parseInt(document.getElementById('continuation_intent').value) || 0;

            const predictedScore = Math.round((deliveryQuality + responseSpeed + supportLevel + expectationsMet + continuationIntent) / 5);
            document.getElementById('predicted_score').textContent = predictedScore + '%';
        }

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
            // const isManualCheckbox = document.getElementById('is_manual');
            // const manualFields = document.getElementById('manualFields');

            // function toggleManualFields() {
            //     if (isManualCheckbox.checked) {
            //         manualFields.style.display = 'block';
            //     } else {
            //         manualFields.style.display = 'none';
            //     }
            // }

            // initial toggle on page load
            // toggleManualFields();

            // toggle on checkbox change
            // isManualCheckbox.addEventListener('change', toggleManualFields);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clientSelect = document.getElementById('client_id');
            const marketerFields = document.getElementById('marketer-fields');
            const marketerSelect = document.querySelector('select[name="marketer_id"]');
            const commissionInput = document.querySelector('input[name="marketer_commission_percent"]');

            // خزن القيم الأصلية من صفحة الـ HTML (مثلاً من قيم الحقول الحالية)
            const originalClientId = clientSelect.dataset.originalClientId || null;
            const originalMarketerId = marketerSelect.value || '';
            const originalCommissionPercent = commissionInput.value || '';

            async function checkClientProjects(clientId) {
                if (!clientId) {
                    marketerFields.style.display = 'none';
                    return;
                }

                try {
                    const response = await fetch(`/admin/clients/${clientId}/has-projects`);
                    const data = await response.json();

                    if (data.hasProjects === false) {
                        marketerFields.style.display = 'block';
                    } else {
                        marketerFields.style.display = marketerSelect.value ? 'block' : 'none';
                    }
                } catch (error) {
                    console.error('Error fetching client projects status:', error);
                    marketerFields.style.display = 'none';
                }
            }

            clientSelect.addEventListener('change', function () {
                const selectedClientId = this.value;

                if (originalClientId && selectedClientId === originalClientId) {
                    // العميل الأصلي، أعد تعبئة القيم الأصلية
                    marketerSelect.value = originalMarketerId;
                    commissionInput.value = originalCommissionPercent;
                    marketerFields.style.display = 'block';
                } else {
                    // عميل جديد، امسح القيم وأخفي الحقول
                    marketerSelect.value = '';
                    commissionInput.value = '';
                    marketerFields.style.display = 'none';

                    checkClientProjects(selectedClientId);
                }
            });

            marketerSelect.addEventListener('change', function () {
                if (this.value) {
                    marketerFields.style.display = 'block';
                } else {
                    marketerFields.style.display = 'none';
                }
            });

            // تحقق أولي عند تحميل الصفحة:
            if (marketerSelect.value) {
                marketerFields.style.display = 'block';
            } else if (clientSelect.value) {
                checkClientProjects(clientSelect.value);
            } else {
                marketerFields.style.display = 'none';
            }
        });

    </script>
@endpush
