<x-dashboard title="تقييم الرضا الوظيفي">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">تقييم الرضا الوظيفي</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">تقييم رضاك الوظيفي لشهر {{ $currentMonth }}/{{ $currentYear }}</h6>
        </div>
        <div class="card-body">
            @if ($existing)
                <div class="alert alert-info">
                    <h5>لقد قمت بتقديم تقييمك بالفعل لهذا الشهر</h5>
                    <p>تقييمك الحالي: <strong>{{ round(($existing->salary_compensation + $existing->work_environment + $existing->colleagues_relationship + $existing->management_relationship + $existing->growth_opportunities + $existing->work_life_balance) / 6, 1) }}/10</strong></p>
                    <p>يمكنك تحديث تقييمك في أي وقت.</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.satisfaction.submit') }}" method="POST">
                @csrf
                @if($existing)
                    @method('PUT')
                @endif
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                <input type="hidden" name="month" value="{{ $currentMonth }}">
                <input type="hidden" name="year" value="{{ $currentYear }}">

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">مقاييس الرضا الوظيفي</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="salary_compensation" class="form-label">الراتب والتعويضات 💰 (1-10)</label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="salary_compensation" name="salary_compensation" style="width: 75%"
                                           min="1" max="10" value="{{ $existing ? $existing->salary_compensation : 3 }}"
                                           oninput="document.getElementById('salary_compensation_value').textContent = this.value; updatePredictedScore()">
                                    <span class="input-group-text" id="salary_compensation_value">{{ $existing ? $existing->salary_compensation : 3 }}</span>
                                </div>
                                <div class="form-text">
                                    1 = غير راضٍ تماماً، 10 = راضٍ تماماً
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="work_environment" class="form-label">بيئة العمل 🏢 (1-10)</label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="work_environment" name="work_environment" style="width: 75%"
                                           min="1" max="10" value="{{ $existing ? $existing->work_environment : 3 }}"
                                           oninput="document.getElementById('work_environment_value').textContent = this.value; updatePredictedScore()">
                                    <span class="input-group-text" id="work_environment_value">{{ $existing ? $existing->work_environment : 3 }}</span>
                                </div>
                                <div class="form-text">
                                    1 = غير راضٍ تماماً، 10 = راضٍ تماماً
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="colleagues_relationship" class="form-label">العلاقات مع الزملاء 🤝 (1-10)</label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="colleagues_relationship" name="colleagues_relationship" style="width: 75%"
                                           min="1" max="10" value="{{ $existing ? $existing->colleagues_relationship : 3 }}"
                                           oninput="document.getElementById('colleagues_relationship_value').textContent = this.value; updatePredictedScore()">
                                    <span class="input-group-text" id="colleagues_relationship_value">{{ $existing ? $existing->colleagues_relationship : 3 }}</span>
                                </div>
                                <div class="form-text">
                                    1 = غير راضٍ تماماً، 10 = راضٍ تماماً
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="management_relationship" class="form-label">العلاقة مع الإدارة 👔 (1-10)</label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="management_relationship" name="management_relationship" style="width: 75%"
                                           min="1" max="10" value="{{ $existing ? $existing->management_relationship : 3 }}"
                                           oninput="document.getElementById('management_relationship_value').textContent = this.value; updatePredictedScore()">
                                    <span class="input-group-text" id="management_relationship_value">{{ $existing ? $existing->management_relationship : 3 }}</span>
                                </div>
                                <div class="form-text">
                                    1 = غير راضٍ تماماً، 10 = راضٍ تماماً
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="growth_opportunities" class="form-label">فرص النمو والتطور 📈 (1-10)</label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="growth_opportunities" name="growth_opportunities" style="width: 75%"
                                           min="1" max="10" value="{{ $existing ? $existing->growth_opportunities : 3 }}"
                                           oninput="document.getElementById('growth_opportunities_value').textContent = this.value; updatePredictedScore()">
                                    <span class="input-group-text" id="growth_opportunities_value">{{ $existing ? $existing->growth_opportunities : 3 }}</span>
                                </div>
                                <div class="form-text">
                                    1 = غير راضٍ تماماً، 10 = راضٍ تماماً
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="work_life_balance" class="form-label">التوازن بين العمل والحياة 🕒 (1-10)</label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="work_life_balance" name="work_life_balance" style="width: 75%"
                                           min="1" max="10" value="{{ $existing ? $existing->work_life_balance : 3 }}"
                                           oninput="document.getElementById('work_life_balance_value').textContent = this.value; updatePredictedScore()">
                                    <span class="input-group-text" id="work_life_balance_value">{{ $existing ? $existing->work_life_balance : 3 }}</span>
                                </div>
                                <div class="form-text">
                                    1 = غير راضٍ تماماً، 10 = راضٍ تماماً
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-info">
                                    <strong>الرضا الوظيفي الكلي = (الراتب والتعويضات + بيئة العمل + العلاقات مع الزملاء + العلاقة مع الإدارة + فرص النمو والتطور + التوازن بين العمل والحياة) / 6</strong>
                                    <div class="mt-2">
                                        <span>النتيجة المتوقعة: </span>
                                        <span id="predicted_score" class="fw-bold">
                                            {{ $existing ? round(($existing->salary_compensation + $existing->work_environment + $existing->colleagues_relationship + $existing->management_relationship + $existing->growth_opportunities + $existing->work_life_balance) / 6, 1) : '3.0' }}/10
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">{{ $existing ? 'تحديث التقييم' : 'حفظ التقييم' }}</button>
                </div>
            </form>
        </div>
    </div>

    @push('js')
        <script>
            function updatePredictedScore() {
                const salary_compensation = parseFloat(document.getElementById('salary_compensation').value);
                const work_environment = parseFloat(document.getElementById('work_environment').value);
                const colleagues_relationship = parseFloat(document.getElementById('colleagues_relationship').value);
                const management_relationship = parseFloat(document.getElementById('management_relationship').value);
                const growth_opportunities = parseFloat(document.getElementById('growth_opportunities').value);
                const work_life_balance = parseFloat(document.getElementById('work_life_balance').value);

                const total = salary_compensation + work_environment + colleagues_relationship + management_relationship + growth_opportunities + work_life_balance;
                const average = total / 6;

                document.getElementById('predicted_score').textContent = average.toFixed(1) + '/10';
            }
        </script>
    @endpush
</x-dashboard>
