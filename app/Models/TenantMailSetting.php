<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One saved SMTP configuration belonging to a tenant. A tenant can hold
 * several (e.g. "Support inbox", "Billing"), but only one is ever active at
 * a time — MailConfigurator sends through whichever row has is_active=true,
 * falling back to the platform default when none does.
 */
class TenantMailSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'is_active',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'smtp_from_address',
        'smtp_from_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'smtp_password' => 'encrypted',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isUsable(): bool
    {
        return filled($this->smtp_host) && filled($this->smtp_port);
    }
}
