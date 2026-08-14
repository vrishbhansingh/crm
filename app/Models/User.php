<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    public function getConnectionName()
    {
        return config('tenancy.master_connection', 'mysql');
    }

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

    /**
     * Manager-tier roles see all of their tenant's data (leads, dashboard
     * widgets, ...); everyone else sees only records assigned to them.
     * Drives the unified interface's per-page data scoping.
     */
    public function hasElevatedAccess(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Admin', 'Manager', 'Sales Manager']);
    }
}
