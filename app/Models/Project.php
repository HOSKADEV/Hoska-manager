<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'is_manual' => 'boolean',
        'manual_hours_spent' => 'float',
        'manual_cost' => 'float',
    ];

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
    public function links()
    {
        return $this->hasMany(ProjectLink::class);
    }


    // حساب الأيام المتبقية تلقائيًا
    public function getRemainingDaysAttribute()
    {
        if (!$this->delivery_date) {
            return null;
        }
        return Carbon::now()->diffInDays(Carbon::parse($this->delivery_date), false);
    }

    public function getStatusTextAttribute()
    {
        $remaining = $this->remaining_days;

        if (is_null($remaining)) {
            return 'unknown';
        }

        if ($remaining < 0) {
            return 'expired';
        } elseif ($remaining >= 0 && $remaining <= 1) {
            return 'due_today';
        } else {
            return 'active';
        }
    }

    public function getRowClassAttribute()
    {
        return match ($this->status_text) {
            'expired' => 'table-danger',   // أحمر لمنتهٍ (expired)
            'due_today' => 'table-warning', // برتقالي لليوم الأخير
            'active' => 'table-success',   // أخضر للباقي (active)
            default => '',
        };
    }
}
