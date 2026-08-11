<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        'approval_status',
        'signup_source',
        'contact_email',
        'plan',
        'timezone',
        'locale',
        'max_users',
        'trial_ends_at',
        'approved_at',
        'approved_by',
        'admin_user_id',
        'rejection_reason',
        'database_name',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'provision_status',
        'schema_version',
        'provisioned_at',
        'last_health_check_at',
        'last_health_status',
        'provision_error',
        'settings',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'approved_at' => 'datetime',
        'database_password' => 'encrypted',
        'provisioned_at' => 'datetime',
        'last_health_check_at' => 'datetime',
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function getConnectionName()
    {
        return config('tenancy.master_connection', 'mysql');
    }
}
