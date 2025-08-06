<x-dashboard title="Work KPIs & Statistics">
    @push('css')
        <style>
            .card-kpi {
                border-left-width: 0.25rem !important;
            }

            .border-income {
                border-left-color: #28a745 !important;
                /* أخضر */
            }

            .border-expenses {
                border-left-color: #dc3545 !important;
                /* أحمر */
            }

            .border-salaries {
                border-left-color: #007bff !important;
                /* أزرق */
            }

            .border-profits {
                border-left-color: #ffc107 !important;
                /* أصفر */
            }

            .border-csat {
                border-left-color: #6f42c1 !important;
                /* بنفسجي */
            }
        </style>
    @endpush

    <div class="row mb-4">

        <!-- Annual Income -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-kpi border-income shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Annual Income
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        ${{ number_format($annualIncome, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Annual Expenses -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-kpi border-expenses shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Annual Expenses
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        ${{ number_format($annualExpenses, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Annual Salaries -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-kpi border-salaries shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Annual Salaries
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        ${{ number_format($annualSalaries, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Annual Profits -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-kpi border-profits shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Annual Profits
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        ${{ number_format($annualProfits, 2) }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Customer Satisfaction (CSAT) Example -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card card-kpi border-csat shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">
                        Customer Satisfaction (CSAT)
                    </div>
                    <canvas id="csatChart" style="max-width: 150px; margin: auto; height: 150px;"></canvas>
                    <div class="h3 font-weight-bold text-gray-800 mt-2">
                        {{ number_format($csatScore, 1) }}%
                    </div>
                    <small class="text-muted">Based on recent surveys</small>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- الرسم البياني الآخر -->
            <canvas id="incomeChart" style="max-width: 100%; height: 200px;"></canvas>
        </div>
    </div>

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // رسم بياني الدخل الشهري
            const incomeCtx = document.getElementById('incomeChart').getContext('2d');
            new Chart(incomeCtx, {
                type: 'line',
                data: {
                    labels: @json($monthsLabels),
                    datasets: [{
                        label: 'Monthly Income',
                        data: @json($monthlyIncomeData),
                        borderColor: 'rgba(40, 167, 69, 1)', // أخضر
                        fill: false,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // رسم بياني CSAT (دائري)
            const csatCtx = document.getElementById('csatChart').getContext('2d');
            new Chart(csatCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Satisfied', 'Unsatisfied'],
                    datasets: [{
                        data: [{{ $csatScore }}, {{ 100 - $csatScore }}],
                        backgroundColor: [
                            'rgba(111, 66, 193, 0.8)',  // بنفسجي (راضي)
                            'rgba(200, 200, 200, 0.3)'  // رمادي فاتح (غير راضي)
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '75%',
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        </script>
    @endpush
</x-dashboard>
