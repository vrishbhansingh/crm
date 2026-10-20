<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Reminders, scheduled campaigns and queued sends all depend on the OS
 * running `php artisan schedule:run` every minute. The scheduler stamps a
 * heartbeat each run; if it goes stale, the automations are silently dead,
 * so pages that rely on them show a banner instead of letting users believe
 * everything is fine.
 */
class AutomationHealth
{
    public const KEY = 'crm.scheduler.heartbeat';

    public static function beat(): void
    {
        Cache::put(self::KEY, now()->timestamp, now()->addDay());
    }

    public static function lastBeat(): ?int
    {
        $value = Cache::get(self::KEY);

        return $value ? (int) $value : null;
    }

    public static function schedulerRunning(): bool
    {
        $last = self::lastBeat();

        return $last !== null && (now()->timestamp - $last) <= 180;
    }
}
