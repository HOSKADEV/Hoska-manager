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

        Timesheet::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'work_date' => $monthStart,
            ],
            [
                'hours_worked' => 0,
                'project_id' => null,
            ]
        );
    });

    static::updated(function ($employee) {
        $monthStart = now()->startOfMonth()->toDateString();

        // مثال: لنفترض أنك تستقبل قيمة الساعات من المتغير $hoursFromTask
        $hoursFromTask = request()->input('hours_worked', null);

        $updateData = [
            'project_id' => null,
        ];

        if (!is_null($hoursFromTask)) {
            $updateData['hours_worked'] = $hoursFromTask;
        }

        Timesheet::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'work_date' => $monthStart,
            ],
            $updateData
        );
    });
}

}
