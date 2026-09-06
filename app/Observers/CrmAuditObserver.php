<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CrmAuditObserver
{
    private const HIDDEN_FIELDS = ['password', 'remember_token', 'session_token'];

    public function created(Model $model): void
    {
        $this->record($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getRawOriginal($key);
        }

        $this->record($model, 'updated', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $model->getRawOriginal(), null);
    }

    /**
     * actor_id is a real FK to users(id) — Auth::guard('web')->id() can
     * point at a session whose user no longer exists (see
     * PlatformAuditLogger::record(), which guards the same pattern), and
     * this observer fires on every save/delete of the models it's attached
     * to. Auditing must never be able to take down the real write it's
     * recording just because its own row couldn't be inserted.
     */
    private function record(Model $model, string $event, ?array $old, ?array $new): void
    {
        $tenantId = $model->getAttribute('tenant_id');

        try {
            AuditLog::withoutEvents(fn () => AuditLog::create([
                'tenant_id' => $tenantId,
                'actor_id' => Auth::guard('web')->id(),
                'event' => $event,
                'auditable_type' => class_basename($model),
                'auditable_id' => $model->getKey(),
                'old_values' => $this->redact($old),
                'new_values' => $this->redact($new),
                'ip_address' => app()->runningInConsole() ? null : request()->ip(),
                'user_agent' => app()->runningInConsole() ? null : mb_substr((string) request()->userAgent(), 0, 500),
            ]));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function redact(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach (self::HIDDEN_FIELDS as $field) {
            if (array_key_exists($field, $values)) {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }
}
