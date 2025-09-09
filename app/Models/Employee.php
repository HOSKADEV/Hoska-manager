<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Employee extends Model
{
    //
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'employee_project');
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }
    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    /**
     * Check if employee has submitted satisfaction rating for the current month
     *
     * @return bool
     */
    public function hasSatisfactionThisMonth()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return $this->satisfactions()
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->exists();
    }

    /**
     * Get the satisfaction rating for the current month if exists
     *
     * @return EmployeeSatisfaction|null
     */
    public function getSatisfactionThisMonth()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return $this->satisfactions()
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();
    }

    /**
     * Relationship with employee satisfaction ratings
     */
    public function satisfactions()
    {
        return $this->hasMany(EmployeeSatisfaction::class);
    }


    protected static function booted()
    {
        static::created(function ($employee) {
            $monthStart = now()->startOfMonth()->toDateString();

            // لا تنشئ إلا إذا لم يكن موجود
            $timesheet = Timesheet::where('employee_id', $employee->id)
                ->whereDate('work_date', $monthStart)
                ->first();

            if ($timesheet) {
                // موجود مسبقاً → لا تفعل شيء أو عدّله حسب الحاجة
                return;
            }

            $monthSalary = 0;
            if ($employee->payment_type === 'monthly') {
                // راتب شهري ثابت
                $monthSalary = $employee->rate;
            }

            // غير موجود → أنشئ جديد
            Timesheet::create([
                'employee_id' => $employee->id,
                'work_date' => $monthStart,
                'hours_worked' => 0,
                'project_id' => null,
                'month_salary' => $monthSalary,
                'is_paid' => false,
            ]);
        });


        static::updated(function ($employee) {
            // قيمة الساعات الجديدة (إن وجدت في الطلب)
            $hoursFromTask = request()->input('hours_worked', null);

            // نبحث عن كل الأشهر الغير مدفوعة للموظف
            $timesheets = Timesheet::where('employee_id', $employee->id)
                ->where('is_paid', false)
                ->get();

            foreach ($timesheets as $timesheet) {
                // نستخدم الساعات المرسلة أو الساعات الحالية للسجل
                $hours = $hoursFromTask ?? $timesheet->hours_worked;

                // لا تحدث إلا إذا تغيّر السعر أو أُرسلت الساعات
                if (!is_null($hoursFromTask) || $employee->isDirty('rate')) {
                    $monthSalary = 0;

                    if ($employee->payment_type === 'monthly') {
                        $monthSalary = $employee->rate;
                    } elseif ($employee->payment_type === 'hourly') {
                        $monthSalary = $hours * $employee->rate;
                    } elseif ($employee->payment_type === 'per_project') {
                        $monthSalary = ($hours / 8) * $employee->rate;
                    }

                    $timesheet->update([
                        'hours_worked' => $hours,
                        'month_salary' => $monthSalary,
                    ]);
                }
            }
        });
    }
}
