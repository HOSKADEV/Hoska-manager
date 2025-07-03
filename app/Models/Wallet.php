<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $guarded = [];

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
