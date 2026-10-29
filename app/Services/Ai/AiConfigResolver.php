<?php

namespace App\Services\Ai;

use App\Models\AiConfiguration;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AiKeyFailed;
use App\Services\MailConfigurator;
use Illuminate\Support\Facades\Notification;

/**
 * Decides which AI key a company's request uses, and keeps track of which
 * ones are healthy.
 *
 * Order: the company's own active configurations by priority. A key that
 * recently failed is tried last (15-minute cool-down) rather than skipped
 * for good, so a temporary rate limit heals by itself. Only a company with
 * NO saved configuration falls back to the server-wide ANTHROPIC_API_KEY
 * (if one is set), so one company's keys are never spent on another's behalf.
 */
class AiConfigResolver
{
    public const COOLDOWN_MINUTES = 15;
    public const RENOTIFY_HOURS = 24;

    public function hasAny(?int $tenantId): bool
    {
        if ($tenantId && AiConfiguration::where('tenant_id', $tenantId)->where('is_active', true)->exists()) {
            return true;
        }

        return filled(config('ai.api_key'));
    }

    /** @return array<int, array{key: string, model: string, config: ?AiConfiguration}> */
    public function candidates(?int $tenantId): array
    {
        $configs = $tenantId
            ? AiConfiguration::where('tenant_id', $tenantId)->where('is_active', true)->orderBy('priority')->orderBy('id')->get()
            : collect();

        if ($configs->isEmpty()) {
            return filled(config('ai.api_key'))
                ? [['key' => (string) config('ai.api_key'), 'model' => (string) config('ai.model'), 'config' => null]]
                : [];
        }

        [$cooling, $healthy] = $configs->partition(fn (AiConfiguration $c) => $c->status === 'failing'
            && $c->failed_at && $c->failed_at->gt(now()->subMinutes(self::COOLDOWN_MINUTES)));

        return $healthy->concat($cooling)->map(fn (AiConfiguration $c) => [
            'key' => (string) $c->api_key,
            'model' => $c->model ?: (string) config('ai.model'),
            'config' => $c,
        ])->values()->all();
    }

    public function markSuccess(?AiConfiguration $config): void
    {
        $config?->forceFill(['status' => 'ok', 'last_error' => null, 'last_used_at' => now(), 'failed_at' => null, 'notified_at' => null])->save();
    }

    /** @param  bool  $stillWorking  whether another configuration remains to take over */
    public function markFailed(AiConfiguration $config, \Throwable $e, bool $stillWorking): void
    {
        $reason = AiErrors::reason($e);
        $config->forceFill(['status' => 'failing', 'last_error' => $reason, 'failed_at' => now()])->save();

        if (! $config->notified_at || $config->notified_at->lt(now()->subHours(self::RENOTIFY_HOURS))) {
            $this->alert($config, $reason, $stillWorking);
            $config->forceFill(['notified_at' => now()])->save();
        }
    }

    private function alert(AiConfiguration $config, string $reason, bool $stillWorking): void
    {
        try {
            $notification = new AiKeyFailed($config, $reason, $stillWorking);

            User::where('tenant_id', $config->tenant_id)->where('status', 'Active')->get()
                ->filter(fn (User $u) => $u->hasElevatedAccess())
                ->each(fn (User $u) => $u->notify($notification));

            if ($config->notify_email) {
                app(MailConfigurator::class)->configureFor(Tenant::find($config->tenant_id));
                Notification::route('mail', $config->notify_email)->notify($notification);
            }
        } catch (\Throwable $exception) {
            report($exception); // an alert must never break the request that triggered it
        }
    }
}
