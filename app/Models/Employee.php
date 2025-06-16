<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        Timesheet::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'work_date' => now()->toDateString(),
            ],
            [
                'hours_worked' => 0,
                'project_id' => null,
            ]
        );
    });

    static::updated(function ($employee) {
        Timesheet::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'work_date' => now()->toDateString(),
            ],
            [
                'hours_worked' => 0,
                'project_id' => null,
            ]
        );
    });
}


}
