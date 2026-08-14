<?php

namespace App\Services;

use App\Models\PlatformAuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PlatformAuditLogger
{
    public function record(string $event, ?Tenant $tenant = null, ?User $target = null, array $metadata = []): PlatformAuditLog
    {
        return PlatformAuditLog::create([
            'actor_id' => Auth::id(),
            'tenant_id' => $tenant?->id,
            'target_user_id' => $target?->id,
            'event' => $event,
            'metadata' => $this->redact($metadata),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }

    private function redact(array $metadata): array
    {
        foreach (['password', 'database_password', 'session_token'] as $key) {
            if (array_key_exists($key, $metadata)) {
                $metadata[$key] = '[REDACTED]';
            }
        }

        return $metadata;
    }
}
