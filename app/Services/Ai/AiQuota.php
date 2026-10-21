<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/** Per-user daily cap shared by the chat assistant and the one-click AI helpers. */
class AiQuota
{
    public static function take(User $user): bool
    {
        $key = 'ai:quota:'.$user->id.':'.now()->toDateString();
        $count = Cache::increment($key);
        if ($count === 1) {
            Cache::put($key, 1, now()->endOfDay()->addMinutes(5));
        }

        return $count <= (int) config('ai.daily_message_limit');
    }
}
