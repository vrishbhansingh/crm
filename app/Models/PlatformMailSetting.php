<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformMailSetting extends Model
{
    protected $fillable = [
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username',
        'smtp_password', 'smtp_from_address', 'smtp_from_name',
    ];

    protected $casts = [
        'smtp_password' => 'encrypted',
    ];

    public function getConnectionName()
    {
        return config('tenancy.master_connection', 'mysql');
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function isConfigured(): bool
    {
        return filled($this->smtp_host) && filled($this->smtp_port);
    }
}
