<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

    // public static function updateEmployeeTimesheet($employee_id, $work_date)
    // {
    //     // تأكد أن التاريخ بدون وقت بصيغة Y-m-d
    //     $work_date = Carbon::parse($work_date)->toDateString();

    //     // جلب الموظف مع المهام لنفس التاريخ
    //     $employee = Employee::with(['tasks' => function ($query) use ($work_date) {
    //         $query->whereDate('start_time', $work_date);
    //     }])->find($employee_id);

    //     if (!$employee) return;

    //     $tasksForDate = $employee->tasks;

    //     // فلترة المهام الصالحة
    //     $validTasks = $tasksForDate->filter(function ($task) {
    //         return $task->start_time && $task->end_time;
    //     });

    //     // البحث عن التايمشيت الحالي
    //     $timesheet = Timesheet::where('employee_id', $employee_id)
    //         ->whereDate('work_date', $work_date)
    //         ->first();

    //     if ($validTasks->isEmpty()) {
    //         if ($timesheet) {
    //             $timesheet->delete();
    //         }
    //         return;
    //     }

    //     // حساب عدد الساعات
    //     $totalHours = $validTasks->sum(fn($task) => $task->duration_in_hours);

    //     // تحديد المشروع إن وجد واحد فقط
    //     $projectIds = $validTasks->pluck('project_id')->unique();
    //     $projectId = $projectIds->count() === 1 ? $projectIds->first() : null;

    //     $data = [
    //         'employee_id' => $employee_id,
    //         'work_date' => $work_date,
    //         'hours_worked' => $totalHours,
    //         'project_id' => $projectId,
    //     ];

    //     $timesheet
    //         ? $timesheet->update($data)
    //         : Timesheet::create($data);
    // }

    // public static function updateMonthlyTimesheet($employee_id, $taskDate)
    // {
    //     $monthStart = Carbon::parse($taskDate)->startOfMonth()->toDateString();
    //     $monthEnd = Carbon::parse($taskDate)->endOfMonth()->toDateString();

    //     // جلب المهام خلال الشهر
    //     $employee = Employee::with(['tasks' => function ($query) use ($monthStart, $monthEnd) {
    //         $query->whereDate('start_time', '>=', $monthStart)
    //             ->whereDate('start_time', '<=', $monthEnd);
    //     }])->find($employee_id);

    //     if (!$employee) return;

    //     $tasksForMonth = $employee->tasks;

    //     // فلترة المهام التي لها وقت بداية ونهاية
    //     $validTasks = $tasksForMonth->filter(fn($task) => $task->start_time && $task->end_time);

    //     if ($validTasks->isEmpty()) {
    //         // حذف التايمشيت الشهري إن وجد
    //         Timesheet::where('employee_id', $employee_id)
    //             ->whereDate('work_date', $monthStart)
    //             ->delete();
    //         return;
    //     }

    //     $totalHours = $validTasks->sum(fn($task) => $task->duration_in_hours);

    //     $data = [
    //         'employee_id' => $employee_id,
    //         'work_date' => $monthStart, // اليوم الأول في الشهر كمرجع
    //         'hours_worked' => $totalHours,
    //         'project_id' => null // أو احسب المشروع إن أردت
    //     ];

    //     $timesheet = Timesheet::where('employee_id', $employee_id)
    //         ->whereDate('work_date', $monthStart)
    //         ->first();

    //     $timesheet
    //         ? $timesheet->update($data)
    //         : Timesheet::create($data);
    // }

public static function updateMonthlyTimesheet($employee_id, $taskDate)
{
    $monthStart = Carbon::parse($taskDate)->startOfMonth()->toDateString();
    $monthEnd = Carbon::parse($taskDate)->endOfMonth()->toDateString();

    Log::info("Updating monthly timesheet for employee {$employee_id} for month starting {$monthStart}");

    $employee = Employee::with(['tasks' => function ($query) use ($monthStart, $monthEnd) {
        $query->whereDate('start_time', '>=', $monthStart)
              ->whereDate('start_time', '<=', $monthEnd);
    }])->find($employee_id);

    if (!$employee) {
        Log::warning("Employee {$employee_id} not found");
        return;
    }

    $validTasks = $employee->tasks->filter(fn($task) => $task->start_time && $task->end_time);

    if ($validTasks->isEmpty()) {
        Timesheet::updateOrCreate(
            [
                'employee_id' => $employee_id,
                'work_date' => $monthStart,
            ],
            [
                'hours_worked' => 0,
                'project_id' => null,
            ]
        );

        Log::info("No valid tasks for employee {$employee_id} in month {$monthStart}, timesheet set to 0 hours");
        return;
    }

    $totalHours = $validTasks->sum(fn($task) => $task->duration_in_hours);

    $data = [
        'employee_id' => $employee_id,
        'work_date' => $monthStart,
        'hours_worked' => $totalHours,
        'project_id' => null,
    ];

    $timesheet = Timesheet::where('employee_id', $employee_id)
        ->whereDate('work_date', $monthStart)
        ->first();

    if ($timesheet) {
        $timesheet->update($data);
        Log::info("Updated timesheet ID {$timesheet->id} for employee {$employee_id}");
    } else {
        Timesheet::create($data);
        Log::info("Created new timesheet for employee {$employee_id} for month {$monthStart}");
    }
}

}
