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
                $duration = (int) $development->duration_days;

                $development->delivery_date = Carbon::parse($development->start_date)
                    ->addDays($duration);
            }
        });
    }

    // ✅ الأيام المتبقية حتى التسليم
    public function getRemainingDaysAttribute()
    {
        if (!$this->delivery_date) {
            return null;
        }

        return now()->diffInDays(Carbon::parse($this->delivery_date), false);
    }

    public function getPaidAmountAttribute() {
        return $this->invoices->sum('amount');
    }

    public function getCurrencyAttribute() {
        return $this->currency ?? $this->project->currency;
    }

    // ✅ الحالة النصية حسب الأيام المتبقية
    public function getStatusTextAttribute()
    {
        if ($this->delivered_at) {
            return 'delivered';
        }

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
            'expired' => 'table-danger',
            'due_today' => 'table-warning',
            'active' => 'table-success',
            'delivered' => 'table-primary', // الأزرق لحالة تم التسليم
            default => '',
        };
    }
}
