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

            // غير موجود → أنشئ جديد
            Timesheet::create([
                'employee_id' => $employee->id,
                'work_date' => $monthStart,
                'hours_worked' => 0,
                'project_id' => null,
                'month_salary' => 0,
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

            // عدل فقط إذا كان هناك ساعات جديدة
            if (!is_null($hoursFromTask)) {
                $timesheet->update([
                    'hours_worked' => $hoursFromTask,
                    'month_salary' => $hoursFromTask * $employee->rate,
                    // نحتفظ بـ is_paid كما هو
                ]);
            }
        });
    }
}
