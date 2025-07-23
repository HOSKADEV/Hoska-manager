<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Development extends Model
{
    //
    protected $guarded = [];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

public function invoices()
{
    return $this->hasMany(Invoice::class, 'development_id');
}



    // ✅ احتساب delivery_date تلقائيًا عند تعيين start_date أو duration_days
    protected static function booted()
    {
        static::saving(function ($development) {
            if ($development->start_date && $development->duration_days) {
                $development->delivery_date = Carbon::parse($development->start_date)
                    ->addDays($development->duration_days);
            }
        });
    }

    // ✅ الأيام المتبقية حتى التسليم
    public function getRemainingDaysAttribute()
    {
        if (!$this->delivery_date) {
            return null;
        }

        return Carbon::now()->diffInDays(Carbon::parse($this->delivery_date), false);
    }

    // ✅ الحالة النصية حسب الأيام المتبقية
    public function getStatusTextAttribute()
    {
        $remaining = $this->remaining_days;

        return match (true) {
            is_null($remaining) => 'unknown',
            $remaining < 0 => 'expired',
            $remaining === 0 => 'due_today',
            default => 'active',
        };
    }

    // ✅ لتلوين الصف بناءً على الحالة
    public function getRowClassAttribute()
    {
        return match ($this->status_text) {
            'expired' => 'table-danger',
            'due_today' => 'table-warning',
            'active' => 'table-success',
            default => '',
        };
    }
}
