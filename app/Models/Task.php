<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

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
            'monthly' => $rate,
            default => 0,
        };
    }

    protected static function booted() {
        static::saved(function ($task) {
            if ($task->employee_id && $task->start_time) {
                Timesheet::updateMonthlyTimesheet($task->employee_id, $task->start_time->toDateString());
            }
        });

        static::deleted(function ($task) {
            if ($task->employee_id && $task->start_time) {
                Timesheet::updateMonthlyTimesheet($task->employee_id, $task->start_time->toDateString());
            }
        });
    }
}
