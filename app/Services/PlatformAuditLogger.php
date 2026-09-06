<?php

namespace App\Services;

use App\Models\PlatformAuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PlatformAuditLogger
{
    /**
     * Every call site fires this and ignores the result — auditing is a
     * secondary concern that must never be able to take down the actual
     * operation it's recording. actor_id/target_user_id are real FKs, and
     * Auth::id() can legitimately point at a user that no longer exists by
     * the time this runs (e.g. a stale session surviving the tenant.deleted
     * flow this same logger records); swallow that rather than 500 the
     * caller's real work, which — unlike this log entry — already
     * committed.
     */
    public function record(string $event, ?Tenant $tenant = null, ?User $target = null, array $metadata = []): ?PlatformAuditLog
    {
        try {
            return PlatformAuditLog::create([
                'actor_id' => Auth::id(),
                'tenant_id' => $tenant?->id,
                'target_user_id' => $target?->id,
                'event' => $event,
                'metadata' => $this->redact($metadata),
                'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
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
