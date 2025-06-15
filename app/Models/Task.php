<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $guarded = [];

    protected $casts = ['due_date' => 'date', 'start_time' => 'datetime', 'end_time' => 'datetime'];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function getDurationInHoursAttribute()
    {
        if (!$this->start_time || !$this->end_time) return 0;

        return round($this->start_time->diffInMinutes($this->end_time) / 60, 2);
    }

    public function getCostAttribute()
    {
        if (!$this->employee) return 0;

        $hours = $this->duration_in_hours;
        $rate = $this->employee->rate;

        return match ($this->employee->payment_type) {
            'hourly' => $hours * $rate,
            'per_project' => ($hours / 8) * $rate,
            'monthly' => ($hours / 160) * $rate,
            default => 0,
        };
    }

    public static function updateEmployeeTimesheet($employee_id)
    {
        $employee = Employee::with(['tasks', 'projects'])->find($employee_id);
        if (!$employee) return;

        // حساب مجموع الساعات من المهام المنتهية فقط
        $totalHours = $employee->tasks->whereNotNull('start_time')->whereNotNull('end_time')->sum(function ($task) {
            return $task->duration_in_hours;
        });

        // استخدم أول تاريخ متوفر من المهام كـ work_date (أو اليوم الحالي كخيار افتراضي)
        $workDate = optional($employee->tasks->first())->start_time?->toDateString() ?? now()->toDateString();

        // تحديث أو إنشاء تايمشيت
        Timesheet::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'work_date' => $workDate,
            ],
            [
                'hours_worked' => $totalHours,
                'project_id' => optional($employee->projects->first())->id,
            ]
        );
    }

    protected static function booted()
    {
        static::saved(function ($task) {
            self::updateEmployeeTimesheet($task->employee_id);
        });

        static::deleted(function ($task) {
            self::updateEmployeeTimesheet($task->employee_id);
        });
    }
}
