<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSatisfaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'salary_compensation', // الراتب والتعويضات 💰
        'work_environment', // بيئة العمل 🏢 (المكتب، الأدوات، الراحة).
        'colleagues_relationship', // العلاقات مع الزملاء 🤝
        'management_relationship', // العلاقة مع الإدارة 👔
        'growth_opportunities', // فرص النمو والتطور 📈
        'work_life_balance', // التوازن بين العمل والحياة 🕒
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
