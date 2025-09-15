<?php

namespace App\Models;

use App\Helpers\CurrencyHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        // 'is_manual' => 'boolean',
        // 'manual_hours_spent' => 'float',
        // 'manual_cost' => 'float',
        'delivery_quality' => 'integer',
        'response_speed' => 'integer',
        'support_level' => 'integer',
        'expectations_met' => 'integer',
        'continuation_intent' => 'integer',
        'final_satisfaction_score' => 'integer',
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
    public function marketer()
    {
        return $this->belongsTo(User::class, 'marketer_id');
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
    public function ourTasks()
    {
        return $this->hasMany(OurTask::class);
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
    
    public function team_manager()
    {
        return $this->belongsTo(Employee::class, 'team_manager_id');
    }

    public function contracts()
    {
        return $this->morphMany(Contract::class, 'contractable');
    }

    public function getTotalAmountProjectWithDevelopmentsAttribute() {
        $amount = $this->total_amount;
        if ($this->developments) {
            foreach ($this->developments as $development) {
                $amount += CurrencyHelper::convert($development->amount, $development->currency, $this->currency);
            }
        }
        return $amount;
    }

    public function getTotalPaidAmountProjectWithDevelopmentsAttribute()
    {
        $total = 0;
        if ($this->payments) {
            foreach ($this->payments as $payment) {
                $total += CurrencyHelper::convert(
                    $payment->amount,
                    $payment->invoice->currency,   // source currency
                    $this->currency       // project currency
                );
            }
        }

        if ($this->manual_cost) {
            $total += $this->manual_cost;
        }

        return $total;
    }

    public function getTotalExpensesAttribute()
    {
        $expenses = 0;

        // Only completed tasks
        $tasks = $this->tasks()->where('status', 'completed')->get();

        if ($tasks) {
            foreach ($tasks as $task) {
                $hours = $task->duration_in_hours;
                $employee = $task->employee;

                if ($employee) {
                    $rate = $employee->rate ?? 0;
                    $cost = $hours * $rate;

                    // Convert cost into project currency
                    $rateToProjectCurrency = CurrencyHelper::convert(1, $employee->currency, $this->currency);
                    $expenses += $cost * $rateToProjectCurrency;
                }
            }
        }

        // Add costs from our tasks
        $ourTasks = $this->ourTasks;
        if ($ourTasks) {
            foreach ($ourTasks as $ourTask) {
                $expenses += $ourTask->cost;
            }
        }

        return $expenses;
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

    /**
     * حساب رضاء العملاء النهائي
     * رضاء العملاء النهائي = (جودة التسليم + سرعة الاستجابة + مستوى الدعم + تحقيق التوقعات + نية الاستمرار)
     */
    public function calculateFinalSatisfactionScore()
    {
        // الحصول على قيم المكونات أو استخدام القيم الافتراضية إذا لم تكن موجودة
        $deliveryQuality = $this->delivery_quality ?? 0;
        $responseSpeed = $this->response_speed ?? 0;
        $supportLevel = $this->support_level ?? 0;
        $expectationsMet = $this->expectations_met ?? 0;
        $continuationIntent = $this->continuation_intent ?? 0;

        // حساب متوسط الدرجات
        $finalScore = ($deliveryQuality + $responseSpeed + $supportLevel + $expectationsMet + $continuationIntent) / 5;

        // تحديث قيمة رضاء العملاء النهائي في قاعدة البيانات
        $this->final_satisfaction_score = round($finalScore);
        $this->save();

        return $this->final_satisfaction_score;
    }
}
