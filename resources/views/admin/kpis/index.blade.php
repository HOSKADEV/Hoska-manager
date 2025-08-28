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
            .chart-container {
                position: relative;
                height: 250px;
                width: 100%;
            }
            /* .year-selector {
                width: auto;
                display: inline-block;
            } */
            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .page-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
            }
        </style>
    @endpush

    <div class="page-header">
        <h1 class="h3 mb-0 text-gray-800">KPIs & Statistics</h1>
        <div class="year-selector-container">
            <select class="form-control year-selector" id="yearSelector" aria-label="Select Year">
                @foreach($availableYears as $availableYear)
                    <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mb-4">
        <!-- Annual Income -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-kpi border-income shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Annual Income
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($annualIncome, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Annual Expenses -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-kpi border-expenses shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Annual Expenses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($annualExpenses, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Annual Salaries -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-kpi border-salaries shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Annual Salaries
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($annualSalaries, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Annual Profits -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-kpi border-profits shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Annual Profits
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($annualProfits, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Customer Satisfaction (CSAT) Example -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-kpi border-csat shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2 text-center">
                            <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">
                                Customer Satisfaction (CSAT)
                            </div>
                            <canvas id="csatChart" style="max-width: 150px; margin: auto; height: 150px;"></canvas>
                            <div class="h3 font-weight-bold text-gray-800 mt-2">
                                {{ number_format($csatScore, 1) }}%
                            </div>
                            <small class="text-muted">Based on recent surveys</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-smile fa-3x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Income Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Income</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- المخططات الجديدة مع خيار اختيار العام -->
    <!-- Profits Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Annual Profits</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="profitsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Projects</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="projectsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Annual Expenses</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="expensesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // رسم بياني الدخل الشهري
            const incomeCtx = document.getElementById('incomeChart').getContext('2d');
            new Chart(incomeCtx, {
                type: 'bar',
                data: {
                    labels: @json($monthsLabels),
                    datasets: [{
                        label: 'Monthly Income',
                        data: @json($monthlyIncomeData),
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
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

            // بيانات حقيقية من وحدة التحكم
            const profitsData = @json($monthlyProfitsData);
            const projectsData = @json($monthlyProjectsData);
            const expensesData = @json($monthlyExpensesData);

            // رسم بياني الأرباح
            const profitsCtx = document.getElementById('profitsChart').getContext('2d');
            const profitsChart = new Chart(profitsCtx, {
                type: 'bar',
                data: {
                    labels: @json($monthsLabels),
                    datasets: [{
                        label: 'Monthly Profits',
                        data: profitsData,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // رسم بياني المشاريع
            const projectsCtx = document.getElementById('projectsChart').getContext('2d');
            const projectsChart = new Chart(projectsCtx, {
                type: 'line',
                data: {
                    labels: @json($monthsLabels),
                    datasets: [{
                        label: 'Projects Count',
                        data: projectsData,
                        borderColor: 'rgba(0, 123, 255, 1)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(0, 123, 255, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // رسم بياني المصاريف
            const expensesCtx = document.getElementById('expensesChart').getContext('2d');
            const expensesChart = new Chart(expensesCtx, {
                type: 'bar',
                data: {
                    labels: @json($monthsLabels),
                    datasets: [{
                        label: 'Monthly Expenses',
                        data: expensesData,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // معالجة تغيير العام في المخططات
            document.getElementById('yearSelector').addEventListener('change', function() {
                // تحديث الصفحة مع العام المحدد
                const url = new URL(window.location.href);
                url.searchParams.set('year', this.value);
                window.location.href = url.toString();
            });
        </script>
    @endpush
</x-dashboard>

{{-- <x-dashboard title="Work KPIs & Statistics">
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
</x-dashboard> --}}
