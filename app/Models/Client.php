<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }


    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function marketer()
    {
        return $this->belongsTo(User::class, 'marketer_id');
    }
}
