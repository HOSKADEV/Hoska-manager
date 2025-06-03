<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    //
    protected $guarded = [];

    protected $casts = ['work_date' => 'date', 'hours_worked' => 'decimal:2'];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
