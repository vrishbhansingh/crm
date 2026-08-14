<?php

namespace App\Support;

use Spatie\Permission\PermissionRegistrar;

class PermissionTeam
{
    public const PLATFORM_TEAM_ID = 0;

    public static function idForTenant(?int $tenantId): int
    {
        return $tenantId ?? self::PLATFORM_TEAM_ID;
    }

    public static function set(?int $tenantId): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(self::idForTenant($tenantId));
    }

    public static function run(?int $tenantId, callable $callback): mixed
    {
        $registrar = app(PermissionRegistrar::class);
        $previous = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId(self::idForTenant($tenantId));

        try {
            return $callback();
        } finally {
            $registrar->setPermissionsTeamId($previous ?? self::PLATFORM_TEAM_ID);
        }
    }
}
