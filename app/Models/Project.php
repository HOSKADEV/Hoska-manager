<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_project');
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function developments()
    {
        return $this->hasMany(Development::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }
}
