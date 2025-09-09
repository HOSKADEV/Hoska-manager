<x-dashboard title="تفاصيل تقييم الرضا الوظيفي">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">تفاصيل تقييم الرضا الوظيفي</h1>
        <a href="{{ route('admin.employee-satisfaction.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> العودة لقائمة التقييمات
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">بيانات التقييم</h6>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>الموظف:</strong> {{ $satisfaction->employee->name }}</p>
                    <p><strong>الشهر:</strong> {{ $satisfaction->month }}</p>
                    <p><strong>السنة:</strong> {{ $satisfaction->year }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>تاريخ الإنشاء:</strong> {{ $satisfaction->created_at->format('Y-m-d H:i:s') }}</p>
                    <p><strong>تاريخ التحديث:</strong> {{ $satisfaction->updated_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">تفاصيل التقييم:</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>الراتب والتعويضات 💰</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ ($satisfaction->salary_compensation / 10) * 100 }}%;" aria-valuenow="{{ $satisfaction->salary_compensation }}" aria-valuemin="0" aria-valuemax="10">
                                        {{ $satisfaction->salary_compensation }}/10
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>بيئة العمل 🏢</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ ($satisfaction->work_environment / 10) * 100 }}%;" aria-valuenow="{{ $satisfaction->work_environment }}" aria-valuemin="0" aria-valuemax="10">
                                        {{ $satisfaction->work_environment }}/10
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>العلاقات مع الزملاء 🤝</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ ($satisfaction->colleagues_relationship / 10) * 100 }}%;" aria-valuenow="{{ $satisfaction->colleagues_relationship }}" aria-valuemin="0" aria-valuemax="10">
                                        {{ $satisfaction->colleagues_relationship }}/10
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>العلاقة مع الإدارة 👔</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ ($satisfaction->management_relationship / 10) * 100 }}%;" aria-valuenow="{{ $satisfaction->management_relationship }}" aria-valuemin="0" aria-valuemax="10">
                                        {{ $satisfaction->management_relationship }}/10
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>فرص النمو والتطور 📈</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ ($satisfaction->growth_opportunities / 10) * 100 }}%;" aria-valuenow="{{ $satisfaction->growth_opportunities }}" aria-valuemin="0" aria-valuemax="10">
                                        {{ $satisfaction->growth_opportunities }}/10
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>التوازن بين العمل والحياة 🕒</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ ($satisfaction->work_life_balance / 10) * 100 }}%;" aria-valuenow="{{ $satisfaction->work_life_balance }}" aria-valuemin="0" aria-valuemax="10">
                                        {{ $satisfaction->work_life_balance }}/10
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                    <h6 class="mb-3">الرضا الوظيفي الكلي:</h6>
                    <div class="circular-progress mb-3" style="position: relative; width: 180px; height: 180px;">
                        <svg viewBox="0 0 36 36" class="circular-progress-bar" style="width: 100%; height: 100%;">
                            <path class="circular-progress-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="3"/>
                            <path class="circular-progress-fill" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#28a745" stroke-width="3" stroke-dasharray="{{ (($satisfaction->salary_compensation + $satisfaction->work_environment + $satisfaction->colleagues_relationship + $satisfaction->management_relationship + $satisfaction->growth_opportunities + $satisfaction->work_life_balance) / 6 / 10) * 100 }}, 100"/>
                        </svg>
                        <div class="circular-progress-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.5rem; font-weight: bold;">
                            {{ round(($satisfaction->salary_compensation + $satisfaction->work_environment + $satisfaction->colleagues_relationship + $satisfaction->management_relationship + $satisfaction->growth_opportunities + $satisfaction->work_life_balance) / 6, 1) }}/10
                        </div>
                    </div>
                    <p class="text-center">الرضا الوظيفي الكلي = متوسط جميع التقييمات</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">الإجراءات</h6>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.employee-satisfaction.edit', $satisfaction->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> تعديل التقييم
                </a>
                <form action="{{ route('admin.employee-satisfaction.destroy', $satisfaction->id) }}" method="POST" style="display: inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا التقييم؟')">
                        <i class="fas fa-trash"></i> حذف التقييم
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-dashboard>