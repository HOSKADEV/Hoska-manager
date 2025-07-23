<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //
    protected $guarded = [];

    protected $casts = ['is_paid' => 'boolean', 'invoice_date' => 'date', 'due_date' => 'date'];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    public function development()
    {
        return $this->belongsTo(Development::class, 'development_id');
    }

}
