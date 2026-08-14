<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAuditLog extends Model
{
    protected $fillable = ['actor_id', 'tenant_id', 'target_user_id', 'event', 'metadata', 'ip_address'];

    protected $casts = ['metadata' => 'array'];

    public function getConnectionName()
    {
        return config('tenancy.master_connection', 'mysql');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
