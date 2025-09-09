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
            $monthSalary = $employee->payment_type === 'monthly' ? $employee->rate : 0;

            Timesheet::updateOrCreate(
                [
                    'employee_id' => $employee_id,
                    'work_date' => $monthStart,
                ],
                [
                    'hours_worked' => 0,
                    'project_id' => null,
                    'month_salary' => $monthSalary, // أجر شهري ثابت لو الدفع شهري
                ]
            );

            Log::info("No valid tasks for employee {$employee_id} in month {$monthStart}, timesheet set to 0 hours");
            return;
        }

        $totalHours = $validTasks->sum(fn($task) => $task->duration_in_hours);

        if ($employee->payment_type === 'monthly') {
            // الراتب ثابت للشهر بغض النظر عن الساعات
            $monthlySalary = $employee->rate;
        } else {
            // حساب الراتب حسب الساعات والعمل
            $monthlySalary = $totalHours * $employee->rate;
        }

        $data = [
            'employee_id' => $employee_id,
            'work_date' => $monthStart,
            'hours_worked' => $totalHours,
            'project_id' => null,
            'month_salary' => $monthlySalary,
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

    public function getRateAttribute() {
        if ($this->employee->payment_type == 'monthly') {
            return $this->month_salary;
        } else if ($this->employee->payment_type == 'hourly') {
            return $this->hours_worked > 0
                ? $this->month_salary / $this->hours_worked
                : 0;
        } else if ($this->employee->payment_type == 'per_project') {
            return $this->month_salary;
        }
    }
}
