<x-dashboard title="قياس الرضا الوظيفي">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">قياس الرضا الوظيفي</h1>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">فلترة البيانات</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.employee-satisfaction.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="year" class="form-label">السنة</label>
                    <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="month" class="form-label">الشهر</label>
                    <select name="month" id="month" class="form-select" onchange="this.form.submit()">
                        <option value="1" {{ $month == 1 ? 'selected' : '' }}>يناير</option>
                        <option value="2" {{ $month == 2 ? 'selected' : '' }}>فبراير</option>
                        <option value="3" {{ $month == 3 ? 'selected' : '' }}>مارس</option>
                        <option value="4" {{ $month == 4 ? 'selected' : '' }}>أبريل</option>
                        <option value="5" {{ $month == 5 ? 'selected' : '' }}>مايو</option>
                        <option value="6" {{ $month == 6 ? 'selected' : '' }}>يونيو</option>
                        <option value="7" {{ $month == 7 ? 'selected' : '' }}>يوليو</option>
                        <option value="8" {{ $month == 8 ? 'selected' : '' }}>أغسطس</option>
                        <option value="9" {{ $month == 9 ? 'selected' : '' }}>سبتمبر</option>
                        <option value="10" {{ $month == 10 ? 'selected' : '' }}>أكتوبر</option>
                        <option value="11" {{ $month == 11 ? 'selected' : '' }}>نوفمبر</option>
                        <option value="12" {{ $month == 12 ? 'selected' : '' }}>ديسمبر</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="{{ route('admin.employee-satisfaction.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Overall Satisfaction Summary -->
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">متوسط الرضا الوظيفي - {{ $month }}/{{ $year }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">متوسط التقييمات:</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>الراتب والتعويضات 💰</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($overallAverages['salary_compensation'] / 10) * 100 }}%;" aria-valuenow="{{ $overallAverages['salary_compensation'] }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ number_format($overallAverages['salary_compensation'], 1) }}/10
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>بيئة العمل 🏢</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($overallAverages['work_environment'] / 10) * 100 }}%;" aria-valuenow="{{ $overallAverages['work_environment'] }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ number_format($overallAverages['work_environment'], 1) }}/10
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>العلاقات مع الزملاء 🤝</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($overallAverages['colleagues_relationship'] / 10) * 100 }}%;" aria-valuenow="{{ $overallAverages['colleagues_relationship'] }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ number_format($overallAverages['colleagues_relationship'], 1) }}/10
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>العلاقة مع الإدارة 👔</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($overallAverages['management_relationship'] / 10) * 100 }}%;" aria-valuenow="{{ $overallAverages['management_relationship'] }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ number_format($overallAverages['management_relationship'], 1) }}/10
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>فرص النمو والتطور 📈</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($overallAverages['growth_opportunities'] / 10) * 100 }}%;" aria-valuenow="{{ $overallAverages['growth_opportunities'] }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ number_format($overallAverages['growth_opportunities'], 1) }}/10
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>التوازن بين العمل والحياة 🕒</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($overallAverages['work_life_balance'] / 10) * 100 }}%;" aria-valuenow="{{ $overallAverages['work_life_balance'] }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ number_format($overallAverages['work_life_balance'], 1) }}/10
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                            <h6 class="mb-3">متوسط الرضا الوظيفي الكلي:</h6>
                            <div class="circular-progress mb-3" style="position: relative; width: 180px; height: 180px;">
                                <svg viewBox="0 0 36 36" class="circular-progress-bar" style="width: 100%; height: 100%;">
                                    <path class="circular-progress-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="3"/>
                                    <path class="circular-progress-fill" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#28a745" stroke-width="3" stroke-dasharray="{{ ($overallAverages['overall_satisfaction'] / 5) * 100 }}, 100"/>
                                </svg>
                                <div class="circular-progress-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.5rem; font-weight: bold;">
                                    {{ number_format($overallAverages['overall_satisfaction'], 1) }}/10
                                </div>
                            </div>
                            <p class="text-center">الرضا الوظيفي الكلي = متوسط جميع التقييمات</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    {{-- <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">اتجاه الرضا الوظيفي خلال {{ $year }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="satisfactionTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Employee Satisfaction Details -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">تفاصيل رضا الموظفين - {{ $month }}/{{ $year }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="employeeSatisfactionTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>الموظف</th>
                            <th>الراتب والتعويضات 💰</th>
                            <th>بيئة العمل 🏢</th>
                            <th>العلاقات مع الزملاء 🤝</th>
                            <th>العلاقة مع الإدارة 👔</th>
                            <th>فرص النمو والتطور 📈</th>
                            <th>التوازن بين العمل والحياة 🕒</th>
                            <th>الرضا الكلي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($satisfactionData as $data)
                            <tr>
                                <td>{{ $data['employee']->name }}</td>
                                @if($data['satisfaction'])
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($data['satisfaction']->salary_compensation / 10) * 100 }}%;" aria-valuenow="{{ $data['satisfaction']->salary_compensation }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ $data['satisfaction']->salary_compensation }}/10
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($data['satisfaction']->work_environment / 10) * 100 }}%;" aria-valuenow="{{ $data['satisfaction']->work_environment }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ $data['satisfaction']->work_environment }}/10
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($data['satisfaction']->colleagues_relationship / 10) * 100 }}%;" aria-valuenow="{{ $data['satisfaction']->colleagues_relationship }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ $data['satisfaction']->colleagues_relationship }}/10
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($data['satisfaction']->management_relationship / 10) * 100 }}%;" aria-valuenow="{{ $data['satisfaction']->management_relationship }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ $data['satisfaction']->management_relationship }}/10
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($data['satisfaction']->growth_opportunities / 10) * 100 }}%;" aria-valuenow="{{ $data['satisfaction']->growth_opportunities }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ $data['satisfaction']->growth_opportunities }}/10
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($data['satisfaction']->work_life_balance / 10) * 100 }}%;" aria-valuenow="{{ $data['satisfaction']->work_life_balance }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ $data['satisfaction']->work_life_balance }}/10
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ ($data['overall_score'] / 10) * 100 }}%;" aria-valuenow="{{ $data['overall_score'] }}" aria-valuemin="0" aria-valuemax="10">
                                                {{ number_format($data['overall_score'], 1) }}/10
                                            </div>
                                        </div>
                                    </td>
                                @else
                                    <td colspan="7" class="text-center text-muted">لا يوجد تقييم لهذا الشهر</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dashboard>

@push('js')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Trend Chart
            const ctx = document.getElementById('satisfactionTrendChart').getContext('2d');
            const satisfactionTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($monthlyLabels),
                    datasets: [
                        {
                            label: 'الراتب والتعويضات',
                            data: [
                                @foreach($monthlyTrend as $month => $data)
                                    {{ $data['salary_compensation'] }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            ],
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'بيئة العمل',
                            data: [
                                @foreach($monthlyTrend as $month => $data)
                                    {{ $data['work_environment'] }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            ],
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'العلاقات مع الزملاء',
                            data: [
                                @foreach($monthlyTrend as $month => $data)
                                    {{ $data['colleagues_relationship'] }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            ],
                            borderColor: 'rgb(255, 206, 86)',
                            backgroundColor: 'rgba(255, 206, 86, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'العلاقة مع الإدارة',
                            data: [
                                @foreach($monthlyTrend as $month => $data)
                                    {{ $data['management_relationship'] }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            ],
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'فرص النمو والتطور',
                            data: [
                                @foreach($monthlyTrend as $month => $data)
                                    {{ $data['growth_opportunities'] }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            ],
                            borderColor: 'rgb(153, 102, 255)',
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'التوازن بين العمل والحياة',
                            data: [
                                @foreach($monthlyTrend as $month => $data)
                                    {{ $data['work_life_balance'] }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            ],
                            borderColor: 'rgb(255, 159, 64)',
                            backgroundColor: 'rgba(255, 159, 64, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'الرضا الكلي',
                            data: [
                                @foreach($monthlyTrend as $month => $data)
                                    {{ $data['overall_satisfaction'] }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            ],
                            borderColor: 'rgb(40, 167, 69)',
                            backgroundColor: 'rgba(40, 167, 69, 0.2)',
                            borderWidth: 3,
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            title: {
                                display: true,
                                text: 'مستوى الرضا (من 1 إلى 10)'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
