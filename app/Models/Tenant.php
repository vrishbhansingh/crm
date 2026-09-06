<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
        'smtp_enabled',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'smtp_from_address',
        'smtp_from_name',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'approved_at' => 'datetime',
        'database_password' => 'encrypted',
        'provisioned_at' => 'datetime',
        'last_health_check_at' => 'datetime',
        'settings' => 'array',
        'smtp_enabled' => 'boolean',
        'smtp_password' => 'encrypted',
    ];

    public function hasUsableSmtp(): bool
    {
        return $this->activeMailSetting()?->isUsable() ?? false;
    }

    public function mailSettings()
    {
        return $this->hasMany(TenantMailSetting::class);
    }

    /**
     * The one SMTP config currently in use for this tenant, or null if it
     * never configured one (or removed its active config without picking a
     * replacement) — MailConfigurator falls back to the platform default
     * in that case.
     */
    public function activeMailSetting(): ?TenantMailSetting
    {
        return $this->relationLoaded('mailSettings')
            ? $this->mailSettings->firstWhere('is_active', true)
            : $this->mailSettings()->where('is_active', true)->first();
    }

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

    public function isExpired(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isPast();
    }

    public function accessBlockReason(): ?string
    {
        if ($this->approval_status === 'pending') {
            return 'Your company signup is awaiting Super Admin approval.';
        }

        if ($this->approval_status !== 'approved') {
            return 'Your company signup was not approved.';
        }

        if ($this->status !== 'Active') {
            return 'Your organization workspace is inactive.';
        }

        if (config('tenancy.mode') === 'database' && $this->provision_status !== 'ready') {
            return 'Your organization database is not ready. Contact the platform administrator.';
        }

        if ($this->isExpired()) {
            return 'Your organization subscription has expired.';
        }

        return null;
    }

    public function isAccessible(): bool
    {
        return $this->accessBlockReason() === null;
    }

    public function scopeAccessible(Builder $query): Builder
    {
        return $query->where('status', 'Active')
            ->where('approval_status', 'approved')
            ->where(function (Builder $builder) {
                $builder->whereNull('trial_ends_at')->orWhere('trial_ends_at', '>', now());
            });
    }

    public function getConnectionName()
    {
        return config('tenancy.master_connection', 'mysql');
    }
}
