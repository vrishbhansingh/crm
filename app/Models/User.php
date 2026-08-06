<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected $fillable = [
        'tenant_id',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'status',
        'last_login',
        'session_token',
        'legacy_type',
        'legacy_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'session_token',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }
}
