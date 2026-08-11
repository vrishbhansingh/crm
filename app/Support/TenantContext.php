<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Resolves the tenant a request is currently scoped to.
 *
 * Until Phase 1 Step 6 rewires routes/controllers onto the unified `web`
 * guard, no request authenticates through it yet, so id() returns null and
 * every BelongsToTenant scope is a no-op — existing admin/user-guard
 * behavior is unaffected until enforcement is switched on.
 *
 * A null tenant id also naturally represents platform-level Super Admins
 * (users.tenant_id = null), who intentionally see data across all tenants.
 */
class TenantContext
{
    private static ?int $override = null;

    public static function id(): ?int
    {
        if (self::$override !== null) {
            return self::$override;
        }

        $user = Auth::guard('web')->user() ?? request()->user();

        if (! $user) {
            return null;
        }

        if ($user->tenant_id !== null) {
            return (int) $user->tenant_id;
        }

        $selectedTenant = session('tenant_context_id');

        return $selectedTenant !== null ? (int) $selectedTenant : null;
    }

    /**
     * Force a tenant id for the current process (console commands, tests).
     */
    public static function set(?int $tenantId): void
    {
        self::$override = $tenantId;
    }

    public static function clear(): void
    {
        self::$override = null;
    }

    public static function run(int $tenantId, callable $callback): mixed
    {
        $previous = self::$override;
        self::$override = $tenantId;

        try {
            return $callback();
        } finally {
            self::$override = $previous;
        }
    }
}
