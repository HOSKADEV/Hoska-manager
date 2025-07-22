<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
    public function client()
    {
        return $this->hasOne(Client::class);
    }
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function addedClients()
    {
        return $this->hasMany(Client::class, 'added_by');
    }

    public function marketedProjects()
    {
        return $this->hasMany(Project::class, 'marketer_id');
    }

    public function isMarketer()
    {
        return $this->is_marketer;
    }
}
