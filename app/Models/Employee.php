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
            $monthStart = now()->startOfMonth()->toDateString();
            $hoursFromTask = request()->input('hours_worked', null);

            $timesheet = Timesheet::where('employee_id', $employee->id)
                ->whereDate('work_date', $monthStart)
                ->first();

            if (!$timesheet) {
                // لا تقم بإنشاء جديد
                return;
            }

            if (!is_null($hoursFromTask)) {
                $monthSalary = 0;
                if ($employee->payment_type === 'monthly') {
                    // الراتب ثابت شهريًا
                    $monthSalary = $employee->rate;
                } elseif ($employee->payment_type === 'hourly') {
                    $monthSalary = $hoursFromTask * $employee->rate;
                } elseif ($employee->payment_type === 'per_project') {
                    $monthSalary = ($hoursFromTask / 8) * $employee->rate;
                }

                $timesheet->update([
                    'hours_worked' => $hoursFromTask,
                    'month_salary' => $monthSalary,
                    // is_paid لا يتغير
                ]);
            }
        });
    }
}
