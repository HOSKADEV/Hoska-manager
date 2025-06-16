<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    //
    protected $guarded = [];

    protected $casts = ['work_date' => 'date:Y-m-d', 'hours_worked' => 'decimal:2'];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public static function updateEmployeeTimesheet($employee_id, $work_date)
    {
        // تأكد أن التاريخ بدون وقت بصيغة Y-m-d
        $work_date = Carbon::parse($work_date)->toDateString();

        // جلب الموظف مع المهام لنفس التاريخ
        $employee = Employee::with(['tasks' => function ($query) use ($work_date) {
            $query->whereDate('start_time', $work_date)
                ->whereNotNull('start_time')
                ->whereNotNull('end_time');
        }])->find($employee_id);

        if (!$employee) return;

        $tasksForDate = $employee->tasks;

        if ($tasksForDate->isEmpty()) {
            // حذف التايمشيت إذا ما في مهام
            Timesheet::where('employee_id', $employee_id)
                ->whereDate('work_date', $work_date)
                ->delete();
            return;
        }

        // حساب عدد الساعات
        $totalHours = $tasksForDate->sum(fn($task) => $task->duration_in_hours);

        // إذا في مشروع واحد فقط، خزنه
        $projectIds = $tasksForDate->pluck('project_id')->unique();
        $projectId = $projectIds->count() === 1 ? $projectIds->first() : null;

        // تحديث أو إنشاء التايمشيت
        Timesheet::updateOrCreate(
            [
                'employee_id' => $employee_id,
                'work_date' => $work_date,
            ],
            [
                'hours_worked' => $totalHours,
                'project_id' => $projectId,
            ]
        );
    }

}
