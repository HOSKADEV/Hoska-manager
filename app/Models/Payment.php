<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $guarded = [];
    protected $casts = ['payment_date' => 'date'];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function project()
    {
        return $this->hasOneThrough(Project::class, Invoice::class, 'id', 'id', 'invoice_id', 'project_id');
    }
        public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
